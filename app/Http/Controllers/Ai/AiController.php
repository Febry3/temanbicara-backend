<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
class AiController extends Controller
{
    public function generate(Request $request)
    {
        $prompt = $request->input('prompt');

        $response = Http::post(env('GEMINI_API_URL') . '?key=' . env('GEMINI_API_KEY'), [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json([
            'error' => 'Failed to connect to Gemini API',
            'message' => $response->body(),
        ], $response->status());
    }
}
