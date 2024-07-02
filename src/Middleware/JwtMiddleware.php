<?php

declare(strict_types=1);

namespace Src\Middleware;

use Firebase\JWT\{JWT, Key};

class JwtMiddleware
{
    private string $secretKey;
    private string $algorithm = 'HS256';

    public function __construct()
    {
        $this->secretKey = $_ENV['SECRET_KEY'];
    }

    public function handle(callable $next): void
    {

        // Check if the request contains an Authorization header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if (!$authHeader) {
            http_response_code(401);
            echo json_encode(['status' => 'Unauthorized', 'message' => 'Authorization header not found']);
            exit;
        }

        // Extract the JWT token from the Authorization header
        $token = str_replace('Bearer ', '', $authHeader);
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            $_SESSION['user_id'] = $decoded->sub;
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['status' => 'Unauthorized', 'message' => $e->getMessage()]);
            exit;
        }
        $next();
    }
}
