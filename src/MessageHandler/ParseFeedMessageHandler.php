<?php

namespace App\MessageHandler;

use App\Entity\FeedItem;
use App\Feed\FeedProcessorInterface;
use App\Feed\FeedReader;
use App\Message\ParseFeedMessage;
use App\Repository\FeedItemRepository;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Parses feed and stores relevant episodes.
 */
final class ParseFeedMessageHandler implements MessageHandlerInterface {
	/**
	 * The feed reader.
	 * @var FeedReader
	 */
	private FeedReader $feedReader;

	/**
	 * The feed item repository.
	 * @var FeedItemRepository
	 */
	private FeedItemRepository $feedItemRepository;

	/**
	 * All feed processors.
	 * @var FeedProcessorInterface[]
	 */
	private array $feedProcessors;

	/**
	 * Create a message handler.
	 * @param FeedReader $feedReader
	 * @param FeedItemRepository $feedItemRepository
	 * @param ServiceLocator $feedProcessorsLocator
	 */
	public function __construct(
		FeedReader $feedReader,
		FeedItemRepository $feedItemRepository,
		ServiceLocator $feedProcessorsLocator
	) {
		$this->feedReader = $feedReader;
		$this->feedItemRepository = $feedItemRepository;
		$this->feedProcessors = array_map(
			fn(string $id): FeedProcessorInterface => $feedProcessorsLocator->get($id),
			$feedProcessorsLocator->getProvidedServices()
		);
	}

	/**
	 * @inheritDoc
	 */
	public function __invoke(ParseFeedMessage $message) {
		// Get all relevant processors for this feed.
		$feedProcessors = array_filter(
			$this->feedProcessors,
			static fn(FeedProcessorInterface $feedProcessor): bool => in_array(
				$feedProcessor->getId(),
				$message->getFeed()->getProcessorIds()
			)
		);

		foreach ($this->feedReader->getFeedItems($message->getFeed()->getUrl()) as $feedItem) {
			// Skip if the feed item is already stored.
			if ($this->feedItemRepository->findOneBy(['checksum' => $feedItem->getChecksum()])) {
				continue;
			}

			$this->feedItemRepository->save($feedItem);

			foreach ($feedProcessors as $feedProcessor) {
				$feedProcessor->process($feedItem);
			}
		}
	}
}
