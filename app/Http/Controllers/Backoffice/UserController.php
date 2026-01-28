<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        // ตรวจสอบว่าเป็น admin
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $users = User::with('permissions')
            ->orderBy('role')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'permissions' => $user->permissions->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'slug' => $permission->slug,
                            'name' => $permission->name,
                        ];
                    }),
                ];
            });

        $allPermissions = Permission::orderBy('order')->get();

        return Inertia::render('Backoffice/Users/Index', [
            'users' => $users,
            'allPermissions' => $allPermissions,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(Request $request)
    {
        // ตรวจสอบว่าเป็น admin
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $allPermissions = Permission::orderBy('order')->get();

        return Inertia::render('Backoffice/Users/Create', [
            'allPermissions' => $allPermissions,
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        // ตรวจสอบว่าเป็น admin
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,staff',
            'permission_ids' => 'array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        // ถ้าเป็น staff ให้กำหนด permissions
        if ($user->role === 'staff' && isset($validated['permission_ids'])) {
            $user->permissions()->sync($validated['permission_ids']);
        }

        return redirect()->route('backoffice.users.index')
            ->with('success', 'สร้างผู้ใช้เรียบร้อยแล้ว');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(Request $request, User $user)
    {
        // ตรวจสอบว่าเป็น admin
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $allPermissions = Permission::orderBy('order')->get();

        return Inertia::render('Backoffice/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'permissions' => $user->permissions->pluck('id')->toArray(),
            ],
            'allPermissions' => $allPermissions,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        // ตรวจสอบว่าเป็น admin
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,staff',
            'permission_ids' => 'array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        // อัปเดตรหัสผ่านถ้ามีการระบุ
        if (!empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        // ถ้าเป็น staff ให้กำหนด permissions
        if ($user->role === 'staff') {
            $user->permissions()->sync($validated['permission_ids'] ?? []);
        } else {
            // ถ้าเปลี่ยนเป็น admin ให้ลบ permissions ทั้งหมด
            $user->permissions()->detach();
        }

        return redirect()->route('backoffice.users.index')
            ->with('success', 'อัปเดตผู้ใช้เรียบร้อยแล้ว');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user)
    {
        // ตรวจสอบว่าเป็น admin
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        // ห้ามลบตัวเอง
        if ($user->id === $request->user()->id) {
            return redirect()->back()
                ->with('error', 'ไม่สามารถลบบัญชีของตัวเองได้');
        }

        $user->delete();

        return redirect()->route('backoffice.users.index')
            ->with('success', 'ลบผู้ใช้เรียบร้อยแล้ว');
    }
}
