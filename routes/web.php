<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\VenueController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('dashboard', fn() => view('dashboard'))->name('dashboard');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('academic-years/data', [AcademicYearController::class, 'data'])->name('academic-years.data');
    Route::post('academic-years/export/excel', [AcademicYearController::class, 'exportExcel'])->name('academic-years.export.excel');
    Route::post('academic-years/export/pdf', [AcademicYearController::class, 'exportPdf'])->name('academic-years.export.pdf');
    Route::patch('academic-years/{academic_year}/toggle-status', [AcademicYearController::class, 'toggleStatus'])
        ->name('academic-years.toggle-status');

    Route::resource('academic-years', AcademicYearController::class)
        ->except('show');

    Route::get('grades/data', [GradeController::class, 'data'])->name('grades.data');
    Route::post('grades/export/excel', [GradeController::class, 'exportExcel'])->name('grades.export.excel');
    Route::post('grades/export/pdf', [GradeController::class, 'exportPdf'])->name('grades.export.pdf');
    Route::patch('grades/{grade}/toggle-status', [GradeController::class, 'toggleStatus'])
        ->name('grades.toggle-status');

    Route::resource('grades', GradeController::class)
        ->except('show');

    Route::get('divisions/data', [DivisionController::class, 'data'])->name('divisions.data');
    Route::post('divisions/export/excel', [DivisionController::class, 'exportExcel'])->name('divisions.export.excel');
    Route::post('divisions/export/pdf', [DivisionController::class, 'exportPdf'])->name('divisions.export.pdf');
    Route::patch('divisions/{division}/toggle-status', [DivisionController::class, 'toggleStatus'])
        ->name('divisions.toggle-status');

    Route::resource('divisions', DivisionController::class)
        ->except('show');

    Route::get('departments/data', [DepartmentController::class, 'data'])->name('departments.data');
    Route::post('departments/export/excel', [DepartmentController::class, 'exportExcel'])->name('departments.export.excel');
    Route::post('departments/export/pdf', [DepartmentController::class, 'exportPdf'])->name('departments.export.pdf');
    Route::patch('departments/{department}/toggle-status', [DepartmentController::class, 'toggleStatus'])
        ->name('departments.toggle-status');

    Route::resource('departments', DepartmentController::class)
        ->except('show');

    Route::get('designations/data', [DesignationController::class, 'data'])->name('designations.data');
    Route::post('designations/export/excel', [DesignationController::class, 'exportExcel'])->name('designations.export.excel');
    Route::post('designations/export/pdf', [DesignationController::class, 'exportPdf'])->name('designations.export.pdf');
    Route::patch('designations/{designation}/toggle-status', [DesignationController::class, 'toggleStatus'])
        ->name('designations.toggle-status');

    Route::resource('designations', DesignationController::class)
        ->except('show');

    Route::get('classrooms/data', [ClassroomController::class, 'data'])->name('classrooms.data');
    Route::post('classrooms/export/excel', [ClassroomController::class, 'exportExcel'])->name('classrooms.export.excel');
    Route::post('classrooms/export/pdf', [ClassroomController::class, 'exportPdf'])->name('classrooms.export.pdf');
    Route::patch('classrooms/{classroom}/toggle-status', [ClassroomController::class, 'toggleStatus'])
        ->name('classrooms.toggle-status');

    Route::resource('classrooms', ClassroomController::class)
        ->except('show');

    Route::get('venues/data', [VenueController::class, 'data'])->name('venues.data');
    Route::post('venues/export/excel', [VenueController::class, 'exportExcel'])->name('venues.export.excel');
    Route::post('venues/export/pdf', [VenueController::class, 'exportPdf'])->name('venues.export.pdf');
    Route::patch('venues/{venue}/toggle-status', [VenueController::class, 'toggleStatus'])
        ->name('venues.toggle-status');

    Route::resource('venues', VenueController::class)
        ->except('show');

    Route::get('holidays/data', [HolidayController::class, 'data'])->name('holidays.data');
    Route::post('holidays/export/excel', [HolidayController::class, 'exportExcel'])->name('holidays.export.excel');
    Route::post('holidays/export/pdf', [HolidayController::class, 'exportPdf'])->name('holidays.export.pdf');
    Route::patch('holidays/{holiday}/toggle-status', [HolidayController::class, 'toggleStatus'])
        ->name('holidays.toggle-status');

    Route::resource('holidays', HolidayController::class)
        ->except('show');
});



Route::get('/storage-link', function () {
    Artisan::call('storage:link');
    return "Storage link created successfully!";
});

Route::get('/clear-all', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');

    return "All cache cleared!";
});

Route::get('system/migrate/{filename}', function ($filename) {
    Artisan::call('migrate', [
        '--path' => 'database/migrations/' . $filename . '.php',
        '--force' => true,
    ]);
    return '<pre>' . Artisan::output() . '</pre>';
});

Route::get('system/migrate-fresh', function () {
    Artisan::call('migrate:fresh', ['--seed' => true]);
    return  "Database migrated fresh and seeded successfully!";
})->name('system.migrate-fresh');
