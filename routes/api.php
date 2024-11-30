<?php

use App\Http\Controllers\OrderManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::any('/stripe/webhook', [OrderManager::class, 'webhookStripe']);
