<?php

declare(strict_types=1);

namespace OCA\TestCases\Controller;

use OCA\TestCases\Db\Step;
use OCA\TestCases\Db\StepMapper;
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
class StepController extends OCSController {
	private StepMapper $stepMapper;
	private TestCaseMapper $testCaseMapper;

	public function __construct(
		string $appName,
		IRequest $request,
		StepMapper $stepMapper,
		TestCaseMapper $testCaseMapper,
	) {
		parent::__construct($appName, $request);
		$this->stepMapper = $stepMapper;
		$this->testCaseMapper = $testCaseMapper;
	}

	/**
	 * Get all steps
	 *
	 * @param int|null $caseId Optional case ID filter
	 * @return DataResponse<Http::STATUS_OK, array{steps: list<array{id: int, order: int, description: string, caseId: int}>}, array{}>
	 *
	 * 200: Steps returned
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/steps')]
	public function index(?int $caseId = null): DataResponse {
		if ($caseId !== null) {
			$steps = $this->stepMapper->findByCaseId($caseId);
		} else {
			$steps = $this->stepMapper->findAll();
		}
		return new DataResponse([
			'steps' => array_map(fn (Step $s) => $s->jsonSerialize(), $steps),
		]);
	}

	/**
	 * Get a single step
	 *
	 * @param int $id Step ID
	 * @return DataResponse<Http::STATUS_OK, array{id: int, order: int, description: string, caseId: int}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 200: Step returned
	 * 404: Step not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/steps/{id}')]
	public function show(int $id): DataResponse {
		try {
			$step = $this->stepMapper->find($id);
			return new DataResponse($step->jsonSerialize());
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Step not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Create a new step
	 *
	 * @param int $order Step order
	 * @param string $description Step description
	 * @param int $caseId Case ID
	 * @return DataResponse<Http::STATUS_CREATED, array{id: int, order: int, description: string, caseId: int}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: string}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 201: Step created
	 * 400: Bad request
	 * 404: Case not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'POST', url: '/api/steps')]
	public function create(int $order, string $description, int $caseId): DataResponse {
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

		$step = new Step();
		$step->setStepOrder($order);
		$step->setDescription($description);
		$step->setCaseId($caseId);
		$step = $this->stepMapper->insert($step);

		return new DataResponse($step->jsonSerialize(), Http::STATUS_CREATED);
	}

	/**
	 * Update a step
	 *
	 * @param int $id Step ID
	 * @param int $order Step order
	 * @param string $description Step description
	 * @return DataResponse<Http::STATUS_OK, array{id: int, order: int, description: string, caseId: int}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: string}, array{}>
	 *
	 * 200: Step updated
	 * 404: Step not found
	 * 400: Bad request
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'PUT', url: '/api/steps/{id}')]
	public function update(int $id, int $order, string $description): DataResponse {
		if (empty(trim($description))) {
			return new DataResponse(
				['error' => 'Description is required'],
				Http::STATUS_BAD_REQUEST
			);
		}

		try {
			$step = $this->stepMapper->find($id);
			$step->setStepOrder($order);
			$step->setDescription($description);
			$step = $this->stepMapper->update($step);

			return new DataResponse($step->jsonSerialize());
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Step not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Delete a step
	 *
	 * @param int $id Step ID
	 * @return DataResponse<Http::STATUS_OK, array{deleted: true}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 200: Step deleted
	 * 404: Step not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'DELETE', url: '/api/steps/{id}')]
	public function destroy(int $id): DataResponse {
		try {
			$step = $this->stepMapper->find($id);
			$this->stepMapper->delete($step);

			return new DataResponse(['deleted' => true]);
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Step not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}
}
