<?php

declare(strict_types=1);

namespace OCA\TestCases\Controller;

use OCA\TestCases\Db\Precondition;
use OCA\TestCases\Db\PreconditionMapper;
use OCA\TestCases\Db\TestCaseMapper;
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
class PreconditionController extends OCSController {
	private PreconditionMapper $preconditionMapper;
	private TestCaseMapper $testCaseMapper;

	public function __construct(
		string $appName,
		IRequest $request,
		PreconditionMapper $preconditionMapper,
		TestCaseMapper $testCaseMapper,
	) {
		parent::__construct($appName, $request);
		$this->preconditionMapper = $preconditionMapper;
		$this->testCaseMapper = $testCaseMapper;
	}

	/**
	 * Get all preconditions
	 *
	 * @param int|null $caseId Optional case ID filter
	 * @return DataResponse<Http::STATUS_OK, array{preconditions: list<array{id: int, description: string, caseId: int}>}, array{}>
	 *
	 * 200: Preconditions returned
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/preconditions')]
	public function index(?int $caseId = null): DataResponse {
		if ($caseId !== null) {
			$preconditions = $this->preconditionMapper->findByCaseId($caseId);
		} else {
			$preconditions = $this->preconditionMapper->findAll();
		}
		return new DataResponse([
			'preconditions' => array_map(fn (Precondition $p) => $p->jsonSerialize(), $preconditions),
		]);
	}

	/**
	 * Get a single precondition
	 *
	 * @param int $id Precondition ID
	 * @return DataResponse<Http::STATUS_OK, array{id: int, description: string, caseId: int}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 200: Precondition returned
	 * 404: Precondition not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/preconditions/{id}')]
	public function show(int $id): DataResponse {
		try {
			$precondition = $this->preconditionMapper->find($id);
			return new DataResponse($precondition->jsonSerialize());
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Precondition not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Create a new precondition
	 *
	 * @param string $description Precondition description
	 * @param int $caseId Case ID
	 * @return DataResponse<Http::STATUS_CREATED, array{id: int, description: string, caseId: int}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: string}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 201: Precondition created
	 * 400: Bad request
	 * 404: Case not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'POST', url: '/api/preconditions')]
	public function create(string $description, int $caseId): DataResponse {
		if (empty(trim($description))) {
			return new DataResponse(
				['error' => 'Description is required'],
				Http::STATUS_BAD_REQUEST
			);
		}

		try {
			$this->testCaseMapper->find($caseId);
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Case not found'],
				Http::STATUS_NOT_FOUND
			);
		}

		$precondition = new Precondition();
		$precondition->setDescription($description);
		$precondition->setCaseId($caseId);
		$precondition = $this->preconditionMapper->insert($precondition);

		return new DataResponse($precondition->jsonSerialize(), Http::STATUS_CREATED);
	}

	/**
	 * Update a precondition
	 *
	 * @param int $id Precondition ID
	 * @param string $description Precondition description
	 * @return DataResponse<Http::STATUS_OK, array{id: int, description: string, caseId: int}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: string}, array{}>
	 *
	 * 200: Precondition updated
	 * 404: Precondition not found
	 * 400: Bad request
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'PUT', url: '/api/preconditions/{id}')]
	public function update(int $id, string $description): DataResponse {
		if (empty(trim($description))) {
			return new DataResponse(
				['error' => 'Description is required'],
				Http::STATUS_BAD_REQUEST
			);
		}

		try {
			$precondition = $this->preconditionMapper->find($id);
			$precondition->setDescription($description);
			$precondition = $this->preconditionMapper->update($precondition);

			return new DataResponse($precondition->jsonSerialize());
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Precondition not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Delete a precondition
	 *
	 * @param int $id Precondition ID
	 * @return DataResponse<Http::STATUS_OK, array{deleted: true}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 200: Precondition deleted
	 * 404: Precondition not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'DELETE', url: '/api/preconditions/{id}')]
	public function destroy(int $id): DataResponse {
		try {
			$precondition = $this->preconditionMapper->find($id);
			$this->preconditionMapper->delete($precondition);

			return new DataResponse(['deleted' => true]);
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Precondition not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}
}
