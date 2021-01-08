<?php

namespace App\MessageHandler;

use App\Component\FeedParser;
use App\Entity\FeedItem;
use App\Message\ParseFeedMessage;
use App\Repository\FeedItemRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Parses feed and stores relevant episodes.
 */
final class ParseFeedMessageHandler implements MessageHandlerInterface {
	/**
	 * The feed parser.
	 * @var FeedParser
	 */
	private FeedParser $feedParser;

	/**
	 * The feed item repository.
	 * @var FeedItemRepository
	 */
	private FeedItemRepository $feedItemRepository;

	/**
	 * Create a message handler.
	 * @param FeedParser $feedParser
	 * @param FeedItemRepository $feedItemRepository
	 */
	public function __construct(
		FeedParser $feedParser,
		FeedItemRepository $feedItemRepository
	) {
		$this->feedParser = $feedParser;
		$this->feedItemRepository = $feedItemRepository;
	}

	/**
	 * @inheritDoc
	 */
	public function __invoke(ParseFeedMessage $message) {
		// Get all items from the feed, filter stored items.
		$feedItems = array_filter(
			$this->feedParser->getFeedItems($message->getFeed()->getUrl()),
			fn (FeedItem $feedItem): bool => null === $this->feedItemRepository->findOneBy([
				'checksum' => $feedItem->getChecksum(),
			])
		);

		foreach ($feedItems as $feedItem) {
			$this->feedItemRepository->save($feedItem);
		}
	}
}
