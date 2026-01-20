<?php

declare(strict_types=1);

namespace Db;

use OCA\TestCases\Db\TestCase as TestCaseEntity;
use PHPUnit\Framework\TestCase;

final class TestCaseTest extends TestCase {
	public function testGettersAndSetters(): void {
		$testCase = new TestCaseEntity();
		$testCase->setCaseNumber(123);
		$testCase->setName('Login functionality');
		$testCase->setDescription('Test user login with valid credentials');

		$this->assertEquals(123, $testCase->getCaseNumber());
		$this->assertEquals('Login functionality', $testCase->getName());
		$this->assertEquals('Test user login with valid credentials', $testCase->getDescription());
	}

	public function testNullDescription(): void {
		$testCase = new TestCaseEntity();
		$testCase->setCaseNumber(1);
		$testCase->setName('Simple test');
		$testCase->setDescription(null);

		$this->assertNull($testCase->getDescription());
	}

	public function testJsonSerialize(): void {
		$testCase = new TestCaseEntity();
		$testCase->setCaseNumber(456);
		$testCase->setName('File sync');
		$testCase->setDescription('Test file synchronization');

		$json = $testCase->jsonSerialize();

		$this->assertArrayHasKey('id', $json);
		$this->assertArrayHasKey('caseNumber', $json);
		$this->assertArrayHasKey('name', $json);
		$this->assertArrayHasKey('description', $json);
		$this->assertEquals(456, $json['caseNumber']);
		$this->assertEquals('File sync', $json['name']);
	}
}
