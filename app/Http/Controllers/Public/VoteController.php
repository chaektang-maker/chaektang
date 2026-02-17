<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\UserVote;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    /**
     * โหวตให้คะแนนความแม่นสำหรับสำนัก
     * User ที่ login สามารถโหวตได้ 3 ครั้งต่องวด
     */
    public function store(Request $request)
    {
        $request->validate([
            'source_id' => 'required|exists:sources,id',
            'draw_date' => 'required|date',
        ]);

        $sourceId = $request->source_id;
        $drawDate = $request->draw_date;
        $userId = Auth::id();

        // ต้อง login ถึงจะโหวตได้
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาล็อกอินเพื่อโหวต',
            ], 401);
        }

        // ตรวจสอบจำนวนครั้งที่โหวตไปแล้วในงวดนี้ (รวมทุกสำนัก)
        $userVoteCount = UserVote::where('user_id', $userId)
            ->where('draw_date', $drawDate)
            ->count();

        // โหวตได้สูงสุด 3 ครั้งต่องวด
        if ($userVoteCount >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'คุณโหวตครบ 3 ครั้งแล้วในงวดนี้',
                'remaining_votes' => 0,
            ], 400);
        }

        // บันทึกการโหวต
        $vote = UserVote::create([
            'user_id' => $userId,
            'source_id' => $sourceId,
            'draw_date' => $drawDate,
            'ip_address' => null, // ไม่ใช้ ip_address สำหรับ user ที่ login
        ]);

        // นับจำนวนโหวตทั้งหมดของสำนักนี้ในงวดนี้
        $voteCount = UserVote::where('source_id', $sourceId)
            ->where('draw_date', $drawDate)
            ->count();

        // นับจำนวนครั้งที่เหลือ
        $remainingVotes = 3 - ($userVoteCount + 1);

        return response()->json([
            'success' => true,
            'message' => 'โหวตสำเร็จ',
            'vote_count' => $voteCount,
            'remaining_votes' => $remainingVotes,
        ]);
    }

    /**
     * ตรวจสอบสถานะการโหวตของผู้ใช้
     */
    public function check(Request $request)
    {
        $request->validate([
            'source_id' => 'required|exists:sources,id',
            'draw_date' => 'required|date',
        ]);

        $userId = Auth::id();

        // นับจำนวนโหวตทั้งหมดของสำนักนี้ในงวดนี้
        $voteCount = UserVote::where('source_id', $request->source_id)
            ->where('draw_date', $request->draw_date)
            ->count();

        // ถ้าไม่ login
        if (!$userId) {
            return response()->json([
                'is_logged_in' => false,
                'vote_count' => $voteCount,
                'user_vote_count' => 0,
                'remaining_votes' => 0,
            ]);
        }

        // นับจำนวนครั้งที่ user โหวตไปแล้วในงวดนี้ (รวมทุกสำนัก)
        $userVoteCount = UserVote::where('user_id', $userId)
            ->where('draw_date', $request->draw_date)
            ->count();

        // นับจำนวนครั้งที่ user โหวตให้สำนักนี้ในงวดนี้
        $userVoteForSource = UserVote::where('user_id', $userId)
            ->where('source_id', $request->source_id)
            ->where('draw_date', $request->draw_date)
            ->count();

        $remainingVotes = max(0, 3 - $userVoteCount);

        return response()->json([
            'is_logged_in' => true,
            'vote_count' => $voteCount,
            'user_vote_count' => $userVoteCount,
            'user_vote_for_source' => $userVoteForSource,
            'remaining_votes' => $remainingVotes,
        ]);
    }

    /**
     * ดึงจำนวนโหวตสำหรับหลายสำนักในงวดเดียวกัน
     */
    public function getVoteCounts(Request $request)
    {
        $request->validate([
            'source_ids' => 'required|array',
            'source_ids.*' => 'exists:sources,id',
            'draw_date' => 'required|date',
        ]);

        $voteCounts = UserVote::whereIn('source_id', $request->source_ids)
            ->where('draw_date', $request->draw_date)
            ->select('source_id', DB::raw('COUNT(*) as count'))
            ->groupBy('source_id')
            ->pluck('count', 'source_id')
            ->toArray();

        return response()->json([
            'vote_counts' => $voteCounts,
        ]);
    }

    /**
     * แสดงหน้า leaderboard คะแนนโหวตรวม
     */
    public function leaderboard(Request $request)
    {
        $drawDate = $request->get('draw_date');
        
        // ถ้าไม่ระบุงวด ให้ใช้งวดล่าสุด
        if (!$drawDate) {
            $latestDraw = UserVote::orderBy('draw_date', 'desc')
                ->value('draw_date');
            $drawDate = $latestDraw ?: now()->toDateString();
        }

        // ดึงคะแนนโหวตรวมของทุกสำนักในงวดนี้
        $voteCounts = UserVote::where('draw_date', $drawDate)
            ->join('sources', 'sources.id', '=', 'user_votes.source_id')
            ->where('sources.status', 'active')
            ->select('sources.id', 'sources.name', DB::raw('COUNT(*) as vote_count'))
            ->groupBy('sources.id', 'sources.name')
            ->orderByDesc('vote_count')
            ->get()
            ->map(function ($item) {
                return [
                    'source_id' => $item->id,
                    'source_name' => $item->name,
                    'vote_count' => $item->vote_count,
                ];
            });

        // ดึงรายการงวดที่มีการโหวต
        $availableDrawDates = UserVote::select('draw_date')
            ->distinct()
            ->orderByDesc('draw_date')
            ->pluck('draw_date')
            ->map(function ($date) {
                return [
                    'value' => $date,
                    'label' => $date,
                ];
            })
            ->toArray();

        return \Inertia\Inertia::render('Vote/Leaderboard', [
            'voteCounts' => $voteCounts,
            'availableDrawDates' => $availableDrawDates,
            'selectedDrawDate' => $drawDate,
        ]);
    }
}
