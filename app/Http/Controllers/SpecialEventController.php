<?php

namespace App\Http\Controllers;

use App\Exports\SpecialEventsExport;
use App\Http\Requests\SpecialEventRequest;
use App\Mail\SpecialEventDetailsMail;
use App\Models\Division;
use App\Models\SpecialEvent;
use App\Services\SpecialEventService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

class SpecialEventController extends Controller implements HasMiddleware
{
    public function __construct(private readonly SpecialEventService $specialEventService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.special-event', only: ['index', 'data', 'show', 'divisionsByGrades', 'sendMail', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.special-event', only: ['create', 'store']),
            new Middleware('can:edit.special-event', only: ['edit', 'update']),
            new Middleware('can:delete.special-event', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('special-events.index', [
            'eventTypes' => $this->specialEventService->eventTypes(),
            'statuses' => SpecialEvent::STATUSES,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->specialEventService->query($request->only([
            'event_type_id',
            'status',
        ]));

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request): void {
                $keyword = $request->input('search.value');

                if (! $keyword) {
                    return;
                }

                $query->where(function ($query) use ($keyword): void {
                    $query->where('event_code', 'like', "%{$keyword}%")
                        ->orWhere('event_title', 'like', "%{$keyword}%")
                        ->orWhere('venue', 'like', "%{$keyword}%")
                        ->orWhereHas('eventType', fn ($eventTypeQuery) => $eventTypeQuery->where('title', 'like', "%{$keyword}%"))
                        ->orWhereHas('staffCoordinators', fn ($staffQuery) => $staffQuery->where('name', 'like', "%{$keyword}%"))
                        ->orWhereHas('teacherCoordinators', fn ($teacherQuery) => $teacherQuery->where('name', 'like', "%{$keyword}%"))
                        ->orWhereHas('grades', fn ($gradeQuery) => $gradeQuery->where('grade', 'like', "%{$keyword}%"));
                });
            })
            ->addColumn('select', fn (SpecialEvent $specialEvent): string => sprintf(
                '<input type="checkbox" class="special-event-row-check" value="%d">',
                $specialEvent->id
            ))
            ->editColumn('event_start_date', fn (SpecialEvent $specialEvent): string => $specialEvent->event_start_date?->format('d M Y') ?? '-')
            ->editColumn('event_end_date', fn (SpecialEvent $specialEvent): string => $specialEvent->event_end_date?->format('d M Y') ?? '-')
            ->addColumn('coordinator', fn (SpecialEvent $specialEvent): string => $this->badges(
                collect()
                    ->merge($specialEvent->staffCoordinators->pluck('name'))
                    ->merge($specialEvent->teacherCoordinators->pluck('name'))
                    ->all()
            ))
            ->addColumn('applicable_classes', fn (SpecialEvent $specialEvent): string => $this->badges($specialEvent->grades->pluck('grade')->all()))
            ->editColumn('status', fn (SpecialEvent $specialEvent): string => sprintf(
                '<span class="%s">%s</span>',
                $this->statusClass($specialEvent->status),
                SpecialEvent::STATUSES[$specialEvent->status] ?? ucfirst($specialEvent->status)
            ))
            ->addColumn('actions', fn (SpecialEvent $specialEvent): string => $this->actionButtons($specialEvent))
            ->rawColumns(['select', 'coordinator', 'applicable_classes', 'status', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('special-events.form', [
            'specialEvent' => new SpecialEvent([
                'event_code' => $this->specialEventService->nextCode(),
                'media_coverable' => false,
                'outside_candidates' => false,
                'status' => 'draft',
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function store(SpecialEventRequest $request): RedirectResponse
    {
        $this->specialEventService->create($request->validated());

        return redirect()
            ->route('special-events.index')
            ->with('success', 'Special event created successfully.');
    }

    public function show(SpecialEvent $specialEvent): View
    {
        $specialEvent->load([
            'eventType',
            'academicYear',
            'grades',
            'divisions',
            'staffCoordinators',
            'teacherCoordinators',
            'timings',
            'attachments',
            'creator',
        ]);

        return view('special-events.show', compact('specialEvent'));
    }

    public function edit(SpecialEvent $specialEvent): View
    {
        $specialEvent->load([
            'grades',
            'divisions',
            'staffCoordinators',
            'teacherCoordinators',
            'timings',
            'attachments',
        ]);

        return view('special-events.form', [
            'specialEvent' => $specialEvent,
            ...$this->formOptions(),
        ]);
    }

    public function update(SpecialEventRequest $request, SpecialEvent $specialEvent): RedirectResponse
    {
        $this->specialEventService->update($specialEvent, $request->validated());

        return redirect()
            ->route('special-events.index')
            ->with('success', 'Special event updated successfully.');
    }

    public function destroy(Request $request, SpecialEvent $specialEvent): JsonResponse|RedirectResponse
    {
        $this->specialEventService->delete($specialEvent);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Special event deleted successfully.']);
        }

        return redirect()
            ->route('special-events.index')
            ->with('success', 'Special event deleted successfully.');
    }

    public function divisionsByGrades(Request $request): JsonResponse
    {
        $gradeIds = collect($request->input('grade_ids', []))->filter()->map(fn ($id): int => (int) $id)->all();

        return response()->json(
            Division::query()
                ->active()
                ->with('grade')
                ->when($gradeIds, fn ($query) => $query->whereIn('grade_id', $gradeIds))
                ->orderBy('division')
                ->get(['id', 'division', 'grade_id'])
                ->map(fn (Division $division): array => [
                    'id' => $division->id,
                    'text' => ($division->grade?->grade ? $division->grade->grade.' - ' : '').$division->division,
                    'grade_id' => $division->grade_id,
                ])
        );
    }

    public function sendMail(Request $request, SpecialEvent $specialEvent): JsonResponse
    {
        $emails = collect(preg_split('/[\s,;]+/', (string) $request->input('emails')))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $validator = Validator::make([
            ...$request->only(['subject', 'description']),
            'emails' => $emails,
        ], [
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'emails' => ['required', 'array', 'min:1', 'max:10'],
            'emails.*' => ['email'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Please check the mail details.',
                'errors' => $validator->errors(),
            ], 422);
        }

        Mail::to($emails)->queue(new SpecialEventDetailsMail(
            $specialEvent,
            $request->input('subject'),
            $request->input('description'),
        ));

        return response()->json([
            'message' => 'Special event mail queued successfully.',
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $specialEvents = $this->selectedSpecialEvents($request);

        if ($specialEvents->isEmpty()) {
            return back()->with('error', 'Select at least one special event to export.');
        }

        return Excel::download(new SpecialEventsExport($specialEvents), 'special-events.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $specialEvents = $this->selectedSpecialEvents($request);

        if ($specialEvents->isEmpty()) {
            return back()->with('error', 'Select at least one special event to export.');
        }

        return Pdf::loadView('special-events.export-pdf', ['specialEvents' => $specialEvents])
            ->download('special-events.pdf');
    }

    private function formOptions(): array
    {
        return [
            'eventTypes' => $this->specialEventService->eventTypes(),
            'academicYears' => $this->specialEventService->academicYears(),
            'grades' => $this->specialEventService->grades(),
            'divisions' => $this->specialEventService->divisions(),
            'staff' => $this->specialEventService->staff(),
            'teachers' => $this->specialEventService->teachers(),
            'participants' => SpecialEvent::PARTICIPANTS,
            'statuses' => SpecialEvent::STATUSES,
        ];
    }

    private function selectedSpecialEvents(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->specialEventService->selectedForExport($ids);
    }

    private function actionButtons(SpecialEvent $specialEvent): string
    {
        $buttons = sprintf(
            '<a href="%s" class="btn-edit" title="View"><i class="fa-solid fa-eye"></i></a>',
            route('special-events.show', $specialEvent)
        );

        $buttons .= sprintf(
            '<a href="#!" class="btn-view special-event-mail-btn" title="Send Mail" data-mail-url="%s" data-event-title="%s"><i class="fa-solid fa-user"></i></a>',
            route('special-events.send-mail', $specialEvent),
            e($specialEvent->event_title)
        );

        if (request()->user()?->can('edit.special-event')) {
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('special-events.edit', $specialEvent)
            );
        }

        if (request()->user()?->can('delete.special-event')) {
            $buttons .= view('special-events.partials.delete-button', compact('specialEvent'))->render();
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }

    private function badges(array $items): string
    {
        if (empty($items)) {
            return '-';
        }

        return collect($items)
            ->filter()
            ->map(fn (string $item): string => '<span class="badge bg-light text-dark border me-1 mb-1">'.e($item).'</span>')
            ->implode('');
    }

    private function statusClass(string $status): string
    {
        return match ($status) {
            'active', 'complete' => 'status-green',
            'cancelled', 'inactive' => 'status-red',
            'draft', 'postponed' => 'status-orange',
            default => '',
        };
    }
}
