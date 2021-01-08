<?php

namespace App\Factory;

use App\Entity\FeedItem;

/**
 * Factory to create {@see FeedItem}.
 */
class FeedItemFactory {
	/**
	 * Create a {@see FeedItem} based on data.
	 * @param string[] $data
	 * @return FeedItem
	 */
	public function create(array $data): FeedItem {
		if (!isset($data['title'], $data['link'], $data['description'])) {
			throw new \InvalidArgumentException('Incompete data provided');
		}

		// Replace whitespace in URL with %20 to pass validation.
		$data['link'] = strtr($data['link'], [' ' => '%20']);

		$feedItem = new FeedItem();
		$feedItem->setTitle($data['title']);
		$feedItem->setLink($data['link']);
		$feedItem->setDescription($data['description']);
		$feedItem->generateChecksum();

		return $feedItem;
	}
}
