<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlatformController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $platforms = Platform::withCount('affiliateProducts')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'logo_url' => $p->logo_url,
                'sort_order' => $p->sort_order,
                'is_active' => $p->is_active,
                'affiliate_products_count' => $p->affiliate_products_count,
            ]);

        return Inertia::render('Backoffice/Platforms/Index', [
            'platforms' => $platforms,
        ]);
    }

    public function create(Request $request)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        return Inertia::render('Backoffice/Platforms/Create');
    }

    public function store(Request $request)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'logo_url' => 'nullable|string|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;

        Platform::create($validated);

        return redirect()->route('backoffice.platforms.index')
            ->with('success', 'สร้างแพลตฟอร์มเรียบร้อยแล้ว');
    }

    public function edit(Request $request, Platform $platform)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        return Inertia::render('Backoffice/Platforms/Edit', [
            'platform' => [
                'id' => $platform->id,
                'name' => $platform->name,
                'slug' => $platform->slug,
                'logo_url' => $platform->logo_url,
                'sort_order' => $platform->sort_order,
                'is_active' => $platform->is_active,
            ],
        ]);
    }

    public function update(Request $request, Platform $platform)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'logo_url' => 'nullable|string|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;

        $platform->update($validated);

        return redirect()->route('backoffice.platforms.index')
            ->with('success', 'อัปเดตแพลตฟอร์มเรียบร้อยแล้ว');
    }

    public function destroy(Request $request, Platform $platform)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $platform->delete();

        return redirect()->route('backoffice.platforms.index')
            ->with('success', 'ลบแพลตฟอร์มเรียบร้อยแล้ว');
    }
}
