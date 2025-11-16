<?php

namespace App\Exceptions;

use RuntimeException;

class CachedErrorException extends RuntimeException
{
    protected array $errorData;

    public function __construct(array $errorData, string $message = 'Previous error retrieved from cache.', int $code = 0, ?\Throwable $previous = null)
    {
        $this->errorData = $errorData;
        parent::__construct($message, $code, $previous);
    }

    public function getErrorData(): array
    {
        return $this->errorData;
    }

    public function getStatusCode(): int
    {
        return $this->errorData['status_code'] ?? 500;
    }

    public function getUserMessage(): string
    {
        return $this->errorData['user_message'] ?? 'An error occurred while processing your request.';
    }
}
