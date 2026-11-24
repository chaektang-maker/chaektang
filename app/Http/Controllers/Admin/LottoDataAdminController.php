<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LottoData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LottoDataAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = LottoData::query();

        // Search filter
        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('lotto_id', 'like', "%{$search}%")
                  ->orWhere('date_text', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->string('status')->toString() !== '') {
            $status = $request->string('status')->toString();
            $query->where('is_fetched', $status === 'fetched');
        }

        // Paginate records
        $lottoData = $query
            ->orderByDesc('draw_date')
            ->orderByDesc('lotto_id')
            ->paginate(15)
            ->withQueryString();

        // Count stats
        $totalRecords = LottoData::count();
        $fetchedRecords = LottoData::where('is_fetched', true)->count();
        $pendingRecords = $totalRecords - $fetchedRecords;

        return Inertia::render('Admin/LottoData/Index', [
            'filters' => [
                'q' => $request->string('q')->toString(),
                'status' => $request->string('status')->toString(),
            ],
            'lottoData' => $lottoData,
            'stats' => [
                'total' => $totalRecords,
                'fetched' => $fetchedRecords,
                'pending' => $pendingRecords,
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/LottoData/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'lotto_id' => ['required', 'string', 'max:50', 'unique:lotto_data,lotto_id', 'regex:/^[0-9]{8}$/'],
            'url' => ['required', 'string', 'max:255'],
            'date_text' => ['required', 'string', 'max:255'],
            'draw_date' => ['nullable', 'date'],
            'is_fetched' => ['required', 'boolean'],
        ], [
            'lotto_id.regex' => 'รูปแบบ Lotto ID ต้องเป็นตัวเลข 8 หลัก (วันเดือนปี พ.ศ. เช่น 16082566)'
        ]);

        LottoData::create($data);

        return redirect()->route('backoffice.lotto-data.index')->with('success', 'เพิ่มข้อมูลหวยสำเร็จแล้ว');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LottoData $lottoData): Response
    {
        return Inertia::render('Admin/LottoData/Edit', [
            'lottoData' => [
                'lotto_id' => $lottoData->lotto_id,
                'url' => $lottoData->url,
                'date_text' => $lottoData->date_text,
                'draw_date' => $lottoData->draw_date?->format('Y-m-d'),
                'is_fetched' => (bool) $lottoData->is_fetched,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LottoData $lottoData)
    {
        $data = $request->validate([
            'lotto_id' => ['required', 'string', 'max:50', 'unique:lotto_data,lotto_id,' . $lottoData->lotto_id . ',lotto_id', 'regex:/^[0-9]{8}$/'],
            'url' => ['required', 'string', 'max:255'],
            'date_text' => ['required', 'string', 'max:255'],
            'draw_date' => ['nullable', 'date'],
            'is_fetched' => ['required', 'boolean'],
        ], [
            'lotto_id.regex' => 'รูปแบบ Lotto ID ต้องเป็นตัวเลข 8 หลัก (วันเดือนปี พ.ศ. เช่น 16082566)'
        ]);

        $lottoData->update($data);

        return redirect()->route('backoffice.lotto-data.index')->with('success', 'อัปเดตข้อมูลหวยสำเร็จแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LottoData $lottoData)
    {
        $lottoData->delete();

        return redirect()->route('backoffice.lotto-data.index')->with('success', 'ลบข้อมูลหวยสำเร็จแล้ว');
    }
}
