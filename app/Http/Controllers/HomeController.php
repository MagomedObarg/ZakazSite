<?php

namespace App\Http\Controllers;

use App\Http\Request;
use App\Http\Response;

class HomeController
{
    public function __invoke(Request $request): Response
    {
        return Response::view('home', [
            'title' => 'Procurement Insight Analyzer',
            'form' => [
                'procurement_url' => '',
            ],
            'flash' => $request->session()->peekFlash(),
        ]);
    }
}
