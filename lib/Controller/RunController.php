<?php

declare(strict_types=1);

namespace OCA\TestCases\Controller;

use DateTime;
use OCA\TestCases\Db\Run;
use OCA\TestCases\Db\RunMapper;
use OCA\TestCases\Db\ReleaseMapper;
use OCA\TestCases\Db\RunCase;
use OCA\TestCases\Db\RunCaseMapper;
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
class RunController extends OCSController {
	private RunMapper $runMapper;
	private ReleaseMapper $releaseMapper;
	private RunCaseMapper $runCaseMapper;
	private TestCaseMapper $testCaseMapper;

	public function __construct(
		string $appName,
		IRequest $request,
		RunMapper $runMapper,
		ReleaseMapper $releaseMapper,
		RunCaseMapper $runCaseMapper,
		TestCaseMapper $testCaseMapper,
	) {
		parent::__construct($appName, $request);
		$this->runMapper = $runMapper;
		$this->releaseMapper = $releaseMapper;
		$this->runCaseMapper = $runCaseMapper;
		$this->testCaseMapper = $testCaseMapper;
	}

	/**
	 * Parse a datetime string in ISO 8601 format
	 *
	 * @param string $value The datetime string to parse
	 * @param string $fieldName The name of the field for error messages
	 * @return DateTime|DataResponse The parsed DateTime or an error DataResponse
	 */
	private function parseDateTime(string $value, string $fieldName): DateTime|DataResponse {
		$dateTime = DateTime::createFromFormat(DateTime::ATOM, $value);
		if ($dateTime === false) {
			return new DataResponse(
				['error' => "Invalid $fieldName datetime format. Use ISO 8601."],
				Http::STATUS_BAD_REQUEST
			);
		}
		return $dateTime;
	}

	/**
	 * Get all runs
	 *
	 * @param int|null $releaseId Optional release ID filter
	 * @return DataResponse<Http::STATUS_OK, array{runs: list<array{id: int, name: string, start: ?string, end: ?string, releaseId: int}>}, array{}>
	 *
	 * 200: Runs returned
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/runs')]
	public function index(?int $releaseId = null): DataResponse {
		if ($releaseId !== null) {
			$runs = $this->runMapper->findByReleaseId($releaseId);
		} else {
			$runs = $this->runMapper->findAll();
		}
		return new DataResponse([
			'runs' => array_map(fn (Run $r) => $r->jsonSerialize(), $runs),
		]);
	}

	/**
	 * Get a single run
	 *
	 * @param int $id Run ID
	 * @return DataResponse<Http::STATUS_OK, array{id: int, name: string, start: ?string, end: ?string, releaseId: int}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 200: Run returned
	 * 404: Run not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/runs/{id}')]
	public function show(int $id): DataResponse {
		try {
			$run = $this->runMapper->find($id);
			return new DataResponse($run->jsonSerialize());
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Run not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Create a new run
	 *
	 * @param string $name Run name
	 * @param int $releaseId Release ID
	 * @param string|null $start Start datetime (ISO 8601)
	 * @param string|null $end End datetime (ISO 8601)
	 * @return DataResponse<Http::STATUS_CREATED, array{id: int, name: string, start: ?string, end: ?string, releaseId: int}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: string}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 201: Run created
	 * 400: Bad request
	 * 404: Release not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'POST', url: '/api/runs')]
	public function create(string $name, int $releaseId, ?string $start = null, ?string $end = null): DataResponse {
		if (empty(trim($name))) {
			return new DataResponse(
				['error' => 'Name is required'],
				Http::STATUS_BAD_REQUEST
			);
		}

		try {
			$this->releaseMapper->find($releaseId);
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Release not found'],
				Http::STATUS_NOT_FOUND
			);
		}

		$run = new Run();
		$run->setName($name);
		$run->setReleaseId($releaseId);

		if ($start !== null) {
			$startResult = $this->parseDateTime($start, 'start');
			if ($startResult instanceof DataResponse) {
				return $startResult;
			}
			$run->setStart($startResult);
		}

		if ($end !== null) {
			$endResult = $this->parseDateTime($end, 'end');
			if ($endResult instanceof DataResponse) {
				return $endResult;
			}
			$run->setEnd($endResult);
		}

		$run = $this->runMapper->insert($run);

		return new DataResponse($run->jsonSerialize(), Http::STATUS_CREATED);
	}

	/**
	 * Update a run
	 *
	 * @param int $id Run ID
	 * @param string $name Run name
	 * @param string|null $start Start datetime (ISO 8601)
	 * @param string|null $end End datetime (ISO 8601)
	 * @return DataResponse<Http::STATUS_OK, array{id: int, name: string, start: ?string, end: ?string, releaseId: int}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: string}, array{}>
	 *
	 * 200: Run updated
	 * 404: Run not found
	 * 400: Bad request
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'PUT', url: '/api/runs/{id}')]
	public function update(int $id, string $name, ?string $start = null, ?string $end = null): DataResponse {
		if (empty(trim($name))) {
			return new DataResponse(
				['error' => 'Name is required'],
				Http::STATUS_BAD_REQUEST
			);
		}

		try {
			$run = $this->runMapper->find($id);
			$run->setName($name);

			if ($start !== null) {
				$startResult = $this->parseDateTime($start, 'start');
				if ($startResult instanceof DataResponse) {
					return $startResult;
				}
				$run->setStart($startResult);
			}

			if ($end !== null) {
				$endResult = $this->parseDateTime($end, 'end');
				if ($endResult instanceof DataResponse) {
					return $endResult;
				}
				$run->setEnd($endResult);
			}

			$run = $this->runMapper->update($run);

			return new DataResponse($run->jsonSerialize());
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Run not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Delete a run
	 *
	 * @param int $id Run ID
	 * @return DataResponse<Http::STATUS_OK, array{deleted: true}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 200: Run deleted
	 * 404: Run not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'DELETE', url: '/api/runs/{id}')]
	public function destroy(int $id): DataResponse {
		try {
			$run = $this->runMapper->find($id);
			$this->runCaseMapper->deleteByRunId($id);
			$this->runMapper->delete($run);

			return new DataResponse(['deleted' => true]);
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Run not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Get cases for a run
	 *
	 * @param int $id Run ID
	 * @return DataResponse<Http::STATUS_OK, array{cases: list<int>}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 200: Cases returned
	 * 404: Run not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/runs/{id}/cases')]
	public function getCases(int $id): DataResponse {
		try {
			$this->runMapper->find($id);
			$runCases = $this->runCaseMapper->findByRunId($id);
			$caseIds = array_map(fn (RunCase $rc) => $rc->getCaseId(), $runCases);

			return new DataResponse(['cases' => $caseIds]);
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Run not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Add a case to a run
	 *
	 * @param int $id Run ID
	 * @param int $caseId Case ID
	 * @return DataResponse<Http::STATUS_CREATED, array{added: true}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 201: Case added
	 * 404: Run or Case not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'POST', url: '/api/runs/{id}/cases')]
	public function addCase(int $id, int $caseId): DataResponse {
		try {
			$this->runMapper->find($id);
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Run not found'],
				Http::STATUS_NOT_FOUND
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

		$runCase = new RunCase();
		$runCase->setRunId($id);
		$runCase->setCaseId($caseId);
		$this->runCaseMapper->insert($runCase);

		return new DataResponse(['added' => true], Http::STATUS_CREATED);
	}
}
