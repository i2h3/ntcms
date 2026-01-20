<?php

declare(strict_types=1);

namespace OCA\TestCases\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<RelatedCase>
 */
class RelatedCaseMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'testcases_related_cases', RelatedCase::class);
	}

	/**
	 * @return RelatedCase[]
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
	 * @throws Exception
	 */
	public function deleteByCaseId(int $caseId): void {
		$qb = $this->db->getQueryBuilder();
		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('case_id', $qb->createNamedParameter($caseId, IQueryBuilder::PARAM_INT)));
		$qb->executeStatement();
	}
}
