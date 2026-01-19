<?php

declare(strict_types=1);

namespace Db;

use OCA\TestCases\Db\Precondition;
use PHPUnit\Framework\TestCase;

final class PreconditionTest extends TestCase {
	public function testGettersAndSetters(): void {
		$precondition = new Precondition();
		$precondition->setDescription('User must be logged in');
		$precondition->setCaseId(1);

		$this->assertEquals('User must be logged in', $precondition->getDescription());
		$this->assertEquals(1, $precondition->getCaseId());
	}

	public function testJsonSerialize(): void {
		$precondition = new Precondition();
		$precondition->setDescription('Network connection required');
		$precondition->setCaseId(5);

		$json = $precondition->jsonSerialize();

		$this->assertArrayHasKey('id', $json);
		$this->assertArrayHasKey('description', $json);
		$this->assertArrayHasKey('caseId', $json);
		$this->assertEquals('Network connection required', $json['description']);
		$this->assertEquals(5, $json['caseId']);
	}
}
