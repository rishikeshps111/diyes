<?php

namespace App\Http\Controllers;

use App\Exports\TitleMastersExport;
use App\Services\TitleMasterService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

abstract class TitleMasterController extends Controller
{
    abstract protected function service(): TitleMasterService;

    /**
     * @return array{singular:string, plural:string, route:string, permission:string, filename:string}
     */
    abstract protected function masterConfig(): array;

    public function index(): View
    {
        return view('title-masters.index', ['master' => $this->masterConfig()]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->service()->query($request->only('is_active'));

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request): void {
                $keyword = $request->input('search.value');

                if ($keyword) {
                    $query->where(function ($query) use ($keyword): void {
                        $query->where('code', 'like', "%{$keyword}%")
                            ->orWhere('title', 'like', "%{$keyword}%");
                    });
                }
            })
            ->addColumn('select', fn (Model $record): string => sprintf(
                '<input type="checkbox" class="title-master-row-check" value="%d">',
                $record->getKey(),
            ))
            ->editColumn('is_active', fn (Model $record): string => sprintf(
                '<span class="%s">%s</span>',
                $record->is_active ? 'status-green' : 'status-red',
                $record->is_active ? 'Active' : 'Inactive',
            ))
            ->addColumn('actions', fn (Model $record): string => $this->actionButtons($record))
            ->rawColumns(['select', 'is_active', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('title-masters.form', [
            'master' => $this->masterConfig(),
            'record' => $this->service()->make([
                'code' => $this->service()->nextCode(),
                'is_active' => true,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->service()->create($this->validated($request));
        $master = $this->masterConfig();

        return redirect()->route($master['route'].'.index')
            ->with('success', $master['singular'].' created successfully.');
    }

    public function edit(int|string $recordId): View
    {
        return view('title-masters.form', [
            'master' => $this->masterConfig(),
            'record' => $this->service()->findOrFail($recordId),
        ]);
    }

    public function update(Request $request, int|string $recordId): RedirectResponse
    {
        $record = $this->service()->findOrFail($recordId);
        $this->service()->update($record, $this->validated($request));
        $master = $this->masterConfig();

        return redirect()->route($master['route'].'.index')
            ->with('success', $master['singular'].' updated successfully.');
    }

    public function destroy(Request $request, int|string $recordId): JsonResponse|RedirectResponse
    {
        $this->service()->delete($this->service()->findOrFail($recordId));
        $master = $this->masterConfig();
        $message = $master['singular'].' deleted successfully.';

        return $request->expectsJson()
            ? response()->json(compact('message'))
            : redirect()->route($master['route'].'.index')->with('success', $message);
    }

    public function toggleStatus(Request $request, int|string $recordId): JsonResponse|RedirectResponse
    {
        $record = $this->service()->toggleStatus($this->service()->findOrFail($recordId));
        $message = $this->masterConfig()['singular'].' status updated successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'is_active' => $record->is_active,
                'status' => $record->is_active ? 'Active' : 'Inactive',
            ]);
        }

        return back()->with('success', $message);
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $records = $this->selectedRecords($request);
        $master = $this->masterConfig();

        if ($records->isEmpty()) {
            return back()->with('error', 'Select at least one '.strtolower($master['singular']).' to export.');
        }

        return Excel::download(new TitleMastersExport($records), $master['filename'].'.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $records = $this->selectedRecords($request);
        $master = $this->masterConfig();

        if ($records->isEmpty()) {
            return back()->with('error', 'Select at least one '.strtolower($master['singular']).' to export.');
        }

        return Pdf::loadView('title-masters.export-pdf', compact('records', 'master'))
            ->download($master['filename'].'.pdf');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function selectedRecords(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()->map(fn ($id): int => (int) $id)->unique()->values()->all();

        return $this->service()->selectedForExport($ids);
    }

    private function actionButtons(Model $record): string
    {
        $master = $this->masterConfig();
        $buttons = '';

        if (request()->user()?->can('edit.'.$master['permission'])) {
            $buttons .= sprintf(
                '<input type="checkbox" class="toggle-btn title-master-status-toggle" data-toggle-url="%s" %s>',
                e(route($master['route'].'.toggle-status', $record->getKey())),
                $record->is_active ? 'checked' : '',
            );
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                e(route($master['route'].'.edit', $record->getKey())),
            );
        }

        if (request()->user()?->can('delete.'.$master['permission'])) {
            $buttons .= sprintf(
                '<button type="button" class="btn-delete border-0 title-master-delete-btn" data-delete-url="%s"><i class="fa-solid fa-trash"></i></button>',
                e(route($master['route'].'.destroy', $record->getKey())),
            );
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }
}
