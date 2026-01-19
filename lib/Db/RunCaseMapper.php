<?php

declare(strict_types=1);

namespace OCA\TestCases\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<RunCase>
 */
class RunCaseMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'testcases_run_cases', RunCase::class);
	}

	/**
	 * @return RunCase[]
	 * @throws Exception
	 */
	public function findByRunId(int $runId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('run_id', $qb->createNamedParameter($runId, IQueryBuilder::PARAM_INT)));
		return $this->findEntities($qb);
	}

	/**
	 * @return RunCase[]
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
	public function deleteByRunId(int $runId): void {
		$qb = $this->db->getQueryBuilder();
		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('run_id', $qb->createNamedParameter($runId, IQueryBuilder::PARAM_INT)));
		$qb->executeStatement();
	}
}
