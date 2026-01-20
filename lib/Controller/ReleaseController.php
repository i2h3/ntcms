<?php

declare(strict_types=1);

namespace OCA\TestCases\Controller;

use OCA\TestCases\Db\Release;
use OCA\TestCases\Db\ReleaseMapper;
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
class ReleaseController extends OCSController {
	private ReleaseMapper $releaseMapper;
	private ProductMapper $productMapper;

	public function __construct(
		string $appName,
		IRequest $request,
		ReleaseMapper $releaseMapper,
		ProductMapper $productMapper,
	) {
		parent::__construct($appName, $request);
		$this->releaseMapper = $releaseMapper;
		$this->productMapper = $productMapper;
	}

	/**
	 * Get all releases
	 *
	 * @param int|null $productId Optional product ID filter
	 * @return DataResponse<Http::STATUS_OK, array{releases: list<array{id: int, name: string, description: ?string, productId: int}>}, array{}>
	 *
	 * 200: Releases returned
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/releases')]
	public function index(?int $productId = null): DataResponse {
		if ($productId !== null) {
			$releases = $this->releaseMapper->findByProductId($productId);
		} else {
			$releases = $this->releaseMapper->findAll();
		}
		return new DataResponse([
			'releases' => array_map(fn (Release $r) => $r->jsonSerialize(), $releases),
		]);
	}

	/**
	 * Get a single release
	 *
	 * @param int $id Release ID
	 * @return DataResponse<Http::STATUS_OK, array{id: int, name: string, description: ?string, productId: int}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 200: Release returned
	 * 404: Release not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/releases/{id}')]
	public function show(int $id): DataResponse {
		try {
			$release = $this->releaseMapper->find($id);
			return new DataResponse($release->jsonSerialize());
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Release not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Create a new release
	 *
	 * @param string $name Release name
	 * @param int $productId Product ID
	 * @param string|null $description Release description
	 * @return DataResponse<Http::STATUS_CREATED, array{id: int, name: string, description: ?string, productId: int}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: string}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 201: Release created
	 * 400: Bad request
	 * 404: Product not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'POST', url: '/api/releases')]
	public function create(string $name, int $productId, ?string $description = null): DataResponse {
		if (empty(trim($name))) {
			return new DataResponse(
				['error' => 'Name is required'],
				Http::STATUS_BAD_REQUEST
			);
		}

		try {
			$this->productMapper->find($productId);
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Product not found'],
				Http::STATUS_NOT_FOUND
			);
		}

		$release = new Release();
		$release->setName($name);
		$release->setProductId($productId);
		$release->setDescription($description);
		$release = $this->releaseMapper->insert($release);

		return new DataResponse($release->jsonSerialize(), Http::STATUS_CREATED);
	}

	/**
	 * Update a release
	 *
	 * @param int $id Release ID
	 * @param string $name Release name
	 * @param string|null $description Release description
	 * @return DataResponse<Http::STATUS_OK, array{id: int, name: string, description: ?string, productId: int}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: string}, array{}>
	 *
	 * 200: Release updated
	 * 404: Release not found
	 * 400: Bad request
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'PUT', url: '/api/releases/{id}')]
	public function update(int $id, string $name, ?string $description = null): DataResponse {
		if (empty(trim($name))) {
			return new DataResponse(
				['error' => 'Name is required'],
				Http::STATUS_BAD_REQUEST
			);
		}

		try {
			$release = $this->releaseMapper->find($id);
			$release->setName($name);
			$release->setDescription($description);
			$release = $this->releaseMapper->update($release);

			return new DataResponse($release->jsonSerialize());
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Release not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Delete a release
	 *
	 * @param int $id Release ID
	 * @return DataResponse<Http::STATUS_OK, array{deleted: true}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 200: Release deleted
	 * 404: Release not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'DELETE', url: '/api/releases/{id}')]
	public function destroy(int $id): DataResponse {
		try {
			$release = $this->releaseMapper->find($id);
			$this->releaseMapper->delete($release);

			return new DataResponse(['deleted' => true]);
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Release not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}
}
