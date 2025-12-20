<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\Attendance\AttendanceCategoryController;
use App\Http\Controllers\Attendance\StudentAttendanceController;

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
    Route::get('/', [AttendanceController::class, 'index'])->name('index')->middleware('student.attendance');
    Route::get('/attendances', [AttendanceController::class, 'getAttendances'])->name('get-attendances');
    Route::get('/form-data', [AttendanceController::class, 'getFormData'])->name('form-data');
    Route::get('/form-data/students', [AttendanceController::class, 'getFormDataStudents'])->name('form-data-students');
    Route::post('/', [AttendanceController::class, 'store'])->name('store');
    Route::post('/{id}/students', [AttendanceController::class, 'storeStudentAttendances'])->name('store-students')->where('id', '[0-9]+');
    // Student Attendance Routes (folder only - using query parameters to avoid plugin path issues)
    Route::get('/view/students', [StudentAttendanceController::class, 'getStudentAttendances'])->name('view.students');
    Route::post('/students/time-in', [StudentAttendanceController::class, 'timeIn'])->name('students.time-in');
    Route::post('/students/time-out', [StudentAttendanceController::class, 'timeOut'])->name('students.time-out');
    // Print route must be before /{id} route to avoid route conflicts
    // Apply middleware to ensure students can only access attendances they belong to
    Route::get('/{id}/print', [AttendanceController::class, 'printPdf'])->name('print')->where('id', '[0-9]+')->middleware('student.attendance');
    Route::get('/{id}', [AttendanceController::class, 'show'])->name('show')->where('id', '[0-9]+')->middleware('student.attendance');
    Route::put('/{id}', [AttendanceController::class, 'update'])->name('update')->where('id', '[0-9]+');
    Route::delete('/{id}', [AttendanceController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
    Route::post('/{id}/lock', [AttendanceController::class, 'lock'])->name('lock')->where('id', '[0-9]+');
    Route::post('/{id}/unlock', [AttendanceController::class, 'unlock'])->name('unlock')->where('id', '[0-9]+');
    
    // Student Attendance Routes
    Route::post('/students/{id}/approve', [AttendanceController::class, 'approveStudentAttendance'])->name('students.approve')->where('id', '[0-9]+');
    Route::post('/students/{id}/disapprove', [AttendanceController::class, 'disapproveStudentAttendance'])->name('students.disapprove')->where('id', '[0-9]+');
    Route::post('/students/{id}/update-status', [AttendanceController::class, 'updateStudentAttendanceStatus'])->name('students.update-status')->where('id', '[0-9]+');
    Route::post('/students/{id}/update-remarks', [AttendanceController::class, 'updateStudentAttendanceRemarks'])->name('students.update-remarks')->where('id', '[0-9]+');
    Route::post('/students/bulk-approve', [AttendanceController::class, 'bulkApproveStudentAttendances'])->name('students.bulk-approve');
    Route::post('/students/bulk-disapprove', [AttendanceController::class, 'bulkDisapproveStudentAttendances'])->name('students.bulk-disapprove');
    
    // Comment Routes
    Route::get('/comments', [StudentAttendanceController::class, 'getComments'])->name('comments.index');
    Route::post('/comments', [StudentAttendanceController::class, 'storeComment'])->name('comments.store');
    Route::put('/comments/{id}', [StudentAttendanceController::class, 'updateComment'])->name('comments.update')->where('id', '[0-9]+');
    Route::delete('/comments/{id}', [StudentAttendanceController::class, 'deleteComment'])->name('comments.delete')->where('id', '[0-9]+');
});
