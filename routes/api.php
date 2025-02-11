<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/generate-report', [ReportController::class, 'generate']);
    Route::get('/get-report/{report}', [ReportController::class, 'show']);
    Route::get('/list-reports', [ReportController::class, 'index']);
});
