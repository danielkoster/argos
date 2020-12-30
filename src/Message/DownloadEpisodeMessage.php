<?php

namespace App\Message;

use App\Entity\EpisodeCandidate;

/**
 * Message to download an episode.
 */
final class DownloadEpisodeMessage {
	/**
	 * The episode ID.
	 * @var int
	 */
	private int $episodeId;

	/**
	 * Create a message.
	 * @param EpisodeCandidate $episode
	 */
	public function __construct(EpisodeCandidate $episode) {
		$this->episodeId = $episode->getId();
	}

	/**
	 * Get the episode ID.
	 * @return int
	 */
	public function getEpisodeId(): int {
		return $this->episodeId;
	}
}
