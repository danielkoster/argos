<?php

namespace App\Factory;

use App\Entity\EpisodeCandidate;
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
	 * The episode being created.
	 * @var EpisodeCandidate
	 */
	private EpisodeCandidate $episode;

	/**
	 * Create a factory to create {@see EpisodeCandidate}.
	 * @param ShowRepository $showRepository
	 */
	public function __construct(ShowRepository $showRepository) {
		$this->showRepository = $showRepository;
	}

	/**
	 * Tries to create an {@see EpisodeCandidate} based on data.
	 * @param mixed[] $data
	 * @return EpisodeCandidate
	 */
	public function create(array $data): ?EpisodeCandidate {
		if (!isset($data['title'], $data['link'])) {
			return null;
		}

		try {
			$this->episode = new EpisodeCandidate();
			$this->setShow($data);
			$this->episode->setDownloadLink($data['link']);
			$this->setSeasonData($data);
			$this->setQuality($data);

			return $this->episode;
		} catch (\InvalidArgumentException $exception) {
			return null;
		}
	}

	/**
	 * Sets the show based on data.
	 * @param mixed[] $data
	 * @throws \InvalidArgumentException
	 */
	private function setShow(array $data): void {
		foreach ($this->showRepository->findAll() as $show) {
			// Replace non-word characters and check if the title of the show occurs in the title of the provided data.
			$title_show = preg_replace('/\W/', '', $show->getName());
			$title_data = preg_replace('/\W/', '', $data['title']);

			if (stripos($title_data, $title_show) === 0) {
				$this->episode->setShow($show);

				return;
			}
		}

		throw new \InvalidArgumentException('No matching show found');
	}

	/**
	 * Sets season data based on data.
	 * @param mixed[] $data
	 * @throws \InvalidArgumentException
	 */
	private function setSeasonData(array $data): void {
		$patterns = [
			'/S(\d{1,2})E(\d{1,2})/i',
			'/(\d{1,2})X(\d{1,2})/i',
		];

		foreach ($patterns as $pattern) {
			if (preg_match($pattern, $data['title'], $matches)) {
				$this->episode->setSeasonNumber((int) $matches[1]);
				$this->episode->setEpisodeNumber((int) $matches[2]);

				return;
			}
		}

		throw new \InvalidArgumentException('No season data found');
	}

	/**
	 * Sets quality based on data.
	 * @param mixed[] $data
	 * @throws \InvalidArgumentException
	 */
	private function setQuality(array $data): void {
		if (!preg_match('/(\d{1,4})[P|p|i]/', $data['title'], $matches)) {
			throw new \InvalidArgumentException('No quality found');
		}

		$this->episode->setQuality((int) $matches[1]);
	}
}
