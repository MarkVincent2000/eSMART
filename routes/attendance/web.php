<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\Attendance\AttendanceCategoryController;

/*
|--------------------------------------------------------------------------
| Attendance Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Attendance module. These routes handle
| attendance session management, category management, and related operations.
|
*/

Route::prefix('attendance')->name('attendance.')->group(function () {
    
    // Attendance Category Routes (Must be before /{id} routes)
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [AttendanceCategoryController::class, 'index'])->name('index');
        Route::get('/active', [AttendanceCategoryController::class, 'getActiveCategories'])->name('active');
        Route::post('/', [AttendanceCategoryController::class, 'store'])->name('store');
        Route::post('/update-order', [AttendanceCategoryController::class, 'updateOrder'])->name('update-order');
        Route::get('/{id}', [AttendanceCategoryController::class, 'show'])->name('show')->where('id', '[0-9]+');
        Route::put('/{id}', [AttendanceCategoryController::class, 'update'])->name('update')->where('id', '[0-9]+');
        Route::delete('/{id}', [AttendanceCategoryController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
        Route::post('/{id}/activate', [AttendanceCategoryController::class, 'activate'])->name('activate')->where('id', '[0-9]+');
        Route::post('/{id}/deactivate', [AttendanceCategoryController::class, 'deactivate'])->name('deactivate')->where('id', '[0-9]+');
    });

    // Attendance Session Routes
    Route::get('/', [AttendanceController::class, 'index'])->name('index');
    Route::get('/attendances', [AttendanceController::class, 'getAttendances'])->name('get-attendances');
    Route::get('/form-data', [AttendanceController::class, 'getFormData'])->name('form-data');
    Route::post('/', [AttendanceController::class, 'store'])->name('store');
    Route::post('/{id}/students', [AttendanceController::class, 'storeStudentAttendances'])->name('store-students')->where('id', '[0-9]+');
    Route::get('/{id}', [AttendanceController::class, 'show'])->name('show')->where('id', '[0-9]+');
    Route::put('/{id}', [AttendanceController::class, 'update'])->name('update')->where('id', '[0-9]+');
    Route::delete('/{id}', [AttendanceController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
    Route::post('/{id}/lock', [AttendanceController::class, 'lock'])->name('lock')->where('id', '[0-9]+');
    Route::post('/{id}/unlock', [AttendanceController::class, 'unlock'])->name('unlock')->where('id', '[0-9]+');
    
    // Student Attendance Routes
    Route::post('/students/{id}/approve', [AttendanceController::class, 'approveStudentAttendance'])->name('students.approve')->where('id', '[0-9]+');
    Route::post('/students/{id}/disapprove', [AttendanceController::class, 'disapproveStudentAttendance'])->name('students.disapprove')->where('id', '[0-9]+');
    Route::post('/students/bulk-approve', [AttendanceController::class, 'bulkApproveStudentAttendances'])->name('students.bulk-approve');
    Route::post('/students/bulk-disapprove', [AttendanceController::class, 'bulkDisapproveStudentAttendances'])->name('students.bulk-disapprove');
});
