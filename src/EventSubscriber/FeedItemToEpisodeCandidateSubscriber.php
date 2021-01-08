<?php

namespace App\EventSubscriber;

use App\Entity\EpisodeCandidate;
use App\Entity\FeedItem;
use App\Factory\EpisodeCandidateFactory;
use App\Repository\EpisodeCandidateRepository;
use App\Repository\EpisodeRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;

/**
 * Subscriber which checks if an {@see EpisodeCandidate} needs to be created when a {@see FeedItem} is created.
 */
class FeedItemToEpisodeCandidateSubscriber implements EventSubscriber {
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
	 * Create an event subscriber.
	 * @param EpisodeCandidateFactory $episodeCandidateFactory
	 * @param EpisodeCandidateRepository $episodeCandidateRepository
	 * @param EpisodeRepository $episodeRepository
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		EpisodeCandidateFactory $episodeCandidateFactory,
		EpisodeCandidateRepository $episodeCandidateRepository,
		EpisodeRepository $episodeRepository,
		LoggerInterface $logger
	) {
		$this->episodeCandidateFactory = $episodeCandidateFactory;
		$this->episodeCandidateRepository = $episodeCandidateRepository;
		$this->episodeRepository = $episodeRepository;
		$this->logger = $logger;
	}

	/**
	 * @inheritDoc
	 */
	public function getSubscribedEvents(): array {
		return [
			Events::postPersist,
		];
	}

	/**
	 * Triggered after an entity is persisted the first time.
	 * @param FeedItem $feedItem
	 * @param LifecycleEventArgs $args The arguments.
	 */
	public function postPersist(LifecycleEventArgs $args): void {
		$feedItem = $args->getObject();
		if (!$feedItem instanceof FeedItem) {
			return;
		}

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
		$tvShow = $episode->getTvShow();

		// Filter if the quality is too low.
		if ($episode->getQuality() < $tvShow->getMinimumQuality()) {
			return false;
		}

		// Filter if the episode is from a too old season.
		if ($episode->getSeasonNumber() < $tvShow->getFollowFromSeason()) {
			return false;
		}

		// Filter if the episode if from the correct season, but too old.
		if (
			$episode->getSeasonNumber() === $tvShow->getFollowFromSeason()
			&& $episode->getEpisodeNumber() < $tvShow->getFollowFromEpisode()
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
