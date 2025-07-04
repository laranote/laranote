<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OpenRouterAIRequest;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class OpenRouterAIController extends Controller
{
    /**
     * Generate content using OpenRouter AI
     *
     * @param OpenRouterAIRequest $request
     * @return JsonResponse
     */
    public function generate(OpenRouterAIRequest $request): JsonResponse
    {
        $project = Project::first();
        $apiKey = $project->openrouter_api_key;

        // Check if API key is not set
        if (!$apiKey) {
            \Log::warning('No OpenRouter API key configured');

            return response()->json([
                'error' => 'OpenRouter API key not configured',
                'errorCode' => 'API_KEY_MISSING',
                'message' => 'Please add your OpenRouter API key in the project settings.'
            ], 500);
        }

        try {
            $finalPrompt = $request->prompt;
            $temperature = 0.7;
            $model = $request->model ?? 'anthropic/claude-3.5-sonnet'; // Default model

            \Log::info('Sending request to OpenRouter API', [
                'prompt' => $finalPrompt,
                'type' => $request->type,
                'model' => $model
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name'),
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $finalPrompt
                    ]
                ],
                'temperature' => $temperature,
                'max_tokens' => 2048,
                'top_p' => 0.95,
            ]);

            \Log::info('OpenRouter API raw response', ['response' => $response->body()]);

            if ($response->successful()) {
                $data = $response->json();

                \Log::info('OpenRouter API parsed response', ['data' => $data]);

                // Extract the text from the response
                $text = '';

                if (isset($data['choices'][0]['message']['content'])) {
                    $text = $data['choices'][0]['message']['content'];
                } else {
                    \Log::warning('Unexpected OpenRouter API response structure', ['data' => $data]);
                    $text = 'Received response from OpenRouter but in an unexpected format. Please check the logs.';
                }

                return response()->json(['content' => $text]);
            } else {
                $errorDetails = $response->json();
                \Log::error('OpenRouter API error response', ['error' => $errorDetails]);

                // Extract specific error information
                $errorCode = $errorDetails['error']['code'] ?? $response->status();
                $errorMessage = $errorDetails['error']['message'] ?? 'Unknown error';
                $errorType = $errorDetails['error']['type'] ?? 'ERROR';

                // User-friendly error message based on the error
                $userMessage = 'Failed to get response from OpenRouter AI';

                if ($errorCode == 429) {
                    $userMessage = 'Rate limit exceeded. Please try again later.';
                } elseif ($errorCode == 400) {
                    $userMessage = 'Invalid request to OpenRouter AI. Please check your input.';
                } elseif ($errorCode == 401 || $errorCode == 403) {
                    $userMessage = 'Authentication error with OpenRouter AI. Please check your API key.';
                } elseif ($errorCode == 402) {
                    $userMessage = 'Insufficient credits on your OpenRouter account.';
                }

                return response()->json([
                    'error' => $userMessage,
                    'errorCode' => $errorCode,
                    'errorType' => $errorType,
                    'details' => $errorDetails
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Exception when calling OpenRouter API', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            // User-friendly error message based on the exception
            $userMessage = 'Error connecting to OpenRouter AI';

            if (str_contains($e->getMessage(), 'cURL error 28')) {
                $userMessage = 'Connection timeout when calling OpenRouter AI. The service might be down.';
            } elseif (str_contains($e->getMessage(), 'cURL error 6')) {
                $userMessage = 'Could not resolve host for OpenRouter AI. Please check your internet connection.';
            }

            return response()->json([
                'error' => $userMessage,
                'errorCode' => 'CONNECTION_ERROR',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
