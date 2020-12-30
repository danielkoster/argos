<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Base episodeCandidateRepository.
 */
abstract class AbstractRepository extends ServiceEntityRepository {
	/**
	 * Symfony's validator.
	 * @var ValidatorInterface
	 */
	private ValidatorInterface $validator;

	/**
	 * Creates a new episodeCandidateRepository.
	 * @param ManagerRegistry $registry The registry.
	 * @param ValidatorInterface $validator Symfony's validator.
	 */
	public function __construct(ManagerRegistry $registry, ValidatorInterface $validator) {
		parent::__construct($registry, static::getEntityClass());

		$this->validator = $validator;
	}

	/**
	 * Returns the entity class this episodeCandidateRepository manages.
	 * @return string The class.
	 */
	abstract protected static function getEntityClass(): string;

	/**
	 * Deletes an entity from the database.
	 * @param object $entity The entity to delete.
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function delete(object $entity): void {
		$this->validateEntityType($entity);

		$this->getEntityManager()->remove($entity);
		$this->getEntityManager()->flush();
	}

	/**
	 * Persists an entity in the database.
	 * @param object $entity The entity to save.
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function save(object $entity): void {
		$this->validateEntityType($entity);

		$violations = $this->validator->validate($entity);
		if (0 !== $violations->count()) {
			throw new ValidationFailedException($entity, $violations);
		}

		$this->getEntityManager()->persist($entity);
		$this->getEntityManager()->flush();
	}

	/**
	 * Validates an entity by checking if this episodeCandidateRepository can manage it.
	 * @param object $entity The entity.
	 */
	protected function validateEntityType(object $entity): void {
		if (get_class($entity) !== static::getEntityClass()) {
			throw new \InvalidArgumentException(sprintf(
				'This episodeCandidateRepository only manages entities of type "%s" (provided "%s" ).',
				static::getEntityClass(),
				get_class($entity)
			));
		}
	}
}
