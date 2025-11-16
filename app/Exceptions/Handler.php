<?php

namespace App\Exceptions;

use App\Http\Request;
use App\Http\Response;
use Throwable;

class Handler
{
    protected array $context = [];

    public function __construct(protected ?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new SimpleLogger();
    }

    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function report(Throwable $exception): void
    {
        $context = array_merge($this->context, [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);

        if ($exception instanceof CachedErrorException) {
            $this->logger->info('Serving cached error response', $context);
        } else {
            $this->logger->error('Exception occurred: ' . $exception->getMessage(), $context);
        }
    }

    public function render(Request $request, Throwable $exception): Response
    {
        $this->report($exception);

        if ($this->expectsJson($request)) {
            return $this->renderJsonResponse($exception);
        }

        return $this->renderWebResponse($exception);
    }

    protected function expectsJson(Request $request): bool
    {
        return str_starts_with($request->path(), '/api/') || 
               $request->header('Accept') === 'application/json' ||
               $request->header('Content-Type') === 'application/json';
    }

    protected function renderJsonResponse(Throwable $exception): Response
    {
        $statusCode = $this->getStatusCode($exception);
        $errorCode = $this->getErrorCode($exception);
        $message = $this->getUserMessage($exception);

        return Response::json([
            'status' => 'error',
            'error_code' => $errorCode,
            'message' => $message,
        ], $statusCode);
    }

    protected function renderWebResponse(Throwable $exception): Response
    {
        $message = $this->getUserMessage($exception);

        return Response::redirect('/', [
            'errors' => [
                'general' => $message,
            ],
        ]);
    }

    protected function getStatusCode(Throwable $exception): int
    {
        if (method_exists($exception, 'getStatusCode')) {
            return $exception->getStatusCode();
        }

        if ($exception instanceof ProcurementNotFoundException) {
            return 404;
        }

        if ($exception instanceof ProcurementParseException) {
            return 422;
        }

        if ($exception instanceof DeepSeekException) {
            return 502;
        }

        if ($exception instanceof CachedErrorException) {
            return $exception->getStatusCode();
        }

        if ($exception instanceof \RuntimeException) {
            return 400;
        }

        return 500;
    }

    protected function getErrorCode(Throwable $exception): string
    {
        if ($exception instanceof ProcurementNotFoundException) {
            return 'PROCUREMENT_NOT_FOUND';
        }

        if ($exception instanceof ProcurementParseException) {
            return 'PROCUREMENT_PARSE_ERROR';
        }

        if ($exception instanceof DeepSeekException) {
            return 'AI_SERVICE_ERROR';
        }

        if ($exception instanceof CachedErrorException) {
            return $exception->getErrorData()['error_code'] ?? 'CACHED_ERROR';
        }

        return 'INTERNAL_ERROR';
    }

    protected function getUserMessage(Throwable $exception): string
    {
        if (method_exists($exception, 'getUserMessage')) {
            return $exception->getUserMessage();
        }

        if ($exception instanceof ProcurementNotFoundException) {
            return 'The procurement notice you requested could not be found. Please verify the URL and try again.';
        }

        if ($exception instanceof ProcurementParseException) {
            return 'The procurement notice format is invalid or incomplete. Please ensure the URL points to a valid procurement document.';
        }

        if ($exception instanceof DeepSeekException) {
            return 'The AI analysis service is temporarily unavailable. Please try again later.';
        }

        if ($exception instanceof CachedErrorException) {
            return $exception->getUserMessage();
        }

        if ($exception instanceof \RuntimeException) {
            return $exception->getMessage();
        }

        return 'An unexpected error occurred while processing your request. Please try again later.';
    }
}
