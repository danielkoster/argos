<?php

namespace App\MessageHandler;

use App\Entity\Episode;
use App\Entity\EpisodeCandidate;
use App\Message\DownloadEpisodeMessage;
use App\Repository\EpisodeCandidateRepository;
use App\Repository\EpisodeRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Transmission\Transmission;

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
	 * Transmission client.
	 * @var Transmission
	 */
	private Transmission $transmission;

	/**
	 * Create a message handler.
	 * @param EpisodeCandidateRepository $episodeCandidateRepository
	 * @param EpisodeRepository $episodeRepository
	 * @param Transmission $transmission
	 */
	public function __construct(
		EpisodeCandidateRepository $episodeCandidateRepository,
		EpisodeRepository $episodeRepository,
		Transmission $transmission
	) {
		$this->episodeCandidateRepository = $episodeCandidateRepository;
		$this->episodeRepository = $episodeRepository;
		$this->transmission = $transmission;
	}

	/**
	 * @inheritDoc
	 */
	public function __invoke(DownloadEpisodeMessage $message) {
		$episode = $this->episodeCandidateRepository->find($message->getEpisodeId());
		if (null === $episode) {
			return;
		}

		// The episode has been downloaded already.
		$downloadedEpisode = $this->episodeRepository->findSimilar($episode);
		if (null !== $downloadedEpisode) {
			return;
		}

		// Find all similar episodes.
		$episodes = $this->episodeCandidateRepository->findSimilar($episode);

		// Sort the episodes, best first.
		usort($episodes, [$this, 'compareEpisodes']);

		// Download the first episode.
		$this->downloadEpisode(reset($episodes));

		// Delete all episodes.
		array_walk(
			$episodes,
			fn(EpisodeCandidate $candidate): void => $this->episodeCandidateRepository->delete($candidate)
		);
	}

	/**
	 * Compares two episodes.
	 * @param EpisodeCandidate $right
	 * @param EpisodeCandidate $left
	 * @return int
	 */
	private function compareEpisodes(EpisodeCandidate $left, EpisodeCandidate $right): int {
		return $left->getQuality() <=> $right->getQuality();
	}

	/**
	 * Download an episode.
	 * @param EpisodeCandidate $episodeCandidate
	 */
	private function downloadEpisode(EpisodeCandidate $episodeCandidate): void {
		// Add torrent to client.
		//$this->transmission->add();

		// Store the episode.
		$episode = (new Episode())
			->setShow($episodeCandidate->getShow())
			->setDownloadLink($episodeCandidate->getDownloadLink())
			->setSeasonNumber($episodeCandidate->getSeasonNumber())
			->setEpisodeNumber($episodeCandidate->getEpisodeNumber())
			->setQuality($episodeCandidate->getQuality());

		$this->episodeRepository->save($episode);

		return;
	}
}
