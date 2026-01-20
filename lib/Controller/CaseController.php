<?php

declare(strict_types=1);

namespace OCA\TestCases\Controller;

use OCA\TestCases\Db\TestCase;
use OCA\TestCases\Db\TestCaseMapper;
use OCA\TestCases\Db\CasePlatform;
use OCA\TestCases\Db\CasePlatformMapper;
use OCA\TestCases\Db\RelatedCase;
use OCA\TestCases\Db\RelatedCaseMapper;
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
class CaseController extends OCSController {
	private TestCaseMapper $testCaseMapper;
	private CasePlatformMapper $casePlatformMapper;
	private RelatedCaseMapper $relatedCaseMapper;
	private PlatformMapper $platformMapper;

	public function __construct(
		string $appName,
		IRequest $request,
		TestCaseMapper $testCaseMapper,
		CasePlatformMapper $casePlatformMapper,
		RelatedCaseMapper $relatedCaseMapper,
		PlatformMapper $platformMapper,
	) {
		parent::__construct($appName, $request);
		$this->testCaseMapper = $testCaseMapper;
		$this->casePlatformMapper = $casePlatformMapper;
		$this->relatedCaseMapper = $relatedCaseMapper;
		$this->platformMapper = $platformMapper;
	}

	/**
	 * Get all cases
	 *
	 * @return DataResponse<Http::STATUS_OK, array{cases: list<array{id: int, caseNumber: int, name: string, description: ?string}>}, array{}>
	 *
	 * 200: Cases returned
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/cases')]
	public function index(): DataResponse {
		$cases = $this->testCaseMapper->findAll();
		return new DataResponse([
			'cases' => array_map(fn (TestCase $c) => $c->jsonSerialize(), $cases),
		]);
	}

	/**
	 * Get a single case
	 *
	 * @param int $id Case ID
	 * @return DataResponse<Http::STATUS_OK, array{id: int, caseNumber: int, name: string, description: ?string}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 200: Case returned
	 * 404: Case not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/cases/{id}')]
	public function show(int $id): DataResponse {
		try {
			$case = $this->testCaseMapper->find($id);
			return new DataResponse($case->jsonSerialize());
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Case not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Create a new case
	 *
	 * @param int $caseNumber Case number
	 * @param string $name Case name
	 * @param list<int> $platformIds Platform IDs (at least one required)
	 * @param string|null $description Case description
	 * @return DataResponse<Http::STATUS_CREATED, array{id: int, caseNumber: int, name: string, description: ?string}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: string}, array{}>
	 *
	 * 201: Case created
	 * 400: Bad request
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'POST', url: '/api/cases')]
	public function create(int $caseNumber, string $name, array $platformIds, ?string $description = null): DataResponse {
		if (empty(trim($name))) {
			return new DataResponse(
				['error' => 'Name is required'],
				Http::STATUS_BAD_REQUEST
			);
		}

		if (empty($platformIds)) {
			return new DataResponse(
				['error' => 'At least one platform is required'],
				Http::STATUS_BAD_REQUEST
			);
		}

		// Verify all platforms exist
		foreach ($platformIds as $platformId) {
			try {
				$this->platformMapper->find($platformId);
			} catch (DoesNotExistException) {
				return new DataResponse(
					['error' => 'Platform not found: ' . $platformId],
					Http::STATUS_BAD_REQUEST
				);
			}
		}

		$case = new TestCase();
		$case->setCaseNumber($caseNumber);
		$case->setName($name);
		$case->setDescription($description);
		$case = $this->testCaseMapper->insert($case);

		// Add platform associations
		foreach ($platformIds as $platformId) {
			$casePlatform = new CasePlatform();
			$casePlatform->setCaseId($case->getId());
			$casePlatform->setPlatformId($platformId);
			$this->casePlatformMapper->insert($casePlatform);
		}

		return new DataResponse($case->jsonSerialize(), Http::STATUS_CREATED);
	}

	/**
	 * Update a case
	 *
	 * @param int $id Case ID
	 * @param int $caseNumber Case number
	 * @param string $name Case name
	 * @param string|null $description Case description
	 * @return DataResponse<Http::STATUS_OK, array{id: int, caseNumber: int, name: string, description: ?string}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: string}, array{}>
	 *
	 * 200: Case updated
	 * 404: Case not found
	 * 400: Bad request
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'PUT', url: '/api/cases/{id}')]
	public function update(int $id, int $caseNumber, string $name, ?string $description = null): DataResponse {
		if (empty(trim($name))) {
			return new DataResponse(
				['error' => 'Name is required'],
				Http::STATUS_BAD_REQUEST
			);
		}

		try {
			$case = $this->testCaseMapper->find($id);
			$case->setCaseNumber($caseNumber);
			$case->setName($name);
			$case->setDescription($description);
			$case = $this->testCaseMapper->update($case);

			return new DataResponse($case->jsonSerialize());
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Case not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Delete a case
	 *
	 * @param int $id Case ID
	 * @return DataResponse<Http::STATUS_OK, array{deleted: true}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 200: Case deleted
	 * 404: Case not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'DELETE', url: '/api/cases/{id}')]
	public function destroy(int $id): DataResponse {
		try {
			$case = $this->testCaseMapper->find($id);
			$this->casePlatformMapper->deleteByCaseId($id);
			$this->relatedCaseMapper->deleteByCaseId($id);
			$this->testCaseMapper->delete($case);

			return new DataResponse(['deleted' => true]);
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Case not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Get platforms for a case
	 *
	 * @param int $id Case ID
	 * @return DataResponse<Http::STATUS_OK, array{platforms: list<int>}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 200: Platforms returned
	 * 404: Case not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/cases/{id}/platforms')]
	public function getPlatforms(int $id): DataResponse {
		try {
			$this->testCaseMapper->find($id);
			$casePlatforms = $this->casePlatformMapper->findByCaseId($id);
			$platformIds = array_map(fn (CasePlatform $cp) => $cp->getPlatformId(), $casePlatforms);

			return new DataResponse(['platforms' => $platformIds]);
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Case not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Set platforms for a case
	 *
	 * @param int $id Case ID
	 * @param list<int> $platformIds Platform IDs
	 * @return DataResponse<Http::STATUS_OK, array{platforms: list<int>}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: string}, array{}>
	 *
	 * 200: Platforms set
	 * 404: Case not found
	 * 400: Bad request
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'PUT', url: '/api/cases/{id}/platforms')]
	public function setPlatforms(int $id, array $platformIds): DataResponse {
		if (empty($platformIds)) {
			return new DataResponse(
				['error' => 'At least one platform is required'],
				Http::STATUS_BAD_REQUEST
			);
		}

		try {
			$this->testCaseMapper->find($id);
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Case not found'],
				Http::STATUS_NOT_FOUND
			);
		}

		// Verify all platforms exist
		foreach ($platformIds as $platformId) {
			try {
				$this->platformMapper->find($platformId);
			} catch (DoesNotExistException) {
				return new DataResponse(
					['error' => 'Platform not found: ' . $platformId],
					Http::STATUS_BAD_REQUEST
				);
			}
		}

		// Remove existing and add new
		$this->casePlatformMapper->deleteByCaseId($id);
		foreach ($platformIds as $platformId) {
			$casePlatform = new CasePlatform();
			$casePlatform->setCaseId($id);
			$casePlatform->setPlatformId($platformId);
			$this->casePlatformMapper->insert($casePlatform);
		}

		return new DataResponse(['platforms' => $platformIds]);
	}

	/**
	 * Get related cases
	 *
	 * @param int $id Case ID
	 * @return DataResponse<Http::STATUS_OK, array{relatedCases: list<int>}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>
	 *
	 * 200: Related cases returned
	 * 404: Case not found
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'GET', url: '/api/cases/{id}/related')]
	public function getRelated(int $id): DataResponse {
		try {
			$this->testCaseMapper->find($id);
			$relatedCases = $this->relatedCaseMapper->findByCaseId($id);
			$relatedIds = array_map(fn (RelatedCase $rc) => $rc->getRelatedCaseId(), $relatedCases);

			return new DataResponse(['relatedCases' => $relatedIds]);
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Case not found'],
				Http::STATUS_NOT_FOUND
			);
		}
	}

	/**
	 * Set related cases
	 *
	 * @param int $id Case ID
	 * @param list<int> $relatedCaseIds Related case IDs
	 * @return DataResponse<Http::STATUS_OK, array{relatedCases: list<int>}, array{}>|DataResponse<Http::STATUS_NOT_FOUND, array{error: string}, array{}>|DataResponse<Http::STATUS_BAD_REQUEST, array{error: string}, array{}>
	 *
	 * 200: Related cases set
	 * 404: Case not found
	 * 400: Bad request
	 */
	#[NoAdminRequired]
	#[ApiRoute(verb: 'PUT', url: '/api/cases/{id}/related')]
	public function setRelated(int $id, array $relatedCaseIds): DataResponse {
		try {
			$this->testCaseMapper->find($id);
		} catch (DoesNotExistException) {
			return new DataResponse(
				['error' => 'Case not found'],
				Http::STATUS_NOT_FOUND
			);
		}

		// Verify all related cases exist
		foreach ($relatedCaseIds as $relatedCaseId) {
			if ($relatedCaseId === $id) {
				return new DataResponse(
					['error' => 'Case cannot be related to itself'],
					Http::STATUS_BAD_REQUEST
				);
			}
			try {
				$this->testCaseMapper->find($relatedCaseId);
			} catch (DoesNotExistException) {
				return new DataResponse(
					['error' => 'Related case not found: ' . $relatedCaseId],
					Http::STATUS_BAD_REQUEST
				);
			}
		}

		// Remove existing and add new
		$this->relatedCaseMapper->deleteByCaseId($id);
		foreach ($relatedCaseIds as $relatedCaseId) {
			$relatedCase = new RelatedCase();
			$relatedCase->setCaseId($id);
			$relatedCase->setRelatedCaseId($relatedCaseId);
			$this->relatedCaseMapper->insert($relatedCase);
		}

		return new DataResponse(['relatedCases' => $relatedCaseIds]);
	}
}
