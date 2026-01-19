<?php

declare(strict_types=1);

namespace Db;

use OCA\TestCases\Db\Platform;
use PHPUnit\Framework\TestCase;

final class PlatformTest extends TestCase {
	public function testGettersAndSetters(): void {
		$platform = new Platform();
		$platform->setName('Windows');

		$this->assertEquals('Windows', $platform->getName());
	}

	public function testJsonSerialize(): void {
		$platform = new Platform();
		$platform->setName('macOS');

		$json = $platform->jsonSerialize();

		$this->assertArrayHasKey('id', $json);
		$this->assertArrayHasKey('name', $json);
		$this->assertEquals('macOS', $json['name']);
	}
}
