<?php

declare(strict_types=1);

namespace OCA\TestCases\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @method string getName()
 * @method void setName(string $name)
 */
class Platform extends Entity implements JsonSerializable {
	protected string $name = '';

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('name', 'string');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'name' => $this->name,
		];
	}
}
