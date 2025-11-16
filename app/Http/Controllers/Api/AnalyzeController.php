<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Handler;
use App\Http\Request;
use App\Http\Response;
use App\Services\AnalysisService;

class AnalyzeController
{
    public function __construct(
        protected AnalysisService $analysis,
        protected ?Handler $exceptionHandler = null
    ) {
        $this->exceptionHandler = $exceptionHandler ?? new Handler();
    }

    public function __invoke(Request $request): Response
    {
        $url = trim((string) ($request->json()['procurement_url'] ?? $request->input('procurement_url', '')));

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return Response::json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'procurement_url' => ['The procurement_url field must be a valid URL.'],
                ],
            ], 422);
        }

        try {
            $this->exceptionHandler->setContext([
                'route' => 'POST /api/analyze',
                'url' => $url,
                'request_id' => uniqid('req_', true),
            ]);

            $result = $this->analysis->analyzeUrl($url);

            return Response::json([
                'status' => 'ok',
                'data' => $result->toArray(),
            ], 200);
        } catch (\Throwable $exception) {
            return $this->exceptionHandler->render($request, $exception);
        }
    }
}
