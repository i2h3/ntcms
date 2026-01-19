<?php

declare(strict_types=1);

namespace OCA\TestCases\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<CasePlatform>
 */
class CasePlatformMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'testcases_case_platforms', CasePlatform::class);
	}

	/**
	 * @return CasePlatform[]
	 * @throws Exception
	 */
	public function findByCaseId(int $caseId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('case_id', $qb->createNamedParameter($caseId, IQueryBuilder::PARAM_INT)));
		return $this->findEntities($qb);
	}

	/**
	 * @return CasePlatform[]
	 * @throws Exception
	 */
	public function findByPlatformId(int $platformId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('platform_id', $qb->createNamedParameter($platformId, IQueryBuilder::PARAM_INT)));
		return $this->findEntities($qb);
	}

	/**
	 * @throws Exception
	 */
	public function deleteByCaseId(int $caseId): void {
		$qb = $this->db->getQueryBuilder();
		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('case_id', $qb->createNamedParameter($caseId, IQueryBuilder::PARAM_INT)));
		$qb->executeStatement();
	}
}
