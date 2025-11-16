<?php

namespace App\Exceptions;

use RuntimeException;

class DeepSeekException extends RuntimeException
{
    public function __construct(string $message = 'AI analysis service unavailable.', int $code = 502, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return 502;
    }

    public function getUserMessage(): string
    {
        return 'The AI analysis service is temporarily unavailable. Please try again later.';
    }
}
