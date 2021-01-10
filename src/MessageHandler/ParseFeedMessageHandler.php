<?php

namespace App\MessageHandler;

use App\Entity\FeedItem;
use App\Feed\FeedProcessorInterface;
use App\Feed\FeedReader;
use App\Message\ParseFeedMessage;
use App\Repository\FeedItemRepository;
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
	 * @param FeedProcessorInterface[] $feedProcessors
	 */
	public function __construct(
		FeedReader $feedReader,
		FeedItemRepository $feedItemRepository,
		array $feedProcessors
	) {
		$this->feedReader = $feedReader;
		$this->feedItemRepository = $feedItemRepository;
		$this->feedProcessors = $feedProcessors;
	}

	/**
	 * @inheritDoc
	 */
	public function __invoke(ParseFeedMessage $message) {
		// Get all items from the feed, filter stored items.
		$feedItems = array_filter(
			$this->feedReader->getFeedItems($message->getFeed()->getUrl()),
			fn (FeedItem $feedItem): bool => null === $this->feedItemRepository->findOneBy([
				'checksum' => $feedItem->getChecksum(),
			])
		);

		if (empty($feedItems)) {
			return;
		}

		// Get all relevant processors for this feed.
		$feedProcessors = array_filter(
			$this->feedProcessors,
			static fn(FeedProcessorInterface $feedProcessor): bool => in_array(
				$feedProcessor->getId(),
				$message->getFeed()->getProcessorIds()
			)
		);

		foreach ($feedItems as $feedItem) {
			foreach ($feedProcessors as $feedProcessor) {
				$this->feedItemRepository->save($feedItem);
				$feedProcessor->process($feedItem);
			}
		}
	}
}
