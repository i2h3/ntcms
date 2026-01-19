<?php

declare(strict_types=1);

namespace Db;

use OCA\TestCases\Db\Release;
use PHPUnit\Framework\TestCase;

final class ReleaseTest extends TestCase {
	public function testGettersAndSetters(): void {
		$release = new Release();
		$release->setName('1.2.3');
		$release->setDescription('Major update with new features');
		$release->setProductId(1);

		$this->assertEquals('1.2.3', $release->getName());
		$this->assertEquals('Major update with new features', $release->getDescription());
		$this->assertEquals(1, $release->getProductId());
	}

	public function testNullDescription(): void {
		$release = new Release();
		$release->setName('1.0.0');
		$release->setDescription(null);
		$release->setProductId(1);

		$this->assertNull($release->getDescription());
	}

	public function testJsonSerialize(): void {
		$release = new Release();
		$release->setName('1.2.3');
		$release->setDescription('A release');
		$release->setProductId(2);

		$json = $release->jsonSerialize();

		$this->assertArrayHasKey('id', $json);
		$this->assertArrayHasKey('name', $json);
		$this->assertArrayHasKey('description', $json);
		$this->assertArrayHasKey('productId', $json);
		$this->assertEquals('1.2.3', $json['name']);
		$this->assertEquals('A release', $json['description']);
		$this->assertEquals(2, $json['productId']);
	}
}
