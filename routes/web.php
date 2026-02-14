<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\User\UserController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();
//Language Translation
Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);

// Landing page (public, no auth required)
Route::get('/', [App\Http\Controllers\HomeController::class, 'root'])->name('root');
Route::get('/landing', [App\Http\Controllers\HomeController::class, 'landing'])->name('landing');

// Dashboard (requires authentication)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'dashboard'])->name('dashboard');
});

// Custom grouped routes

//Notification

//Update User Details
Route::post('/update-profile/{id}', [App\Http\Controllers\HomeController::class, 'updateProfile'])->name('updateProfile');
Route::post('/update-password/{id}', [App\Http\Controllers\HomeController::class, 'updatePassword'])->name('updatePassword');

// Engagement Routes
require __DIR__.'/engagement/web.php';

// Attendance Routes
require __DIR__.'/attendance/web.php';

// Academic Routes
require __DIR__.'/academic/web.php';

// Student & Teacher Print Routes
Route::middleware('auth')->group(function () {
    Route::get('/students/print', [App\Http\Controllers\Student\StudentController::class, 'print'])->name('students.print');
    Route::get('/teacher/workloads/print', [App\Http\Controllers\Teacher\WorkloadPrintController::class, 'print'])->name('teacher.workloads.print');
    Route::get('/teacher/grading/print/{id}', [App\Http\Controllers\Teacher\GradePrintController::class, 'print'])->name('teacher.grading.print')->where('id', '[0-9]+');
    Route::get('/teacher/grading/pdf/{id}', [App\Http\Controllers\Teacher\GradePrintController::class, 'pdf'])->name('teacher.grading.pdf')->where('id', '[0-9]+');
    
    // Manual PDF Route
    Route::get('/manual/pdf', function () {
        $path = resource_path('views/dashboard/pdf/Smart-Campus-User-Manual.pdf');
        if (!file_exists($path)) {
            abort(404, 'Manual file not found.');
        }
        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Smart-Campus-User-Manual.pdf"'
        ]);
    })->name('manual.show.pdf');
});

Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('index');
