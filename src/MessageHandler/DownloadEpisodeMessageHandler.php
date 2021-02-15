<?php

namespace App\MessageHandler;

use App\Client\CorruptTorrentException;
use App\Client\TorrentClientException;
use App\Entity\Episode;
use App\Entity\EpisodeCandidate;
use App\Message\DownloadEpisodeMessage;
use App\Repository\EpisodeCandidateRepository;
use App\Repository\EpisodeRepository;
use App\Service\DownloadService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Transmission\Exception\ClientException;

/**
 * Downloads an episode.
 */
final class DownloadEpisodeMessageHandler implements MessageHandlerInterface {
	/**
	 * The episode candidate repository.
	 * @var EpisodeCandidateRepository
	 */
	private EpisodeCandidateRepository $episodeCandidateRepository;

	/**
	 * The episode repository.
	 * @var EpisodeRepository
	 */
	private EpisodeRepository $episodeRepository;

	/**
	 * The download service.
	 * @var DownloadService
	 */
	private DownloadService $downloadService;

	/**
	 * PSR logger.
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * Names of favoured uploaders.
	 * @var string[]
	 */
	private array $favouredUploaders;

	/**
	 * Names of unfavoured uploaders.
	 * @var string[]
	 */
	private array $unfavouredUploaders;

	/**
	 * Create a message handler.
	 * @param EpisodeCandidateRepository $episodeCandidateRepository
	 * @param EpisodeRepository $episodeRepository
	 * @param DownloadService $downloadService
	 * @param LoggerInterface $logger
	 * @param string[] $favouredUploaders
	 * @param string[] $unfavouredUploaders
	 */
	public function __construct(
		EpisodeCandidateRepository $episodeCandidateRepository,
		EpisodeRepository $episodeRepository,
		DownloadService $downloadService,
		LoggerInterface $logger,
		array $favouredUploaders,
		array $unfavouredUploaders
	) {
		$this->episodeCandidateRepository = $episodeCandidateRepository;
		$this->episodeRepository = $episodeRepository;
		$this->favouredUploaders = $favouredUploaders;
		$this->downloadService = $downloadService;
		$this->unfavouredUploaders = $unfavouredUploaders;
		$this->logger = $logger;
	}

	/**
	 * @inheritDoc
	 */
	public function __invoke(DownloadEpisodeMessage $message) {
		// The candidate is already deleted.
		$episodeCandidate = $this->episodeCandidateRepository->find($message->getEpisodeId());
		if (null === $episodeCandidate) {
			$this->logger->info(
				'Episode candidate {id} is already deleted',
				['id' => $message->getEpisodeId()]
			);

			return;
		}

		// The episode has been downloaded already.
		$downloadedEpisode = $this->episodeRepository->findSimilar($episodeCandidate);
		if (!empty($downloadedEpisode)) {
			$this->episodeCandidateRepository->delete($episodeCandidate);
			$this->logger->info(
				'Episode "{episode}" has been downloaded already',
				['episode' => $episodeCandidate]
			);

			return;
		}

		// Find all similar candidates.
		$episodeCandidates = $this->episodeCandidateRepository->findSimilar($episodeCandidate);

		// Sort the candidates, best first.
		usort(
			$episodeCandidates,
			fn(EpisodeCandidate $left, EpisodeCandidate $right): int => $this->compareEpisodes($left, $right)
		);

		$episodeCandidates = array_reverse($episodeCandidates);

		// Download the first episode which can be downloaded.
		$episodeCandidate = $this->downloadFirstPossibleEpisodeCandidate($episodeCandidates);
		if (null === $episodeCandidate) {
			$this->logger->info(
				'No candidate for "{episode}" could be downloaded',
				['episode' => $episodeCandidate]
			);

			return;
		}

		// Store the episode.
		$episode = (new Episode())
			->setShow($episodeCandidate->getShow())
			->setDownloadLink($episodeCandidate->getDownloadLink())
			->setSeasonNumber($episodeCandidate->getSeasonNumber())
			->setEpisodeNumber($episodeCandidate->getEpisodeNumber())
			->setQuality($episodeCandidate->getQuality())
			->setIsProper($episodeCandidate->getIsProper());

		$this->episodeRepository->save($episode);

		// Delete all episodes.
		foreach ($episodeCandidates as $episodeCandidate) {
			$this->episodeCandidateRepository->delete($episodeCandidate);
		}
	}

	/**
	 * Tries to download an episode candidate, skips corrupt torrents.
	 * @param EpisodeCandidate[] $episodeCandidates
	 * @return EpisodeCandidate|null
	 * @throws TorrentClientException
	 */
	private function downloadFirstPossibleEpisodeCandidate(array $episodeCandidates): ?EpisodeCandidate {
		while (!empty($episodeCandidates)) {
			$episodeCandidate = array_shift($episodeCandidates);

			try {
				$this->downloadService->downloadEpisodeCandidate($episodeCandidate);

				return $episodeCandidate;
			} catch (CorruptTorrentException $exception) {
				// Delete the corrupted episode candidate and try the next one.
				$this->episodeCandidateRepository->delete($episodeCandidate);

				continue;
			}
		}

		return null;
	}

	/**
	 * Compares two episodes.
	 * @param EpisodeCandidate $right
	 * @param EpisodeCandidate $left
	 * @return int
	 */
	private function compareEpisodes(EpisodeCandidate $left, EpisodeCandidate $right): int {
		// Proper is better.
		if ($left->getIsProper() !== $right->getIsProper()) {
			return $left->getIsProper() <=> $right->getIsProper();
		}

		// Higher quality is better.
		if ($left->getQuality() !== $right->getQuality()) {
			return $left->getQuality() <=> $right->getQuality();
		}

		// Favoured uploaders are better.
		$left_favoured = $this->isUploaderInList($left, $this->favouredUploaders);
		$right_favoured = $this->isUploaderInList($right, $this->favouredUploaders);
		if ($left_favoured !== $right_favoured) {
			return $left_favoured <=> $right_favoured;
		}

		// Unfavoured uploaders are worse.
		$left_unfavoured = $this->isUploaderInList($left, $this->unfavouredUploaders);
		$right_unfavoured = $this->isUploaderInList($right, $this->unfavouredUploaders);
		if ($left_unfavoured !== $right_unfavoured) {
			// Left and right are reversed to sort unfavoured lower.
			return $right_unfavoured <=> $left_unfavoured;
		}

		return 0;
	}

	/**
	 * Whether an episode candidate's uploader is in a list of uploaders.
	 * @param string[] $uploaders
	 * @param EpisodeCandidate $candidate
	 * @return bool
	 */
	private function isUploaderInList(EpisodeCandidate $candidate, array $uploaders): bool {
		foreach ($uploaders as $uploader) {
			if (stripos($candidate->getDownloadLink(), $uploader) !== false) {
				return true;
			}
		}

		return false;
	}
}
