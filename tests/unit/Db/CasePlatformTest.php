<?php

declare(strict_types=1);

namespace Db;

use OCA\TestCases\Db\CasePlatform;
use PHPUnit\Framework\TestCase;

final class CasePlatformTest extends TestCase {
	public function testGettersAndSetters(): void {
		$casePlatform = new CasePlatform();
		$casePlatform->setCaseId(1);
		$casePlatform->setPlatformId(2);

		$this->assertEquals(1, $casePlatform->getCaseId());
		$this->assertEquals(2, $casePlatform->getPlatformId());
	}

	public function testJsonSerialize(): void {
		$casePlatform = new CasePlatform();
		$casePlatform->setCaseId(3);
		$casePlatform->setPlatformId(4);

		$json = $casePlatform->jsonSerialize();

		$this->assertArrayHasKey('id', $json);
		$this->assertArrayHasKey('caseId', $json);
		$this->assertArrayHasKey('platformId', $json);
		$this->assertEquals(3, $json['caseId']);
		$this->assertEquals(4, $json['platformId']);
	}
}
