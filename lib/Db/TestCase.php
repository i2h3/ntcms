<?php

declare(strict_types=1);

namespace OCA\TestCases\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @method int getCaseNumber()
 * @method void setCaseNumber(int $caseNumber)
 * @method string getName()
 * @method void setName(string $name)
 * @method string|null getDescription()
 * @method void setDescription(?string $description)
 */
class TestCase extends Entity implements JsonSerializable {
	protected int $caseNumber = 0;
	protected string $name = '';
	protected ?string $description = null;

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('caseNumber', 'integer');
		$this->addType('name', 'string');
		$this->addType('description', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'caseNumber' => $this->caseNumber,
			'name' => $this->name,
			'description' => $this->description,
		];
	}
}
