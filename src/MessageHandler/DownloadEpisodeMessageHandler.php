<?php

namespace App\MessageHandler;

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
	 * Names of preferred uploaders.
	 * @var string[]
	 */
	private array $preferredUploaders;

	/**
	 * Create a message handler.
	 * @param EpisodeCandidateRepository $episodeCandidateRepository
	 * @param EpisodeRepository $episodeRepository
	 * @param DownloadService $downloadService
	 * @param string[] $preferredUploaders
	 */
	public function __construct(
		EpisodeCandidateRepository $episodeCandidateRepository,
		EpisodeRepository $episodeRepository,
		DownloadService $downloadService,
		array $preferredUploaders
	) {
		$this->episodeCandidateRepository = $episodeCandidateRepository;
		$this->episodeRepository = $episodeRepository;
		$this->preferredUploaders = $preferredUploaders;
		$this->downloadService = $downloadService;
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

		// Find all similar episodes.
		$episodes = $this->episodeCandidateRepository->findSimilar($episode);

		// Sort the episodes, best first.
		usort($episodes, [$this, 'compareEpisodes']);
		$episodes = array_reverse($episodes);

		// Download the first episode.
		$this->downloadService->downloadEpisodeCandidate(reset($episodes));

		// Delete all episodes.
		array_walk(
			$episodes,
			fn(EpisodeCandidate $candidate) => $this->episodeCandidateRepository->delete($candidate)
		);
	}

	/**
	 * Compares two episodes.
	 * @param EpisodeCandidate $right
	 * @param EpisodeCandidate $left
	 * @return int
	 */
	private function compareEpisodes(EpisodeCandidate $left, EpisodeCandidate $right): int {
		// Better quality always comes first.
		if ($left->getQuality() !== $right->getQuality()) {
			return $left->getQuality() <=> $right->getQuality();
		}

		return $this->hasPreferredUploader($left) <=> $this->hasPreferredUploader($right);
	}

	/**
	 * Whether an episode is from a preferred uploader.
	 * @param EpisodeCandidate $candidate
	 * @return bool
	 */
	private function hasPreferredUploader(EpisodeCandidate $candidate): bool {
		foreach ($this->preferredUploaders as $uploader) {
			if (stripos($candidate->getDownloadLink(), $uploader) !== false) {
				return true;
			}
		}

		return false;
	}
}
