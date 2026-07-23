<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

// Main Attendance Capture UI
Route::get('/', [AttendanceController::class, 'index'])->name('attendance.index');

// API routes for automated scanner
Route::post('/api/scan', [AttendanceController::class, 'scan'])->name('attendance.scan');
Route::get('/api/logs', [AttendanceController::class, 'logs'])->name('attendance.logs');
