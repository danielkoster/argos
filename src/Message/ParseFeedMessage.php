<?php

namespace App\Message;

use App\Entity\Feed;

/**
 * Message to parse a feed.
 */
final class ParseFeedMessage {
	/**
	 * The feed.
	 * @var Feed
	 */
	private Feed $feed;

	/**
	 * Create a message.
	 * @param Feed $feed
	 */
	public function __construct(Feed $feed) {
		$this->feed = $feed;
	}

	/**
	 * Get the feed.
	 * @return Feed
	 */
	public function getFeed(): Feed {
		return $this->feed;
	}
}
