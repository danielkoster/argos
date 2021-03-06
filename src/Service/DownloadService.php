<?php

namespace App\Service;

use App\Client\CorruptTorrentException;
use App\Client\TorrentClientException;
use App\Client\TransmissionClient;
use App\Entity\EpisodeCandidate;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service to download items.
 */
class DownloadService {
	/**
	 * Transmission client.
	 * @var TransmissionClient
	 */
	private TransmissionClient $transmission;

	/**
	 * Symfony's filesystem.
	 * @var Filesystem
	 */
	private Filesystem $filesystem;

	/**
	 * Symfony's HTTP client.
	 * @var HttpClientInterface
	 */
	private HttpClientInterface $httpClient;

	/**
	 * Base path to store downloaded files.
	 * @var string
	 */
	private string $downloadPath;

	/**
	 * Create a service.
	 * @param TransmissionClient $transmission
	 * @param Filesystem $filesystem
	 * @param HttpClientInterface $httpClient
	 * @param string $downloadPath
	 */
	public function __construct(
		TransmissionClient $transmission,
		Filesystem $filesystem,
		HttpClientInterface $httpClient,
		string $downloadPath
	) {
		$this->transmission = $transmission;
		$this->filesystem = $filesystem;
		$this->httpClient = $httpClient;
		$this->downloadPath = $downloadPath;
	}

	/**
	 * Download a torrent.
	 * @param string $torrent
	 * @param string $downloadPathSuffix
	 * @throws CorruptTorrentException
	 * @throws TorrentClientException
	 */
	public function download(string $torrent, string $downloadPathSuffix = 'misc'): void {
		// Download torrent and pass content so the torrent file doesn't have to exist on the client server.
		$torrent = $this->httpClient->request('GET', $torrent)->getContent();
		$downloadPath = sprintf(
			'%s/%s',
			rtrim($this->downloadPath, '/'),
			ltrim($downloadPathSuffix, '/'),
		);

		$this->transmission->add($torrent, $downloadPath);
	}

	/**
	 * Download an episode candidate.
	 * @param EpisodeCandidate $episodeCandidate
	 * @throws CorruptTorrentException
	 * @throws TorrentClientException
	 */
	public function downloadEpisodeCandidate(EpisodeCandidate $episodeCandidate): void {
		// Generate a Plex-compliant path to download the torrent to.
		$downloadPathSuffix = sprintf(
			'tv-shows/%s/Season %s/',
			$episodeCandidate->getShow()->getName(),
			str_pad($episodeCandidate->getSeasonNumber(), 2, 0, STR_PAD_LEFT)
		);

		$this->download($episodeCandidate->getDownloadLink(), $downloadPathSuffix);
	}
}
