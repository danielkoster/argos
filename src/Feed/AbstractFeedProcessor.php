<?php

namespace App\Feed;

/**
 * Base implementation of a feed processor.
 */
abstract class AbstractFeedProcessor implements FeedProcessorInterface {
	/**
	 * The Symfony service ID.
	 * @var string
	 */
	private string $serviceId;

	/**
	 * Create a feed processor.
	 * @param string $serviceId
	 */
	public function __construct(string $serviceId) {
		$this->serviceId = $serviceId;
	}

	/**
	 * @inheritDoc
	 */
	public function getServiceId(): string {
		return $this->serviceId;
	}
}
