<?php

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\AnalysisService;

class ResultController
{
    public function __construct(protected AnalysisService $analysis)
    {
    }

    public function show(Request $request, string $id): Response
    {
        $result = $this->analysis->find($id);

        if (! $result) {
            return Response::view('result.missing', [
                'title' => 'Result unavailable',
                'message' => 'We could not find that analysis result. It may have expired.',
            ], 404);
        }

        return Response::view('result.show', [
            'title' => 'Procurement Analysis Result',
            'result' => $result->toArray(),
            'flash' => $request->session()->peekFlash(),
        ]);
    }
}
