<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    requests: Object,
    filters: {
        type: Object,
        default: () => ({ status: '', q: '' }),
    },
    stats: {
        type: Object,
        default: () => ({ total: 0, pending: 0, approved: 0, rejected: 0 }),
    },
});

const page = usePage();

const search = ref(props.filters?.q || '');
const statusFilter = ref(props.filters?.status || '');

const applyFilters = (e) => {
    e?.preventDefault?.();
    router.get(
        route('backoffice.vip-requests.index'),
        {
            status: statusFilter.value,
            q: search.value,
        },
        { preserveState: true, replace: true }
    );
};

const selectStatusTab = (status) => {
    statusFilter.value = status;
    applyFilters();
};

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    router.get(route('backoffice.vip-requests.index'), {}, { preserveState: true, replace: true });
};

const statusClass = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-amber-100 text-amber-800 border border-amber-300';
        case 'approved':
            return 'bg-emerald-100 text-emerald-800 border border-emerald-300';
        case 'rejected':
            return 'bg-red-100 text-red-800 border border-red-300';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const statusLabel = (status) => {
    switch (status) {
        case 'pending':
            return '⏳ รออนุมัติ';
        case 'approved':
            return '✅ อนุมัติแล้ว';
        case 'rejected':
            return '❌ ปฏิเสธ';
        default:
            return status;
    }
};

const recommendedPackageText = (amount) => {
    const val = Number(amount || 0);
    if (val >= 450) return '90 วัน (3 เดือน)';
    if (val >= 179) return '30 วัน (1 เดือน)';
    return '15 วัน (รายงวด)';
};
</script>

<template>
    <Head title="คำขอ VIP จากการโอนเงิน" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-gray-800">คำขอ VIP จากการโอนเงิน</h2>
                    <p class="text-sm text-gray-500 mt-0.5">ตรวจสอบรายการแจ้งโอนเงินและอนุมัติสิทธิ์สมาชิก VIP</p>
                </div>
                <Link :href="route('backoffice.customers.index')">
                    <SecondaryButton>👥 ไปหน้าจัดการสมาชิกทั้งหมด</SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-5">
                <div v-if="page.props.flash?.success" class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 font-medium">
                    ✅ {{ page.props.flash.success }}
                </div>
                <div v-if="page.props.flash?.error" class="rounded-xl bg-red-50 border border-red-200 p-4 text-red-800 font-medium">
                    ⚠️ {{ page.props.flash.error }}
                </div>

                <!-- Status Filter Tabs -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <button
                        type="button"
                        @click="selectStatusTab('')"
                        class="p-4 rounded-2xl border text-left transition shadow-sm"
                        :class="statusFilter === '' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
                    >
                        <div class="text-xs font-bold uppercase tracking-wider opacity-75">ทั้งหมด</div>
                        <div class="text-2xl font-extrabold mt-1">{{ stats?.total ?? 0 }}</div>
                    </button>

                    <button
                        type="button"
                        @click="selectStatusTab('pending')"
                        class="p-4 rounded-2xl border text-left transition shadow-sm"
                        :class="statusFilter === 'pending' ? 'bg-amber-500 text-white border-amber-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-amber-50'"
                    >
                        <div class="text-xs font-bold uppercase tracking-wider" :class="statusFilter === 'pending' ? 'text-white' : 'text-amber-700'">
                            ⏳ รออนุมัติ
                        </div>
                        <div class="text-2xl font-extrabold mt-1" :class="statusFilter === 'pending' ? 'text-white' : 'text-amber-600'">
                            {{ stats?.pending ?? 0 }}
                        </div>
                    </button>

                    <button
                        type="button"
                        @click="selectStatusTab('approved')"
                        class="p-4 rounded-2xl border text-left transition shadow-sm"
                        :class="statusFilter === 'approved' ? 'bg-emerald-600 text-white border-emerald-700' : 'bg-white text-gray-700 border-gray-200 hover:bg-emerald-50'"
                    >
                        <div class="text-xs font-bold uppercase tracking-wider" :class="statusFilter === 'approved' ? 'text-white' : 'text-emerald-700'">
                            ✅ อนุมัติแล้ว
                        </div>
                        <div class="text-2xl font-extrabold mt-1" :class="statusFilter === 'approved' ? 'text-white' : 'text-emerald-600'">
                            {{ stats?.approved ?? 0 }}
                        </div>
                    </button>

                    <button
                        type="button"
                        @click="selectStatusTab('rejected')"
                        class="p-4 rounded-2xl border text-left transition shadow-sm"
                        :class="statusFilter === 'rejected' ? 'bg-red-600 text-white border-red-700' : 'bg-white text-gray-700 border-gray-200 hover:bg-red-50'"
                    >
                        <div class="text-xs font-bold uppercase tracking-wider" :class="statusFilter === 'rejected' ? 'text-white' : 'text-red-700'">
                            ❌ ปฏิเสธ
                        </div>
                        <div class="text-2xl font-extrabold mt-1" :class="statusFilter === 'rejected' ? 'text-white' : 'text-red-600'">
                            {{ stats?.rejected ?? 0 }}
                        </div>
                    </button>
                </div>

                <!-- Search & Status Bar -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <form class="flex flex-col md:flex-row md:items-end gap-4" @submit="applyFilters">
                        <div class="flex-1">
                            <InputLabel value="ค้นหาชื่อสมาชิก, อีเมล, ชื่อบัญชีที่โอน หรือหมายเหตุ" />
                            <TextInput
                                v-model="search"
                                type="text"
                                placeholder="พิมพ์คำค้นหา..."
                                class="mt-1 block w-full text-sm"
                            />
                        </div>
                        <div class="w-full md:w-52">
                            <InputLabel value="ตัวกรองสถานะ" />
                            <select
                                v-model="statusFilter"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            >
                                <option value="">ทั้งหมด</option>
                                <option value="pending">⏳ รออนุมัติ</option>
                                <option value="approved">✅ อนุมัติแล้ว</option>
                                <option value="rejected">❌ ปฏิเสธ</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <PrimaryButton type="submit">ค้นหา</PrimaryButton>
                            <SecondaryButton type="button" @click="clearFilters">ล้างตัวกรอง</SecondaryButton>
                        </div>
                    </form>
                </div>

                <!-- Requests Table -->
                <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">วันที่ขอ</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">สมาชิก</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ยอดโอน / แพ็กเกจ</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ข้อมูลการโอน</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">สถานะ</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">จัดการโดย</th>
                                    <th class="px-6 py-3 text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="r in requests.data" :key="r.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ new Date(r.created_at).toLocaleString('th-TH', { dateStyle: 'medium', timeStyle: 'short' }) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <div class="font-semibold text-gray-900">{{ r.customer?.name || '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ r.customer?.email || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-extrabold text-gray-900">
                                            ฿{{ r.amount ? Number(r.amount).toLocaleString('th-TH', { minimumFractionDigits: 2 }) : '-' }}
                                        </div>
                                        <div class="text-xs text-indigo-600 font-medium">
                                            แนะนำ: {{ recommendedPackageText(r.amount) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600">
                                        <div>บัญชี: <strong>{{ r.bank_account_name || '-' }}</strong></div>
                                        <div v-if="r.bank_account_number">เลข: {{ r.bank_account_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold" :class="statusClass(r.status)">
                                            {{ statusLabel(r.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <span v-if="r.approver">{{ r.approver.name }}</span>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <Link
                                            :href="route('backoffice.vip-requests.show', r.id)"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold transition"
                                            :class="r.status === 'pending' ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                        >
                                            {{ r.status === 'pending' ? 'ตรวจสอบ / อนุมัติ' : 'ดูรายละเอียด' }}
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!requests.data?.length">
                                    <td class="px-6 py-10 text-center text-sm text-gray-500" colspan="7">
                                        ไม่พบรายการคำขอ VIP ตามเงื่อนไขที่เลือก
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="requests.links?.length > 3" class="p-4 bg-gray-50 border-t border-gray-100 flex flex-wrap gap-1 justify-end">
                        <Link
                            v-for="link in requests.links"
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
    </AuthenticatedLayout>
</template>
