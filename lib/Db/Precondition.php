<?php

declare(strict_types=1);

namespace OCA\TestCases\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @method string getDescription()
 * @method void setDescription(string $description)
 * @method int getCaseId()
 * @method void setCaseId(int $caseId)
 */
class Precondition extends Entity implements JsonSerializable {
	protected string $description = '';
	protected int $caseId = 0;

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('description', 'string');
		$this->addType('caseId', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'description' => $this->description,
			'caseId' => $this->caseId,
		];
	}
}
