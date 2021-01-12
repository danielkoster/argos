<?php

namespace App\MessageHandler;

use App\Entity\Episode;
use App\Entity\EpisodeCandidate;
use App\Message\DownloadEpisodeMessage;
use App\Repository\EpisodeCandidateRepository;
use App\Repository\EpisodeRepository;
use App\Service\DownloadService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

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
	 * @param string[] $favouredUploaders
	 * @param string[] $unfavouredUploaders
	 */
	public function __construct(
		EpisodeCandidateRepository $episodeCandidateRepository,
		EpisodeRepository $episodeRepository,
		DownloadService $downloadService,
		array $favouredUploaders,
		array $unfavouredUploaders
	) {
		$this->episodeCandidateRepository = $episodeCandidateRepository;
		$this->episodeRepository = $episodeRepository;
		$this->favouredUploaders = $favouredUploaders;
		$this->downloadService = $downloadService;
		$this->unfavouredUploaders = $unfavouredUploaders;
	}

	/**
	 * @inheritDoc
	 */
	public function __invoke(DownloadEpisodeMessage $message) {
		// The candidate is already deleted.
		$episode = $this->episodeCandidateRepository->find($message->getEpisodeId());
		if (null === $episode) {
			return;
		}

		// The episode has been downloaded already.
		$downloadedEpisode = $this->episodeRepository->findSimilar($episode);
		if (!empty($downloadedEpisode)) {
			$this->episodeCandidateRepository->delete($episode);

			return;
		}

		// Find all similar candidates.
		$episodeCandidates = $this->episodeCandidateRepository->findSimilar($episode);

		// Sort the candidates, best first.
		usort(
			$episodeCandidates,
			fn(EpisodeCandidate $left, EpisodeCandidate $right): int => $this->compareEpisodes($left, $right)
		);

		$episodeCandidates = array_reverse($episodeCandidates);

		// Download the first episode.
		$episodeCandidate = reset($episodeCandidates);
		$this->downloadService->downloadEpisodeCandidate($episodeCandidate);

		// Store the episode.
		$episode = (new Episode())
			->setShow($episodeCandidate->getShow())
			->setDownloadLink($episodeCandidate->getDownloadLink())
			->setSeasonNumber($episodeCandidate->getSeasonNumber())
			->setEpisodeNumber($episodeCandidate->getEpisodeNumber())
			->setQuality($episodeCandidate->getQuality());

		$this->episodeRepository->save($episode);

		// Delete all episodes.
		foreach ($episodeCandidates as $episodeCandidate) {
			$this->episodeCandidateRepository->delete($episodeCandidate);
		}
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
