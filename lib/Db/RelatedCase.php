<?php

declare(strict_types=1);

namespace OCA\TestCases\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * Junction table for Case-Case (related cases) many-to-many relationship
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @method int getCaseId()
 * @method void setCaseId(int $caseId)
 * @method int getRelatedCaseId()
 * @method void setRelatedCaseId(int $relatedCaseId)
 */
class RelatedCase extends Entity implements JsonSerializable {
	protected int $caseId = 0;
	protected int $relatedCaseId = 0;

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('caseId', 'integer');
		$this->addType('relatedCaseId', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'caseId' => $this->caseId,
			'relatedCaseId' => $this->relatedCaseId,
		];
	}
}
