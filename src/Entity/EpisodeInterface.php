<?php

namespace App\Entity;

/**
 * Interface for an episode.
 */
interface EpisodeInterface {
	/**
	 * Get the {@see TvShow} this episode belongs to.
	 * @return TvShow
	 */
	public function getTvShow(): TvShow;

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
