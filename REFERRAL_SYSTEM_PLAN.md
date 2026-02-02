# แผนพัฒนา Referral System

## 🎯 เป้าหมาย
สร้างระบบที่ผู้ใช้หาเงินได้จากการแนะนำเพื่อน

---

## 📋 Features ที่จะทำ

### 1. **Referral Link สำหรับผู้ใช้**
- ผู้ใช้แต่ละคนมี unique referral code/link
- ตัวอย่าง: `https://chaektang.com/?ref=ABC123`
- แสดงใน Dashboard

### 2. **ติดตาม Referral**
- เมื่อมีคนสมัครสมาชิกผ่าน referral link → บันทึก referral
- เมื่อมีคนซื้อสินค้า/VIP → คำนวณ commission

### 3. **Dashboard แสดงรายได้**
- แสดงจำนวนคนที่แนะนำ
- แสดงรายได้ทั้งหมด
- แสดงยอดเงินที่ถอนได้
- แสดงประวัติรายได้

### 4. **ระบบถอนเงิน**
- ผู้ใช้สามารถถอนเงินได้
- Admin อนุมัติการถอนเงิน
- ส่งเงินเข้าบัญชีธนาคาร

---

## 🗄️ Database Schema

### Migration 1: `create_referrals_table`
```php
Schema::create('referrals', function (Blueprint $table) {
    $table->id();
    $table->string('referral_code')->unique();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->integer('total_referrals')->default(0);
    $table->decimal('total_earnings', 10, 2)->default(0);
    $table->timestamps();
    
    $table->index('referral_code');
    $table->index('user_id');
});
```

### Migration 2: `create_referral_tracking_table`
```php
Schema::create('referral_tracking', function (Blueprint $table) {
    $table->id();
    $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('referred_id')->constrained('users')->onDelete('cascade');
    $table->string('referral_code');
    $table->enum('status', ['pending', 'active', 'inactive'])->default('pending');
    $table->timestamps();
    
    $table->unique(['referrer_id', 'referred_id']);
    $table->index('referral_code');
});
```

### Migration 3: `create_user_commissions_table`
```php
Schema::create('user_commissions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('referral_tracking_id')->nullable()->constrained('referral_tracking')->onDelete('set null');
    $table->enum('type', ['signup', 'affiliate', 'vip', 'subscription']);
    $table->decimal('amount', 10, 2);
    $table->decimal('commission_rate', 5, 2)->default(0); // เปอร์เซ็นต์
    $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
    $table->text('description')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'status']);
    $table->index('type');
});
```

### Migration 4: `create_user_wallet_table`
```php
Schema::create('user_wallet', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
    $table->decimal('balance', 10, 2)->default(0);
    $table->decimal('total_earned', 10, 2)->default(0);
    $table->decimal('total_withdrawn', 10, 2)->default(0);
    $table->timestamps();
});
```

### Migration 5: `create_withdrawals_table`
```php
Schema::create('withdrawals', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->decimal('amount', 10, 2);
    $table->string('bank_account_name');
    $table->string('bank_account_number');
    $table->string('bank_name')->nullable();
    $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('approved_at')->nullable();
    $table->text('rejection_reason')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'status']);
});
```

### Migration 6: เพิ่มฟิลด์ใน `users` table
```php
Schema::table('users', function (Blueprint $table) {
    $table->string('referral_code')->unique()->nullable()->after('email');
    $table->string('referred_by_code')->nullable()->after('referral_code');
});
```

---

## 💻 Code Implementation

### Model: `Referral.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Referral extends Model
{
    protected $fillable = [
        'referral_code',
        'user_id',
        'total_referrals',
        'total_earnings',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tracking(): HasMany
    {
        return $this->hasMany(ReferralTracking::class, 'referral_code', 'referral_code');
    }

    public static function generateCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }
}
```

### Model: `UserCommission.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCommission extends Model
{
    protected $fillable = [
        'user_id',
        'referral_tracking_id',
        'type',
        'amount',
        'commission_rate',
        'status',
        'description',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tracking(): BelongsTo
    {
        return $this->belongsTo(ReferralTracking::class);
    }
}
```

### Model: `UserWallet.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
        'total_earned',
        'total_withdrawn',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function addBalance(float $amount): void
    {
        $this->balance += $amount;
        $this->total_earned += $amount;
        $this->save();
    }

    public function withdraw(float $amount): bool
    {
        if ($this->balance >= $amount) {
            $this->balance -= $amount;
            $this->total_withdrawn += $amount;
            $this->save();
            return true;
        }
        return false;
    }
}
```

---

## 🔧 Controller: `ReferralController.php`

### Methods:
1. `index()` - แสดง Dashboard
2. `track()` - ติดตาม referral เมื่อมีคนสมัครสมาชิก
3. `commissions()` - แสดงรายการ commission
4. `withdraw()` - ขอถอนเงิน
5. `withdrawHistory()` - ประวัติการถอนเงิน

---

## 🎨 Vue Components

### 1. `ReferralDashboard.vue`
- แสดง referral link
- แสดงสถิติ (จำนวนคนที่แนะนำ, รายได้)
- ปุ่มแชร์

### 2. `CommissionHistory.vue`
- ตารางแสดงรายการ commission
- Filter ตาม type, status

### 3. `WithdrawForm.vue`
- Form ขอถอนเงิน
- แสดงยอดเงินที่ถอนได้

### 4. `WithdrawHistory.vue`
- ประวัติการถอนเงิน

---

## ⚙️ Configuration

### `config/referral.php`
```php
<?php

return [
    'signup_bonus' => 50, // บาท
    'affiliate_commission_rate' => 0.15, // 15%
    'vip_commission_rate' => 0.25, // 25%
    'min_withdrawal' => 100, // บาท
    'withdrawal_fee' => 0, // บาท
];
```

---

## 📝 Routes

```php
Route::middleware('auth')->group(function () {
    Route::prefix('referral')->name('referral.')->group(function () {
        Route::get('/', [ReferralController::class, 'index'])->name('index');
        Route::get('/commissions', [ReferralController::class, 'commissions'])->name('commissions');
        Route::post('/withdraw', [ReferralController::class, 'withdraw'])->name('withdraw');
        Route::get('/withdraw-history', [ReferralController::class, 'withdrawHistory'])->name('withdraw-history');
    });
});
```

---

## 🎯 Next Steps

1. ✅ สร้าง migrations
2. ✅ สร้าง models
3. ✅ สร้าง controllers
4. ✅ สร้าง Vue components
5. ✅ Integrate กับระบบสมัครสมาชิก
6. ✅ Integrate กับระบบ Affiliate
7. ✅ Integrate กับระบบ VIP
8. ✅ สร้างหน้า Admin สำหรับจัดการ withdrawals
