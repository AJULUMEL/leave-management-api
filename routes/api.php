<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OAuthController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\AdminLeaveRequestController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/auth/github/redirect', [OAuthController::class, 'redirectToGithub']);
Route::get('/auth/github/callback', [OAuthController::class, 'handleGithubCallback']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:employee')->group(function () {
        Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
        Route::get('/my-leave-requests', [LeaveRequestController::class, 'index']);
        Route::get('/my-leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show']);
    });

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/leave-requests', [AdminLeaveRequestController::class, 'index']);
        Route::get('/leave-requests/{leaveRequest}', [AdminLeaveRequestController::class, 'show']);
        Route::patch('/leave-requests/{leaveRequest}/approve', [AdminLeaveRequestController::class, 'approve']);
        Route::patch('/leave-requests/{leaveRequest}/reject', [AdminLeaveRequestController::class, 'reject']);
    });
});