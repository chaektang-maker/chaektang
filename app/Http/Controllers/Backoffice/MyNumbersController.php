<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\LotteryNumber;
use App\Models\LottoData;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MyNumbersController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = LotteryNumber::query()
            ->with('source')
            ->where('source_id', $user->source_id);

        if ($drawDate = $request->string('draw_date')->toString()) {
            $query->where('draw_date', $drawDate);
        }

        $numbers = $query
            ->orderByDesc('draw_date')
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $availableDrawDates = LottoData::query()
            ->whereNotNull('draw_date')
            ->orderByDesc('draw_date')
            ->get()
            ->pluck('draw_date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->values();

        return Inertia::render('Backoffice/MyNumbers/Index', [
            'filters' => [
                'draw_date' => $request->string('draw_date')->toString(),
            ],
            'numbers' => $numbers,
            'availableDrawDates' => $availableDrawDates,
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();

        $availableDrawDates = LottoData::query()
            ->whereNotNull('draw_date')
            ->orderByDesc('draw_date')
            ->get()
            ->pluck('draw_date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->values();

        return Inertia::render('Backoffice/MyNumbers/Create', [
            'availableDrawDates' => $availableDrawDates,
            'source' => $user->source,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'draw_date' => [
                'required',
                'date',
                'exists:lotto_data,draw_date',
                Rule::unique('lottery_numbers', 'draw_date')->where('source_id', $user->source_id),
            ],
            'two_digit' => ['nullable', 'string', 'max:10'],
            'three_digit' => ['nullable', 'string', 'max:10'],
            'running_numbers' => ['nullable', 'string'],
        ], [
            'draw_date.unique' => 'คุณได้บันทึกเลขเด็ดของงวดนี้ไปแล้ว กรุณาใช้เมนูแก้ไขแทน',
        ]);

        $running = null;
        if (!empty($data['running_numbers'])) {
            $running = collect(explode(',', $data['running_numbers']))
                ->map(fn ($v) => trim($v))
                ->filter()
                ->values()
                ->all();
        }

        LotteryNumber::create([
            'source_id' => $user->source_id,
            'draw_date' => $data['draw_date'],
            'two_digit' => $data['two_digit'] ?? null,
            'three_digit' => $data['three_digit'] ?? null,
            'running_numbers' => $running,
        ]);

        try {
            if (\App\Models\LotteryResult::where('draw_date', $data['draw_date'])->exists()) {
                app(\App\Services\AccuracyCalculationService::class)->calculateForDraw($data['draw_date'], true);
            }
        } catch (\Exception $e) {
        }

        return redirect()->route('backoffice.my-numbers.index')
            ->with('success', 'บันทึกเลขของสำนักสำเร็จ');
    }

    public function edit(Request $request, LotteryNumber $number)
    {
        $user = $request->user();

        if ($number->source_id !== $user->source_id) {
            abort(403, 'คุณไม่มีสิทธิ์แก้ไขเลขชุดนี้');
        }

        $availableDrawDates = LottoData::query()
            ->whereNotNull('draw_date')
            ->orderByDesc('draw_date')
            ->get()
            ->pluck('draw_date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->values();

        $number->load('source');

        return Inertia::render('Backoffice/MyNumbers/Edit', [
            'number' => $number,
            'availableDrawDates' => $availableDrawDates,
        ]);
    }

    public function update(Request $request, LotteryNumber $number)
    {
        $user = $request->user();

        if ($number->source_id !== $user->source_id) {
            abort(403, 'คุณไม่มีสิทธิ์แก้ไขเลขชุดนี้');
        }

        $data = $request->validate([
            'draw_date' => [
                'required',
                'date',
                'exists:lotto_data,draw_date',
                Rule::unique('lottery_numbers', 'draw_date')
                    ->where('source_id', $user->source_id)
                    ->ignore($number->id),
            ],
            'two_digit' => ['nullable', 'string', 'max:10'],
            'three_digit' => ['nullable', 'string', 'max:10'],
            'running_numbers' => ['nullable', 'string'],
        ], [
            'draw_date.unique' => 'คุณได้บันทึกเลขเด็ดของงวดนี้ไปแล้ว กรุณาใช้เมนูแก้ไขแทน',
        ]);

        $running = null;
        if (!empty($data['running_numbers'])) {
            $running = collect(explode(',', $data['running_numbers']))
                ->map(fn ($v) => trim($v))
                ->filter()
                ->values()
                ->all();
        }

        $number->update([
            'draw_date' => $data['draw_date'],
            'two_digit' => $data['two_digit'] ?? null,
            'three_digit' => $data['three_digit'] ?? null,
            'running_numbers' => $running,
        ]);

        try {
            if (\App\Models\LotteryResult::where('draw_date', $data['draw_date'])->exists()) {
                app(\App\Services\AccuracyCalculationService::class)->calculateForDraw($data['draw_date'], true);
            }
        } catch (\Exception $e) {
        }

        return redirect()->route('backoffice.my-numbers.index')
            ->with('success', 'บันทึกเลขของสำนักสำเร็จ');
    }
}

