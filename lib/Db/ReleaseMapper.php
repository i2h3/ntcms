<?php

declare(strict_types=1);

namespace OCA\TestCases\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<Release>
 */
class ReleaseMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'testcases_releases', Release::class);
	}

	/**
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 * @throws Exception
	 */
	public function find(int $id): Release {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		return $this->findEntity($qb);
	}

	/**
	 * @return Release[]
	 * @throws Exception
	 */
	public function findAll(): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName());
		return $this->findEntities($qb);
	}

	/**
	 * @return Release[]
	 * @throws Exception
	 */
	public function findByProductId(int $productId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('product_id', $qb->createNamedParameter($productId, IQueryBuilder::PARAM_INT)));
		return $this->findEntities($qb);
	}
}
