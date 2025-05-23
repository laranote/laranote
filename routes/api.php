<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GeminiAIController;
use App\Http\Controllers\Api\UserCanEditController;
use App\Http\Controllers\Api\PostIsPublicController;
use App\Http\Controllers\Api\FalAIController;

Route::post('/user/can-edit', UserCanEditController::class);
Route::post('/posts/is-public', PostIsPublicController::class);
Route::post('/gemini-ai/generate', [GeminiAIController::class, 'generate']);
Route::match(['get', 'post'], '/fal-ai/proxy', [FalAIController::class, 'proxy']);
