<?php

declare(strict_types=1);

namespace OCA\TestCases\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * Junction table for Case-Platform many-to-many relationship
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @method int getCaseId()
 * @method void setCaseId(int $caseId)
 * @method int getPlatformId()
 * @method void setPlatformId(int $platformId)
 */
class CasePlatform extends Entity implements JsonSerializable {
	protected int $caseId = 0;
	protected int $platformId = 0;

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('caseId', 'integer');
		$this->addType('platformId', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'caseId' => $this->caseId,
			'platformId' => $this->platformId,
		];
	}
}
