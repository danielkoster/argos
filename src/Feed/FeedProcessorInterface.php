<?php

namespace App\Feed;

use App\Entity\FeedItem;

/**
 * Interface for a feed processor.
 */
interface FeedProcessorInterface {
	/**
	 * Download all strategy.
	 */
	public const STRATEGY_DOWNLOAD_ALL = 'download_all';

	/**
	 * Download wanted episodes.
	 */
	public const STRATEGY_WANTED_EPISODES = 'wanted_episodes';

	/**
	 * All download strategies.
	 */
	public const STRATEGY_OPTIONS = [
		self::STRATEGY_DOWNLOAD_ALL,
		self::STRATEGY_WANTED_EPISODES,
	];

	/**
	 * Processes a {@see FeedItem}.
	 * @param FeedItem $feedItem
	 */
	public function process(FeedItem $feedItem): void;

	/**
	 * Get the service ID.
	 * @return string
	 */
	public function getId(): string;
}
