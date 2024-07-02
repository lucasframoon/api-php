<?php

declare(strict_types=1);

namespace Src\Controller;

use Src\Util\Util;
use Src\Model\User;
use Src\Helper\HttpRequestHelper;
use Src\Repository\UserRepository;

class UserController extends Controller
{
    public function __construct(
        private UserRepository $repository,
        private HttpRequestHelper $httpRequestHelper
    ) {
    }

    public function register(): array
    {
        $missingParameter = [];
        if (!$name = $_POST['name'] ?? null) {
            $missingParameter[] = 'name';
        }

        if (!$email = $_POST['email'] ?? null) {
            $missingParameter[] = 'email';
        }

        if (!$password = $_POST['password'] ?? null) {
            $missingParameter[] = 'password';
        }

        if (!empty($missingParameter)) {
            return $this->errorResponse($this->getMissingParametersText($missingParameter), 'MISSING_PARAMETERS');
        }

        if (!Util::isValidEMail($email)) {
            return $this->errorResponse('Invalid email', 'INVALID_PARAMETER');
        }

        $user = new User([
            'name'      => $name,
            'email'     => $email,
            'password'  => password_hash($password, PASSWORD_BCRYPT)
        ]);

        try {
            $this->repository->save($user);
            return $this->successResponse(['message' => 'User created successfully']);
        } catch (\Exception $e) {
            if ($e->getMessage() == 'ALREADY_EXISTS') {
                return $this->errorResponse('User already exists', 'ALREADY_EXISTS');
            }
            return $this->errorResponse('Failed to create user', 'ERROR');
        }
    }

    public function getData(array $params): array
    {
        $id = $params['id'] ?? null;
        if (!Util::isValidId($id)) {
            return $this->errorResponse('ID must be an integer', 'INVALID_PARAMETER');
        }

        $data = $this->repository->getData((int)$id, true);
        if ($data) {
            return $this->successResponse(['user' => $data['user'], 'addresses' => $data['addresses']]);
        }

        return $this->successResponse(['user' => [], 'addresses' => []]);
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

        $user = $this->repository->getModel((int)$id, true);
        if (!$user->getId()) {
            return $this->errorResponse('Address not found', 'NOT_FOUND');
        }

        if ($name = $data['name'] ?? null) {
            $user->setName($name);
        }

        if ($email = $data['email'] ?? null) {
            $user->setEmail($email);
        }

        try {
            $this->repository->save($user);
            return $this->successResponse(['message' => 'User updated successfully']);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update user', 'ERROR');
        }
    }

    public function delete(array $params): array
    {
        $id = $params['id'] ?? null;
        if (!Util::isValidId($id)) {
            return $this->errorResponse('ID must be an integer', 'INVALID_PARAMETER');
        }

        $result = $this->repository->delete((int)$id);
        if (!$result) {
            return $this->errorResponse('User not found', 'NOT_FOUND');
        }

        return $this->successResponse(['message' => 'User deleted']);
    }
}
