<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LottoData;
use Illuminate\Http\Request;

class LottoDataAdminController extends Controller
{
    /**
     * แสดงหน้า admin interface
     */
    public function index()
    {
        // นับจำนวนข้อมูลใน database
        $totalRecords = LottoData::count();
        $fetchedRecords = LottoData::where('is_fetched', true)->count();
        $pendingRecords = $totalRecords - $fetchedRecords;
        
        // ดึงข้อมูลล่าสุด 10 รายการที่ยังไม่ได้ดึง
        $recentData = LottoData::where('is_fetched', false)
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();
        
        return view('admin.lotto-data.index', [
            'totalRecords' => $totalRecords,
            'fetchedRecords' => $fetchedRecords,
            'pendingRecords' => $pendingRecords,
            'recentData' => $recentData,
        ]);
    }
}
