<?php

namespace App\Repository;

use App\Entity\Feed;

/**
 * Repository to manage {@see Feed}.
 * @method Feed|null find($id, $lockMode = null, $lockVersion = null)
 * @method Feed|null findOneBy(array $criteria, array $orderBy = null)
 * @method Feed[]    findAll()
 * @method Feed[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedRepository extends AbstractRepository {
	/**
	 * @inheritDoc
	 */
	protected static function getEntityClass(): string {
		return Feed::class;
	}
}
