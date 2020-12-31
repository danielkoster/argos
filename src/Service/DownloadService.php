<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\EpisodeCandidate;
use App\Repository\EpisodeRepository;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Transmission\Transmission;

/**
 * Service to download items.
 */
class DownloadService {
	/**
	 * The episode repository.
	 * @var EpisodeRepository
	 */
	private EpisodeRepository $episodeRepository;

	/**
	 * Transmission client.
	 * @var Transmission
	 */
	private Transmission $transmission;

	/**
	 * Symfony's chatter.
	 * @var ChatterInterface
	 */
	private ChatterInterface $chatter;

	/**
	 * Create a service.
	 * @param EpisodeRepository $episodeRepository
	 * @param Transmission $transmission
	 * @param ChatterInterface $chatter
	 */
	public function __construct(
		EpisodeRepository $episodeRepository,
		Transmission $transmission,
		ChatterInterface $chatter
	) {
		$this->episodeRepository = $episodeRepository;
		$this->transmission = $transmission;
		$this->chatter = $chatter;
	}

	/**
	 * Download an episode candidate.
	 * @param EpisodeCandidate $episodeCandidate
	 */
	public function downloadEpisodeCandidate(EpisodeCandidate $episodeCandidate): void {
		// Add torrent to client.
		//$this->transmission->add();

		// Store the episode.
		$episode = (new Episode())
			->setShow($episodeCandidate->getShow())
			->setDownloadLink($episodeCandidate->getDownloadLink())
			->setSeasonNumber($episodeCandidate->getSeasonNumber())
			->setEpisodeNumber($episodeCandidate->getEpisodeNumber())
			->setQuality($episodeCandidate->getQuality());

		$this->episodeRepository->save($episode);

		$this->chatter->send(new ChatMessage(sprintf(
			"An episode has been downloaded\!\n%s",
			str_replace('-', '\\-', (string) $episode)
		)));
	}
}
