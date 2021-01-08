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
	 * The episode repository.
	 * @var EpisodeRepository
	 */
	private EpisodeRepository $episodeRepository;

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
	 * @param EpisodeRepository $episodeRepository
	 * @param Filesystem $filesystem
	 * @param HttpClientInterface $httpClient
	 * @param string $downloadPath
	 */
	public function __construct(
		Transmission $transmission,
		EpisodeRepository $episodeRepository,
		Filesystem $filesystem,
		HttpClientInterface $httpClient,
		string $downloadPath
	) {
		$this->transmission = $transmission;
		$this->episodeRepository = $episodeRepository;
		$this->filesystem = $filesystem;
		$this->httpClient = $httpClient;
		$this->downloadPath = $downloadPath;
	}

	/**
	 * Download an episode candidate.
	 * @param EpisodeCandidate $episodeCandidate
	 */
	public function downloadEpisodeCandidate(EpisodeCandidate $episodeCandidate): void {
		// Generate a Plex-accepted path to download the torrent to.
		$downloadPath = sprintf(
			'%s/tv-shows/%s/Season %s/',
			rtrim($this->downloadPath, '/'),
			$episodeCandidate->getShow()->getName(),
			str_pad($episodeCandidate->getSeasonNumber(), 2, 0, STR_PAD_LEFT)
		);

		// Add torrent through base64 so the torrent file doesn't have to exist on the Transmission server.
		$torrent = $this->httpClient->request('GET', $episodeCandidate->getDownloadLink())->getContent();
		$this->transmission->add(base64_encode($torrent), true, $downloadPath);

		// Store the episode.
		$episode = (new Episode())
			->setShow($episodeCandidate->getShow())
			->setDownloadLink($episodeCandidate->getDownloadLink())
			->setSeasonNumber($episodeCandidate->getSeasonNumber())
			->setEpisodeNumber($episodeCandidate->getEpisodeNumber())
			->setQuality($episodeCandidate->getQuality());

		$this->episodeRepository->save($episode);
	}
}
