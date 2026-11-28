<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LotteryNumber;
use App\Models\Source;
use App\Models\LottoData;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class LotteryNumberController extends Controller
{
    /**
     * รายการงวดจาก lotto_data เท่านั้น (ล็อกไม่ให้เลือกวันที่มั่ว)
     */
    private function getAvailableDrawDates(): array
    {
        return LottoData::query()
            ->whereNotNull('draw_date')
            ->orderByDesc('draw_date')
            ->get()
            ->map(fn ($d) => $d->draw_date instanceof \DateTimeInterface ? $d->draw_date->format('Y-m-d') : substr((string) $d->draw_date, 0, 10))
            ->values()
            ->all();
    }

    public function index(Request $request)
    {
        $availableDrawDates = $this->getAvailableDrawDates();

        $query = LotteryNumber::query()->with('source');

        if ($sourceId = $request->integer('source_id')) {
            $query->where('source_id', $sourceId);
        }

        $drawDate = $request->string('draw_date')->toString();
        if ($drawDate && in_array($drawDate, $availableDrawDates, true)) {
            $query->where('draw_date', $drawDate);
        }

        $numbers = $query
            ->orderByDesc('draw_date')
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Numbers/Index', [
            'filters' => [
                'source_id' => $request->integer('source_id') ?: null,
                'draw_date' => $drawDate,
            ],
            'sources' => Source::query()->orderBy('name')->get(['id', 'name', 'status']),
            'availableDrawDates' => $availableDrawDates,
            'numbers' => $numbers,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Numbers/Create', [
            'sources' => Source::query()->orderBy('name')->get(['id', 'name', 'status']),
            'availableDrawDates' => $this->getAvailableDrawDates(),
        ]);
    }

    /**
     * หน้ากรอกเลขด่วนทั้งงวดแบบตารางเดียว (Bulk Entry) สำหรับพนักงาน/แอดมินคีย์เลขแทนทุกสำนัก
     */
    public function bulkEdit(Request $request)
    {
        $availableDrawDates = $this->getAvailableDrawDates();
        $selectedDrawDate = $request->string('draw_date')->toString();
        if (!$selectedDrawDate || !in_array($selectedDrawDate, $availableDrawDates, true)) {
            $selectedDrawDate = $availableDrawDates[0] ?? '';
        }

        $sources = Source::query()
            ->where('status', 'active')
            ->orderByDesc('is_promoted')
            ->orderByDesc('popularity_score')
            ->orderBy('name')
            ->get(['id', 'name', 'is_promoted', 'promoted_until']);

        $existingBySource = $selectedDrawDate
            ? LotteryNumber::where('draw_date', $selectedDrawDate)
                ->get()
                ->keyBy('source_id')
            : collect();

        $rows = $sources->map(function (Source $source) use ($existingBySource) {
            /** @var LotteryNumber|null $num */
            $num = $existingBySource->get($source->id);
            $runningStr = '';
            if ($num && is_array($num->running_numbers)) {
                $runningStr = implode(',', $num->running_numbers);
            }

            return [
                'source_id' => $source->id,
                'source_name' => $source->name,
                'is_promoted' => $source->isCurrentlyPromoted(),
                'two_digit' => $num?->two_digit ?? '',
                'three_digit' => $num?->three_digit ?? '',
                'running_numbers' => $runningStr,
                'has_existing' => $num !== null,
            ];
        })->values();

        return Inertia::render('Admin/Numbers/Bulk', [
            'availableDrawDates' => $availableDrawDates,
            'selectedDrawDate' => $selectedDrawDate,
            'rows' => $rows,
        ]);
    }

    /**
     * บันทึกเลขด่วนทั้งงวดพร้อมกัน (Bulk Save)
     */
    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'draw_date' => ['required', 'date', 'exists:lotto_data,draw_date'],
            'items' => ['required', 'array'],
            'items.*.source_id' => ['required', 'exists:sources,id'],
            'items.*.two_digit' => ['nullable', 'string', 'max:10'],
            'items.*.three_digit' => ['nullable', 'string', 'max:10'],
            'items.*.running_numbers' => ['nullable', 'string', 'max:50'],
        ]);

        $drawDate = $data['draw_date'];
        $savedCount = 0;

        foreach ($data['items'] as $item) {
            $twoDigit = trim((string) ($item['two_digit'] ?? ''));
            $threeDigit = trim((string) ($item['three_digit'] ?? ''));
            $runningRaw = trim((string) ($item['running_numbers'] ?? ''));

            // ถ้าไม่ได้กรอกเลขใดๆ เลย ข้ามสำนักนี้ (แต่ถ้าเคยมีอยู่แล้วและลบออกหมด ให้ลบรายการเดิม)
            if ($twoDigit === '' && $threeDigit === '' && $runningRaw === '') {
                LotteryNumber::where('source_id', $item['source_id'])
                    ->where('draw_date', $drawDate)
                    ->delete();
                continue;
            }

            $running = null;
            if ($runningRaw !== '') {
                $running = collect(explode(',', $runningRaw))
                    ->map(fn ($v) => trim($v))
                    ->filter(fn ($v) => $v !== '')
                    ->values()
                    ->all();
            }

            LotteryNumber::updateOrCreate(
                [
                    'source_id' => $item['source_id'],
                    'draw_date' => $drawDate,
                ],
                [
                    'two_digit' => $twoDigit !== '' ? $twoDigit : null,
                    'three_digit' => $threeDigit !== '' ? $threeDigit : null,
                    'running_numbers' => !empty($running) ? $running : null,
                ]
            );
            $savedCount++;
        }

        try {
            if (\App\Models\LotteryResult::where('draw_date', $drawDate)->exists()) {
                app(\App\Services\AccuracyCalculationService::class)->calculateForDraw($drawDate, true);
            }
        } catch (\Exception $e) {
        }

        return redirect()
            ->route('backoffice.numbers.bulk', ['draw_date' => $drawDate])
            ->with('success', "บันทึกเลขเด็ดด่วนประจำงวด {$drawDate} สำเร็จ ({$savedCount} สำนัก)");
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'source_id' => ['required', 'exists:sources,id'],
            'draw_date' => [
                'required',
                'date',
                'exists:lotto_data,draw_date',
                Rule::unique('lottery_numbers', 'draw_date')->where('source_id', $request->input('source_id')),
            ],
            'two_digit' => ['nullable', 'string', 'max:10'],
            'three_digit' => ['nullable', 'string', 'max:10'],
            'running_numbers' => ['nullable', 'string'], // "1,2,3"
        ], [
            'draw_date.unique' => 'สำนักนี้มีข้อมูลเลขเด็ดในงวดที่เลือกอยู่แล้ว กรุณาแก้ไขรายการเดิมแทน',
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
            'source_id' => $data['source_id'],
            'draw_date' => $data['draw_date'],
            'two_digit' => $data['two_digit'] ?? null,
            'three_digit' => $data['three_digit'] ?? null,
            'running_numbers' => $running,
        ]);

        // หากงวดนี้มีผลหวยออกแล้ว ให้คำนวณคะแนนความแม่นยำใหม่ทันที
        try {
            if (\App\Models\LotteryResult::where('draw_date', $data['draw_date'])->exists()) {
                app(\App\Services\AccuracyCalculationService::class)->calculateForDraw($data['draw_date'], true);
            }
        } catch (\Exception $e) {
            // ข้ามหากยังไม่สามารถคำนวณได้
        }

        return redirect()->route('backoffice.numbers.index')->with('success', 'บันทึกเลขสำเร็จ');
    }

    public function edit(LotteryNumber $number)
    {
        $number->load('source');

        return Inertia::render('Admin/Numbers/Edit', [
            'number' => $number,
            'sources' => Source::query()->orderBy('name')->get(['id', 'name', 'status']),
            'availableDrawDates' => $this->getAvailableDrawDates(),
        ]);
    }

    public function update(Request $request, LotteryNumber $number)
    {
        $data = $request->validate([
            'source_id' => ['required', 'exists:sources,id'],
            'draw_date' => [
                'required',
                'date',
                'exists:lotto_data,draw_date',
                Rule::unique('lottery_numbers', 'draw_date')
                    ->where('source_id', $request->input('source_id'))
                    ->ignore($number->id),
            ],
            'two_digit' => ['nullable', 'string', 'max:10'],
            'three_digit' => ['nullable', 'string', 'max:10'],
            'running_numbers' => ['nullable', 'string'],
        ], [
            'draw_date.unique' => 'สำนักนี้มีข้อมูลเลขเด็ดในงวดที่เลือกอยู่แล้ว กรุณาแก้ไขรายการเดิมแทน',
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
            'source_id' => $data['source_id'],
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

        return redirect()->route('backoffice.numbers.index')->with('success', 'บันทึกสำเร็จ');
    }

    public function destroy(LotteryNumber $number)
    {
        $number->delete();

        return redirect()->route('backoffice.numbers.index')->with('success', 'ลบเลขสำเร็จ');
    }
}
