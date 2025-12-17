<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Academic Routes
|
| Here are the routes for the Academic module. These routes handle
| academic timeline management, semester management, and related operations.
*/

Route::prefix('academic')->name('academic.')->group(function () {
    // Timeline
    Route::get('/timeline', [App\Http\Controllers\Academic\AcademicController::class, 'index'])
        ->name('academic-index');

    // Semester routes
    Route::get('/semesters', [App\Http\Controllers\Academic\AcademicController::class, 'getAllSemesters'])
        ->name('semesters.get');

    Route::post('/semester', [App\Http\Controllers\Academic\AcademicController::class, 'storeSemester'])
        ->name('semester.store');

    Route::get('/semester/{id}', [App\Http\Controllers\Academic\AcademicController::class, 'getSemester'])
        ->where('id', '[0-9]+')
        ->name('semester.get');

    Route::put('/semester/{id}', [App\Http\Controllers\Academic\AcademicController::class, 'updateSemester'])
        ->where('id', '[0-9]+')
        ->name('semester.update');

    Route::put('/semester/{id}/status', [App\Http\Controllers\Academic\AcademicController::class, 'updateSemesterStatus'])
        ->where('id', '[0-9]+')
        ->name('semester.updateStatus');

    // Reactivate a previous semester pair and set it as the active/displayed one
    Route::post('/semesters/reactivate', [App\Http\Controllers\Academic\AcademicController::class, 'reactivateSemesters'])
        ->name('semesters.reactivate');

    // Quarter routes
    Route::put('/quarter/{id}', [App\Http\Controllers\Academic\AcademicController::class, 'updateQuarter'])
        ->where('id', '[0-9]+')
        ->name('quarter.update');
});
