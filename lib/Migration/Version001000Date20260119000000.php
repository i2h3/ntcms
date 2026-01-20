<?php

declare(strict_types=1);

namespace OCA\TestCases\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * @psalm-suppress UnusedClass
 */
class Version001000Date20260119000000 extends SimpleMigrationStep {
	/**
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// Products table
		if (!$schema->hasTable('testcases_products')) {
			$table = $schema->createTable('testcases_products');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
				'length' => 255,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['name'], 'testcases_products_name_idx');
		}

		// Releases table
		if (!$schema->hasTable('testcases_releases')) {
			$table = $schema->createTable('testcases_releases');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('description', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('product_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['product_id'], 'testcases_releases_product_idx');
		}

		// Runs table
		if (!$schema->hasTable('testcases_runs')) {
			$table = $schema->createTable('testcases_runs');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('start', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('end', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('release_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['release_id'], 'testcases_runs_release_idx');
		}

		// Cases table
		if (!$schema->hasTable('testcases_cases')) {
			$table = $schema->createTable('testcases_cases');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('case_number', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('description', Types::TEXT, [
				'notnull' => false,
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['case_number'], 'testcases_cases_number_idx');
		}

		// Preconditions table
		if (!$schema->hasTable('testcases_preconditions')) {
			$table = $schema->createTable('testcases_preconditions');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('description', Types::TEXT, [
				'notnull' => true,
			]);
			$table->addColumn('case_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['case_id'], 'testcases_preconditions_case_idx');
		}

		// Steps table
		if (!$schema->hasTable('testcases_steps')) {
			$table = $schema->createTable('testcases_steps');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('step_order', Types::INTEGER, [
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('description', Types::TEXT, [
				'notnull' => true,
			]);
			$table->addColumn('case_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['case_id'], 'testcases_steps_case_idx');
		}

		// Expectations table
		if (!$schema->hasTable('testcases_expectations')) {
			$table = $schema->createTable('testcases_expectations');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('description', Types::TEXT, [
				'notnull' => true,
			]);
			$table->addColumn('step_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['step_id'], 'testcases_expectations_step_idx');
		}

		// Platforms table
		if (!$schema->hasTable('testcases_platforms')) {
			$table = $schema->createTable('testcases_platforms');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
				'length' => 255,
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['name'], 'testcases_platforms_name_idx');
		}

		// Case-Platform junction table (many-to-many)
		if (!$schema->hasTable('testcases_case_platforms')) {
			$table = $schema->createTable('testcases_case_platforms');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('case_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->addColumn('platform_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['case_id'], 'testcases_cp_case_idx');
			$table->addIndex(['platform_id'], 'testcases_cp_platform_idx');
			$table->addUniqueIndex(['case_id', 'platform_id'], 'testcases_cp_unique_idx');
		}

		// Related cases junction table (many-to-many, self-referential)
		if (!$schema->hasTable('testcases_related_cases')) {
			$table = $schema->createTable('testcases_related_cases');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('case_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->addColumn('related_case_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['case_id'], 'testcases_rc_case_idx');
			$table->addIndex(['related_case_id'], 'testcases_rc_related_idx');
			$table->addUniqueIndex(['case_id', 'related_case_id'], 'testcases_rc_unique_idx');
		}

		// Run-Case junction table (many-to-many)
		if (!$schema->hasTable('testcases_run_cases')) {
			$table = $schema->createTable('testcases_run_cases');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('run_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->addColumn('case_id', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['run_id'], 'testcases_runc_run_idx');
			$table->addIndex(['case_id'], 'testcases_runc_case_idx');
			$table->addUniqueIndex(['run_id', 'case_id'], 'testcases_runc_unique_idx');
		}

		return $schema;
	}
}
