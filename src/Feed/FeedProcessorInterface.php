<?php

namespace App\Feed;

use App\Entity\FeedItem;

/**
 * Interface for a feed processor.
 */
interface FeedProcessorInterface {
	/**
	 * Processes a {@see FeedItem}.
	 * @param FeedItem $feedItem
	 */
	public function process(FeedItem $feedItem): void;

	/**
	 * Get the service ID.
	 * @return string
	 */
	public function getServiceId(): string;
}
