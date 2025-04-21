<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectsController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);
Route::post('/auth/password/email', [AuthController::class, 'sendPasswordResetEmail']);
Route::post('/auth/password/reset', [AuthController::class, 'resetPassword']);
Route::post('/auth/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail']);
Route::post('/auth/email/resend', [AuthController::class, 'resendVerificationEmail']);
Route::post('/api/reset-password', [AuthController::class, 'resetPassword']);

// Protected Routes (require authentication)
Route::middleware('auth:api')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'getUserInfo']);

    Route::get('/projects', [ProjectsController::class, 'index']);
    Route::post('/projects', [ProjectsController::class, 'store']);
    Route::get('/projects/{id}', [ProjectsController::class, 'show']);
    Route::put('/projects/{id}', [ProjectsController::class, 'update']);
    Route::delete('/projects/{id}', [ProjectsController::class, 'destroy']);
    
    // Project relationships
    Route::get('/projects/{id}/issues', [ProjectsController::class, 'getProjectIssues']);
    Route::get('/projects/{id}/sprints', [ProjectsController::class, 'getProjectSprints']);
    Route::get('/projects/{id}/members', [ProjectsController::class, 'getProjectMembers']);
    Route::post('/projects/{id}/members', [ProjectsController::class, 'addProjectMember']);
    Route::delete('/projects/{id}/members/{userId}', [ProjectsController::class, 'removeProjectMember']);
    
});

