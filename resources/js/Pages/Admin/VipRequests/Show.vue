<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    request: Object,
});

const page = usePage();

const approveForm = useForm({
    days: 30,
});

const approve = () => {
    approveForm.post(route('backoffice.vip-requests.approve', props.request.id));
};

const rejectForm = useForm({});

const reject = () => {
    if (!confirm('ยืนยันปฏิเสธคำขอ VIP นี้?')) return;
    rejectForm.post(route('backoffice.vip-requests.reject', props.request.id));
};
</script>

<template>
    <Head title="รายละเอียดคำขอ VIP" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">รายละเอียดคำขอ VIP</h2>
                <Link :href="route('backoffice.vip-requests.index')">
                    <SecondaryButton>ย้อนกลับ</SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8 space-y-4">
                <div v-if="page.props.flash?.success" class="rounded-lg bg-green-50 border border-green-200 p-4 text-green-800">
                    {{ page.props.flash.success }}
                </div>
                <div v-if="page.props.flash?.error" class="rounded-lg bg-red-50 border border-red-200 p-4 text-red-800">
                    {{ page.props.flash.error }}
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">ข้อมูลผู้ใช้</h3>
                        <p class="text-sm text-gray-800">
                            {{ request.customer?.name }}<br />
                            <span class="text-gray-500">{{ request.customer?.email }}</span>
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-1">ยอดโอน</h3>
                            <p class="text-lg font-bold text-gray-900">
                                {{ request.amount ? request.amount.toLocaleString('th-TH', { minimumFractionDigits: 2 }) : '-' }} บาท
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-1">วันที่ขอ</h3>
                            <p class="text-sm text-gray-800">
                                {{ new Date(request.created_at).toLocaleString('th-TH') }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-1">ชื่อบัญชี</h3>
                            <p class="text-sm text-gray-800">
                                {{ request.bank_account_name || '-' }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-1">เลขที่บัญชี</h3>
                            <p class="text-sm text-gray-800">
                                {{ request.bank_account_number || '-' }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-1">หมายเหตุจากลูกค้า</h3>
                        <p class="text-sm text-gray-800 whitespace-pre-line">
                            {{ request.note || '-' }}
                        </p>
                    </div>

                    <div v-if="request.slip_url" class="border-t pt-4 mt-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">สลิปการโอนเงิน</h3>
                        <a :href="request.slip_url" target="_blank" rel="noopener" class="inline-block">
                            <img
                                :src="request.slip_url"
                                alt="สลิปโอนเงิน"
                                class="max-w-full max-h-96 rounded-lg border border-gray-200 shadow-sm object-contain"
                            />
                        </a>
                        <p class="mt-2 text-xs text-gray-500">คลิกที่รูปเพื่อเปิดขนาดเต็ม</p>
                    </div>

                    <div v-if="request.status !== 'pending'" class="border-t pt-4 mt-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-1">สถานะคำขอ</h3>
                        <p class="text-sm text-gray-800">
                            {{ request.status }} โดย {{ request.approver?.name || '-' }} ณ
                            {{ request.approved_at ? new Date(request.approved_at).toLocaleString('th-TH') : '-' }}
                        </p>
                    </div>

                    <div v-if="request.status === 'pending'" class="border-t pt-4 mt-4 space-y-4">
                        <h3 class="text-sm font-semibold text-gray-700">จัดการคำขอ</h3>

                        <form class="space-y-3" @submit.prevent="approve">
                            <div>
                                <InputLabel value="จำนวนวัน VIP ที่ให้" />
                                <TextInput v-model="approveForm.days" type="number" class="mt-1 block w-32" min="1" max="365" />
                                <InputError class="mt-2" :message="approveForm.errors.days" />
                            </div>
                            <div class="flex items-center gap-3">
                                <PrimaryButton type="submit" :disabled="approveForm.processing">
                                    อนุมัติ VIP
                                </PrimaryButton>
                                <SecondaryButton type="button" :disabled="rejectForm.processing" @click="reject">
                                    ปฏิเสธคำขอ
                                </SecondaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

