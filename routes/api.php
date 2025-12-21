<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('orders')->group(function () {
  Route::get('/', [OrderController::class, 'index']);
  Route::post('/', [OrderController::class, 'store']);
  Route::get('{order_number}', [OrderController::class, 'show']);
  Route::put('{order_number}', [OrderController::class, 'update']);
  Route::delete('{order_number}', [OrderController::class, 'destroy']);
});
