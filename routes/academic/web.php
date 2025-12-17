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

    Route::get('/semester/{id}', [App\Http\Controllers\Academic\AcademicController::class, 'getSemester'])
        ->where('id', '[0-9]+')
        ->name('semester.get');

    Route::put('/semester/{id}', [App\Http\Controllers\Academic\AcademicController::class, 'updateSemester'])
        ->where('id', '[0-9]+')
        ->name('semester.update');

    // Quarter routes
    Route::put('/quarter/{id}', [App\Http\Controllers\Academic\AcademicController::class, 'updateQuarter'])
        ->where('id', '[0-9]+')
        ->name('quarter.update');
});
