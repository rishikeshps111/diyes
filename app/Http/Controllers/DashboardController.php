<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = today();

        return view('dashboard', [
            'currentAcademicYear' => AcademicYear::query()
                ->where('is_active', true)
                ->orderByDesc('start_date')
                ->value('academic_year') ?? 'Not set',
            'activeGrades' => Grade::query()->where('is_active', true)->count(),
            'totalDivisions' => Division::query()->count(),
            'totalSubjects' => Subject::query()->count(),
            'totalTeachers' => Teacher::query()->count(),
            'publishedTimetables' => Timetable::query()->where('status', 'published')->count(),
            'draftTimetables' => Timetable::query()->where('status', 'draft')->count(),
            'pendingApprovals' => Teacher::query()->where('is_verified', false)->count(),
            'todaysClasses' => TimetableEntry::query()
                ->where('day', $today->format('l'))
                ->where('entry_type', 'period')
                ->whereHas('timetable', fn ($query) => $query
                    ->where('status', 'published')
                    ->whereDate('applicable_from', '<=', $today)
                    ->whereDate('applicable_to', '>=', $today))
                ->count(),
        ]);
    }
}
