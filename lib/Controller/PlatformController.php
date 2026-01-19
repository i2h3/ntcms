<?php

declare(strict_types=1);

namespace OCA\TestCases\Controller;

use OCA\TestCases\Db\Platform;
use OCA\TestCases\Db\PlatformMapper;
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
class PlatformController extends OCSController {
	private PlatformMapper $platformMapper;

	public function __construct(
		string $appName,
		IRequest $request,
		PlatformMapper $platformMapper,
	) {
		parent::__construct($appName, $request);
		$this->platformMapper = $platformMapper;
	}

	/**
	 * Get all platforms
	 *
	 * @return DataResponse<Http::STATUS_OK, array{platforms: list<array{id: int, name: string}>}, array{}>
	 *
	 * 200: Platforms returned
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/platforms')]
	public function index(): DataResponse {
		$platforms = $this->platformMapper->findAll();
		return new DataResponse([
			'platforms' => array_map(fn (Platform $p) => $p->jsonSerialize(), $platforms),
		]);
	}

	/**
	 * Get a single platform
	 *
	 * @param int $id Platform ID
	 * @return DataResponse<Http::STATUS_OK, array{id: int, name: string}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 200: Platform returned
	 * 404: Platform not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/platforms/{id}')]
	public function show(int $id): DataResponse {
		try {
			$platform = $this->platformMapper->find($id);
			return new DataResponse($platform->jsonSerialize());
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Platform not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Create a new platform
	 *
	 * @param string $name Platform name
	 * @return DataResponse<Http::STATUS_CREATED, array{id: int, name: string}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: string}, array{}>
	 *
	 * 201: Platform created
	 * 400: Bad request
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'POST', url: '/api/platforms')]
	public function create(string $name): DataResponse {
		if (empty(trim($name))) {
			return new DataResponse(
				['error' => 'Name is required'],
				Http::STATUS_BAD_REQUEST
			);
		}

		$platform = new Platform();
		$platform->setName($name);
		$platform = $this->platformMapper->insert($platform);

		return new DataResponse($platform->jsonSerialize(), Http::STATUS_CREATED);
	}

	/**
	 * Update a platform
	 *
	 * @param int $id Platform ID
	 * @param string $name Platform name
	 * @return DataResponse<Http::STATUS_OK, array{id: int, name: string}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: string}, array{}>
	 *
	 * 200: Platform updated
	 * 404: Platform not found
	 * 400: Bad request
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'PUT', url: '/api/platforms/{id}')]
	public function update(int $id, string $name): DataResponse {
		if (empty(trim($name))) {
			return new DataResponse(
				['error' => 'Name is required'],
				Http::STATUS_BAD_REQUEST
			);
		}

		try {
			$platform = $this->platformMapper->find($id);
			$platform->setName($name);
			$platform = $this->platformMapper->update($platform);

			return new DataResponse($platform->jsonSerialize());
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Platform not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Delete a platform
	 *
	 * @param int $id Platform ID
	 * @return DataResponse<Http::STATUS_OK, array{deleted: true}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 200: Platform deleted
	 * 404: Platform not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'DELETE', url: '/api/platforms/{id}')]
	public function destroy(int $id): DataResponse {
		try {
			$platform = $this->platformMapper->find($id);
			$this->platformMapper->delete($platform);

			return new DataResponse(['deleted' => true]);
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Platform not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}
}
