<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminProfileRequest;
use App\Models\AdminProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminManagementController extends Controller
{
    public function index(): View
    {
        return view('admins.index', [
            'admins' => User::query()
                ->with('adminProfile')
                ->where('role', 'admin')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(AdminProfileRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->string('name')->toString(),
                'contact_number' => $request->string('contact_number')->toString(),
                'password' => $request->string('password')->toString(),
                'role' => 'admin',
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            AdminProfile::create([
                'user_id' => $user->id,
                'age' => (int) $request->age,
                'contact_number' => $request->string('contact_number')->toString(),
            ]);
        });

        return back()->with('status', 'Admin added successfully with contact number and password login access.');
    }

    public function update(AdminProfileRequest $request, User $admin): RedirectResponse
    {
        DB::transaction(function () use ($request, $admin) {
            $admin->fill([
                'name' => $request->string('name')->toString(),
                'contact_number' => $request->string('contact_number')->toString(),
            ]);

            if ($request->filled('password')) {
                $admin->password = $request->string('password')->toString();
            }

            $admin->save();

            $admin->adminProfile()->updateOrCreate(
                ['user_id' => $admin->id],
                [
                    'age' => (int) $request->age,
                    'contact_number' => $request->string('contact_number')->toString(),
                ]
            );
        });

        return back()->with('status', 'Admin updated successfully.');
    }

    public function destroy(User $admin): RedirectResponse
    {
        if (auth()->id() === $admin->id) {
            return back()->withErrors(['admin' => 'You cannot delete the currently logged-in admin.']);
        }

        $admin->delete();

        return back()->with('status', 'Admin deleted successfully.');
    }
}
