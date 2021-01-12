<?php

namespace App\Client;

/**
 * Interface for a torrent client.
 */
interface TorrentClientInterface {
	/**
	 * Add a torrent to the client.
	 * Torrent contents must be provided, not a path.
	 * @param string $torrent
	 * @param string $downloadPath
	 * @throws TorrentClientException
	 */
	public function add(string $torrent, string $downloadPath): void;
}