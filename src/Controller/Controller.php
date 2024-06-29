<?php

declare(strict_types=1);

namespace Src\Controller;

class Controller
{
    /**
     * Retrieves the parameters from the input stream
     *
     * @param string $method The HTTP method
     * @return array|null The decoded parameters or null if the decoding fails
     */
    public static function getInputStreamParams(string $method): ?array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $data;
    }

    /**
     * Returns a JSON-encoded success response with the given data and an optional message
     *
     * @param array $data An optional array of data to include in the response
     * @return array
     */
    public static function successResponse(array $data = []): array
    {
        $response = ['status' => 'success'];

        foreach ($data as $key => $value) {
            $response[$key] = $value;
        }

        return $response;
    }

    /**
     * Returns a JSON-encoded error response with the given data and an optional message
     *
     * @param array $data An optional array of data to include in the response
     * @param string $message An optional error message to include in the response
     * @return array
     */
    public function errorResponse(array $data = [], string $message = ''): array
    {
        http_response_code(400);
        $response = ['status' => 'error'];
        $response['message'] = $message;

        foreach ($data as $key => $value) {
            $response[$key] = $value;
        }

        return $response;
    }
}
