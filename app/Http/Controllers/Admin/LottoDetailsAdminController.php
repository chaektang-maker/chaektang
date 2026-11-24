<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LottoDetail;
use App\Models\LottoData;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LottoDetailsAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = LottoDetail::query()->with('lottoData');

        // Search filter (by lotto_id, date, endpoint)
        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('lotto_id', 'like', "%{$search}%")
                  ->orWhere('date', 'like', "%{$search}%")
                  ->orWhere('endpoint', 'like', "%{$search}%");
            });
        }

        $lottoDetails = $query
            ->orderByDesc('lotto_id')
            ->paginate(15)
            ->withQueryString();

        // Calculate stats
        $totalDetails = LottoDetail::count();
        $totalLottoData = LottoData::count();
        $missingDetails = LottoData::whereDoesntHave('detail')->count();

        return Inertia::render('Admin/LottoDetails/Index', [
            'filters' => [
                'q' => $request->string('q')->toString(),
            ],
            'lottoDetails' => $lottoDetails,
            'stats' => [
                'total' => $totalDetails,
                'totalLottoData' => $totalLottoData,
                'missing' => $missingDetails,
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        // Get all lotto_data to choose from
        $lottoDataList = LottoData::orderByDesc('draw_date')
            ->orderByDesc('lotto_id')
            ->get(['lotto_id', 'draw_date']);

        return Inertia::render('Admin/LottoDetails/Create', [
            'lottoDataList' => $lottoDataList,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'lotto_id' => ['required', 'string', 'exists:lotto_data,lotto_id', 'unique:lotto_details,lotto_id'],
            'date' => ['nullable', 'string', 'max:255'],
            'endpoint' => ['nullable', 'string', 'max:255'],
        ], [
            'lotto_id.unique' => 'งวดหวยนี้มีรายละเอียดหวยในระบบอยู่แล้ว',
            'lotto_id.exists' => 'ไม่พบข้อมูลหลักของงวดหวยนี้ในระบบ',
        ]);

        LottoDetail::create($data);

        return redirect()->route('backoffice.lotto-details-admin.index')->with('success', 'เพิ่มรายละเอียดงวดหวยสำเร็จแล้ว');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LottoDetail $lottoDetailsAdmin): Response
    {
        // Get all lotto_data to choose from
        $lottoDataList = LottoData::orderByDesc('draw_date')
            ->orderByDesc('lotto_id')
            ->get(['lotto_id', 'draw_date']);

        return Inertia::render('Admin/LottoDetails/Edit', [
            'lottoDetail' => $lottoDetailsAdmin,
            'lottoDataList' => $lottoDataList,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LottoDetail $lottoDetailsAdmin): RedirectResponse
    {
        $data = $request->validate([
            'lotto_id' => ['required', 'string', 'exists:lotto_data,lotto_id', 'unique:lotto_details,lotto_id,' . $lottoDetailsAdmin->id],
            'date' => ['nullable', 'string', 'max:255'],
            'endpoint' => ['nullable', 'string', 'max:255'],
        ], [
            'lotto_id.unique' => 'งวดหวยนี้มีรายละเอียดหวยในระบบอยู่แล้ว',
            'lotto_id.exists' => 'ไม่พบข้อมูลหลักของงวดหวยนี้ในระบบ',
        ]);

        $lottoDetailsAdmin->update($data);

        return redirect()->route('backoffice.lotto-details-admin.index')->with('success', 'อัปเดตรายละเอียดงวดหวยสำเร็จแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LottoDetail $lottoDetailsAdmin): RedirectResponse
    {
        $lottoDetailsAdmin->delete();

        return redirect()->route('backoffice.lotto-details-admin.index')->with('success', 'ลบรายละเอียดงวดหวยสำเร็จแล้ว');
    }
}
