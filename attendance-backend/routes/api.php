<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AttendanceController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Class routes
    Route::apiResource('classes', ClassController::class);
    
    // Section routes
    Route::apiResource('sections', SectionController::class);
    
    // Student routes
    Route::apiResource('students', StudentController::class);
    
    // Attendance routes
    Route::prefix('attendance')->group(function () {
        Route::post('/bulk', [AttendanceController::class, 'bulkStore']);
        Route::get('/report/monthly', [AttendanceController::class, 'monthlyReport']);
        Route::get('/statistics', [AttendanceController::class, 'statistics']);
        Route::get('/today', [AttendanceController::class, 'todayAttendance']);
    });
    
    Route::apiResource('attendance', AttendanceController::class)->except(['store']);
});
