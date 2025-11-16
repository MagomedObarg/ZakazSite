<?php

namespace App\Exceptions;

use RuntimeException;

class ProcurementParseException extends RuntimeException
{
    public function __construct(string $message = 'Failed to parse procurement notice.', int $code = 422, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return 422;
    }

    public function getUserMessage(): string
    {
        return 'The procurement notice format is invalid or incomplete. Please ensure the URL points to a valid procurement document.';
    }
}
