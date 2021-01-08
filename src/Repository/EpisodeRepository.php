<?php

namespace App\Repository;

use App\Entity\Episode;
use App\Entity\EpisodeInterface;

/**
 * Repository to manage {@see Episode}.
 * @method Episode|null find($id, $lockMode = null, $lockVersion = null)
 * @method Episode|null findOneBy(array $criteria, array $orderBy = null)
 * @method Episode[]    findAll()
 * @method Episode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EpisodeRepository extends AbstractRepository {
	/**
	 * @inheritDoc
	 */
	protected static function getEntityClass(): string {
		return Episode::class;
	}

	/**
	 * Find episodes which are similar to the given episode.
	 * @param EpisodeInterface $episode
	 * @return Episode[]
	 */
	public function findSimilar(EpisodeInterface $episode): array {
		return $this->findBy([
			'tvShow' => $episode->getTvShow(),
			'seasonNumber' => $episode->getSeasonNumber(),
			'episodeNumber' => $episode->getEpisodeNumber(),
		]);
	}
}
