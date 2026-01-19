<?php

declare(strict_types=1);

namespace OCA\TestCases\Controller;

use OCA\TestCases\Db\Expectation;
use OCA\TestCases\Db\ExpectationMapper;
use OCA\TestCases\Db\StepMapper;
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
class ExpectationController extends OCSController {
	private ExpectationMapper $expectationMapper;
	private StepMapper $stepMapper;

	public function __construct(
		string $appName,
		IRequest $request,
		ExpectationMapper $expectationMapper,
		StepMapper $stepMapper,
	) {
		parent::__construct($appName, $request);
		$this->expectationMapper = $expectationMapper;
		$this->stepMapper = $stepMapper;
	}

	/**
	 * Get all expectations
	 *
	 * @param int|null $stepId Optional step ID filter
	 * @return DataResponse<Http::STATUS_OK, array{expectations: list<array{id: int, description: string, stepId: int}>}, array{}>
	 *
	 * 200: Expectations returned
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/expectations')]
	public function index(?int $stepId = null): DataResponse {
		if ($stepId !== null) {
			$expectations = $this->expectationMapper->findByStepId($stepId);
		} else {
			$expectations = $this->expectationMapper->findAll();
		}
		return new DataResponse([
			'expectations' => array_map(fn (Expectation $e) => $e->jsonSerialize(), $expectations),
		]);
	}

	/**
	 * Get a single expectation
	 *
	 * @param int $id Expectation ID
	 * @return DataResponse<Http::STATUS_OK, array{id: int, description: string, stepId: int}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 200: Expectation returned
	 * 404: Expectation not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/expectations/{id}')]
	public function show(int $id): DataResponse {
		try {
			$expectation = $this->expectationMapper->find($id);
			return new DataResponse($expectation->jsonSerialize());
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Expectation not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Create a new expectation
	 *
	 * @param string $description Expectation description
	 * @param int $stepId Step ID
	 * @return DataResponse<Http::STATUS_CREATED, array{id: int, description: string, stepId: int}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: string}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 201: Expectation created
	 * 400: Bad request
	 * 404: Step not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'POST', url: '/api/expectations')]
	public function create(string $description, int $stepId): DataResponse {
		if (empty(trim($description))) {
			return new DataResponse(
				['error' => 'Description is required'],
				Http::STATUS_BAD_REQUEST
			);
		}

		try {
			$this->stepMapper->find($stepId);
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Step not found'],
				Http::STATUS_NOT_FOUND
			);
		}

		$expectation = new Expectation();
		$expectation->setDescription($description);
		$expectation->setStepId($stepId);
		$expectation = $this->expectationMapper->insert($expectation);

		return new DataResponse($expectation->jsonSerialize(), Http::STATUS_CREATED);
	}

	/**
	 * Update an expectation
	 *
	 * @param int $id Expectation ID
	 * @param string $description Expectation description
	 * @return DataResponse<Http::STATUS_OK, array{id: int, description: string, stepId: int}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: string}, array{}>
	 *
	 * 200: Expectation updated
	 * 404: Expectation not found
	 * 400: Bad request
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'PUT', url: '/api/expectations/{id}')]
	public function update(int $id, string $description): DataResponse {
		if (empty(trim($description))) {
			return new DataResponse(
				['error' => 'Description is required'],
				Http::STATUS_BAD_REQUEST
			);
		}

		try {
			$expectation = $this->expectationMapper->find($id);
			$expectation->setDescription($description);
			$expectation = $this->expectationMapper->update($expectation);

			return new DataResponse($expectation->jsonSerialize());
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Expectation not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Delete an expectation
	 *
	 * @param int $id Expectation ID
	 * @return DataResponse<Http::STATUS_OK, array{deleted: true}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 200: Expectation deleted
	 * 404: Expectation not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'DELETE', url: '/api/expectations/{id}')]
	public function destroy(int $id): DataResponse {
		try {
			$expectation = $this->expectationMapper->find($id);
			$this->expectationMapper->delete($expectation);

			return new DataResponse(['deleted' => true]);
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Expectation not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}
}
