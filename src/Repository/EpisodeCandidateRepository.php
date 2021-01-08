<?php

namespace App\Repository;

use App\Entity\EpisodeCandidate;
use App\Entity\EpisodeInterface;

/**
 * Repository to manage {@see EpisodeCandidate}.
 * @method EpisodeCandidate|null find($id, $lockMode = null, $lockVersion = null)
 * @method EpisodeCandidate|null findOneBy(array $criteria, array $orderBy = null)
 * @method EpisodeCandidate[]    findAll()
 * @method EpisodeCandidate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EpisodeCandidateRepository extends AbstractRepository {
	/**
	 * @inheritDoc
	 */
	protected static function getEntityClass(): string {
		return EpisodeCandidate::class;
	}

	/**
	 * Find episodes which are similar to the given episode.
	 * @param EpisodeInterface $episode
	 * @return EpisodeCandidate[]
	 */
	public function findSimilar(EpisodeInterface $episode): array {
		return $this->findBy([
			'show' => $episode->getShow(),
			'seasonNumber' => $episode->getSeasonNumber(),
			'episodeNumber' => $episode->getEpisodeNumber(),
		]);
	}
}
