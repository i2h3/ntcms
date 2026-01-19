<?php

declare(strict_types=1);

namespace OCA\TestCases\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @method string getName()
 * @method void setName(string $name)
 * @method DateTime|null getStart()
 * @method void setStart(?DateTime $start)
 * @method DateTime|null getEnd()
 * @method void setEnd(?DateTime $end)
 * @method int getReleaseId()
 * @method void setReleaseId(int $releaseId)
 */
class Run extends Entity implements JsonSerializable {
	protected string $name = '';
	protected ?DateTime $start = null;
	protected ?DateTime $end = null;
	protected int $releaseId = 0;

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('name', 'string');
		$this->addType('start', 'datetime');
		$this->addType('end', 'datetime');
		$this->addType('releaseId', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'name' => $this->name,
			'start' => $this->start?->format(DateTime::ATOM),
			'end' => $this->end?->format(DateTime::ATOM),
			'releaseId' => $this->releaseId,
		];
	}
}
