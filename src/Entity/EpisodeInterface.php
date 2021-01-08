<?php

namespace App\Entity;

/**
 * Interface for an episode.
 */
interface EpisodeInterface {
	/**
	 * Get the {@see Show} this episode belongs to.
	 * @return Show
	 */
	public function getShow(): Show;

	/**
	 * Get the URL to download this episode.
	 * @return string
	 */
	public function getDownloadLink(): string;

	/**
	 * Get the season number.
	 * @return int
	 */
	public function getSeasonNumber(): int;

	/**
	 * Get the episode number.
	 * @return int
	 */
	public function getEpisodeNumber(): int;

	/**
	 * Get the quality.
	 * @return int
	 */
	public function getQuality(): int;
}
