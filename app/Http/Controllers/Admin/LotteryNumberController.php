<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LotteryNumber;
use App\Models\Source;
use App\Models\LottoData;
use Illuminate\Http\Request;
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
            ->map(fn ($d) => $d->draw_date->format('Y-m-d'))
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'source_id' => ['required', 'exists:sources,id'],
            'draw_date' => ['required', 'date', 'exists:lotto_data,draw_date'],
            'two_digit' => ['nullable', 'string', 'max:10'],
            'three_digit' => ['nullable', 'string', 'max:10'],
            'running_numbers' => ['nullable', 'string'], // "1,2,3"
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
            'draw_date' => ['required', 'date', 'exists:lotto_data,draw_date'],
            'two_digit' => ['nullable', 'string', 'max:10'],
            'three_digit' => ['nullable', 'string', 'max:10'],
            'running_numbers' => ['nullable', 'string'],
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

        return redirect()->route('backoffice.numbers.index')->with('success', 'บันทึกสำเร็จ');
    }

    public function destroy(LotteryNumber $number)
    {
        $number->delete();

        return redirect()->route('backoffice.numbers.index')->with('success', 'ลบเลขสำเร็จ');
    }
}
