<?php

declare(strict_types=1);

namespace OCA\TestCases\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * Junction table for Run-Case many-to-many relationship
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @method int getRunId()
 * @method void setRunId(int $runId)
 * @method int getCaseId()
 * @method void setCaseId(int $caseId)
 */
class RunCase extends Entity implements JsonSerializable {
	protected int $runId = 0;
	protected int $caseId = 0;

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('runId', 'integer');
		$this->addType('caseId', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'runId' => $this->runId,
			'caseId' => $this->caseId,
		];
	}
}
