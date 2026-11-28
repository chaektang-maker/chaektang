<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Source;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $users = User::with(['permissions', 'source:id,name,is_promoted'])
            ->orderBy('role')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'source_id' => $user->source_id,
                    'source_name' => $user->source?->name,
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

    public function create(Request $request)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $allPermissions = Permission::orderBy('order')->get();
        $sources = Source::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Backoffice/Users/Create', [
            'allPermissions' => $allPermissions,
            'sources' => $sources,
        ]);
    }

    private function normalizeLoginIdentifier(string $rawLogin): string
    {
        $raw = trim($rawLogin);
        $digitsOnly = preg_replace('/[^0-9]/', '', $raw);

        if (!str_contains($raw, '@')) {
            if (!preg_match('/^0[0-9]{9}$/', $digitsOnly)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => 'กรุณากรอกเบอร์โทรศัพท์มือถือ 10 หลัก (เช่น 0812345678) หรืออีเมลให้ถูกต้อง',
                ]);
            }
            return $digitsOnly;
        }

        if (!filter_var($raw, FILTER_VALIDATE_EMAIL)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            ]);
        }

        return strtolower($raw);
    }

    public function store(Request $request)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $request->merge([
            'email' => $this->normalizeLoginIdentifier((string) $request->input('email', '')),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,staff',
            'source_id' => 'nullable|exists:sources,id',
            'new_source_name' => 'nullable|string|max:255',
            'permission_ids' => 'array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $sourceId = $validated['source_id'] ?? null;
        if (!$sourceId && !empty($validated['new_source_name'])) {
            $newSource = Source::firstOrCreate(
                ['name' => trim($validated['new_source_name'])],
                ['status' => 'active', 'popularity_score' => 0]
            );
            $sourceId = $newSource->id;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'source_id' => $sourceId,
        ]);

        if ($user->role === 'staff' && isset($validated['permission_ids'])) {
            $user->permissions()->sync($validated['permission_ids']);
        }

        return redirect()->route('backoffice.users.index')
            ->with('success', 'สร้างผู้ใช้เรียบร้อยแล้ว');
    }

    public function edit(Request $request, User $user)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $allPermissions = Permission::orderBy('order')->get();
        $sources = Source::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Backoffice/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'source_id' => $user->source_id,
                'permissions' => $user->permissions->pluck('id')->toArray(),
            ],
            'allPermissions' => $allPermissions,
            'sources' => $sources,
        ]);
    }

    public function update(Request $request, User $user)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $request->merge([
            'email' => $this->normalizeLoginIdentifier((string) $request->input('email', '')),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,staff',
            'source_id' => 'nullable|exists:sources,id',
            'new_source_name' => 'nullable|string|max:255',
            'permission_ids' => 'array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $sourceId = $validated['source_id'] ?? null;
        if (!$sourceId && !empty($validated['new_source_name'])) {
            $newSource = Source::firstOrCreate(
                ['name' => trim($validated['new_source_name'])],
                ['status' => 'active', 'popularity_score' => 0]
            );
            $sourceId = $newSource->id;
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'source_id' => $sourceId,
        ]);

        if (!empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        if ($user->role === 'staff') {
            $user->permissions()->sync($validated['permission_ids'] ?? []);
        } else {
            $user->permissions()->detach();
        }

        return redirect()->route('backoffice.users.index')
            ->with('success', 'อัปเดตผู้ใช้เรียบร้อยแล้ว');
    }

    public function destroy(Request $request, User $user)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        if ($user->id === $request->user()->id) {
            return redirect()->back()
                ->with('error', 'ไม่สามารถลบบัญชีของตัวเองได้');
        }

        $user->delete();

        return redirect()->route('backoffice.users.index')
            ->with('success', 'ลบผู้ใช้เรียบร้อยแล้ว');
    }
}
