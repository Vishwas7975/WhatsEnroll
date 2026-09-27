<?php

use App\Http\Controllers\Bot\BotController;
use App\Http\Controllers\Payment\PaymentController;
use Illuminate\Support\Facades\Route;

// WhatsApp Webhook
Route::get('/webhook', [BotController::class, 'verify']);
Route::post('/webhook', [BotController::class, 'handle']);

// Razorpay Webhook
Route::post('/razorpay/webhook', [PaymentController::class, 'webhook']);