<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIChatController;

Route::post('/ai/chat', [AIChatController::class, 'handle']);
