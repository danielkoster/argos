<?php

namespace App\Feed;

use App\Entity\EpisodeCandidate;
use App\Entity\FeedItem;
use App\Factory\EpisodeCandidateFactory;
use App\Repository\EpisodeCandidateRepository;
use App\Repository\EpisodeRepository;
use Psr\Log\LoggerInterface;

/**
 * Feed processor which downloads wanted episodes.
 */
class WantedEpisodeFeedProcessor extends AbstractFeedProcessor {
	/**
	 * The episode candidate factory.
	 * @var EpisodeCandidateFactory
	 */
	private EpisodeCandidateFactory $episodeCandidateFactory;

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
	 * PSR logger.
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * @inheritDoc
	 * @param EpisodeCandidateFactory $episodeCandidateFactory
	 * @param EpisodeCandidateRepository $episodeCandidateRepository
	 * @param EpisodeRepository $episodeRepository
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		string $serviceId,
		EpisodeCandidateFactory $episodeCandidateFactory,
		EpisodeCandidateRepository $episodeCandidateRepository,
		EpisodeRepository $episodeRepository,
		LoggerInterface $logger
	) {
		parent::__construct($serviceId);

		$this->episodeCandidateFactory = $episodeCandidateFactory;
		$this->episodeCandidateRepository = $episodeCandidateRepository;
		$this->episodeRepository = $episodeRepository;
		$this->logger = $logger;
	}

	/**
	 * @inheritDoc
	 */
	public function process(FeedItem $feedItem): void {
		$episodeCandidate = $this->episodeCandidateFactory->createFromFeedItem($feedItem);
		if (!$episodeCandidate instanceof EpisodeCandidate || false === $this->isRelevant($episodeCandidate)) {
			return;
		}

		$this->episodeCandidateRepository->save($episodeCandidate);
		$this->logger->info('Saved episode candidate "{candidate}"', ['candidate' => $episodeCandidate]);
	}

	/**
	 * Determine if an episode should be filtered.
	 * @param EpisodeCandidate $episode
	 * @return bool
	 */
	private function isRelevant(EpisodeCandidate $episode): bool {
		$show = $episode->getShow();

		// Filter if the quality is too low.
		if ($episode->getQuality() < $show->getMinimumQuality()) {
			return false;
		}

		// Filter if the episode is from a too old season.
		if ($episode->getSeasonNumber() < $show->getFollowFromSeason()) {
			return false;
		}

		// Filter if the episode if from the correct season, but too old.
		if (
			$episode->getSeasonNumber() === $show->getFollowFromSeason()
			&& $episode->getEpisodeNumber() < $show->getFollowFromEpisode()
		) {
			return false;
		}

		// Filter if this candidate has been stored already.
		if ($this->isEpisodeCandidateStored($episode)) {
			return false;
		}

		// Filter if the episode has been downloaded already.
		if ($this->episodeRepository->findSimilar($episode)) {
			return false;
		}

		return true;
	}

	/**
	 * Check if an episode candidate has been stored already.
	 * @param EpisodeCandidate $episode
	 * @return bool
	 */
	private function isEpisodeCandidateStored(EpisodeCandidate $episode): bool {
		return null !== $this->episodeCandidateRepository->findOneBy([
			'downloadLink' => $episode->getDownloadLink(),
		]);
	}
}
