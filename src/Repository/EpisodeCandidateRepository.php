<?php

namespace App\Repository;

use App\Entity\Episode;
use App\Entity\EpisodeCandidate;

/**
 * Repository to manage {@see EpisodeCandidate}.
 * @method EpisodeCandidate|null find($id, $lockMode = null, $lockVersion = null)
 * @method EpisodeCandidate|null findOneBy(array $criteria, array $orderBy = null)
 * @method EpisodeCandidate[]    findAll()
 * @method EpisodeCandidate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method EpisodeCandidate[]   findSimilar(Episode $episode)
 */
class EpisodeCandidateRepository extends EpisodeRepository {
	/**
	 * @inheritDoc
	 */
	protected static function getEntityClass(): string {
		return EpisodeCandidate::class;
	}
}
