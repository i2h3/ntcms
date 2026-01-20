<?php

declare(strict_types=1);

namespace Db;

use OCA\TestCases\Db\RelatedCase;
use PHPUnit\Framework\TestCase;

final class RelatedCaseTest extends TestCase {
	public function testGettersAndSetters(): void {
		$relatedCase = new RelatedCase();
		$relatedCase->setCaseId(1);
		$relatedCase->setRelatedCaseId(2);

		$this->assertEquals(1, $relatedCase->getCaseId());
		$this->assertEquals(2, $relatedCase->getRelatedCaseId());
	}

	public function testJsonSerialize(): void {
		$relatedCase = new RelatedCase();
		$relatedCase->setCaseId(5);
		$relatedCase->setRelatedCaseId(10);

		$json = $relatedCase->jsonSerialize();

		$this->assertArrayHasKey('id', $json);
		$this->assertArrayHasKey('caseId', $json);
		$this->assertArrayHasKey('relatedCaseId', $json);
		$this->assertEquals(5, $json['caseId']);
		$this->assertEquals(10, $json['relatedCaseId']);
	}
}
