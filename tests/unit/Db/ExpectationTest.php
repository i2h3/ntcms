<?php

declare(strict_types=1);

namespace Db;

use OCA\TestCases\Db\Expectation;
use PHPUnit\Framework\TestCase;

final class ExpectationTest extends TestCase {
	public function testGettersAndSetters(): void {
		$expectation = new Expectation();
		$expectation->setDescription('Login form should be displayed');
		$expectation->setStepId(1);

		$this->assertEquals('Login form should be displayed', $expectation->getDescription());
		$this->assertEquals(1, $expectation->getStepId());
	}

	public function testJsonSerialize(): void {
		$expectation = new Expectation();
		$expectation->setDescription('User should be redirected');
		$expectation->setStepId(5);

		$json = $expectation->jsonSerialize();

		$this->assertArrayHasKey('id', $json);
		$this->assertArrayHasKey('description', $json);
		$this->assertArrayHasKey('stepId', $json);
		$this->assertEquals('User should be redirected', $json['description']);
		$this->assertEquals(5, $json['stepId']);
	}
}
