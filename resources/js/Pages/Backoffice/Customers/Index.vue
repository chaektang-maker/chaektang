<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    customers: Object,
    filters: Object,
    stats: Object,
});

const page = usePage();

const search = ref(props.filters?.q || '');
const vipStatus = ref(props.filters?.vip_status || '');

const applyFilters = (e) => {
    e?.preventDefault?.();
    router.get(
        route('backoffice.customers.index'),
        {
            q: search.value,
            vip_status: vipStatus.value,
        },
        { preserveState: true, replace: true }
    );
};

const clearFilters = () => {
    search.value = '';
    vipStatus.value = '';
    router.get(route('backoffice.customers.index'), {}, { preserveState: true, replace: true });
};

// Modal สำหรับเพิ่ม/ต่ออายุ VIP ให้ลูกค้ารายคน
const selectedCustomer = ref(null);
const grantVipForm = useForm({
    days: 30,
    amount: 0,
});

const openGrantVipModal = (customer) => {
    selectedCustomer.value = customer;
    grantVipForm.days = 30;
    grantVipForm.amount = 179;
};

const closeGrantVipModal = () => {
    selectedCustomer.value = null;
};

const selectPackagePreset = (days, amount) => {
    grantVipForm.days = days;
    grantVipForm.amount = amount;
};

const submitGrantVip = () => {
    if (!selectedCustomer.value) return;
    grantVipForm.post(route('backoffice.customers.grant-vip', selectedCustomer.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeGrantVipModal();
        },
    });
};

const revokeVip = (customer) => {
    if (!confirm(`ยืนยันการยกเลิกสถานะ VIP ของ "${customer.name}" ทันทีหรือไม่?`)) return;
    router.post(
        route('backoffice.customers.revoke-vip', customer.id),
        {},
        { preserveScroll: true }
    );
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
    <Head title="จัดการสมาชิก (Customers)" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-gray-800">
                        จัดการสมาชิกหน้าเว็บ (Customers)
                    </h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        ตรวจสอบรายชื่อสมาชิก สถานะ VIP และเพิ่ม/ต่ออายุ VIP ให้สมาชิกรายบุคคล
                    </p>
                </div>
                <Link :href="route('backoffice.vip-requests.index')">
                    <SecondaryButton>ไปหน้าคำขอโอนเงิน VIP</SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Flash Messages -->
                <div v-if="page.props.flash?.success" class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 font-medium">
                    ✅ {{ page.props.flash.success }}
                </div>
                <div v-if="page.props.flash?.error" class="rounded-xl bg-red-50 border border-red-200 p-4 text-red-800 font-medium">
                    ⚠️ {{ page.props.flash.error }}
                </div>

                <!-- Summary Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">สมาชิกทั้งหมด</span>
                        <div class="text-3xl font-extrabold text-gray-900 mt-2">{{ stats?.total ?? 0 }} <span class="text-sm font-normal text-gray-500">คน</span></div>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-600">👑 สมาชิก VIP ปัจจุบัน</span>
                        <div class="text-3xl font-extrabold text-amber-600 mt-2">{{ stats?.vip ?? 0 }} <span class="text-sm font-normal text-gray-500">คน</span></div>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">สมาชิกทั่วไป (Free)</span>
                        <div class="text-3xl font-extrabold text-gray-700 mt-2">{{ stats?.free ?? 0 }} <span class="text-sm font-normal text-gray-500">คน</span></div>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <form class="flex flex-col md:flex-row md:items-end gap-4" @submit="applyFilters">
                        <div class="flex-1">
                            <InputLabel value="ค้นหาชื่อ หรือ อีเมลสมาชิก" />
                            <TextInput
                                v-model="search"
                                type="text"
                                placeholder="พิมพ์ชื่อ หรือ อีเมล..."
                                class="mt-1 block w-full text-sm"
                            />
                        </div>
                        <div class="w-full md:w-56">
                            <InputLabel value="สถานะสมาชิก" />
                            <select
                                v-model="vipStatus"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            >
                                <option value="">ทั้งหมด</option>
                                <option value="vip">👑 เฉพาะสมาชิก VIP</option>
                                <option value="free">สมาชิกทั่วไป (Free)</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <PrimaryButton type="submit">ค้นหา</PrimaryButton>
                            <SecondaryButton type="button" @click="clearFilters">ล้างตัวกรอง</SecondaryButton>
                        </div>
                    </form>
                </div>

                <!-- Customers Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ชื่อสมาชิก / อีเมล</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">สถานะ VIP</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">วันหมดอายุ VIP</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">วันที่สมัคร</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">จัดการสิทธิ์ VIP</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="c in customers?.data" :key="c.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#{{ c.id }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-sm text-gray-900">{{ c.name }}</div>
                                        <div class="text-xs text-gray-500">{{ c.email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            v-if="c.is_vip"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300"
                                        >
                                            👑 VIP (เหลือ {{ c.vip_days_remaining }} วัน)
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600"
                                        >
                                            สมาชิกทั่วไป
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ c.vip_ends_at ? formatDateTime(c.vip_ends_at) : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDateTime(c.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                        <button
                                            type="button"
                                            @click="openGrantVipModal(c)"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-sm transition"
                                        >
                                            {{ c.is_vip ? '👑 ต่ออายุ VIP' : '👑 เปิดสิทธิ์ VIP' }}
                                        </button>
                                        <button
                                            v-if="c.is_vip"
                                            type="button"
                                            @click="revokeVip(c)"
                                            class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 text-xs font-semibold border border-red-200 transition"
                                        >
                                            ยกเลิก VIP
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!customers?.data?.length">
                                    <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                        ไม่พบข้อมูลสมาชิกตามเงื่อนไขที่ค้นหา
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="customers?.links?.length > 3" class="p-4 bg-gray-50 border-t border-gray-100 flex flex-wrap gap-1 justify-end">
                        <Link
                            v-for="link in customers.links"
                            :key="link.label"
                            :href="link.url || ''"
                            class="px-3 py-1 rounded border text-sm"
                            :class="link.active ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-200'"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal เพิ่ม/ต่ออายุ VIP -->
        <div
            v-if="selectedCustomer"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeGrantVipModal"
        >
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="text-lg font-bold text-gray-900">
                        👑 {{ selectedCustomer.is_vip ? 'ต่ออายุสมาชิก VIP' : 'เปิดสิทธิ์สมาชิก VIP' }}
                    </h3>
                    <button type="button" @click="closeGrantVipModal" class="text-gray-400 hover:text-gray-600 text-xl leading-none">
                        &times;
                    </button>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-sm">
                    <div class="font-bold text-gray-900">{{ selectedCustomer.name }}</div>
                    <div class="text-gray-600 text-xs">{{ selectedCustomer.email }}</div>
                    <div v-if="selectedCustomer.is_vip" class="text-xs text-amber-800 mt-1 font-medium">
                        สถานะปัจจุบัน: VIP (หมดอายุ {{ formatDateTime(selectedCustomer.vip_ends_at) }})
                    </div>
                </div>

                <form class="space-y-4" @submit.prevent="submitGrantVip">
                    <div>
                        <InputLabel value="เลือกแพ็กเกจด่วน" />
                        <div class="grid grid-cols-4 gap-2 mt-1.5">
                            <button
                                type="button"
                                @click="selectPackagePreset(15, 99)"
                                class="py-2 px-2 rounded-lg border text-xs font-bold transition"
                                :class="grantVipForm.days === 15 && grantVipForm.amount === 99 ? 'bg-amber-500 text-white border-amber-600' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'"
                            >
                                15 วัน<br /><span class="font-normal">99฿</span>
                            </button>
                            <button
                                type="button"
                                @click="selectPackagePreset(30, 179)"
                                class="py-2 px-2 rounded-lg border text-xs font-bold transition"
                                :class="grantVipForm.days === 30 && grantVipForm.amount === 179 ? 'bg-amber-500 text-white border-amber-600' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'"
                            >
                                30 วัน<br /><span class="font-normal">179฿</span>
                            </button>
                            <button
                                type="button"
                                @click="selectPackagePreset(90, 450)"
                                class="py-2 px-2 rounded-lg border text-xs font-bold transition"
                                :class="grantVipForm.days === 90 && grantVipForm.amount === 450 ? 'bg-amber-500 text-white border-amber-600' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'"
                            >
                                90 วัน<br /><span class="font-normal">450฿</span>
                            </button>
                            <button
                                type="button"
                                @click="selectPackagePreset(15, 0)"
                                class="py-2 px-2 rounded-lg border text-xs font-bold transition"
                                :class="grantVipForm.days === 15 && grantVipForm.amount === 0 ? 'bg-amber-500 text-white border-amber-600' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'"
                            >
                                15 วัน<br /><span class="font-normal">แจกฟรี 0฿</span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <InputLabel value="จำนวนวันที่เพิ่ม (วัน)" />
                            <TextInput v-model="grantVipForm.days" type="number" min="1" max="365" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <InputLabel value="ยอดเงินที่บันทึก (บาท)" />
                            <TextInput v-model="grantVipForm.amount" type="number" min="0" step="0.01" class="mt-1 block w-full" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t">
                        <SecondaryButton type="button" @click="closeGrantVipModal">ยกเลิก</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="grantVipForm.processing">
                            บันทึกสิทธิ์ VIP
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

