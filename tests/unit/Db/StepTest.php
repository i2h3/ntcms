<?php

declare(strict_types=1);

namespace Db;

use OCA\TestCases\Db\Step;
use PHPUnit\Framework\TestCase;

final class StepTest extends TestCase {
	public function testGettersAndSetters(): void {
		$step = new Step();
		$step->setStepOrder(1);
		$step->setDescription('Click the login button');
		$step->setCaseId(1);

		$this->assertEquals(1, $step->getStepOrder());
		$this->assertEquals('Click the login button', $step->getDescription());
		$this->assertEquals(1, $step->getCaseId());
	}

	public function testJsonSerialize(): void {
		$step = new Step();
		$step->setStepOrder(2);
		$step->setDescription('Enter username');
		$step->setCaseId(3);

		$json = $step->jsonSerialize();

		$this->assertArrayHasKey('id', $json);
		$this->assertArrayHasKey('order', $json);
		$this->assertArrayHasKey('description', $json);
		$this->assertArrayHasKey('caseId', $json);
		$this->assertEquals(2, $json['order']);
		$this->assertEquals('Enter username', $json['description']);
		$this->assertEquals(3, $json['caseId']);
	}
}
