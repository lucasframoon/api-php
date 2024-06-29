<?php

namespace Src\Controller;

use Src\Repository\UserRepository;

class UserController extends Controller
{
    public function __construct(
        private UserRepository $repository
    ) {
    }

    public function login(): array
    {
        return ['status' => 'success', 'token' => 'TODO TOKEN'];
    }

    public function register(): array
    {

        if (!$name = $_POST['name'] ?? null) {
            return ['status' => 'error', 'message' => 'missing parameter: name'];
        }

        if (!$email = $_POST['email'] ?? null) {
            return ['status' => 'error', 'message' => 'missing parameter: email'];
        }

        if (!$password = $_POST['password'] ?? null) {
            return ['status' => 'error', 'message' => 'missing parameter: password'];
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
        if (!is_numeric($id) && ctype_digit($id)) {
            return ['status' => 'error', 'message' => 'ID must be an integer'];
        }

        if ($userData = $this->repository->getData((int)$id)) {
            return ['status' => 'success', 'user' => $userData];
        }

        return ['status' => 'error', 'message' => 'User not found'];
    }

    public function update(array $params): array
    {
        $id = $params['id'] ?? null;
        if (!is_numeric($id) && ctype_digit($id)) {
            return ['status' => 'error', 'message' => 'ID must be an integer'];
        }

        $update = [];

        $data = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            return ['status' => 'error', 'message' => 'Invalid JSON'];
        }

        if ($name = $data['name'] ?? null) {
            $update['name'] = $name;
        }

        if ($email = $data['email'] ?? null) {
            $update['email'] = $email;
        }

        return $this->repository->save($update, (int)$id);
    }

    //TODO
    public function delete(): array
    {
        return ['delete'];
        // $this->repository->delete();
    }
}
