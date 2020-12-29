<?php

namespace App\Repository;

use App\Entity\Episode;

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
}
