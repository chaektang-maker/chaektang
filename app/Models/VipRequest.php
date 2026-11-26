<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VipRequest extends Model
{
    protected $fillable = [
        'customer_id',
        'slip_path',
        'extra_slips',
        'slip_hash',
        'amount',
        'bank_account_name',
        'bank_account_number',
        'note',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'extra_slips' => 'array',
        'approved_at' => 'datetime',
    ];

    /**
     * ตรวจสอบและเพิ่มคอลัมน์ extra_slips และ slip_hash อัตโนมัติหากยังไม่ได้รัน migrate
     */
    public static function ensureColumnsExist(): void
    {
        try {
            if (Schema::hasTable('vip_requests')) {
                $hasExtraSlips = Schema::hasColumn('vip_requests', 'extra_slips');
                $hasSlipHash = Schema::hasColumn('vip_requests', 'slip_hash');

                if (!$hasExtraSlips || !$hasSlipHash) {
                    Schema::table('vip_requests', function (Blueprint $table) use ($hasExtraSlips, $hasSlipHash) {
                        if (!$hasExtraSlips) {
                            $table->json('extra_slips')->nullable()->after('slip_path');
                        }
                        if (!$hasSlipHash) {
                            $table->string('slip_hash', 64)->nullable()->index();
                        }
                    });
                }
            }
        } catch (\Throwable $e) {
            // ignore schema check error if any
        }
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
