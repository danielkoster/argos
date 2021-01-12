<?php

namespace App\Client;

use Transmission\Transmission;

/**
 * Transmission client.
 */
class TransmissionClient implements TorrentClientInterface {
	/**
	 * Transmission.
	 * @var Transmission
	 */
	private Transmission $transmission;

	/**
	 * Create a torrent client.
	 * @param string $host
	 * @param string $port
	 * @param string $path
	 * @param string $user
	 * @param string $pass
	 */
	public function __construct(
		string $host,
		string $port,
		string $path,
		string $user,
		string $pass
	) {
		$this->transmission = new Transmission($host, $port, '/' . $path);
		$this->transmission->getClient()->authenticate($user, $pass);
	}

	/**
	 * @inheritDoc
	 */
	public function add(string $torrent, string $downloadPath): void {
		try {
			$this->transmission->add(base64_encode($torrent), true, $downloadPath);
		} catch (\Throwable $exception) {
			throw new TorrentClientException($exception->getMessage(), $exception->getCode(), $exception);
		}
	}
}