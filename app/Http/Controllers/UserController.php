<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\UserService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller implements HasMiddleware
{
    public function __construct(private readonly UserService $userService) {}

    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware(PermissionMiddleware::using('view.user'), ['index', 'data', 'show', 'exportExcel', 'exportPdf']),
            new Middleware(PermissionMiddleware::using('create.user'), ['create', 'store']),
            new Middleware(PermissionMiddleware::using('edit.user'), ['edit', 'update', 'toggleStatus', 'resetPassword']),
            new Middleware(PermissionMiddleware::using('delete.user'), ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('users.index', $this->formOptions());
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->userService->query($request->only([
            'department_id',
            'role_id',
            'last_login_at',
            'is_active',
        ]));

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request): void {
                $keyword = $request->input('search.value');

                if (! $keyword) {
                    return;
                }

                $query->where(function ($query) use ($keyword): void {
                    $query->where('employee_code', 'like', "%{$keyword}%")
                        ->orWhere('username', 'like', "%{$keyword}%")
                        ->orWhere('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhere('phone', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('select', fn (User $user): string => sprintf(
                '<input type="checkbox" class="user-row-check" value="%d">',
                $user->id
            ))
            ->addColumn('role_name', fn (User $user): string => $user->role?->name ? ucfirst($user->role->name) : '-')
            ->addColumn('department', fn (User $user): string => $user->department?->department_name ?? '-')
            ->editColumn('last_login_at', fn (User $user): string => $user->last_login_at?->format('d M Y h:i A') ?? '-')
            ->editColumn('is_active', fn (User $user): string => sprintf(
                '<span class="%s">%s</span>',
                $user->is_active ? 'status-green' : 'status-red',
                $user->is_active ? 'Active' : 'Inactive'
            ))
            ->addColumn('actions', fn (User $user): string => $this->actionButtons($user))
            ->rawColumns(['select', 'is_active', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('users.form', [
            'user' => new User([
                'employee_code' => $this->userService->nextEmployeeCode(),
                'phone_country_code' => '+91',
                'is_active' => true,
                'is_two_factor_enabled' => false,
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $this->userService->create($request->validated());

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        return view('users.show', [
            'user' => $user->load(['department', 'designation', 'role']),
        ]);
    }

    public function edit(User $user): View
    {
        return view('users.form', [
            'user' => $user,
            ...$this->formOptions(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $this->userService->update($user, $request->validated());

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user): JsonResponse|RedirectResponse
    {
        if ($request->user()->is($user)) {
            abort(422, 'You cannot delete your own account.');
        }

        $this->userService->delete($user);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'User deleted successfully.']);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(Request $request, User $user): JsonResponse|RedirectResponse
    {
        if ($request->user()->is($user)) {
            abort(422, 'You cannot deactivate your own account.');
        }

        $user = $this->userService->toggleStatus($user);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'User status updated successfully.',
                'is_active' => $user->is_active,
                'status' => $user->is_active ? 'Active' : 'Inactive',
            ]);
        }

        return back()->with('success', 'User status updated successfully.');
    }

    public function resetPassword(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $this->userService->resetPassword($user, $validated['password']);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Password reset successfully.']);
        }

        return back()->with('success', 'Password reset successfully.');
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $users = $this->selectedUsers($request);

        if ($users->isEmpty()) {
            return back()->with('error', 'Select at least one user to export.');
        }

        return Excel::download(new UsersExport($users), 'users.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $users = $this->selectedUsers($request);

        if ($users->isEmpty()) {
            return back()->with('error', 'Select at least one user to export.');
        }

        return Pdf::loadView('users.export-pdf', ['users' => $users])
            ->download('users.pdf');
    }

    private function selectedUsers(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->userService->selectedForExport($ids);
    }

    private function formOptions(): array
    {
        return [
            'departments' => $this->userService->departments(),
            'designations' => $this->userService->designations(),
            'roles' => $this->userService->roles(),
        ];
    }

    private function actionButtons(User $user): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.user')) {
            $buttons .= view('users.partials.toggle-status', compact('user'))->render();
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('users.edit', $user)
            );
        }

        if (request()->user()?->can('delete.user')) {
            $buttons .= view('users.partials.delete-button', compact('user'))->render();
        }

        $menu = sprintf(
            '<div class="dropdown">
                <button class="dropdown-toggle tgle-cs-btns" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="%s">View Details</a></li>
                    %s
                </ul>
            </div>',
            route('users.show', $user),
            request()->user()?->can('edit.user')
                ? sprintf('<li><button type="button" class="dropdown-item user-reset-password-btn" data-reset-url="%s" data-user-name="%s">Reset Password</button></li>', route('users.reset-password', $user), e($user->name))
                : ''
        );

        return '<div class="action-btns">' . $buttons . $menu . '</div>';
    }
}
