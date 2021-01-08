<?php

namespace App\EventListener;

use App\Entity\EpisodeCandidate;
use App\Message\DownloadEpisodeMessage;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * Listener which dispatches a message to download saved {@see EpisodeCandidate}.
 */
class DownloadEpisodeCandidate {
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
			[new DelayStamp($episodeCandidate->getShow()->getHighQualityWaitingTime() * 60 * 1000)]
		);
	}
}
