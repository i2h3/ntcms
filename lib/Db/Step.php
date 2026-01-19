<?php

declare(strict_types=1);

namespace OCA\TestCases\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @method int getStepOrder()
 * @method void setStepOrder(int $stepOrder)
 * @method string getDescription()
 * @method void setDescription(string $description)
 * @method int getCaseId()
 * @method void setCaseId(int $caseId)
 */
class Step extends Entity implements JsonSerializable {
	protected int $stepOrder = 0;
	protected string $description = '';
	protected int $caseId = 0;

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('stepOrder', 'integer');
		$this->addType('description', 'string');
		$this->addType('caseId', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'order' => $this->stepOrder,
			'description' => $this->description,
			'caseId' => $this->caseId,
		];
	}
}
