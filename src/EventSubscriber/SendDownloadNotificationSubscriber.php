<?php

namespace App\EventSubscriber;

use App\Entity\Episode;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;

/**
 * Sends notifications about downloaded episodes.
 */
class SendDownloadNotificationSubscriber implements EventSubscriber {
	/**
	 * Symfony's notifier.
	 * @var NotifierInterface
	 */
	private NotifierInterface $notifier;

	/**
	 * Create an event subscriber.
	 * @param NotifierInterface $notifier
	 */
	public function __construct(NotifierInterface $notifier) {
		$this->notifier = $notifier;
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
		$episode = $args->getObject();
		if (!$episode instanceof Episode) {
			return;
		}

		$this->notifier->send(new Notification(sprintf(
			"An episode has been downloaded\!\n%s",
			str_replace('-', '\\-', (string) $episode)
		)));
	}
}
