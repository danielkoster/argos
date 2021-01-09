<?php

namespace App\Feed;

use App\Entity\FeedItem;
use App\Factory\FeedItemFactory;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Parser for a feed.
 */
class FeedReader {
	/**
	 * The HTTP client to use for requests.
	 * @var HttpClientInterface
	 */
	private HttpClientInterface $client;

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
	 * @param FeedItemFactory $feedItemFactory
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		HttpClientInterface $client,
		FeedItemFactory $feedItemFactory,
		LoggerInterface $logger
	) {
		$this->client = $client;
		$this->feedItemFactory = $feedItemFactory;
		$this->logger = $logger;
	}

	/**
	 * Get feed items from a feed.
	 * @param string $url
	 * @return FeedItem[]
	 */
	public function getFeedItems(string $url): array {
		return array_map(
			fn (\SimpleXMLElement $data): FeedItem => $this->feedItemFactory->create((array) $data),
			$this->getFeedData($url)
		);
	}

	/**
	 * Get data from a feed url.
	 * @param string $url
	 * @return \SimpleXMLElement[]
	 */
	private function getFeedData(string $url): array {
		try {
			$body = $this->client->request('GET', $url)->getContent();
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
