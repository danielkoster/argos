<?php

namespace App\Repository;

use App\Entity\FeedItem;

/**
 * Repository to manage {@see FeedItem}.
 * @method FeedItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeedItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeedItem[]    findAll()
 * @method FeedItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedItemRepository extends AbstractRepository {
	/**
	 * @inheritDoc
	 */
	protected static function getEntityClass(): string {
		return FeedItem::class;
	}
}
