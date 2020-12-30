<?php

namespace App\Client;

use App\Entity\EpisodeCandidate;
use App\Factory\EpisodeCandidateFactory;
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
	 * @var EpisodeCandidateFactory
	 */
	private EpisodeCandidateFactory $episodeCandidateFactory;

	/**
	 * PSR compliant logger.
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * Create an IPTorrents client.
	 * @param HttpClientInterface $client
	 * @param string $feedUrl
	 * @param EpisodeCandidateFactory $episodeCandidateFactory
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		HttpClientInterface $client,
		string $feedUrl,
		EpisodeCandidateFactory $episodeCandidateFactory,
		LoggerInterface $logger
	) {
		$this->client = $client;
		$this->feedUrl = $feedUrl;
		$this->episodeCandidateFactory = $episodeCandidateFactory;
		$this->logger = $logger;
	}

	/**
	 * Get a list of torrents from the feed.
	 * @return EpisodeCandidate[]
	 */
	public function getEpisodes(): array {
		try {
			$body = $this->client->request('GET', $this->feedUrl)->getContent();
		} catch (\Throwable $exception) {
			$this->logger->error('RSS feed could not be parsed', ['exception' => $exception]);
		}

		$feedItems = (new \SimpleXMLElement($body))->xpath('channel/item');
		if (empty($feedItems)) {
			$this->logger->error('No episodes found in RSS feed, please check URL');
		}

		return array_filter(array_map(
			fn ($entry): ?EpisodeCandidate => $this->episodeCandidateFactory->create((array) $entry),
			$feedItems
		));
	}
}
