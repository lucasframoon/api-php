<?php

namespace Src\Controller;

use Src\Repository\UserRepository;
use Src\Util\Util;

class UserController extends Controller
{
    public function __construct(
        private UserRepository $repository
    ) {
    }

    public function login(): array
    {
        return $this->successResponse(['token' => 'TODO TOKEN']);
    }

    public function register(): array
    {

        if (!$name = $_POST['name'] ?? null) {
            return $this->errorResponse(['message' => 'missing parameter: name']);
        }

        if (!$email = $_POST['email'] ?? null) {
            return $this->errorResponse(['message' => 'missing parameter: email']);
        }

        if (!Util::isValidEMail($email)) {
            return $this->errorResponse(['message' => 'invalid email']);
        }

        if (!$password = $_POST['password'] ?? null) {
            return $this->errorResponse(['message' => 'missing parameter: password']);
        }

        return $this->repository->save([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'id' => null
        ]);
    }

    public function getData(array $params): array
    {
        $id = $params['id'] ?? null;
        if (!Util::isValidId($id)) {
            return $this->errorResponse(['message' => 'ID must be an integer']);
        }

        if ($userData = $this->repository->getData((int)$id)) {
            return $this->successResponse(['user' => $userData]);
        }

        return $this->errorResponse(['message' => 'User not found']);
    }

    public function update(array $params): array
    {
        $id = $params['id'] ?? null;
        if (!Util::isValidId($id)) {
            return $this->errorResponse(['message' => 'ID must be an integer']);
        }

        $update = [];

        $data = $this->getInputStreamParams('PUT');
        if (!$data) {
            return $this->errorResponse(['message' => 'Invalid JSON']);
        }

        if ($name = $data['name'] ?? null) {
            $update['name'] = $name;
        }

        if ($email = $data['email'] ?? null) {
            $update['email'] = $email;
        }

        return $this->repository->save($update, (int)$id);
    }

    public function delete(): array
    {
        $data = $this->getInputStreamParams('DELETE');
        if (!$data) {
            return $this->errorResponse(['message' => 'Invalid JSON']);
        }

        $id = $data['id'] ?? null;
        if (!Util::isValidId($id)) {
            return $this->errorResponse(['message' => 'ID must be an integer']);
        }

        $result = $this->repository->delete((int)$id);
        if (!$result) {
            return $this->errorResponse(['message' => 'User not found']);
        }

        return $this->successResponse(['message' => 'User deleted']);
    }
}
