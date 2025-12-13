<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Engagement\EngagementController;

/*
|--------------------------------------------------------------------------
| Engagement Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Engagement module. These routes handle
| event management, calendar operations, and related functionality.
|
*/

Route::prefix('engagement')->name('engagement.')->group(function () {
    Route::get('/', [EngagementController::class, 'index'])->name('index');
    Route::get('/calendar-events', [EngagementController::class, 'getCalendarEvents'])->name('calendar-events');
    Route::get('/form-data', [EngagementController::class, 'getFormData'])->name('form-data');
    Route::get('/event/{id}', [EngagementController::class, 'getEvent'])->name('event');
    Route::post('/event', [EngagementController::class, 'store'])->name('store');
    Route::put('/event/{id}', [EngagementController::class, 'update'])->name('update');
    Route::delete('/event/{id}', [EngagementController::class, 'destroy'])->name('destroy');
});

