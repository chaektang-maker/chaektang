<?php

namespace App\Http\Controllers\Admin;

use App\Models\Source;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SourceController extends Controller
{
    public function index(Request $request)
    {
        $query = Source::query()->with(['owners:id,name,email,source_id']);

        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('owners', function ($oq) use ($search) {
                        $oq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($hasOwner = $request->string('has_owner')->toString()) {
            if ($hasOwner === 'yes') {
                $query->whereHas('owners');
            } elseif ($hasOwner === 'no') {
                $query->whereDoesntHave('owners');
            }
        }

        $sources = $query
            ->orderByDesc('is_promoted')
            ->orderByDesc('popularity_score')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Sources/Index', [
            'filters' => [
                'q' => $request->string('q')->toString(),
                'status' => $request->string('status')->toString(),
                'has_owner' => $request->string('has_owner')->toString(),
            ],
            'sources' => $sources,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Sources/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,suspended'],
            'popularity_score' => ['nullable', 'integer', 'min:0'],
            'is_promoted' => ['nullable', 'boolean'],
            'promoted_until' => ['nullable', 'date'],
        ]);

        $data['is_promoted'] = (bool)($data['is_promoted'] ?? false);

        Source::create($data);

        return redirect()->route('backoffice.sources.index')->with('success', 'เพิ่มสำนักสำเร็จ');
    }

    public function edit(Source $source)
    {
        return Inertia::render('Admin/Sources/Edit', [
            'source' => [
                'id' => $source->id,
                'name' => $source->name,
                'description' => $source->description,
                'status' => $source->status,
                'popularity_score' => $source->popularity_score,
                'is_promoted' => (bool) $source->is_promoted,
                'promoted_until' => $source->promoted_until?->format('Y-m-d'),
            ],
        ]);
    }

    public function update(Request $request, Source $source)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,suspended'],
            'popularity_score' => ['nullable', 'integer', 'min:0'],
            'is_promoted' => ['nullable', 'boolean'],
            'promoted_until' => ['nullable', 'date'],
        ]);

        $data['is_promoted'] = (bool)($data['is_promoted'] ?? false);

        $oldName = $source->name;
        $source->update($data);

        \App\Models\Platform::where('slug', 'source-' . $source->id)
            ->orWhere('name', $oldName)
            ->update([
                'name' => $source->name,
                'slug' => 'source-' . $source->id,
                'is_active' => $source->status === 'active',
            ]);

        return redirect()->route('backoffice.sources.index')->with('success', 'บันทึกสำเร็จ');
    }

    public function destroy(Source $source)
    {
        \App\Models\Platform::where('slug', 'source-' . $source->id)
            ->orWhere('name', $source->name)
            ->update(['is_active' => false]);

        $source->delete();

        return redirect()->route('backoffice.sources.index')->with('success', 'ลบสำนักสำเร็จ');
    }

    /**
     * สลับสถานะ เปิดแสดง (active) / ซ่อนสำนัก (suspended) ในคลิกเดียวจากตาราง
     */
    public function toggleStatus(Source $source)
    {
        $newStatus = $source->status === 'active' ? 'suspended' : 'active';
        $source->update(['status' => $newStatus]);

        // ซิงค์สถานะร้านค้า Platform ของสำนักนี้ให้เปิด/ซ่อนตามสำนักทันที
        \App\Models\Platform::where('slug', 'source-' . $source->id)
            ->orWhere('name', $source->name)
            ->update(['is_active' => $newStatus === 'active']);

        $msg = $newStatus === 'active'
            ? "เปิดแสดงสำนัก \"{$source->name}\" บนหน้าเว็บเรียบร้อยแล้ว"
            : "ซ่อนสำนัก \"{$source->name}\" จากหน้าเว็บชั่วคราวเรียบร้อยแล้ว";

        return redirect()->back()->with('success', $msg);
    }
}
