<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class TeacherProfileController extends Controller
{
    public function show(Request $request): View
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);

        return view('teachers.portal.profile', ['teacher' => $teacher->load(['department', 'designation'])]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        abort_unless($request->user()->teacher, 403);
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);
        $request->user()->update(['password' => $validated['password']]);

        return back()->with('success', 'Password changed successfully.');
    }
}
