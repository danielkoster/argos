<?php

namespace App\Repository;

use App\Entity\TvShow;

/**
 * Repository to manager {@see TvShow}.
 * @method TvShow|null find($id, $lockMode = null, $lockVersion = null)
 * @method TvShow|null findOneBy(array $criteria, array $orderBy = null)
 * @method TvShow[]    findAll()
 * @method TvShow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TvShowRepository extends AbstractRepository {
	/**
	 * @inheritDoc
	 */
	protected static function getEntityClass(): string {
		return TvShow::class;
	}
}
