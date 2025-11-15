<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AttendanceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

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
