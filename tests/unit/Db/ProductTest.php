<?php

declare(strict_types=1);

namespace Db;

use OCA\TestCases\Db\Product;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase {
	public function testGettersAndSetters(): void {
		$product = new Product();
		$product->setName('Nextcloud Desktop Client');

		$this->assertEquals('Nextcloud Desktop Client', $product->getName());
	}

	public function testJsonSerialize(): void {
		$product = new Product();
		$product->setName('Nextcloud Desktop Client');

		$json = $product->jsonSerialize();

		$this->assertArrayHasKey('id', $json);
		$this->assertArrayHasKey('name', $json);
		$this->assertEquals('Nextcloud Desktop Client', $json['name']);
	}
}
