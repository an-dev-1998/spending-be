<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SpendingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AnalyticController;
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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'getUser']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/change-password', [AuthController::class, 'changePassword']);
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::post('/create-user', [UserController::class, 'createUser']);
    Route::post('/update-user', [UserController::class, 'updateUser']);
    Route::delete('/delete-user', [UserController::class, 'deleteUser']);
    // Spending routes
    Route::apiResource('spendings', SpendingController::class);
    // Category routes
    Route::apiResource('categories', CategoryController::class);
    // Analytics routes
    Route::get('/analytics/spending-by-category', [AnalyticController::class, 'spendingByCategory']);
    Route::get('/analytics/spending-trends', [AnalyticController::class, 'spendingTrends']);
    Route::get('/analytics/top-categories', [AnalyticController::class, 'topCategories']);
    Route::get('/analytics/summary', [AnalyticController::class, 'summary']);
});

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
