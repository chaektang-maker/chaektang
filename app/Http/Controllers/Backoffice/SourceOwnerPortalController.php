<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProduct;
use App\Models\LotteryNumber;
use App\Models\LotteryResult;
use App\Models\LottoData;
use App\Models\Platform;
use App\Models\Source;
use App\Services\AccuracyCalculationService;
use App\Services\MediaStorageService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SourceOwnerPortalController extends Controller
{
    /**
     * ดึงสำนักที่ผู้ใช้นี้กำลังจัดการ (ถ้าเป็นเจ้าสำนักดึงจาก user->source_id / ถ้าเป็นแอดมินเลือกดูได้ทุกสำนัก)
     */
    private function resolveActiveSource(Request $request): ?Source
    {
        $user = $request->user('web');
        if (!$user) {
            abort(403);
        }

        if ($user->isAdmin()) {
            $requestedId = $request->integer('source_id') ?: $user->source_id;
            if ($requestedId) {
                $found = Source::find($requestedId);
                if ($found) {
                    return $found;
                }
            }
            return Source::orderByDesc('is_promoted')->orderBy('name')->first();
        }

        if (!$user->source_id) {
            abort(403, 'บัญชีของคุณยังไม่ได้ผูกกับสำนักหวย กรุณาติดต่อแอดมิน');
        }

        return Source::findOrFail($user->source_id);
    }

    /**
     * ดึงหรือสร้าง Platform ร้านค้าที่ผูกกับสำนักนี้โดยอัตโนมัติ
     */
    private function resolveSourcePlatform(Source $source): Platform
    {
        $slug = 'source-' . $source->id;
        $shouldBeActive = $source->status === 'active';

        $platform = Platform::where('slug', $slug)
            ->orWhere('name', $source->name)
            ->first();

        if (!$platform) {
            $platform = Platform::create([
                'name' => $source->name,
                'slug' => $slug,
                'is_active' => $shouldBeActive,
                'sort_order' => 0,
            ]);
        } else {
            if ($platform->is_active !== $shouldBeActive || $platform->name !== $source->name || $platform->slug !== $slug) {
                $platform->update([
                    'name' => $source->name,
                    'slug' => $slug,
                    'is_active' => $shouldBeActive,
                ]);
            }
        }

        return $platform;
    }

    public function index(Request $request)
    {
        $user = $request->user('web');
        $source = $this->resolveActiveSource($request);

        $allSources = $user->isAdmin()
            ? Source::orderBy('name')->get(['id', 'name', 'is_promoted'])
            : collect();

        if (!$source) {
            return Inertia::render('Backoffice/SourcePortal/Index', [
                'source' => null,
                'allSources' => $allSources,
                'availableDrawDates' => [],
                'numbers' => [],
                'products' => [],
                'isAdmin' => $user->isAdmin(),
            ]);
        }

        $availableDrawDates = LottoData::query()
            ->whereNotNull('draw_date')
            ->orderByDesc('draw_date')
            ->limit(24)
            ->get()
            ->pluck('draw_date')
            ->map(fn ($d) => $d instanceof \DateTimeInterface ? $d->format('Y-m-d') : substr((string) $d, 0, 10))
            ->values();

        $numbers = LotteryNumber::query()
            ->where('source_id', $source->id)
            ->orderByDesc('draw_date')
            ->limit(20)
            ->get()
            ->map(fn (LotteryNumber $n) => [
                'id' => $n->id,
                'draw_date' => $n->draw_date instanceof \DateTimeInterface
                    ? $n->draw_date->format('Y-m-d')
                    : ($n->draw_date ? substr((string) $n->draw_date, 0, 10) : null),
                'two_digit' => $n->two_digit ?? '',
                'three_digit' => $n->three_digit ?? '',
                'running_numbers' => is_array($n->running_numbers) ? implode(', ', $n->running_numbers) : ($n->running_numbers ?? ''),
                'updated_at' => $n->updated_at instanceof \DateTimeInterface ? $n->updated_at->toIso8601String() : (string) $n->updated_at,
            ]);

        $platform = Platform::where('slug', 'source-' . $source->id)
            ->orWhere('name', $source->name)
            ->first();

        $products = $platform
            ? AffiliateProduct::where('platform_id', $platform->id)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get()
                ->map(fn (AffiliateProduct $p) => [
                    'id' => $p->id,
                    'title' => $p->title,
                    'description' => $p->description ?? '',
                    'image_url' => $p->image_url ?? '',
                    'affiliate_url' => $p->affiliate_url ?? '',
                    'is_active' => (bool) $p->is_active,
                ])
            : collect();

        return Inertia::render('Backoffice/SourcePortal/Index', [
            'source' => [
                'id' => $source->id,
                'name' => $source->name,
                'description' => $source->description ?? '',
                'status' => $source->status,
                'view_count' => (int) $source->view_count,
                'is_currently_promoted' => $source->isCurrentlyPromoted(),
                'promoted_until' => $source->promoted_until?->format('Y-m-d'),
            ],
            'allSources' => $allSources,
            'availableDrawDates' => $availableDrawDates,
            'numbers' => $numbers,
            'products' => $products,
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    /**
     * อัปเดตชื่อสำนัก และ คำโปรย / ช่องทางติดต่อ (Line ID / ชื่อเพจ) -> ขึ้นหน้าเว็บทันที
     */
    public function updateProfile(Request $request)
    {
        $source = $this->resolveActiveSource($request);
        if (!$source) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $oldName = $source->name;
        $source->update([
            'name' => trim($validated['name']),
            'description' => trim((string) ($validated['description'] ?? '')) ?: null,
        ]);

        // ซิงค์ชื่อร้านค้า Platform ให้ตรงกับชื่อสำนักและสถานะการแสดงผล
        Platform::where('slug', 'source-' . $source->id)
            ->orWhere('name', $oldName)
            ->update([
                'name' => $source->name,
                'slug' => 'source-' . $source->id,
                'is_active' => $source->status === 'active',
            ]);

        return redirect()->route('backportal.index', ['source_id' => $source->id])
            ->with('success', 'บันทึกข้อมูลสำนักและช่องทางติดต่อขึ้นหน้าเว็บเรียบร้อยแล้ว!');
    }

    /**
     * บันทึกหรือแก้ไขเลขเด็ดประจำงวดของสำนัก -> ขึ้นหน้าเว็บทันทีโดยไม่ต้องรออนุมัติ
     */
    public function saveNumbers(Request $request)
    {
        $source = $this->resolveActiveSource($request);
        if (!$source) {
            abort(404);
        }

        $validated = $request->validate([
            'draw_date' => 'required|date',
            'two_digit' => 'nullable|string|max:10',
            'three_digit' => 'nullable|string|max:10',
            'running_numbers' => 'nullable|string|max:100',
        ]);

        $running = null;
        if (!empty($validated['running_numbers'])) {
            $running = collect(explode(',', $validated['running_numbers']))
                ->map(fn ($v) => trim($v))
                ->filter(fn ($v) => $v !== '')
                ->values()
                ->all();
        }

        LotteryNumber::updateOrCreate(
            [
                'source_id' => $source->id,
                'draw_date' => $validated['draw_date'],
            ],
            [
                'two_digit' => trim((string) ($validated['two_digit'] ?? '')) ?: null,
                'three_digit' => trim((string) ($validated['three_digit'] ?? '')) ?: null,
                'running_numbers' => $running,
            ]
        );

        try {
            if (LotteryResult::where('draw_date', $validated['draw_date'])->exists()) {
                app(AccuracyCalculationService::class)->calculateForDraw($validated['draw_date'], true);
            }
        } catch (\Throwable $e) {
        }

        return redirect()->route('backportal.index', ['source_id' => $source->id])
            ->with('success', "บันทึกเลขเด็ดงวด {$validated['draw_date']} ขึ้นหน้าเว็บเรียบร้อยแล้ว!");
    }

    /**
     * เพิ่มหรือแก้ไขสินค้าวัตถุมงคล/สายมูของสำนัก -> ขึ้นแสดงใน 3 หน้าหลักหน้าบ้านทันที
     */
    public function saveProduct(Request $request)
    {
        $source = $this->resolveActiveSource($request);
        if (!$source) {
            abort(404);
        }

        $validated = $request->validate([
            'id' => 'nullable|integer',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'affiliate_url' => 'required|string|max:2048',
            'image_url' => 'nullable|string|max:2048',
            'image_file' => 'nullable|file|image|max:5120',
            'is_active' => 'nullable|boolean',
        ]);

        $platform = $this->resolveSourcePlatform($source);

        $imageUrl = trim((string) ($validated['image_url'] ?? ''));
        if ($request->hasFile('image_file')) {
            $storedPath = MediaStorageService::store($request->file('image_file'), 'affiliate-products', 'product');
            $imageUrl = MediaStorageService::url($storedPath);
        }

        if (!empty($validated['id'])) {
            $product = AffiliateProduct::where('platform_id', $platform->id)
                ->where('id', $validated['id'])
                ->firstOrFail();

            $product->update([
                'title' => trim($validated['title']),
                'description' => trim((string) ($validated['description'] ?? '')) ?: null,
                'affiliate_url' => trim($validated['affiliate_url']),
                'image_url' => $imageUrl !== '' ? $imageUrl : $product->image_url,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            return redirect()->route('backportal.index', ['source_id' => $source->id])
                ->with('success', "อัปเดตสินค้า '{$product->title}' เรียบร้อยแล้ว!");
        }

        AffiliateProduct::create([
            'platform_id' => $platform->id,
            'title' => trim($validated['title']),
            'description' => trim((string) ($validated['description'] ?? '')) ?: null,
            'affiliate_url' => trim($validated['affiliate_url']),
            'image_url' => $imageUrl !== '' ? $imageUrl : null,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        return redirect()->route('backportal.index', ['source_id' => $source->id])
            ->with('success', "ลงสินค้า '{$validated['title']}' เรียบร้อยแล้ว!");
    }

    public function deleteProduct(Request $request, int $productId)
    {
        $source = $this->resolveActiveSource($request);
        if (!$source) {
            abort(404);
        }

        $platform = $this->resolveSourcePlatform($source);

        $product = AffiliateProduct::where('platform_id', $platform->id)
            ->where('id', $productId)
            ->firstOrFail();

        $title = $product->title;
        $product->delete();

        return redirect()->route('backportal.index', ['source_id' => $source->id])
            ->with('success', "ลบสินค้า '{$title}' เรียบร้อยแล้ว");
    }
}
