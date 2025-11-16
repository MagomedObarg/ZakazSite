<?php

namespace App\Exceptions;

use RuntimeException;

class ProcurementNotFoundException extends RuntimeException
{
    public function __construct(string $message = 'Procurement notice could not be found.', int $code = 404, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return 404;
    }

    public function getUserMessage(): string
    {
        return 'The procurement notice you requested could not be found. Please verify the URL and try again.';
    }
}
