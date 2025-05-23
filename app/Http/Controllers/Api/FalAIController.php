<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FalAIController extends Controller
{

    /**
     * Proxy endpoint for Fal.ai API requests
     * This follows the Fal.ai proxy implementation guidelines
     *
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function proxy(Request $request)
    {
        $apiKey = config('services.fal_ai.key');

        // Check if API key is not set
        if (!$apiKey) {
            Log::warning('No FalAI API key configured for proxy');
            return response()->json([
                'error' => 'FalAI API key not configured',
                'message' => 'Please add your FalAI API key to the .env file (FAL_AI_API_KEY=your_key_here)'
            ], 500);
        }

        // Check for target URL header
        $targetUrl = $request->header('x-fal-target-url');
        if (!$targetUrl) {
            return response()->json([
                'error' => 'Missing target URL',
                'message' => 'The x-fal-target-url header is required'
            ], 400);
        }

        // Validate target URL is within fal.ai or fal.run domains
        if (!preg_match('/^https?:\/\/[^\/]*(\.fal\.ai|\.fal\.run)(\/.*)?$/', $targetUrl)) {
            return response()->json([
                'error' => 'Invalid target URL',
                'message' => 'The target URL must be within the fal.ai or fal.run domains'
            ], 412);
        }

        if ($request->method() === 'POST' &&
            $request->header('Content-Type') !== 'application/json' &&
            !str_contains($request->header('Content-Type') ?? '', 'application/json')) {
            return response()->json([
                'error' => 'Invalid content type',
                'message' => 'Only application/json content type is supported'
            ], 415);
        }

        try {
            $httpClient = Http::withHeaders([
                'Authorization' => 'Key ' . $apiKey,
                'Content-Type' => 'application/json'
            ]);

            $method = strtolower($request->method());

            if ($method === 'get') {
                $response = $httpClient->get($targetUrl);
            } elseif ($method === 'post') {
                $response = $httpClient->post($targetUrl, $request->json()->all());
            } else {
                return response()->json([
                    'error' => 'Method not allowed',
                    'message' => 'Only GET and POST methods are supported'
                ], 405);
            }

            return response()->json($response->json(), $response->status());

        } catch (\Exception $e) {
            Log::error('Exception in Fal.ai proxy', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'target_url' => $targetUrl
            ]);

            return response()->json([
                'error' => 'Proxy error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
