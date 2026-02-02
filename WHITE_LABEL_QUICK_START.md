# White Label Quick Start Guide

## 🚀 เริ่มต้นทำ White Label ใน 5 ขั้นตอน

### Step 1: สร้าง Migration สำหรับ Tenants

```bash
php artisan make:migration create_tenants_table
php artisan make:model Tenant
```

### Step 2: Migration File

```php
// database/migrations/xxxx_create_tenants_table.php
Schema::create('tenants', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('domain')->nullable()->unique();
    $table->string('subdomain')->nullable()->unique();
    $table->string('logo_url')->nullable();
    $table->string('favicon_url')->nullable();
    $table->string('primary_color')->default('#dc2626');
    $table->string('secondary_color')->default('#374151');
    $table->string('email')->nullable();
    $table->string('phone')->nullable();
    $table->enum('status', ['active', 'suspended', 'trial'])->default('trial');
    $table->enum('subscription_plan', ['basic', 'pro', 'enterprise'])->default('basic');
    $table->dateTime('subscription_started_at')->nullable();
    $table->dateTime('subscription_ends_at')->nullable();
    $table->integer('max_users')->default(10);
    $table->integer('max_sources')->default(5);
    $table->json('settings')->nullable();
    $table->timestamps();
});
```

### Step 3: เพิ่ม tenant_id ในตารางที่มีอยู่

```bash
php artisan make:migration add_tenant_id_to_users_table
php artisan make:migration add_tenant_id_to_sources_table
php artisan make:migration add_tenant_id_to_posts_table
# ... และอื่นๆ
```

### Step 4: สร้าง Middleware

```bash
php artisan make:middleware DetectTenant
```

### Step 5: เริ่มทำทีละส่วน

1. **Multi-tenancy core** (2-3 วัน)
2. **Branding system** (1-2 วัน)
3. **Super admin** (2-3 วัน)
4. **Tenant admin** (1-2 วัน)

---

## 💡 ตัวอย่างโค้ดที่ต้องทำ

### 1. Tenant Model

```php
// app/Models/Tenant.php
class Tenant extends Model
{
    protected $fillable = [
        'name', 'slug', 'domain', 'subdomain',
        'logo_url', 'favicon_url',
        'primary_color', 'secondary_color',
        'email', 'phone', 'status',
        'subscription_plan', 'subscription_started_at', 'subscription_ends_at',
        'max_users', 'max_sources', 'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'subscription_started_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function sources()
    {
        return $this->hasMany(Source::class);
    }

    public function isActive()
    {
        return $this->status === 'active' 
            && ($this->subscription_ends_at === null || $this->subscription_ends_at->isFuture());
    }
}
```

### 2. DetectTenant Middleware

```php
// app/Http/Middleware/DetectTenant.php
public function handle(Request $request, Closure $next)
{
    $host = $request->getHost();
    
    // ตรวจสอบจาก custom domain
    $tenant = Tenant::where('domain', $host)->first();
    
    // ถ้าไม่มี ตรวจสอบจาก subdomain
    if (!$tenant) {
        $parts = explode('.', $host);
        if (count($parts) > 2) {
            $subdomain = $parts[0];
            $tenant = Tenant::where('subdomain', $subdomain)->first();
        }
    }
    
    // ถ้าไม่มี ใช้ default tenant (สำหรับ development)
    if (!$tenant) {
        $tenant = Tenant::where('slug', 'default')->first();
    }
    
    if (!$tenant || !$tenant->isActive()) {
        abort(404, 'Tenant not found or inactive');
    }
    
    // เก็บ tenant ใน app container
    app()->instance('tenant', $tenant);
    
    return $next($request);
}
```

### 3. HasTenant Trait

```php
// app/Traits/HasTenant.php
trait HasTenant
{
    protected static function bootHasTenant()
    {
        // Auto-set tenant_id เมื่อสร้าง record
        static::creating(function ($model) {
            if (app()->bound('tenant')) {
                $model->tenant_id = app('tenant')->id;
            }
        });
        
        // Global scope: filter ตาม tenant_id
        static::addGlobalScope('tenant', function ($query) {
            if (app()->bound('tenant')) {
                $query->where('tenant_id', app('tenant')->id);
            }
        });
    }
}
```

### 4. Update Models

```php
// app/Models/User.php
use HasTenant;

protected $fillable = [
    'name', 'email', 'password', 'role',
    'tenant_id', // เพิ่ม
];

// app/Models/Source.php
use HasTenant;

protected $fillable = [
    'name', 'description', 'status',
    'tenant_id', // เพิ่ม
];
```

---

## 🎨 Frontend: Dynamic Branding

### 1. Share Tenant Data กับ Frontend

```php
// app/Http/Middleware/HandleInertiaRequests.php
public function share(Request $request): array
{
    return [
        'tenant' => app()->bound('tenant') ? [
            'id' => app('tenant')->id,
            'name' => app('tenant')->name,
            'logo' => app('tenant')->logo_url,
            'favicon' => app('tenant')->favicon_url,
            'primary_color' => app('tenant')->primary_color,
            'secondary_color' => app('tenant')->secondary_color,
        ] : null,
    ];
}
```

### 2. Vue Component: Dynamic Logo

```vue
<!-- resources/js/Components/Navigation.vue -->
<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const tenant = computed(() => page.props.tenant);

const logo = computed(() => tenant.value?.logo || '/logo-default.jpg');
const primaryColor = computed(() => tenant.value?.primary_color || '#dc2626');
</script>

<template>
    <nav :style="{ '--primary-color': primaryColor }">
        <img :src="logo" alt="Logo" />
        <!-- ... -->
    </nav>
</template>
```

---

## 🔐 Super Admin Routes

```php
// routes/web.php
Route::prefix('super-admin')->name('super-admin.')->middleware(['auth', 'super-admin'])->group(function () {
    Route::resource('tenants', SuperAdmin\TenantController::class);
    Route::get('dashboard', [SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
});
```

---

## 💰 ราคาแนะนำ

### Basic: 1,500 บาท/เดือน
- 5 sources
- 10 users
- Basic features

### Pro: 3,500 บาท/เดือน
- 20 sources
- 50 users
- All features + custom domain

### Enterprise: 10,000+ บาท/เดือน
- Unlimited
- Custom development
- Dedicated support

---

## 🎯 เริ่มทำเลย!

1. **วันนี้**: สร้าง migration และ model
2. **พรุ่งนี้**: สร้าง middleware และ trait
3. **วันถัดไป**: อัปเดต models
4. **สัปดาห์หน้า**: ทำ branding system

**เวลาโดยรวม: 2-3 สัปดาห์** สำหรับ MVP (Minimum Viable Product)
