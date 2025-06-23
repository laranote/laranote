<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GeminiAIRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class GeminiAIController extends Controller
{
    /**
     * Generate content using Gemini AI
     *
     * @param GeminiAIRequest $request
     * @return JsonResponse
     */
    public function generate(GeminiAIRequest $request): JsonResponse
    {

        $apiKey = config('services.gemini.key');

        // Check if API key is not set
        if (!$apiKey) {
            \Log::warning('No Gemini API key configured');

            // Return an error response that will trigger the SweetAlert
            return response()->json([
                'error' => 'Gemini API key not configured',
                'errorCode' => 'API_KEY_MISSING',
                'message' => 'Please add your Gemini API key to the .env file (GEMINI_API_KEY=your_key_here)'
            ], 500);
        }

        try {
            // Prepare the prompt based on the operation type
            $finalPrompt = $request->prompt;
            $temperature = 0.7;

            // Adjust temperature based on operation type
            if ($request->type === 'translate') {
                $temperature = 0.3;

                if (!str_contains($finalPrompt, 'Only return the translated text')) {
                    $finalPrompt .= " Only return the translated text without explanations or notes.";
                }
            } elseif ($request->type === 'summarize') {
                $temperature = 0.5; // Moderate temperature for summaries
            }

            // Log the request for debugging
            \Log::info('Sending request to Gemini API', [
                'prompt' => $finalPrompt,
                'type' => $request->type
            ]);

            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    'parts' => [
                        [
                            'text' => $finalPrompt
                        ]
                    ]
                ],
                'safetySettings' => [
                    [
                        'category' => 'HARM_CATEGORY_HARASSMENT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ]
                ],
                'generationConfig' => [
                    'temperature' => $temperature,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 2048
                ]
            ]);

            \Log::info('Gemini API raw response', ['response' => $response->body()]);

            if ($response->successful()) {
                $data = $response->json();

                \Log::info('Gemini API parsed response', ['data' => $data]);

                // Extract the text from the response
                $text = '';

                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $text = $data['candidates'][0]['content']['parts'][0]['text'];
                } elseif (isset($data['candidates'][0]['text'])) {
                    $text = $data['candidates'][0]['text'];
                } elseif (isset($data['text'])) {
                    $text = $data['text'];
                } else {
                    \Log::warning('Unexpected Gemini API response structure', ['data' => $data]);
                    $text = 'Received response from Gemini but in an unexpected format. Please check the logs.';
                }

                if ($request->type === 'translate') {
                    $paragraphs = explode("\n\n", $text);
                    if (count($paragraphs) > 1) {
                        $text = trim($paragraphs[0]);
                    }

                    $text = preg_replace('/^(Translation:\s*)/i', '', $text);

                    $text = trim($text, '"\'');
                }

                return response()->json(['content' => $text]);
            } else {
                $errorDetails = $response->json();
                \Log::error('Gemini API error response', ['error' => $errorDetails]);

                // Extract specific error information
                $errorCode = $errorDetails['error']['code'] ?? 500;
                $errorMessage = $errorDetails['error']['message'] ?? 'Unknown error';
                $errorStatus = $errorDetails['error']['status'] ?? 'ERROR';

                // Create a user-friendly error message based on the error
                $userMessage = 'Failed to get response from Gemini AI';

                if ($errorCode == 503 && str_contains($errorMessage, 'overloaded')) {
                    $userMessage = 'The Gemini AI service is currently overloaded. Please try again later.';
                } elseif ($errorCode == 400) {
                    $userMessage = 'Invalid request to Gemini AI. Please check your input.';
                } elseif ($errorCode == 401 || $errorCode == 403) {
                    $userMessage = 'Authentication error with Gemini AI. Please check your API key.';
                }

                return response()->json([
                    'error' => $userMessage,
                    'errorCode' => $errorCode,
                    'errorStatus' => $errorStatus,
                    'details' => $errorDetails
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Exception when calling Gemini API', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            // Create a user-friendly error message based on the exception
            $userMessage = 'Error connecting to Gemini AI';

            if (str_contains($e->getMessage(), 'cURL error 28')) {
                $userMessage = 'Connection timeout when calling Gemini AI. The service might be down.';
            } elseif (str_contains($e->getMessage(), 'cURL error 6')) {
                $userMessage = 'Could not resolve host for Gemini AI. Please check your internet connection.';
            }

            return response()->json([
                'error' => $userMessage,
                'errorCode' => 'CONNECTION_ERROR',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
