<?php

namespace App\Http\Controllers;

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
        $url = trim((string) $request->input('procurement_url', ''));

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return Response::redirect('/', [
                'errors' => [
                    'procurement_url' => 'Please provide a valid procurement notice URL.',
                ],
            ]);
        }

        try {
            $result = $this->analysis->analyzeUrl($url);

            return Response::redirect(
                '/result/' . $result->id,
                [
                    'status' => 'Analysis completed successfully.',
                    'analysis_id' => $result->id,
                ]
            );
        } catch (RuntimeException $exception) {
            return Response::redirect('/', [
                'errors' => [
                    'general' => $exception->getMessage(),
                ],
            ]);
        } catch (\Throwable $exception) {
            return Response::redirect('/', [
                'errors' => [
                    'general' => 'We were unable to analyze the procurement notice. Please try again later.',
                ],
            ])->withFlash('exception', $exception->getMessage());
        }
    }
}
