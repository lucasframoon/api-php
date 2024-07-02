<?php

declare(strict_types=1);

namespace Src\Controller;

use Src\Util\Util;
use Src\Helper\HttpRequestHelper;
use Src\Repository\AddressRepository;

class AddressController extends Controller
{
    public function __construct(
        private AddressRepository $repository,
        private HttpRequestHelper $httpRequestHelper
    ) {
    }

    public function new(): array
    {
        $missingParameter = [];

        // If user_id is provided in the request, use it instead of the current authenticated user
        $userId = $_POST['user_id'] ?? ($_SESSION['user_id'] ?? null);

        if (!$userId) {
            return $this->errorResponse('Authentication required', 'UNAUTHORIZED', 401);
        }

        if (!Util::isValidId($userId)) {
            return $this->errorResponse('User id must be an integer', 'INVALID_PARAMETER');
        }

        if (!$street = $_POST['street'] ?? null) {
            $missingParameter[] =  'street';
        }

        if (!$city = $_POST['city'] ?? null) {
            $missingParameter[] =  'city';
        }

        if (!$state = $_POST['state'] ?? null) {
            $missingParameter[] =  'state';
        }

        if (!$postalCode = $_POST['postal_code'] ?? null) {
            $missingParameter[] =  'postal_code';
        }

        if (!$country = $_POST['country'] ?? null) {
            $missingParameter[] =  'country';
        }

        if (!empty($missingParameter)) {
            return $this->errorResponse($this->getMissingParametersText($missingParameter), 'MISSING_PARAMETERS');
        }

        return $this->repository->save([
            'user_id'       => $userId,
            'street'        => $street,
            'city'          => $city,
            'state'         => $state,
            'postal_code'   => $postalCode,
            'country'       => $country
        ]);
    }

    public function getData(array $params): array
    {
        $id = $params['id'] ?? null;
        // If user_id is provided in the request, use it instead of the current authenticated user
        $userId = $params['user_id'] ?? ($_SESSION['user_id'] ?? null);

        if (!$userId) {
            return $this->errorResponse('Authentication required', 'UNAUTHORIZED', 401);
        }

        if (!Util::isValidId($id)) {
            return $this->errorResponse('ID must be an integer', 'INVALID_PARAMETER');
        }

        if (!Util::isValidId($userId)) {
            return $this->errorResponse('User id must be an integer', 'INVALID_PARAMETER');
        }

        if ($addressData = $this->repository->getData((int)$id, (int)$userId)) {
            return $this->successResponse(['address' => $addressData]);
        }

        return $this->successResponse(['address' => []]);
    }

    /**
     * Retrieves the addresses of the current user authenticated
     * Optional query parameters are 'street', 'city', 'state', 'postal_code' and 'country'
     *
     * @return array
     */
    public function getUserAddresses(): array
    {
        $where = [];
        // If user_id is provided in the request, use it instead of the current authenticated user
        $userId = $_GET['user_id'] ?? ($_SESSION['user_id'] ?? null);

        if (!$userId) {
            return $this->errorResponse('Authentication required', 'UNAUTHORIZED', 401);
        }

        //Optional parameters
        if ($street = $_GET['street'] ?? null) {
            $where['street'] = $street;
        }

        if ($city = $_GET['city'] ?? null) {
            $where['city'] = $city;
        }

        if ($state = $_GET['state'] ?? null) {
            $where['state'] = $state;
        }

        if ($postalCode = $_GET['postalcode'] ?? null) {
            $where['postal_code'] = $postalCode;
        }

        if ($country = $_GET['country'] ?? null) {
            $where['country'] = $country;
        }

        $addressData = $this->repository->findByUserId((int)$userId, $where);
        return $this->successResponse(['addresses' => $addressData ?? []]);
    }

    public function update(array $params): array
    {
        $id = $params['id'] ?? null;
        if (!Util::isValidId($id)) {
            return $this->errorResponse('ID must be an integer', 'INVALID_PARAMETER');
        }

        $data = $this->httpRequestHelper->getInputStreamParams('PUT');
        if (!$data) {
            return $this->errorResponse('Invalid JSON', 'INVALID_PARAMETER');
        }

        $update = [];
        if ($userId = $_SESSION['user_id'] ?? null) {
            $update['user_id'] = $userId;
        }

        if ($street = $data['street'] ?? null) {
            $update['street'] = $street;
        }

        if ($city = $data['city'] ?? null) {
            $update['city'] = $city;
        }

        if ($state = $data['state'] ?? null) {
            $update['state'] = $state;
        }

        if ($postalCode = $data['postal_code'] ?? null) {
            $update['postal_code'] = $postalCode;
        }

        if ($country = $data['country'] ?? null) {
            $update['country'] = $country;
        }

        return $this->repository->save($update, (int)$id);
    }

    public function delete(array $params): array
    {
        $id = $params['id'] ?? null;
        if (!Util::isValidId($id)) {
            return $this->errorResponse('ID must be an integer', 'INVALID_PARAMETER');
        }

        $result = $this->repository->delete((int)$id);
        if (!$result) {
            return $this->errorResponse('Address not found', 'NOT_FOUND');
        }

        return $this->successResponse(['message' => 'Address deleted']);
    }
}
