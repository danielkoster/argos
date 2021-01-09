<?php

namespace App\Feed;

use App\Entity\FeedItem;
use App\Service\DownloadService;

/**
 * Feed processor which downloads all items.
 */
class DownloadAllFeedProcessor extends AbstractFeedProcessor {
	/**
	 * The download service.
	 * @var DownloadService
	 */
	private DownloadService $downloadService;

	/**
	 * @inheritDoc
	 * @param DownloadService $downloadService
	 */
	public function __construct(string $serviceId, DownloadService $downloadService) {
		parent::__construct($serviceId);

		$this->downloadService = $downloadService;
	}

	/**
	 * @inheritDoc
	 * @param FeedItem $feedItem
	 */
	public function process(FeedItem $feedItem): void {
		$this->downloadService->download($feedItem->getLink());
	}
}
