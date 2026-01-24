<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Source;
use App\Models\AccuracyScore;
use App\Models\AccuracyHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccuracyController extends Controller
{
    /**
     * แสดงตารางคะแนนความแม่นยำ
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'two_digit'); // two_digit, three_digit, running
        $sortBy = $request->get('sort', 'accuracy'); // accuracy, total_draws, consecutive

        $query = AccuracyScore::with('source')
            ->where('type', $type)
            ->whereHas('source', function ($q) {
                $q->where('status', 'active');
            });

        // เรียงลำดับ
        switch ($sortBy) {
            case 'accuracy':
                $query->orderBy('accuracy_percentage', 'desc')
                    ->orderBy('total_draws', 'desc');
                break;
            case 'total_draws':
                $query->orderBy('total_draws', 'desc')
                    ->orderBy('accuracy_percentage', 'desc');
                break;
            case 'consecutive':
                $query->orderBy('consecutive_correct', 'desc')
                    ->orderBy('accuracy_percentage', 'desc');
                break;
        }

        $scores = $query->get()->map(function ($score) {
            return [
                'id' => $score->id,
                'source_id' => $score->source_id,
                'source_name' => $score->source->name,
                'type' => $score->type,
                'total_draws' => $score->total_draws,
                'correct_count' => $score->correct_count,
                'accuracy_percentage' => (float) $score->accuracy_percentage,
                'consecutive_correct' => $score->consecutive_correct,
            ];
        });

        // ดึงประวัติย้อนหลังสำหรับกราฟ (10 งวดล่าสุด)
        $histories = AccuracyHistory::with('source')
            ->where('type', $type)
            ->whereHas('source', function ($q) {
                $q->where('status', 'active');
            })
            ->orderBy('draw_date', 'desc')
            ->orderBy('source_id')
            ->limit(100)
            ->get()
            ->groupBy('draw_date')
            ->take(10)
            ->map(function ($group, $drawDate) {
                $total = $group->count();
                $correct = $group->where('is_correct', true)->count();
                return [
                    'draw_date' => $drawDate,
                    'total' => $total,
                    'correct' => $correct,
                    'percentage' => $total > 0 ? round(($correct / $total) * 100, 2) : 0,
                ];
            })
            ->reverse()
            ->values();

        return Inertia::render('Accuracy/Index', [
            'scores' => $scores,
            'histories' => $histories,
            'filters' => [
                'type' => $type,
                'sort' => $sortBy,
            ],
        ]);
    }
}
