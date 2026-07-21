<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\EventTypeController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\GeneratedTimetableController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\ModulePrefixController;
use App\Http\Controllers\ProjectCategoryController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectScheduleController;
use App\Http\Controllers\ProjectWeekController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SpecialEventController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubstituteAllocationController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherDocumentController;
use App\Http\Controllers\TeacherSubjectController;
use App\Http\Controllers\TeacherSchedulerController;
use App\Http\Controllers\TeacherAllotmentController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\TimeTableCategoryController;
use App\Http\Controllers\TrainerCategoryController;
use App\Http\Controllers\TrainerTypeController;
use App\Http\Controllers\TrainingScheduleController;
use App\Http\Controllers\TrainingScheduleTrainerController;
use App\Http\Controllers\UserController;
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

    Route::get('roles/data', [RoleController::class, 'data'])->name('roles.data');
    Route::resource('roles', RoleController::class)
        ->except('show');

    Route::get('users/data', [UserController::class, 'data'])->name('users.data');
    Route::post('users/export/excel', [UserController::class, 'exportExcel'])->name('users.export.excel');
    Route::post('users/export/pdf', [UserController::class, 'exportPdf'])->name('users.export.pdf');
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->name('users.toggle-status');
    Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])
        ->name('users.reset-password');

    Route::resource('users', UserController::class);

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

    Route::get('subjects/data', [SubjectController::class, 'data'])->name('subjects.data');
    Route::post('subjects/export/excel', [SubjectController::class, 'exportExcel'])->name('subjects.export.excel');
    Route::post('subjects/export/pdf', [SubjectController::class, 'exportPdf'])->name('subjects.export.pdf');
    Route::patch('subjects/{subject}/toggle-status', [SubjectController::class, 'toggleStatus'])
        ->name('subjects.toggle-status');

    Route::resource('subjects', SubjectController::class)
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
    Route::get('holidays/calendar', [HolidayController::class, 'calendar'])->name('holidays.calendar');

    Route::resource('holidays', HolidayController::class)
        ->except('show');

    Route::get('time-table-categories/data', [TimeTableCategoryController::class, 'data'])->name('time-table-categories.data');
    Route::post('time-table-categories/export/excel', [TimeTableCategoryController::class, 'exportExcel'])->name('time-table-categories.export.excel');
    Route::post('time-table-categories/export/pdf', [TimeTableCategoryController::class, 'exportPdf'])->name('time-table-categories.export.pdf');
    Route::patch('time-table-categories/{time_table_category}/toggle-status', [TimeTableCategoryController::class, 'toggleStatus'])
        ->name('time-table-categories.toggle-status');

    Route::resource('time-table-categories', TimeTableCategoryController::class)
        ->except('show');

    Route::get('project-categories/data', [ProjectCategoryController::class, 'data'])->name('project-categories.data');
    Route::post('project-categories/export/excel', [ProjectCategoryController::class, 'exportExcel'])->name('project-categories.export.excel');
    Route::post('project-categories/export/pdf', [ProjectCategoryController::class, 'exportPdf'])->name('project-categories.export.pdf');
    Route::patch('project-categories/{project_category}/toggle-status', [ProjectCategoryController::class, 'toggleStatus'])
        ->name('project-categories.toggle-status');

    Route::resource('project-categories', ProjectCategoryController::class)
        ->except('show');

    Route::get('event-types/data', [EventTypeController::class, 'data'])->name('event-types.data');
    Route::post('event-types/export/excel', [EventTypeController::class, 'exportExcel'])->name('event-types.export.excel');
    Route::post('event-types/export/pdf', [EventTypeController::class, 'exportPdf'])->name('event-types.export.pdf');
    Route::patch('event-types/{event_type}/toggle-status', [EventTypeController::class, 'toggleStatus'])
        ->name('event-types.toggle-status');

    Route::resource('event-types', EventTypeController::class)
        ->except('show');

    Route::get('trainer-types/data', [TrainerTypeController::class, 'data'])->name('trainer-types.data');
    Route::post('trainer-types/export/excel', [TrainerTypeController::class, 'exportExcel'])->name('trainer-types.export.excel');
    Route::post('trainer-types/export/pdf', [TrainerTypeController::class, 'exportPdf'])->name('trainer-types.export.pdf');
    Route::patch('trainer-types/{recordId}/toggle-status', [TrainerTypeController::class, 'toggleStatus'])
        ->name('trainer-types.toggle-status');
    Route::resource('trainer-types', TrainerTypeController::class)
        ->parameters(['trainer-types' => 'recordId'])
        ->except('show');

    Route::get('trainer-categories/data', [TrainerCategoryController::class, 'data'])->name('trainer-categories.data');
    Route::post('trainer-categories/export/excel', [TrainerCategoryController::class, 'exportExcel'])->name('trainer-categories.export.excel');
    Route::post('trainer-categories/export/pdf', [TrainerCategoryController::class, 'exportPdf'])->name('trainer-categories.export.pdf');
    Route::patch('trainer-categories/{recordId}/toggle-status', [TrainerCategoryController::class, 'toggleStatus'])
        ->name('trainer-categories.toggle-status');
    Route::resource('trainer-categories', TrainerCategoryController::class)
        ->parameters(['trainer-categories' => 'recordId'])
        ->except('show');

    Route::get('module-prefixes/data', [ModulePrefixController::class, 'data'])->name('module-prefixes.data');
    Route::resource('module-prefixes', ModulePrefixController::class)
        ->except(['create', 'store', 'show', 'destroy']);

    Route::get('teachers/data', [TeacherController::class, 'data'])->name('teachers.data');
    Route::delete('teachers/bulk-delete', [TeacherController::class, 'bulkDelete'])->name('teachers.bulk-delete');
    Route::post('teachers/export/excel', [TeacherController::class, 'exportExcel'])->name('teachers.export.excel');
    Route::post('teachers/export/pdf', [TeacherController::class, 'exportPdf'])->name('teachers.export.pdf');
    Route::patch('teachers/{teacher}/verify', [TeacherController::class, 'verify'])->name('teachers.verify');
    Route::get('teachers/{teacher}/documents', [TeacherDocumentController::class, 'index'])->name('teachers.documents.index');
    Route::get('teachers/{teacher}/documents/data', [TeacherDocumentController::class, 'data'])->name('teachers.documents.data');
    Route::post('teachers/{teacher}/documents', [TeacherDocumentController::class, 'store'])->name('teachers.documents.store');
    Route::get('teachers/{teacher}/documents/{document}', [TeacherDocumentController::class, 'show'])->name('teachers.documents.show');
    Route::post('teachers/{teacher}/documents/{document}', [TeacherDocumentController::class, 'update'])->name('teachers.documents.update');
    Route::delete('teachers/{teacher}/documents/{document}', [TeacherDocumentController::class, 'destroy'])->name('teachers.documents.destroy');
    Route::patch('teachers/{teacher}/documents/{document}/verify', [TeacherDocumentController::class, 'verify'])->name('teachers.documents.verify');
    Route::get('teachers/{teacher}/subjects', [TeacherSubjectController::class, 'index'])->name('teachers.subjects.index');
    Route::get('teachers/{teacher}/subjects/data', [TeacherSubjectController::class, 'data'])->name('teachers.subjects.data');
    Route::post('teachers/{teacher}/subjects', [TeacherSubjectController::class, 'store'])->name('teachers.subjects.store');
    Route::get('teachers/{teacher}/subjects/{teacherSubject}', [TeacherSubjectController::class, 'show'])->name('teachers.subjects.show');
    Route::post('teachers/{teacher}/subjects/{teacherSubject}', [TeacherSubjectController::class, 'update'])->name('teachers.subjects.update');
    Route::delete('teachers/{teacher}/subjects/{teacherSubject}', [TeacherSubjectController::class, 'destroy'])->name('teachers.subjects.destroy');
    Route::get('teachers/{teacher}/scheduler', [TeacherSchedulerController::class, 'index'])->name('teachers.scheduler.index');
    Route::get('teachers/{teacher}/scheduler/pdf', [TeacherSchedulerController::class, 'pdf'])->name('teachers.scheduler.pdf');
    Route::get('teacher-allotments', [TeacherAllotmentController::class, 'index'])->name('teacher-allotments.index');
    Route::get('teacher-allotments/pdf', [TeacherAllotmentController::class, 'pdf'])->name('teacher-allotments.pdf');

    Route::resource('teachers', TeacherController::class);

    Route::get('projects/data', [ProjectController::class, 'data'])->name('projects.data');
    Route::post('projects/export/excel', [ProjectController::class, 'exportExcel'])->name('projects.export.excel');
    Route::post('projects/export/pdf', [ProjectController::class, 'exportPdf'])->name('projects.export.pdf');
    Route::patch('projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.update-status');
    Route::get('projects/{project}/schedules', [ProjectScheduleController::class, 'index'])->name('projects.schedules.index');
    Route::get('projects/{project}/schedules/data', [ProjectScheduleController::class, 'data'])->name('projects.schedules.data');
    Route::post('projects/{project}/schedules', [ProjectScheduleController::class, 'store'])->name('projects.schedules.store');
    Route::get('projects/{project}/schedules/{schedule}', [ProjectScheduleController::class, 'show'])->name('projects.schedules.show');
    Route::post('projects/{project}/schedules/{schedule}', [ProjectScheduleController::class, 'update'])->name('projects.schedules.update');
    Route::delete('projects/{project}/schedules/{schedule}', [ProjectScheduleController::class, 'destroy'])->name('projects.schedules.destroy');
    Route::resource('projects', ProjectController::class);

    Route::get('project-weeks/data', [ProjectWeekController::class, 'data'])->name('project-weeks.data');
    Route::get('project-weeks/{project_week}/generate', [ProjectWeekController::class, 'generate'])->name('project-weeks.generate');
    Route::post('project-weeks/{project_week}/generate', [ProjectWeekController::class, 'storeGenerated'])->name('project-weeks.generate.store');
    Route::get('project-weeks/{project_week}/preview', [ProjectWeekController::class, 'preview'])->name('project-weeks.preview');
    Route::get('project-weeks/{project_week}/generated-pdf', [ProjectWeekController::class, 'downloadGeneratedPdf'])->name('project-weeks.generated.pdf');
    Route::post('project-weeks/export/excel', [ProjectWeekController::class, 'exportExcel'])->name('project-weeks.export.excel');
    Route::post('project-weeks/export/pdf', [ProjectWeekController::class, 'exportPdf'])->name('project-weeks.export.pdf');
    Route::resource('project-weeks', ProjectWeekController::class)
        ->except('show');

    Route::get('training-schedules/data', [TrainingScheduleController::class, 'data'])->name('training-schedules.data');
    Route::post('training-schedules/export/excel', [TrainingScheduleController::class, 'exportExcel'])->name('training-schedules.export.excel');
    Route::post('training-schedules/export/pdf', [TrainingScheduleController::class, 'exportPdf'])->name('training-schedules.export.pdf');
    Route::get('training-schedules/{training_schedule}/trainers', [TrainingScheduleTrainerController::class, 'index'])
        ->name('training-schedules.trainers.index');
    Route::get('training-schedules/{training_schedule}/trainers/data', [TrainingScheduleTrainerController::class, 'data'])
        ->name('training-schedules.trainers.data');
    Route::post('training-schedules/{training_schedule}/trainers', [TrainingScheduleTrainerController::class, 'store'])
        ->name('training-schedules.trainers.store');
    Route::get('training-schedules/{training_schedule}/trainers/{trainer}', [TrainingScheduleTrainerController::class, 'show'])
        ->name('training-schedules.trainers.show');
    Route::put('training-schedules/{training_schedule}/trainers/{trainer}', [TrainingScheduleTrainerController::class, 'update'])
        ->name('training-schedules.trainers.update');
    Route::delete('training-schedules/{training_schedule}/trainers/{trainer}', [TrainingScheduleTrainerController::class, 'destroy'])
        ->name('training-schedules.trainers.destroy');
    Route::get('training-schedules/{training_schedule}/substitute-allocations', [SubstituteAllocationController::class, 'index'])
        ->name('training-schedules.substitute-allocations.index');
    Route::get('training-schedules/{training_schedule}/substitute-allocations/data', [SubstituteAllocationController::class, 'data'])
        ->name('training-schedules.substitute-allocations.data');
    Route::get('training-schedules/{training_schedule}/substitute-allocations/create', [SubstituteAllocationController::class, 'create'])
        ->name('training-schedules.substitute-allocations.create');
    Route::get('training-schedules/{training_schedule}/substitute-allocations/periods/{teacher}', [SubstituteAllocationController::class, 'periods'])
        ->name('training-schedules.substitute-allocations.periods');
    Route::post('training-schedules/{training_schedule}/substitute-allocations', [SubstituteAllocationController::class, 'store'])
        ->name('training-schedules.substitute-allocations.store');
    Route::get('training-schedules/{training_schedule}/substitute-allocations/{allocation}', [SubstituteAllocationController::class, 'show'])
        ->name('training-schedules.substitute-allocations.show');
    Route::get('training-schedules/{training_schedule}/substitute-allocations/{allocation}/edit', [SubstituteAllocationController::class, 'edit'])
        ->name('training-schedules.substitute-allocations.edit');
    Route::put('training-schedules/{training_schedule}/substitute-allocations/{allocation}', [SubstituteAllocationController::class, 'update'])
        ->name('training-schedules.substitute-allocations.update');
    Route::delete('training-schedules/{training_schedule}/substitute-allocations/{allocation}', [SubstituteAllocationController::class, 'destroy'])
        ->name('training-schedules.substitute-allocations.destroy');
    Route::resource('training-schedules', TrainingScheduleController::class);

    Route::get('special-events/data', [SpecialEventController::class, 'data'])->name('special-events.data');
    Route::get('special-events/divisions', [SpecialEventController::class, 'divisionsByGrades'])->name('special-events.divisions');
    Route::get('special-events/{special_event}/generate', [SpecialEventController::class, 'generate'])->name('special-events.generate');
    Route::post('special-events/{special_event}/generate', [SpecialEventController::class, 'storeGenerated'])->name('special-events.generate.store');
    Route::get('special-events/{special_event}/preview', [SpecialEventController::class, 'preview'])->name('special-events.preview');
    Route::get('special-events/{special_event}/generated-pdf', [SpecialEventController::class, 'downloadGeneratedPdf'])->name('special-events.generated.pdf');
    Route::post('special-events/{special_event}/send-mail', [SpecialEventController::class, 'sendMail'])->name('special-events.send-mail');
    Route::post('special-events/export/excel', [SpecialEventController::class, 'exportExcel'])->name('special-events.export.excel');
    Route::post('special-events/export/pdf', [SpecialEventController::class, 'exportPdf'])->name('special-events.export.pdf');
    Route::resource('special-events', SpecialEventController::class);

    Route::get('generate-timetable', [GeneratedTimetableController::class, 'index'])
        ->middleware('can:view.timetable')->name('generate-timetable.index');
    Route::get('generate-timetable/pdf', [GeneratedTimetableController::class, 'pdf'])
        ->middleware('can:view.timetable')->name('generate-timetable.pdf');

    Route::get('timetables/data', [TimetableController::class, 'data'])->name('timetables.data');
    Route::get('timetables/{timetable}/generate', [TimetableController::class, 'generate'])->name('timetables.generate');
    Route::post('timetables/{timetable}/generate', [TimetableController::class, 'storeGenerated'])->name('timetables.generate.store');
    Route::get('timetables/{timetable}/preview', [TimetableController::class, 'preview'])->name('timetables.preview');
    Route::get('timetables/{timetable}/generated-pdf', [TimetableController::class, 'downloadGeneratedPdf'])->name('timetables.generated.pdf');
    Route::post('timetables/export/excel', [TimetableController::class, 'exportExcel'])->name('timetables.export.excel');
    Route::post('timetables/export/pdf', [TimetableController::class, 'exportPdf'])->name('timetables.export.pdf');
    Route::resource('timetables', TimetableController::class)
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
