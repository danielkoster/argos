<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\EpisodeCandidate;
use App\Repository\EpisodeRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Transmission\Transmission;

/**
 * Service to download items.
 */
class DownloadService {
	/**
	 * Transmission client.
	 * @var Transmission
	 */
	private Transmission $transmission;

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
	 * @param Transmission $transmission
	 * @param Filesystem $filesystem
	 * @param HttpClientInterface $httpClient
	 * @param string $downloadPath
	 */
	public function __construct(
		Transmission $transmission,
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
	 * @param ?string $downloadPathSuffix
	 * @param string $torrent
	 */
	public function download(string $torrent, string $downloadPathSuffix = 'misc'): void {
		// Add torrent through base64 so the torrent file doesn't have to exist on the Transmission server.
		$torrent = $this->httpClient->request('GET', $torrent);
		$downloadPath = sprintf(
			'%s/%s',
			rtrim($this->downloadPath, '/'),
			ltrim($downloadPathSuffix, '/'),
		);
		$this->transmission->add(base64_encode($torrent), true, $downloadPath);
	}

	/**
	 * Download an episode candidate.
	 * @param EpisodeCandidate $episodeCandidate
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
