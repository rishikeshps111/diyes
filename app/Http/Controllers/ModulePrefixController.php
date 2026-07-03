<?php

namespace App\Http\Controllers;

use App\Http\Requests\ModulePrefixRequest;
use App\Models\ModulePrefix;
use App\Services\ModulePrefixService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ModulePrefixController extends Controller implements HasMiddleware
{
    public function __construct(private readonly ModulePrefixService $modulePrefixService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.module-prefix', only: ['index', 'data']),
            new Middleware('can:edit.module-prefix', only: ['edit', 'update']),
        ];
    }

    public function index(): View
    {
        return view('module-prefixes.index');
    }

    public function data(): JsonResponse
    {
        $query = $this->modulePrefixService->query();

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('module_name', fn (ModulePrefix $modulePrefix): string => $modulePrefix->module_name)
            ->addColumn('actions', fn (ModulePrefix $modulePrefix): string => $this->actionButtons($modulePrefix))
            ->filterColumn('module_name', function ($query, string $keyword): void {
                $query->where('module', 'like', "%{$keyword}%");
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function edit(ModulePrefix $modulePrefix): View
    {
        return view('module-prefixes.form', [
            'modulePrefix' => $modulePrefix,
        ]);
    }

    public function update(ModulePrefixRequest $request, ModulePrefix $modulePrefix): RedirectResponse
    {
        $this->modulePrefixService->update($modulePrefix, $request->validated());

        return redirect()
            ->route('module-prefixes.index')
            ->with('success', 'Module prefix updated successfully.');
    }

    private function actionButtons(ModulePrefix $modulePrefix): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.module-prefix')) {
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('module-prefixes.edit', $modulePrefix)
            );
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }
}
