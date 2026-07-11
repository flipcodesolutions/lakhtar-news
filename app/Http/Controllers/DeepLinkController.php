<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class DeepLinkController extends Controller
{
    public function fallback(): Response
    {
        return response()->view('deep-link.fallback', [], Response::HTTP_NOT_FOUND);
    }
}
