<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProduct;
use App\Models\Platform;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AffiliateProductController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $query = AffiliateProduct::with('platform')
            ->orderBy('platform_id')
            ->orderBy('sort_order')
            ->orderBy('title');

        if ($request->filled('platform_id')) {
            $query->where('platform_id', $request->platform_id);
        }

        $products = $query->get()->map(fn ($p) => [
            'id' => $p->id,
            'platform_id' => $p->platform_id,
            'platform_name' => $p->platform->name ?? null,
            'title' => $p->title,
            'description' => $p->description,
            'image_url' => $p->image_url,
            'affiliate_url' => $p->affiliate_url,
            'sort_order' => $p->sort_order,
            'is_active' => $p->is_active,
        ]);

        $platforms = Platform::orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Backoffice/AffiliateProducts/Index', [
            'products' => $products,
            'platforms' => $platforms,
            'filterPlatformId' => $request->platform_id ? (int) $request->platform_id : null,
        ]);
    }

    public function create(Request $request)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $platforms = Platform::orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $defaultPlatformId = $request->platform_id ? (int) $request->platform_id : ($platforms->first()?->id ?? null);

        return Inertia::render('Backoffice/AffiliateProducts/Create', [
            'platforms' => $platforms,
            'defaultPlatformId' => $defaultPlatformId,
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $validated = $request->validate([
            'platform_id' => 'required|exists:platforms,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'image_url' => 'nullable|string|max:2048',
            'affiliate_url' => 'required|string|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;

        AffiliateProduct::create($validated);

        return redirect()->route('backoffice.affiliate-products.index', ['platform_id' => $validated['platform_id']])
            ->with('success', 'เพิ่มสินค้าเรียบร้อยแล้ว');
    }

    public function edit(Request $request, AffiliateProduct $affiliate_product)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $platforms = Platform::orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Backoffice/AffiliateProducts/Edit', [
            'product' => [
                'id' => $affiliate_product->id,
                'platform_id' => $affiliate_product->platform_id,
                'title' => $affiliate_product->title,
                'description' => $affiliate_product->description,
                'image_url' => $affiliate_product->image_url,
                'affiliate_url' => $affiliate_product->affiliate_url,
                'sort_order' => $affiliate_product->sort_order,
                'is_active' => $affiliate_product->is_active,
            ],
            'platforms' => $platforms,
        ]);
    }

    public function update(Request $request, AffiliateProduct $affiliate_product)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $validated = $request->validate([
            'platform_id' => 'required|exists:platforms,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'image_url' => 'nullable|string|max:2048',
            'affiliate_url' => 'required|string|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;

        $affiliate_product->update($validated);

        return redirect()->route('backoffice.affiliate-products.index', ['platform_id' => $validated['platform_id']])
            ->with('success', 'อัปเดตสินค้าเรียบร้อยแล้ว');
    }

    public function destroy(Request $request, AffiliateProduct $affiliate_product)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Only admin can access this page.');
        }

        $platformId = $affiliate_product->platform_id;
        $affiliate_product->delete();

        return redirect()->route('backoffice.affiliate-products.index', ['platform_id' => $platformId])
            ->with('success', 'ลบสินค้าเรียบร้อยแล้ว');
    }
}
