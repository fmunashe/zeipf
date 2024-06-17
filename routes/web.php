<?php

use App\Http\Controllers\UssdBackendController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('v1/')->group(function () {
    Route::post('zeipf/backend/live', [UssdBackendController::class, 'DataProcessing'])->name("ZeipfBackendRoute");
});
