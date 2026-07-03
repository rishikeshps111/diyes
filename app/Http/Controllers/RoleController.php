<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller implements HasMiddleware
{
    public function __construct(private readonly RoleService $roleService) {}

    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware(PermissionMiddleware::using('view.role'), ['index', 'data']),
            new Middleware(PermissionMiddleware::using('create.role'), ['create', 'store']),
            new Middleware(PermissionMiddleware::using('edit.role'), ['edit', 'update']),
            new Middleware(PermissionMiddleware::using('delete.role'), ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('roles.index');
    }

    public function data(Request $request): JsonResponse
    {
        return DataTables::eloquent($this->roleService->query())
            ->filter(function ($query) use ($request): void {
                $search = $request->input('search.value');

                if ($search) {
                    $query->where('name', 'like', "%{$search}%");
                }
            })
            ->addIndexColumn()
            ->editColumn('name', fn(Role $role): string => ucfirst($role->name))
            ->addColumn('users_count', fn(Role $role): int => $role->users_count)
            ->addColumn('actions', fn(Role $role): string => $this->actionButtons($role))
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('roles.form', [
            'role' => new Role(['guard_name' => RoleService::GUARD]),
            'permissionGroups' => $this->roleService->permissionGroups(),
            'selectedPermissions' => collect(),
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $this->roleService->create($request->validated());

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        $this->abortForProtectedRole($role);

        return view('roles.form', [
            'role' => $role->load('permissions'),
            'permissionGroups' => $this->roleService->permissionGroups(),
            'selectedPermissions' => $role->permissions->pluck('name'),
        ]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $this->abortForProtectedRole($role);

        $this->roleService->update($role, $request->validated());

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Request $request, Role $role): JsonResponse|RedirectResponse
    {
        $this->abortForProtectedRole($role);

        $this->roleService->delete($role);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Role deleted successfully.',
            ]);
        }

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    private function actionButtons(Role $role): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.role')) {
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('roles.edit', $role)
            );
        }

        if (request()->user()?->can('delete.role')) {
            $buttons .= view('roles.partials.delete-button', compact('role'))->render();
        }

        return '<div class="action-btns">' . $buttons . '</div>';
    }

    private function abortForProtectedRole(Role $role): void
    {
        abort_if($role->name === 'admin', 404);
    }
}
