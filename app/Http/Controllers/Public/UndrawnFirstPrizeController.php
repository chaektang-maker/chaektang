<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LottoPrize;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;

class UndrawnFirstPrizeController extends Controller
{
    public function index(Request $request)
    {
        $search = (string) $request->input('search');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        // ดึงเฉพาะรางวัล 6 หลักทั้งหมดที่เคยออกไปแล้วมาเก็บใน array
        // ใช้ array_flip เพื่อให้ค้นหาแบบ O(1) ได้อย่างรวดเร็ว
        $drawnPrizesRaw = LottoPrize::whereIn('prize_id', [
            'prizeFirst', 'prizeFirstNear', 'prizeSecond', 'prizeThird', 'prizeForth', 'prizeFifth'
        ])->pluck('numbers');

        $drawnPrizes = [];
        foreach ($drawnPrizesRaw as $val) {
            if (is_array($val)) {
                foreach ($val as $num) {
                    if ($num) $drawnPrizes[] = $num;
                }
            } elseif ($val) {
                $drawnPrizes[] = $val;
            }
        }
        $drawnPrizes = array_unique($drawnPrizes);
        $drawnPrizesKeys = array_flip($drawnPrizes);

        $paginatedItems = [];
        $totalMatching = 0;

        // ถ้ามีการค้นหา แต่ดันตีกรอกไม่ใช่ตัวเลข ให้ข้ามไปเลย
        if ($search !== '' && !is_numeric($search)) {
            // ไม่ต้อง loop
        } else {
            // Loop ตั้งแต่ 000000 จนถึง 999999
            // วิธีนี้เร็วและใช้ memory น้อยมาก เพราะเก็บ array เฉพาะเท่าที่แสดงหน้าปัจจุบัน (100 รายการ)
            for ($i = 0; $i < 1000000; $i++) {
                $numStr = sprintf('%06d', $i);
                
                // ไม่เอาเลขที่ขึ้นต้นด้วย 0
                if ($numStr[0] === '0') {
                    continue;
                }

                if ($numStr[0] === '1') {
                    continue;
                }

                // ไม่เอาเลข 2 ตัวแรกเป็นเลขซ้ำกัน
                if ($numStr[0] === $numStr[1]) {
                    continue;
                }
                
                // ในเลขทุกตำแหน่ง ต้องไม่มีเลขที่ซ้ำกันติดกัน 3 ตัว
                if (preg_match('/(\d)\1\1/', $numStr)) {
                    continue;
                }
                
                // ถ้าระบุคำค้นหา ต้องตรวจสอบว่าหมายเลขมีคำค้นหานั้นๆ หรือไม่
                if ($search !== '' && !str_contains($numStr, $search)) {
                    continue;
                }
                
                // ตรวจสอบว่าเคยออกเป็นรางวัลที่ 1 ไปหรือยัง
                if (isset($drawnPrizesKeys[$numStr])) {
                    continue;
                }
                
                // ถ้ายังไม่เคยออก จัดเก็บเข้าไปในตัวแปรสำหรับแสดงผล
                if ($totalMatching >= $offset && $totalMatching < $offset + $perPage) {
                    $paginatedItems[] = $numStr;
                }
                
                $totalMatching++;
            }
        }

        $paginator = new LengthAwarePaginator(
            $paginatedItems,
            $totalMatching,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return Inertia::render('Results/Undrawn', [
            'numbers' => $paginator,
            'filters' => ['search' => $search],
        ]);
    }
}
