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
	 * @param string $scheme
	 * @param string $host
	 * @param string $port
	 * @param string $path
	 * @param string $user
	 * @param string $pass
	 */
	public function __construct(
		string $scheme,
		string $host,
		string $port,
		string $path,
		string $user,
		string $pass
	) {
		$this->transmission = new Transmission($host, $port, '/' . $path);
		$this->transmission->getClient()->setScheme($scheme);
		$this->transmission->getClient()->authenticate($user, $pass);
	}

	/**
	 * @inheritDoc
	 */
	public function add(string $torrent, string $downloadPath): void {
		try {
			$this->transmission->add(base64_encode($torrent), true, $downloadPath);
		} catch (\Throwable $exception) {
			if ($exception->getMessage() === 'invalid or corrupt torrent file') {
				throw new CorruptTorrentException($exception->getMessage(), $exception->getCode(), $exception);
			}

			throw new TorrentClientException($exception->getMessage(), $exception->getCode(), $exception);
		}
	}
}