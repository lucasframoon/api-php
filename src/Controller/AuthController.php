<?php

declare(strict_types=1);

namespace Src\Controller;

use Src\Util\Util;
use Firebase\JWT\JWT;
use Src\Repository\UserRepository;

class AuthController extends Controller
{
    private string $secretKey;

    public function __construct(
        private UserRepository $repository
    ) {
        $this->secretKey = $_ENV['SECRET_KEY'];
    }

    public function login(): array
    {
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$email || !$password || !Util::isValidEMail($email)) {
            return $this->errorResponse(['message' => 'Invalid credentials']);
        }

        $user = $this->repository->findUserByEmail($email);
        if (!$user->getId() || !password_verify($password, $user->getPassword())) {
            return $this->errorResponse(['message' => 'Invalid email or password']);
        } else {
            $payload = [
                'iss' => 'your_domain.com',
                'iat' => time(),
                // 'exp' => time() + (60 * 60), // 1 hour expiration TODO ACTIVE
                'sub' => $user->getid()
            ];

            $jwt = JWT::encode($payload, $this->secretKey, 'HS256');

            return $this->successResponse(['token' => $jwt]);
        }
    }
}
