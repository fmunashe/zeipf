<?php

use App\Http\Controllers\UssdBackendController;
use App\Http\Controllers\UssdController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1/')->group(function () {
    Route::post('zeipf/ussd/live', [UssdController::class, 'index']);
});

Route::prefix('v1/')->group(function () {
    Route::post('zeipf/backend/live', [UssdBackendController::class, 'process'])->name("ZeipfBackendRoute");
});
