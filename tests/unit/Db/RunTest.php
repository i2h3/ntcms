<?php

declare(strict_types=1);

namespace Db;

use DateTime;
use OCA\TestCases\Db\Run;
use PHPUnit\Framework\TestCase;

final class RunTest extends TestCase {
	public function testGettersAndSetters(): void {
		$run = new Run();
		$run->setName('Release Candidate 1');
		$run->setReleaseId(1);

		$start = new DateTime('2026-01-15T10:00:00Z');
		$end = new DateTime('2026-01-20T18:00:00Z');
		$run->setStart($start);
		$run->setEnd($end);

		$this->assertEquals('Release Candidate 1', $run->getName());
		$this->assertEquals(1, $run->getReleaseId());
		$this->assertEquals($start, $run->getStart());
		$this->assertEquals($end, $run->getEnd());
	}

	public function testNullDates(): void {
		$run = new Run();
		$run->setName('Test Run');
		$run->setReleaseId(1);
		$run->setStart(null);
		$run->setEnd(null);

		$this->assertNull($run->getStart());
		$this->assertNull($run->getEnd());
	}

	public function testJsonSerialize(): void {
		$run = new Run();
		$run->setName('Release Candidate 1');
		$run->setReleaseId(1);
		$start = new DateTime('2026-01-15T10:00:00+00:00');
		$run->setStart($start);

		$json = $run->jsonSerialize();

		$this->assertArrayHasKey('id', $json);
		$this->assertArrayHasKey('name', $json);
		$this->assertArrayHasKey('start', $json);
		$this->assertArrayHasKey('end', $json);
		$this->assertArrayHasKey('releaseId', $json);
		$this->assertEquals('Release Candidate 1', $json['name']);
		$this->assertEquals($start->format(DateTime::ATOM), $json['start']);
	}
}
