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
        $query = Source::query();

        if ($search = $request->string('q')->toString()) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $sources = $query
            ->orderByDesc('is_promoted')
            ->orderByDesc('popularity_score')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Sources/Index', [
            'filters' => [
                'q' => $request->string('q')->toString(),
                'status' => $request->string('status')->toString(),
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

        return redirect()->route('admin.sources.index')->with('success', 'เพิ่มสำนักสำเร็จ');
    }

    public function edit(Source $source)
    {
        return Inertia::render('Admin/Sources/Edit', [
            'source' => $source,
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

        $source->update($data);

        return redirect()->route('admin.sources.index')->with('success', 'บันทึกสำเร็จ');
    }

    public function destroy(Source $source)
    {
        $source->delete();

        return redirect()->route('admin.sources.index')->with('success', 'ลบสำนักสำเร็จ');
    }
}
