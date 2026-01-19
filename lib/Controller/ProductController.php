<?php

declare(strict_types=1);

namespace OCA\TestCases\Controller;

use OCA\TestCases\Db\Product;
use OCA\TestCases\Db\ProductMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\ApiRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;

/**
 * @psalm-suppress UnusedClass
 */
class ProductController extends OCSController {
	private ProductMapper $productMapper;

	public function __construct(
		string $appName,
		IRequest $request,
		ProductMapper $productMapper,
	) {
		parent::__construct($appName, $request);
		$this->productMapper = $productMapper;
	}

	/**
	 * Get all products
	 *
	 * @return DataResponse<Http::STATUS_OK, array{products: list<array{id: int, name: string}>}, array{}>
	 *
	 * 200: Products returned
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/products')]
	public function index(): DataResponse {
		$products = $this->productMapper->findAll();
		return new DataResponse([
			'products' => array_map(fn (Product $p) => $p->jsonSerialize(), $products),
		]);
	}

	/**
	 * Get a single product
	 *
	 * @param int $id Product ID
	 * @return DataResponse<Http::STATUS_OK, array{id: int, name: string}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 200: Product returned
	 * 404: Product not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/products/{id}')]
	public function show(int $id): DataResponse {
		try {
			$product = $this->productMapper->find($id);
			return new DataResponse($product->jsonSerialize());
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Product not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Create a new product
	 *
	 * @param string $name Product name
	 * @return DataResponse<Http::STATUS_CREATED, array{id: int, name: string}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: string}, array{}>
	 *
	 * 201: Product created
	 * 400: Bad request
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'POST', url: '/api/products')]
	public function create(string $name): DataResponse {
		if (empty(trim($name))) {
			return new DataResponse(
				['error' => 'Name is required'],
				Http::STATUS_BAD_REQUEST
			);
		}

		$product = new Product();
		$product->setName($name);
		$product = $this->productMapper->insert($product);

		return new DataResponse($product->jsonSerialize(), Http::STATUS_CREATED);
	}

	/**
	 * Update a product
	 *
	 * @param int $id Product ID
	 * @param string $name Product name
	 * @return DataResponse<Http::STATUS_OK, array{id: int, name: string}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: string}, array{}>
	 *
	 * 200: Product updated
	 * 404: Product not found
	 * 400: Bad request
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'PUT', url: '/api/products/{id}')]
	public function update(int $id, string $name): DataResponse {
		if (empty(trim($name))) {
			return new DataResponse(
				['error' => 'Name is required'],
				Http::STATUS_BAD_REQUEST
			);
		}

		try {
			$product = $this->productMapper->find($id);
			$product->setName($name);
			$product = $this->productMapper->update($product);

			return new DataResponse($product->jsonSerialize());
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Product not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Delete a product
	 *
	 * @param int $id Product ID
	 * @return DataResponse<Http::STATUS_OK, array{deleted: true}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 200: Product deleted
	 * 404: Product not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'DELETE', url: '/api/products/{id}')]
	public function destroy(int $id): DataResponse {
		try {
			$product = $this->productMapper->find($id);
			$this->productMapper->delete($product);

			return new DataResponse(['deleted' => true]);
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Product not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}
}
