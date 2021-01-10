<?php

namespace App\Feed;

use App\Entity\FeedItem;
use App\Service\DownloadService;

/**
 * Feed processor which downloads all items.
 */
class DownloadAllFeedProcessor implements FeedProcessorInterface {
	/**
	 * The download service.
	 * @var DownloadService
	 */
	private DownloadService $downloadService;

	/**
	 * Create a feed processor.
	 * @param DownloadService $downloadService
	 */
	public function __construct(DownloadService $downloadService) {
		$this->downloadService = $downloadService;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return FeedProcessorInterface::STRATEGY_DOWNLOAD_ALL;
	}

	/**
	 * @inheritDoc
	 */
	public function process(FeedItem $feedItem): void {
		$this->downloadService->download($feedItem->getLink());
	}
}
