<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
    recentPendingVipRequests: {
        type: Array,
        default: () => [],
    },
    promotedSources: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const isSyncing = ref(false);

const syncAndCalculateAccuracy = () => {
    isSyncing.value = true;
    router.post(route('backoffice.results.sync'), {}, {
        onFinish: () => {
            isSyncing.value = false;
        },
    });
};

const formatCurrency = (val) => {
    const num = Number(val || 0);
    return num.toLocaleString('th-TH', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
};

const formatDateTime = (iso) => {
    if (!iso) return '-';
    try {
        return new Date(iso).toLocaleString('th-TH', {
            dateStyle: 'medium',
            timeStyle: 'short',
        });
    } catch {
        return iso;
    }
};
</script>

<template>
    <Head title="แดชบอร์ดหลังบ้าน" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-gray-800">
                        ภาพรวมระบบ (Backoffice Dashboard)
                    </h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        สวัสดีคุณ {{ $page.props.auth.user?.name }} ({{ $page.props.auth.user?.role === 'admin' ? 'ผู้ดูแลระบบ' : 'ทีมงาน' }})
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link href="/" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-sm font-medium shadow-sm">
                        🌐 ไปหน้าเว็บไซต์หลัก
                    </Link>
                    <PrimaryButton
                        v-if="$page.props.auth.user?.is_admin || $page.props.auth.user?.can_access?.results || $page.props.auth.user?.can_access?.lottoDetails"
                        type="button"
                        :disabled="isSyncing"
                        @click="syncAndCalculateAccuracy"
                    >
                        <span v-if="isSyncing">กำลังซิงค์และคำนวณ...</span>
                        <span v-else>🔄 ซิงค์ผลหวย & คำนวณความแม่นยำ</span>
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Flash Messages -->
                <div v-if="page.props.flash?.success" class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 font-medium shadow-sm">
                    ✅ {{ page.props.flash.success }}
                </div>
                <div v-if="page.props.flash?.error" class="rounded-xl bg-red-50 border border-red-200 p-4 text-red-800 font-medium shadow-sm">
                    ⚠️ {{ page.props.flash.error }}
                </div>

                <!-- Alert Banner: Pending VIP Requests -->
                <div
                    v-if="$page.props.auth.user?.is_admin && stats.pending_vip_requests_count > 0"
                    class="rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 p-5 text-white shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                >
                    <div class="flex items-center gap-3">
                        <span class="text-3xl">🔔</span>
                        <div>
                            <h3 class="text-lg font-extrabold">
                                มีคำขอสมัคร VIP รอการตรวจสอบ {{ stats.pending_vip_requests_count }} รายการ!
                            </h3>
                            <p class="text-sm text-amber-100">
                                กรุณาตรวจสอบยอดโอนเงินและกดอนุมัติเพื่อให้ลูกค้าเข้าดูเลขเด็ด VIP ได้ทันที
                            </p>
                        </div>
                    </div>
                    <Link
                        :href="route('backoffice.vip-requests.index')"
                        class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-white text-amber-900 font-bold text-sm hover:bg-amber-50 shadow transition shrink-0"
                    >
                        ไปหน้าอนุมัติ VIP →
                    </Link>
                </div>

                <!-- Section 1: Revenue & Membership Cards (Admin) -->
                <div v-if="$page.props.auth.user?.is_admin" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <!-- รายได้ VIP เดือนนี้ -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">รายได้ VIP เดือนนี้</span>
                            <span class="p-2 rounded-lg bg-emerald-50 text-emerald-600 text-lg">💰</span>
                        </div>
                        <div class="mt-3">
                            <div class="text-3xl font-extrabold text-gray-900">
                                ฿{{ formatCurrency(stats.monthly_vip_revenue) }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                รายได้สะสมทั้งหมด: <strong class="text-gray-700">฿{{ formatCurrency(stats.total_vip_revenue) }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- สมาชิก VIP ที่กำลังใช้งาน -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">สมาชิก VIP ปัจจุบัน</span>
                            <span class="p-2 rounded-lg bg-amber-50 text-amber-600 text-lg">👑</span>
                        </div>
                        <div class="mt-3">
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-extrabold text-amber-600">{{ stats.active_vip_count ?? 0 }}</span>
                                <span class="text-sm text-gray-500">บัญชี</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 flex items-center justify-between">
                                <span>รออนุมัติ: <strong class="text-red-600">{{ stats.pending_vip_requests_count ?? 0 }}</strong> รายการ</span>
                                <Link :href="route('backoffice.vip-requests.index')" class="text-indigo-600 hover:underline font-medium">จัดการ →</Link>
                            </div>
                        </div>
                    </div>

                    <!-- สมาชิกทั้งหมด -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">สมาชิกหน้าเว็บทั้งหมด</span>
                            <span class="p-2 rounded-lg bg-blue-50 text-blue-600 text-lg">👥</span>
                        </div>
                        <div class="mt-3">
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-extrabold text-gray-900">{{ stats.total_customers ?? 0 }}</span>
                                <span class="text-sm text-gray-500">คน</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 flex items-center justify-between">
                                <span>สมัครใหม่วันนี้: <strong class="text-emerald-600">+{{ stats.today_customers ?? 0 }}</strong> คน</span>
                                <Link :href="route('backoffice.customers.index')" class="text-indigo-600 hover:underline font-medium">ดูรายชื่อ →</Link>
                            </div>
                        </div>
                    </div>

                    <!-- สำนักหวย & สปอนเซอร์ -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">สำนักหวยที่เปิดใช้งาน</span>
                            <span class="p-2 rounded-lg bg-purple-50 text-purple-600 text-lg">🏛️</span>
                        </div>
                        <div class="mt-3">
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-extrabold text-gray-900">{{ stats.active_sources_count ?? 0 }}</span>
                                <span class="text-sm text-gray-500">สำนัก</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 flex items-center justify-between">
                                <span>กำลังโปรโมท: <strong class="text-amber-600">⭐ {{ stats.promoted_sources_count ?? 0 }}</strong> สำนัก</span>
                                <Link :href="route('backoffice.sources.index')" class="text-indigo-600 hover:underline font-medium">จัดการ →</Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Operational Status Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <!-- ความคืบหน้าการลงเลขงวดล่าสุด -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">การลงเลขเด็ดงวดล่าสุด</span>
                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-red-50 text-red-700">
                                {{ stats.latest_draw_date_text || stats.latest_draw_date || 'ยังไม่มีงวด' }}
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2 my-2">
                            <span class="text-2xl font-extrabold text-gray-900">
                                {{ stats.latest_draw_sources_submitted ?? 0 }} / {{ stats.active_sources_count ?? 0 }}
                            </span>
                            <span class="text-sm text-gray-500">สำนักที่ลงเลขแล้ว</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden my-3">
                            <div
                                class="bg-red-600 h-2.5 rounded-full transition-all"
                                :style="{
                                    width: `${stats.active_sources_count > 0 ? Math.min(100, Math.round((stats.latest_draw_sources_submitted / stats.active_sources_count) * 100)) : 0}%`
                                }"
                            ></div>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500 mt-2">
                            <span>อัปเดตเลขเด็ดประจำงวด</span>
                            <Link
                                v-if="$page.props.auth.user?.can_access?.numbers"
                                :href="route('backoffice.numbers.bulk')"
                                class="text-indigo-600 hover:underline font-semibold"
                            >
                                ⚡ กรอกเลขด่วนทั้งงวด →
                            </Link>
                        </div>
                    </div>

                    <!-- สถานะข้อมูลผลหวย GLO -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-500">สถานะผลหวย & ความแม่นยำ</span>
                                <span
                                    class="text-xs font-semibold px-2.5 py-0.5 rounded-full"
                                    :class="stats.pending_lotto_details_count > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'"
                                >
                                    {{ stats.pending_lotto_details_count > 0 ? `ค้างดึง ${stats.pending_lotto_details_count} งวด` : 'ข้อมูลครบถ้วน' }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mt-2">
                                งวดที่ค้างดึงรายละเอียดรางวัล: <strong class="text-gray-900">{{ stats.pending_lotto_details_count ?? 0 }}</strong> งวด<br />
                                งวดที่ยังไม่คำนวณความแม่น: <strong class="text-gray-900">{{ stats.uncalculated_results_count ?? 0 }}</strong> งวด
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                            <Link
                                v-if="$page.props.auth.user?.can_access?.lottoData"
                                :href="route('backoffice.lotto-data.index')"
                                class="text-indigo-600 hover:underline font-semibold"
                            >
                                จัดการงวดหวย →
                            </Link>
                            <Link
                                v-if="$page.props.auth.user?.can_access?.results || $page.props.auth.user?.is_admin"
                                :href="route('backoffice.results.index')"
                                class="text-gray-600 hover:text-gray-900 font-medium"
                            >
                                ดูตารางผลหวยที่คำนวณ →
                            </Link>
                        </div>
                    </div>

                    <!-- สินค้าสายมู & บทความ SEO -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-500">คอนเทนต์ & สินค้าสายมู</span>
                                <span class="text-lg">🧿</span>
                            </div>
                            <div class="grid grid-cols-2 gap-3 mt-3">
                                <div class="bg-gray-50 rounded-xl p-3">
                                    <div class="text-xl font-extrabold text-gray-900">{{ stats.active_products_count ?? 0 }}</div>
                                    <div class="text-xs text-gray-500">สินค้าสายมูที่แสดง</div>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3">
                                    <div class="text-xl font-extrabold text-gray-900">{{ stats.published_posts_count ?? 0 }}</div>
                                    <div class="text-xs text-gray-500">บทความที่เผยแพร่</div>
                                </div>
                            </div>
                        </div>
                        <div v-if="$page.props.auth.user?.is_admin" class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                            <Link :href="route('backoffice.affiliate-products.index')" class="text-indigo-600 hover:underline font-semibold">
                                จัดการวัตถุมงคล →
                            </Link>
                            <Link :href="route('backoffice.posts.index')" class="text-indigo-600 hover:underline font-semibold">
                                จัดการบทความ →
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Pending VIP Requests & Promoted Sources (Admin) -->
                <div v-if="$page.props.auth.user?.is_admin" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- รายการคำขอ VIP ล่าสุดที่รออนุมัติ -->
                    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                <span>👑 คำขอ VIP ที่รออนุมัติล่าสุด</span>
                                <span
                                    v-if="stats.pending_vip_requests_count > 0"
                                    class="px-2 py-0.5 text-xs font-bold rounded-full bg-red-100 text-red-700"
                                >
                                    {{ stats.pending_vip_requests_count }}
                                </span>
                            </h3>
                            <Link :href="route('backoffice.vip-requests.index')" class="text-xs font-semibold text-indigo-600 hover:underline">
                                ดูทั้งหมด →
                            </Link>
                        </div>

                        <div v-if="recentPendingVipRequests.length" class="divide-y divide-gray-100">
                            <div
                                v-for="req in recentPendingVipRequests"
                                :key="req.id"
                                class="px-6 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 hover:bg-gray-50"
                            >
                                <div>
                                    <div class="font-semibold text-sm text-gray-900">
                                        {{ req.customer_name }}
                                        <span class="text-xs font-normal text-gray-500">({{ req.customer_email }})</span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        โอนจาก: {{ req.bank_account_name || '-' }} • เมื่อ {{ formatDateTime(req.created_at) }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-extrabold text-emerald-700 text-sm">
                                        ฿{{ formatCurrency(req.amount) }}
                                    </span>
                                    <Link
                                        :href="route('backoffice.vip-requests.show', req.id)"
                                        class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition"
                                    >
                                        ตรวจสอบ / อนุมัติ
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <div v-else class="p-8 text-center text-sm text-gray-500">
                            🎉 ไม่มีคำขอ VIP ค้างตรวจสอบในขณะนี้
                        </div>
                    </div>

                    <!-- สำนักที่กำลังโปรโมท (Sponsored Sources) -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="font-bold text-gray-900">⭐ สำนักแนะนำ (Sponsored)</h3>
                            <Link :href="route('backoffice.sources.index')" class="text-xs font-semibold text-indigo-600 hover:underline">
                                จัดการ →
                            </Link>
                        </div>
                        <div v-if="promotedSources.length" class="divide-y divide-gray-100">
                            <div
                                v-for="s in promotedSources"
                                :key="s.id"
                                class="px-6 py-3.5 flex items-center justify-between gap-2"
                            >
                                <div>
                                    <div class="font-semibold text-sm text-gray-900">{{ s.name }}</div>
                                    <div class="text-xs text-gray-500 flex flex-wrap items-center gap-2 mt-0.5">
                                        <span>{{ s.promoted_until ? `ถึงวันที่ ${s.promoted_until}` : 'ไม่มีกำหนดวันหมดอายุ' }}</span>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-semibold">
                                            👁️ {{ (s.view_count || 0).toLocaleString() }} ครั้ง
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-2 py-0.5 text-xs font-semibold rounded-full"
                                        :class="s.is_currently_promoted ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-700'"
                                    >
                                        {{ s.is_currently_promoted ? 'แสดงผลอยู่' : 'หมดอายุโปรโมท' }}
                                    </span>
                                    <Link :href="route('backoffice.sources.edit', s.id)" class="text-xs text-indigo-600 hover:underline">
                                        แก้ไข
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <div v-else class="p-8 text-center text-sm text-gray-500">
                            ยังไม่มีสำนักที่ตั้งค่าโปรโมท
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
