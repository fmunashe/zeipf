<?php

use App\Http\Controllers\UssdController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::post('ewz/test', [UssdController::class, 'index']);
});
Route::prefix('v1/')->group(function () {
    Route::post('ewz/live', [UssdController::class, 'index']);
});
