<?php

declare(strict_types=1);

namespace Src\Helper;

class HttpRequestHelper
{
    /**
     * Retrieves the parameters from the input stream
     *
     * @param string $method The HTTP method
     * @return array|null The decoded parameters or null if the decoding fails
     */
    public function getInputStreamParams(string $method): ?array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $data;
    }
}
