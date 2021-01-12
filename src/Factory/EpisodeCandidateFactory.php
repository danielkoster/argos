<?php

namespace App\Factory;

use App\Entity\EpisodeCandidate;
use App\Entity\FeedItem;
use App\Repository\ShowRepository;

/**
 * Factory to create {@see EpisodeCandidate}.
 */
class EpisodeCandidateFactory {
	/**
	 * The show episodeCandidateRepository.
	 * @var ShowRepository
	 */
	private ShowRepository $showRepository;

	/**
	 * The episode candidate being created.
	 * @var EpisodeCandidate
	 */
	private EpisodeCandidate $episodeCandidate;

	/**
	 * Create a factory to create {@see EpisodeCandidate}.
	 * @param ShowRepository $showRepository
	 */
	public function __construct(ShowRepository $showRepository) {
		$this->showRepository = $showRepository;
	}

	/**
	 * Tries to create an {@see EpisodeCandidate} based on a {@see FeedItem}.
	 * @param FeedItem $feedItem
	 * @return EpisodeCandidate|null
	 */
	public function createFromFeedItem(FeedItem $feedItem): ?EpisodeCandidate {
		try {
			$this->episodeCandidate = new EpisodeCandidate();
			$this->setShow($feedItem->getTitle());
			$this->episodeCandidate->setDownloadLink($feedItem->getLink());
			$this->setSeasonData($feedItem->getTitle());
			$this->setQuality($feedItem->getTitle());
			$this->setIsProper($feedItem->getTitle());

			return $this->episodeCandidate;
		} catch (\InvalidArgumentException $exception) {
			return null;
		}
	}

	/**
	 * Sets the TV show based on the title.
	 * @param string $title
	 * @throws \InvalidArgumentException
	 */
	private function setShow(string $title): void {
		foreach ($this->showRepository->findAll() as $show) {
			// Replace non-word characters and check if the title of the show occurs in the title of the provided data.
			$title_show = preg_replace('/\W/', '', $show->getName());
			$title_data = preg_replace('/\W/', '', $title);

			if (stripos($title_data, $title_show) === 0) {
				$this->episodeCandidate->setShow($show);

				return;
			}
		}

		throw new \InvalidArgumentException('No matching TV show found');
	}

	/**
	 * Sets season data based on the title.
	 * @param string $title
	 * @throws \InvalidArgumentException
	 */
	private function setSeasonData(string $title): void {
		$patterns = [
			'/S(\d{1,2})E(\d{1,2})/i',
			'/(\d{1,2})X(\d{1,2})/i',
		];

		foreach ($patterns as $pattern) {
			if (preg_match($pattern, $title, $matches)) {
				$this->episodeCandidate->setSeasonNumber((int) $matches[1]);
				$this->episodeCandidate->setEpisodeNumber((int) $matches[2]);

				return;
			}
		}

		throw new \InvalidArgumentException('No season data found');
	}

	/**
	 * Sets quality based on the title.
	 * @param string $title
	 * @throws \InvalidArgumentException
	 */
	private function setQuality(string $title): void {
		if (!preg_match('/(\d{1,4})[P|p|i]/', $title, $matches)) {
			throw new \InvalidArgumentException('No quality found');
		}

		$this->episodeCandidate->setQuality((int) $matches[1]);
	}

	/**
	 * Sets if episode is proper based on the title.
	 * @param string $title
	 * @throws \InvalidArgumentException
	 */
	private function setIsProper(string $title): void {
		$this->episodeCandidate->setIsProper((bool) preg_match('/PROPER|REPACK/i', $title));
	}
}
