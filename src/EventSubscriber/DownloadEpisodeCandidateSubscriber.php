<?php

namespace App\EventSubscriber;

use App\Entity\EpisodeCandidate;
use App\Message\DownloadEpisodeMessage;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * Subscriber which dispatches a message to download saved {@see EpisodeCandidate}.
 */
class DownloadEpisodeCandidateSubscriber implements EventSubscriber {
	/**
	 * Symfony's message bus.
	 * @var MessageBusInterface
	 */
	private MessageBusInterface $bus;

	/**
	 * Create an event subscriber.
	 * @param MessageBusInterface $bus
	 */
	public function __construct(MessageBusInterface $bus) {
		$this->bus = $bus;
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
	 * Triggered when an entity is persisted the first time.
	 * @param LifecycleEventArgs $args The arguments.
	 */
	public function postPersist(LifecycleEventArgs $args): void {
		$episodeCandidate = $args->getObject();
		if (!$episodeCandidate instanceof EpisodeCandidate) {
			return;
		}

		// Dispatch message to download the episode.
		$this->bus->dispatch(
			new DownloadEpisodeMessage($episodeCandidate),
			[new DelayStamp($episodeCandidate->getTvShow()->getHighQualityWaitingTime() * 60 * 1000)]
		);
	}
}
