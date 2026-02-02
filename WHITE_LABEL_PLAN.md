# แผนพัฒนา White Label System สำหรับเว็บหวย

## 🎯 เป้าหมาย
สร้างระบบที่ให้คนอื่นเอาระบบไปใช้ โดยสามารถใส่โลโก้, สี, ชื่อเว็บ, domain ของตัวเองได้

---

## 💡 โมเดลธุรกิจ

### 1. **Subscription Model**
- **Basic Plan**: 1,000-2,000 บาท/เดือน
- **Pro Plan**: 3,000-5,000 บาท/เดือน (มีฟีเจอร์เพิ่มเติม)
- **Enterprise Plan**: 10,000+ บาท/เดือน (custom domain, support)

### 2. **Revenue Streams**
- Subscription fees (รายได้หลัก)
- Setup fee (ครั้งแรก 5,000-10,000 บาท)
- Custom development (ถ้าต้องการฟีเจอร์พิเศษ)
- Support & maintenance (optional)

---

## 🏗️ สถาปัตยกรรมระบบ

### 1. **Multi-Tenancy Architecture**

#### Option A: Single Database + Tenant ID (แนะนำ)
- ใช้ตาราง `tenants` เพื่อเก็บข้อมูลแต่ละ tenant
- ทุกตารางเพิ่ม `tenant_id` เพื่อแยกข้อมูล
- ง่ายต่อการ maintain และ backup

#### Option B: Separate Database per Tenant
- แต่ละ tenant มี database ของตัวเอง
- ปลอดภัยกว่า แต่ซับซ้อนกว่า

**แนะนำ: Option A** เพราะง่ายและเพียงพอ

---

## 📊 Database Schema

### ตาราง `tenants`
```sql
- id
- name (ชื่อบริษัท/เว็บ)
- slug (unique identifier)
- domain (custom domain ถ้ามี)
- subdomain (ถ้าใช้ subdomain)
- logo_url
- favicon_url
- primary_color
- secondary_color
- email
- phone
- status (active, suspended, trial)
- subscription_plan (basic, pro, enterprise)
- subscription_started_at
- subscription_ends_at
- max_users (จำนวน user สูงสุด)
- max_sources (จำนวนสำนักสูงสุด)
- settings (JSON: ฟีเจอร์ที่เปิด/ปิด)
- created_at
- updated_at
```

### เพิ่ม `tenant_id` ในตารางที่มีอยู่:
- `users` (admin/staff ของแต่ละ tenant)
- `sources`
- `lottery_numbers`
- `posts` (บทความ)
- `affiliate_products`
- `subscriptions` (VIP subscriptions ของ tenant)
- และอื่นๆ

---

## 🔧 Technical Implementation

### 1. **Tenant Detection Middleware**

```php
// app/Http/Middleware/DetectTenant.php
public function handle(Request $request, Closure $next)
{
    $host = $request->getHost();
    
    // ตรวจสอบจาก custom domain
    $tenant = Tenant::where('domain', $host)->first();
    
    // ถ้าไม่มี ตรวจสอบจาก subdomain
    if (!$tenant) {
        $subdomain = explode('.', $host)[0];
        $tenant = Tenant::where('subdomain', $subdomain)->first();
    }
    
    // ถ้าไม่มี ใช้ default tenant (สำหรับ development)
    if (!$tenant) {
        $tenant = Tenant::where('slug', 'default')->first();
    }
    
    if (!$tenant || $tenant->status !== 'active') {
        abort(404, 'Tenant not found');
    }
    
    // เก็บ tenant ใน request
    $request->merge(['tenant' => $tenant]);
    app()->instance('tenant', $tenant);
    
    return $next($request);
}
```

### 2. **Tenant Scope Trait**

```php
// app/Traits/HasTenant.php
trait HasTenant
{
    protected static function bootHasTenant()
    {
        static::creating(function ($model) {
            if (app()->bound('tenant')) {
                $model->tenant_id = app('tenant')->id;
            }
        });
        
        static::addGlobalScope('tenant', function ($query) {
            if (app()->bound('tenant')) {
                $query->where('tenant_id', app('tenant')->id);
            }
        });
    }
}
```

### 3. **Model Updates**

```php
// app/Models/User.php
use HasTenant;

protected $fillable = [
    'name',
    'email',
    'password',
    'role',
    'tenant_id', // เพิ่ม
];

// app/Models/Source.php
use HasTenant;

protected $fillable = [
    'name',
    'description',
    'status',
    'tenant_id', // เพิ่ม
];
```

---

## 🎨 Frontend Customization

### 1. **Dynamic Branding**

```vue
<!-- resources/js/Layouts/PublicLayout.vue -->
<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const tenant = computed(() => page.props.tenant || {});

const primaryColor = computed(() => tenant.value.primary_color || '#dc2626');
const logo = computed(() => tenant.value.logo_url || '/logo-default.jpg');
</script>

<template>
    <div :style="{ '--primary-color': primaryColor }">
        <Navigation :logo="logo" />
        <main>
            <slot />
        </main>
        <Footer :logo="logo" />
    </div>
</template>
```

### 2. **Tenant Settings API**

```php
// app/Http/Controllers/TenantController.php
public function settings()
{
    $tenant = app('tenant');
    
    return response()->json([
        'name' => $tenant->name,
        'logo' => $tenant->logo_url,
        'favicon' => $tenant->favicon_url,
        'primary_color' => $tenant->primary_color,
        'secondary_color' => $tenant->secondary_color,
    ]);
}
```

---

## 🔐 Super Admin Panel

### 1. **Tenant Management**

```php
// app/Http/Controllers/SuperAdmin/TenantController.php
- index() // แสดงรายการ tenant ทั้งหมด
- create() // สร้าง tenant ใหม่
- edit() // แก้ไข tenant
- update() // อัปเดต tenant
- suspend() // ระงับ tenant
- activate() // เปิดใช้งาน tenant
- delete() // ลบ tenant
```

### 2. **Features**
- จัดการ tenant ทั้งหมด
- ดูสถิติการใช้งาน
- จัดการ subscription
- ดูรายได้
- Export data

---

## 👤 Tenant Admin Panel

### 1. **Features สำหรับ Tenant**
- จัดการข้อมูลของตัวเอง (sources, numbers, posts)
- จัดการ users ของตัวเอง
- ตั้งค่า branding (logo, สี, ชื่อเว็บ)
- ดูสถิติการใช้งาน
- จัดการ subscription

### 2. **Limitations ตาม Plan**
- Basic: 5 sources, 10 users
- Pro: 20 sources, 50 users
- Enterprise: Unlimited

---

## 🚀 Development Roadmap

### Phase 1: Core Multi-Tenancy (2-3 สัปดาห์)
1. ✅ สร้างตาราง `tenants`
2. ✅ เพิ่ม `tenant_id` ในตารางที่เกี่ยวข้อง
3. ✅ สร้าง `DetectTenant` middleware
4. ✅ สร้าง `HasTenant` trait
5. ✅ อัปเดต models ทั้งหมด

### Phase 2: Branding System (1-2 สัปดาห์)
1. ✅ สร้าง Tenant Settings page
2. ✅ Dynamic logo, colors, favicon
3. ✅ Custom domain support
4. ✅ Subdomain support

### Phase 3: Super Admin Panel (1-2 สัปดาห์)
1. ✅ Tenant management
2. ✅ Subscription management
3. ✅ Analytics & reporting

### Phase 4: Tenant Admin Panel (1 สัปดาห์)
1. ✅ Branding settings
2. ✅ Usage statistics
3. ✅ Subscription management

### Phase 5: Testing & Documentation (1 สัปดาห์)
1. ✅ Test multi-tenancy
2. ✅ Test branding
3. ✅ Create documentation
4. ✅ Create onboarding flow

---

## 💰 Pricing Strategy

### Basic Plan - 1,500 บาท/เดือน
- 5 sources
- 10 users
- Basic features
- Email support

### Pro Plan - 3,500 บาท/เดือน
- 20 sources
- 50 users
- All features
- Priority support
- Custom domain

### Enterprise Plan - 10,000+ บาท/เดือน
- Unlimited sources
- Unlimited users
- All features
- Dedicated support
- Custom domain
- Custom development
- SLA guarantee

---

## 📝 Onboarding Process

### 1. **Sign Up**
- Tenant สมัครผ่านหน้า signup
- กรอกข้อมูลพื้นฐาน (ชื่อ, email, phone)

### 2. **Setup Wizard**
- เลือก subdomain หรือ custom domain
- อัปโหลด logo
- เลือกสี
- ตั้งค่าเบื้องต้น

### 3. **Payment**
- ชำระ setup fee (ถ้ามี)
- ชำระ subscription แรก

### 4. **Activation**
- Super admin ตรวจสอบและ activate
- ส่ง email ยืนยัน

---

## 🔒 Security Considerations

1. **Data Isolation**: ใช้ global scope เพื่อแยกข้อมูลแต่ละ tenant
2. **Access Control**: ตรวจสอบ tenant_id ในทุก query
3. **Rate Limiting**: จำกัดการใช้งานตาม plan
4. **Backup**: Backup แยกตาม tenant
5. **Monitoring**: Monitor การใช้งานของแต่ละ tenant

---

## 📊 Analytics & Reporting

### Super Admin Dashboard
- จำนวน tenant ทั้งหมด
- รายได้รวม
- Tenant ที่ active/suspended
- Usage statistics

### Tenant Dashboard
- จำนวน users
- จำนวน sources
- จำนวน posts
- Traffic statistics

---

## 🎯 Next Steps

1. **เริ่มจาก Phase 1**: สร้าง multi-tenancy core
2. **Test กับ 2-3 tenant**: ตรวจสอบว่าทำงานได้ถูกต้อง
3. **เพิ่ม branding**: ให้ tenant กำหนด logo, สีได้
4. **สร้าง Super Admin**: จัดการ tenant ทั้งหมด
5. **Launch**: เปิดให้คนอื่นสมัครได้

---

## 💡 Tips

1. **เริ่มเล็ก**: เริ่มจาก 2-3 tenant ก่อน แล้วค่อยขยาย
2. **Documentation**: สร้าง documentation ที่ดี
3. **Support**: เตรียม support system
4. **Pricing**: เริ่มจากราคาต่ำก่อน แล้วค่อยปรับ
5. **Marketing**: โฆษณาในกลุ่มที่เกี่ยวข้อง

---

## 🚨 Challenges & Solutions

### Challenge 1: Performance
**Solution**: ใช้ caching, database indexing, CDN

### Challenge 2: Data Isolation
**Solution**: ใช้ global scope และ middleware ตรวจสอบ

### Challenge 3: Custom Domain
**Solution**: ใช้ DNS CNAME หรือ reverse proxy

### Challenge 4: Support
**Solution**: สร้าง support ticket system และ knowledge base

---

## 📚 Resources

- Laravel Multi-Tenancy: https://laravel.com/docs/multi-tenancy
- Spatie Laravel Multi-Tenancy: https://github.com/spatie/laravel-multitenancy
- White Label SaaS Guide: https://www.saas.com/white-label
