<?php

namespace App\Client;

use App\Entity\FeedItem;
use App\Factory\FeedItemFactory;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * API client for IPTorrents.
 */
class IpTorrentsClient {
	/**
	 * The HTTP client to use for requests.
	 * @var HttpClientInterface
	 */
	private HttpClientInterface $client;

	/**
	 * The URL of the RSS feed.
	 * @var string
	 */
	private string $feedUrl;

	/**
	 * Factory to create episodes.
	 * @var FeedItemFactory
	 */
	private FeedItemFactory $feedItemFactory;

	/**
	 * PSR compliant logger.
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * Create an IPTorrents client.
	 * @param HttpClientInterface $client
	 * @param string $feedUrl
	 * @param FeedItemFactory $feedItemFactory
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		HttpClientInterface $client,
		string $feedUrl,
		FeedItemFactory $feedItemFactory,
		LoggerInterface $logger
	) {
		$this->client = $client;
		$this->feedUrl = $feedUrl;
		$this->feedItemFactory = $feedItemFactory;
		$this->logger = $logger;
	}

	/**
	 * Get the feed items.
	 * @return FeedItem[]
	 */
	public function getFeedItems(): array {
		return array_map(
			fn ($entry): FeedItem => $this->feedItemFactory->create((array) $entry),
			$this->getRawFeed()
		);
	}

	/**
	 * Get the raw items from the feed.
	 * @return string[]
	 */
	private function getRawFeed(): array {
		try {
			$body = $this->client->request('GET', $this->feedUrl)->getContent();
		} catch (\Throwable $exception) {
			$this->logger->error('RSS feed could not be parsed', ['exception' => $exception]);

			return [];
		}

		$feedItems = (new \SimpleXMLElement($body))->xpath('channel/item');
		if (empty($feedItems)) {
			$this->logger->error('No episodes found in RSS feed, please check URL');

			return [];
		}

		return $feedItems;
	}
}
