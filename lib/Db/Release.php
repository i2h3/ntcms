<?php

declare(strict_types=1);

namespace OCA\TestCases\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @method string getName()
 * @method void setName(string $name)
 * @method string|null getDescription()
 * @method void setDescription(?string $description)
 * @method int getProductId()
 * @method void setProductId(int $productId)
 */
class Release extends Entity implements JsonSerializable {
	protected string $name = '';
	protected ?string $description = null;
	protected int $productId = 0;

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('name', 'string');
		$this->addType('description', 'string');
		$this->addType('productId', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'name' => $this->name,
			'description' => $this->description,
			'productId' => $this->productId,
		];
	}
}
