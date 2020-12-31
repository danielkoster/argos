<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\EpisodeCandidate;
use App\Entity\EpisodeInterface;
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
		// Add torrent to client.
		$this->downloadTorrentToPath(
			$episodeCandidate->getDownloadLink(),
			$this->generatePath($episodeCandidate)
		);

		// Store the episode.
		$episode = (new Episode())
			->setShow($episodeCandidate->getShow())
			->setDownloadLink($episodeCandidate->getDownloadLink())
			->setSeasonNumber($episodeCandidate->getSeasonNumber())
			->setEpisodeNumber($episodeCandidate->getEpisodeNumber())
			->setQuality($episodeCandidate->getQuality());

		$this->episodeRepository->save($episode);
	}

	/**
	 * Downloads a torrent to a path.
	 * @param string $path
	 * @param string $torrent
	 */
	private function downloadTorrentToPath(string $torrent, string $path): void {
		// Download external torrent to a temporary location.
		if (strpos($torrent, 'http') === 0) {
			$torrent = $this->filesystem->tempnam(sys_get_temp_dir(), 'argos_', '.torrent');
			$this->filesystem->dumpFile(
				$torrent,
				$this->httpClient->request('GET', $torrent)->getContent()
			);
		}

		$this->transmission->add($torrent, $path);
	}

	/**
	 * Generates path to download episode to.
	 * @param EpisodeInterface $episode
	 * @return string
	 */
	private function generatePath(EpisodeInterface $episode): string {
		return sprintf(
			'%s/tv-shows/Season %s/',
			$this->downloadPath,
			str_pad($episode->getSeasonNumber(), 2, 0, STR_PAD_LEFT)
		);
	}
}
