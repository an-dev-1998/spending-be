<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SpendingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AnalyticController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\NotificationSpendingController;
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
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/change-password', [AuthController::class, 'changePassword']);

    Route::get('/user', [UserController::class, 'getUser']);
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::post('/create-user', [UserController::class, 'createUser']);
    Route::post('/update-user', [UserController::class, 'updateUser']);
    Route::delete('/delete-user', [UserController::class, 'deleteUser']);
    Route::post('/upload', [UserController::class, 'upload']);

    Route::apiResource('spendings', SpendingController::class);

    Route::apiResource('categories', CategoryController::class);
    
    Route::get('/analytics/spending', [AnalyticController::class, 'spendingAnalytics']);
    Route::get('/analytics/income', [AnalyticController::class, 'incomeAnalytics']);
    Route::get('/analytics/total', [AnalyticController::class, 'totalAnalytics']);
    Route::get('/analytics/spending-by-date', [AnalyticController::class, 'spendingByDate']);

    Route::apiResource('incomes', IncomeController::class);

    Route::get('/notifications', [NotificationSpendingController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationSpendingController::class, 'unreadCount']);
    Route::post('/notifications/{id}/mark-as-read', [NotificationSpendingController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [NotificationSpendingController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationSpendingController::class, 'destroy']);
});

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});
