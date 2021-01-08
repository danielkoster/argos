<?php

namespace App\MessageHandler;

use App\Client\IpTorrentsClient;
use App\Entity\FeedItem;
use App\Message\ParseFeedMessage;
use App\Repository\FeedItemRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Parses feed and stores relevant episodes.
 */
final class ParseFeedMessageHandler implements MessageHandlerInterface {
	/**
	 * The IPTorrents client.
	 * @var IpTorrentsClient
	 */
	private IpTorrentsClient $client;

	/**
	 * The feed item repository.
	 * @var FeedItemRepository
	 */
	private FeedItemRepository $feedItemRepository;

	/**
	 * Create a message handler.
	 * @param IpTorrentsClient $client
	 * @param FeedItemRepository $feedItemRepository
	 */
	public function __construct(
		IpTorrentsClient $client,
		FeedItemRepository $feedItemRepository
	) {
		$this->client = $client;
		$this->feedItemRepository = $feedItemRepository;
	}

	/**
	 * @inheritDoc
	 */
	public function __invoke(ParseFeedMessage $message) {
		$feedItems = array_filter(
			$this->client->getFeedItems(),
			fn (FeedItem $feedItem): bool => null === $this->feedItemRepository->findOneBy(['checksum' => $feedItem->getChecksum()])
		);

		foreach ($feedItems as $feedItem) {
			$this->feedItemRepository->save($feedItem);
		}
	}
}
