<?php

declare(strict_types=1);

namespace OCA\TestCases\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Seeds the platforms table with default operating systems
 *
 * @psalm-suppress UnusedClass
 */
class Version001000Date20260119000001 extends SimpleMigrationStep {
	private IDBConnection $db;

	public function __construct(IDBConnection $db) {
		$this->db = $db;
	}

	/**
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		$platforms = ['Windows', 'Linux', 'macOS', 'iOS', 'Android'];

		foreach ($platforms as $platformName) {
			// Check if platform already exists
			$qb = $this->db->getQueryBuilder();
			$qb->select('id')
				->from('testcases_platforms')
				->where($qb->expr()->eq('name', $qb->createNamedParameter($platformName)));
			$result = $qb->executeQuery();
			$existing = $result->fetch();
			$result->closeCursor();

			if ($existing === false) {
				$qb = $this->db->getQueryBuilder();
				$qb->insert('testcases_platforms')
					->values([
						'name' => $qb->createNamedParameter($platformName),
					]);
				$qb->executeStatement();
			}
		}
	}
}
