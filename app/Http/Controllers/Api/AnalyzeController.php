<?php

namespace App\Http\Controllers\Api;

use App\Http\Request;
use App\Http\Response;
use App\Services\AnalysisService;
use RuntimeException;

class AnalyzeController
{
    public function __construct(protected AnalysisService $analysis)
    {
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
            $result = $this->analysis->analyzeUrl($url);

            return Response::json([
                'status' => 'ok',
                'data' => $result->toArray(),
            ], 200);
        } catch (RuntimeException $exception) {
            return Response::json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], 400);
        } catch (\Throwable $exception) {
            return Response::json([
                'status' => 'error',
                'message' => 'Unable to process the procurement notice at this time.',
            ], 500);
        }
    }
}
