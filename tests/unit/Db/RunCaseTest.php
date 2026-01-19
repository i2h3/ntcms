<?php

declare(strict_types=1);

namespace Db;

use OCA\TestCases\Db\RunCase;
use PHPUnit\Framework\TestCase;

final class RunCaseTest extends TestCase {
	public function testGettersAndSetters(): void {
		$runCase = new RunCase();
		$runCase->setRunId(1);
		$runCase->setCaseId(2);

		$this->assertEquals(1, $runCase->getRunId());
		$this->assertEquals(2, $runCase->getCaseId());
	}

	public function testJsonSerialize(): void {
		$runCase = new RunCase();
		$runCase->setRunId(3);
		$runCase->setCaseId(4);

		$json = $runCase->jsonSerialize();

		$this->assertArrayHasKey('id', $json);
		$this->assertArrayHasKey('runId', $json);
		$this->assertArrayHasKey('caseId', $json);
		$this->assertEquals(3, $json['runId']);
		$this->assertEquals(4, $json['caseId']);
	}
}
