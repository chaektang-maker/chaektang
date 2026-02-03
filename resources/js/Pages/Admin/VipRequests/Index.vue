<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    requests: Object,
});

const page = usePage();

const statusClass = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <Head title="คำขอ VIP จากการโอนเงิน" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">คำขอ VIP จากการโอนเงิน</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
                <div v-if="page.props.flash?.success" class="rounded-lg bg-green-50 border border-green-200 p-4 text-green-800">
                    {{ page.props.flash.success }}
                </div>
                <div v-if="page.props.flash?.error" class="rounded-lg bg-red-50 border border-red-200 p-4 text-red-800">
                    {{ page.props.flash.error }}
                </div>

                <div
                    v-if="page.props.auth?.user?.pending_vip_requests_count > 0"
                    class="rounded-lg bg-amber-50 border border-amber-200 p-4 text-amber-800 flex items-center gap-2"
                >
                    <span class="text-lg">🔔</span>
                    <span>มีคำขอ VIP รออนุมัติ <strong>{{ page.props.auth.user.pending_vip_requests_count }}</strong> รายการ</span>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่ขอ</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ผู้ใช้</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ยอดโอน</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">จัดการโดย</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="r in requests.data" :key="r.id">
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ new Date(r.created_at).toLocaleString('th-TH') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ r.customer?.name }}<br />
                                        <span class="text-xs text-gray-500">{{ r.customer?.email }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ r.amount ? r.amount.toLocaleString('th-TH', { minimumFractionDigits: 2 }) : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold" :class="statusClass(r.status)">
                                            {{ r.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <span v-if="r.approver">
                                            {{ r.approver.name }}
                                        </span>
                                        <span v-else>-</span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <Link :href="route('backoffice.vip-requests.show', r.id)" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                            ดูรายละเอียด
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!requests.data?.length">
                                    <td class="px-6 py-6 text-center text-sm text-gray-500" colspan="6">ยังไม่มีคำขอ VIP</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 flex flex-wrap gap-2">
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

