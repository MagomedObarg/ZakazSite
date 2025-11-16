<?php

namespace App\Http\Controllers;

use App\Exceptions\Handler;
use App\Http\Request;
use App\Http\Response;
use App\Services\AnalysisService;
use RuntimeException;

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
        $url = trim((string) $request->input('procurement_url', ''));

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return Response::redirect('/', [
                'errors' => [
                    'procurement_url' => 'Please provide a valid procurement notice URL.',
                ],
            ]);
        }

        $requestId = uniqid('req_', true);

        try {
            $this->exceptionHandler->setContext([
                'route' => 'POST /analyze',
                'url' => $url,
                'request_id' => $requestId,
            ]);

            $result = $this->analysis->analyzeUrl($url);

            return Response::redirect(
                '/result/' . $result->id,
                [
                    'status' => 'Analysis completed successfully.',
                    'analysis_id' => $result->id,
                ]
            );
        } catch (\Throwable $exception) {
            return $this->exceptionHandler->render($request, $exception);
        }
    }
}
