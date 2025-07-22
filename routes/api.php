<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\SubTaskController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        //oauth route
        Route::get('/oauth/google', [AuthController::class, 'oAuthUrl']);
        Route::get('/oauth/google/callback', [AuthController::class, 'oAuthCallback']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::apiResource('tasks', TaskController::class);
        });
    });

    // Public plans
    Route::get('plans', [PlanController::class, 'index']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        // Tasks
        Route::apiResource('tasks', TaskController::class)->only(['index', 'store', 'show', 'destroy']);
        Route::post('tasks/{id}', [TaskController::class, 'update']);
        Route::post('/subtasks/change-status', [SubtaskController::class, 'changeStatus']);
        Route::apiResource('subtasks', SubtaskController::class)->only(['index', 'store', 'destroy']);
        Route::post('subtasks', [SubtaskController::class, 'store']); // CREATE
        Route::post('subtasks/{id}', [SubtaskController::class, 'update']); // UPDATE
        Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show', 'destroy']);
        Route::apiResource('payments', PaymentController::class)->only(['index', 'store', 'show']);
    });
    Route::post('/payments/callback', [PaymentController::class, 'callback']);
});
