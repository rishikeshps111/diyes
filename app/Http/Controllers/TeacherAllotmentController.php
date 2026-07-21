<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class TeacherAllotmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array { return [new Middleware('can:view.teacher')]; }

    public function index(Request $request, TeacherSchedulerController $scheduler, GeneratedTimetableController $generated): View
    {
        [$filters, $teacher, $previews] = $this->result($request, $scheduler, $generated, false);
        return view('teacher-allotments.index', ['teachers' => $this->teachers(), 'filters' => $filters, 'teacher' => $teacher, 'previews' => $previews]);
    }

    public function pdf(Request $request, TeacherSchedulerController $scheduler, GeneratedTimetableController $generated)
    {
        [$filters, $teacher, $previews] = $this->result($request, $scheduler, $generated, true);
        return Pdf::loadView('teacher-allotments.pdf', compact('filters', 'teacher', 'previews'))->setPaper('a4', 'landscape')
            ->download('teacher-allotment-'.str($teacher->name)->slug().'-'.$filters['from_date'].'-to-'.$filters['to_date'].'.pdf');
    }

    private function result(Request $request, TeacherSchedulerController $scheduler, GeneratedTimetableController $generated, bool $required): array
    {
        $rule = $required ? 'required' : 'nullable';
        $filters = $request->validate([
            'teacher_id' => [$rule, 'integer', 'exists:teachers,id'],
            'from_date' => [$rule, 'date'],
            'to_date' => [$rule, 'date', 'after_or_equal:from_date'],
        ]);
        $teacher = !empty($filters['teacher_id']) ? Teacher::find($filters['teacher_id']) : null;
        $previews = collect();
        if ($teacher && !empty($filters['from_date']) && !empty($filters['to_date'])) {
            $from = Carbon::parse($filters['from_date'])->startOfDay(); $to = Carbon::parse($filters['to_date'])->endOfDay();
            abort_if($from->diffInDays($to) > 92, 422, 'The date range may not exceed 93 days.');
            foreach (CarbonPeriod::create($from->copy()->startOfWeek(), '1 week', $to) as $week) {
                $start = Carbon::parse($week)->startOfWeek(); $end = $start->copy()->endOfWeek();
                $preview = $scheduler->buildPreview($teacher, $generated, $start, $end);
                $validDays = $preview['days']->filter(fn (Carbon $date) => $date->betweenIncluded($from, $to))->keys();
                $preview['cells'] = $preview['cells']->filter(fn ($entries, $key) => $validDays->contains(explode('|', $key)[0]));
                if ($preview['cells']->isNotEmpty()) {
                    $previews->push($preview);
                }
            }
        }
        return [$filters + ['teacher_id' => null, 'from_date' => null, 'to_date' => null], $teacher, $previews];
    }

    private function teachers() { return Teacher::query()->orderBy('name')->get(['id', 'name', 'employee_id']); }
}
