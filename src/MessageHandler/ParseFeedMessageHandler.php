<?php

namespace App\MessageHandler;

use App\Client\IpTorrentsClient;
use App\Entity\Episode;
use App\Message\DownloadEpisodeMessage;
use App\Message\ParseFeedMessage;
use App\Repository\EpisodeCandidateRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * Parses feed and stores relevant episodes.
 */
final class ParseFeedMessageHandler implements MessageHandlerInterface {
	/**
	 * The IPTorrents client.
	 * @var IpTorrentsClient
	 */
	private IpTorrentsClient $client;

	/**
	 * The episode episodeCandidateRepository.
	 * @var EpisodeCandidateRepository
	 */
	private EpisodeCandidateRepository $episodeCandidateRepository;

	/**
	 * PSR compliant logger.
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * Symfony's message bus.
	 * @var MessageBusInterface
	 */
	private MessageBusInterface $bus;

	/**
	 * Create a message handler.
	 * @param IpTorrentsClient $client
	 * @param EpisodeCandidateRepository $episodeCandidateRepository
	 * @param LoggerInterface $logger
	 * @param MessageBusInterface $bus
	 */
	public function __construct(
		IpTorrentsClient $client,
		EpisodeCandidateRepository $episodeCandidateRepository,
		LoggerInterface $logger,
		MessageBusInterface $bus
	) {
		$this->client = $client;
		$this->episodeCandidateRepository = $episodeCandidateRepository;
		$this->logger = $logger;
		$this->bus = $bus;
	}

	/**
	 * @inheritDoc
	 */
	public function __invoke(ParseFeedMessage $message) {
		$episodes = array_filter($this->client->getEpisodes(), [$this, 'filterEpisodes']);
		foreach ($episodes as $episode) {
			$this->logger->info('Saved episode candidate "{episode}"', ['episode' => $episode]);
			$this->episodeCandidateRepository->save($episode);

			// Dispatch message to download the episode.
			$this->bus->dispatch(
				new DownloadEpisodeMessage($episode),
				[new DelayStamp($episode->getShow()->getHighQualityWaitingTime() * 60 * 1000)]
			);
		}
	}

	/**
	 * Determine if an episode should be filtered.
	 * @param Episode $episode
	 * @return bool
	 */
	private function filterEpisodes(Episode $episode): bool {
		$show = $episode->getShow();

		// Filter if the quality is too low.
		if ($episode->getQuality() < $show->getMinimumQuality()) {
			return false;
		}

		// Filter if the episode is from a too old season.
		if ($episode->getSeasonNumber() < $show->getFollowFromSeason()) {
			return false;
		}

		// Filter if the episode if from the correct season, but too old.
		if (
			$episode->getSeasonNumber() === $show->getFollowFromSeason()
			&& $episode->getEpisodeNumber() < $show->getFollowFromEpisode()
		) {
			return false;
		}

		// Filter if the episode has been downloaded already.
		if ($this->isEpisodeDownloaded($episode)) {
			return false;
		}

		return true;
	}

	/**
	 * Check if a episode has been downloaded already.
	 * @param Episode $episode
	 * @return bool
	 */
	private function isEpisodeDownloaded(Episode $episode): bool {
		return (bool) $this->episodeCandidateRepository->findBy([
			'show' => $episode->getShow(),
			'seasonNumber' => $episode->getSeasonNumber(),
			'episodeNumber' => $episode->getEpisodeNumber(),
		]);
	}
}
