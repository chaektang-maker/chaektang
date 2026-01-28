<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PermissionController extends Controller
{
    /**
     * Display a listing of staff users and their permissions.
     */
    public function index(Request $request)
    {
        // ตรวจสอบว่าเป็น admin
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }
        $staffUsers = User::where('role', 'staff')
            ->with('permissions')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
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

        return Inertia::render('Backoffice/Permissions/Index', [
            'staffUsers' => $staffUsers,
            'allPermissions' => $allPermissions,
        ]);
    }

    /**
     * Update permissions for a staff user.
     */
    public function update(Request $request, User $user)
    {
        // ตรวจสอบว่าเป็น admin
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }
        
        // ตรวจสอบว่าเป็น staff เท่านั้น
        if ($user->role !== 'staff') {
            abort(403, 'Can only update permissions for staff users.');
        }

        $request->validate([
            'permission_ids' => 'array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $user->permissions()->sync($request->permission_ids ?? []);

        return redirect()->back()->with('success', 'อัปเดตสิทธิ์เรียบร้อยแล้ว');
    }
}
