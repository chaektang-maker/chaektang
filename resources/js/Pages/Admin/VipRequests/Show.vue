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

// คำนวณจำนวนวัน VIP เริ่มต้นอัตโนมัติตามยอดเงินที่โอน (ตรงกับแพ็กเกจหน้าบ้าน 99฿=15วัน, 179฿=30วัน, 450฿=90วัน)
const calcInitialDays = () => {
    if (props.request?.recommended_days) {
        return Number(props.request.recommended_days);
    }
    const amt = Number(props.request?.amount || 0);
    if (amt >= 450) return 90;
    if (amt >= 179) return 30;
    return 15;
};

const approveForm = useForm({
    days: calcInitialDays(),
});

const approve = () => {
    approveForm.post(route('backoffice.vip-requests.approve', props.request.id));
};

const rejectForm = useForm({});

const reject = () => {
    if (!confirm('ยืนยันปฏิเสธคำขอ VIP นี้?')) return;
    rejectForm.post(route('backoffice.vip-requests.reject', props.request.id));
};

const statusLabel = (status) => {
    switch (status) {
        case 'pending':
            return '⏳ รออนุมัติ';
        case 'approved':
            return '✅ อนุมัติแล้ว';
        case 'rejected':
            return '❌ ปฏิเสธแล้ว';
        default:
            return status;
    }
};
</script>

<template>
    <Head title="รายละเอียดคำขอ VIP" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-gray-800">รายละเอียดคำขอ VIP #{{ request.id }}</h2>
                    <p class="text-sm text-gray-500 mt-0.5">ตรวจสอบยอดเงินและอนุมัติสิทธิ์สมาชิก VIP</p>
                </div>
                <Link :href="route('backoffice.vip-requests.index')">
                    <SecondaryButton>← ย้อนกลับ</SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-4">
                <div v-if="page.props.flash?.success" class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 font-medium">
                    ✅ {{ page.props.flash.success }}
                </div>
                <div v-if="page.props.flash?.error" class="rounded-xl bg-red-50 border border-red-200 p-4 text-red-800 font-medium">
                    ⚠️ {{ page.props.flash.error }}
                </div>

                <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-6 space-y-5">
                    <!-- ข้อมูลสมาชิก -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">ข้อมูลสมาชิกที่ยื่นคำขอ</h3>
                            <div class="text-base font-bold text-gray-900">{{ request.customer?.name || '-' }}</div>
                            <div class="text-sm text-gray-500">{{ request.customer?.email || '-' }}</div>
                        </div>
                        <div class="text-left sm:text-right">
                            <span
                                v-if="request.customer_is_vip"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300"
                            >
                                👑 สมาชิกเป็น VIP อยู่แล้ว (ต่ออายุเพิ่ม)
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700"
                            >
                                สมาชิกทั่วไป (สมัคร VIP ใหม่)
                            </span>
                            <div v-if="request.customer_vip_ends_at" class="text-xs text-gray-500 mt-1">
                                หมดอายุเดิม: {{ new Date(request.customer_vip_ends_at).toLocaleString('th-TH', { dateStyle: 'medium', timeStyle: 'short' }) }}
                            </div>
                        </div>
                    </div>

                    <!-- ยอดโอน & แพ็กเกจ -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-amber-50/70 border border-amber-200 rounded-xl p-4">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-800 mb-1">ยอดโอนที่แจ้ง</h3>
                            <p class="text-2xl font-extrabold text-gray-900">
                                ฿{{ request.amount ? Number(request.amount).toLocaleString('th-TH', { minimumFractionDigits: 2 }) : '-' }}
                            </p>
                            <p class="text-xs font-semibold text-amber-900 mt-1">
                                ตรงกับ: {{ request.package_label || `${calcInitialDays()} วัน` }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-800 mb-1">วันที่ส่งคำขอ</h3>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ new Date(request.created_at).toLocaleString('th-TH', { dateStyle: 'long', timeStyle: 'medium' }) }}
                            </p>
                        </div>
                    </div>

                    <!-- ข้อมูลบัญชีที่โอน -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-100">
                            <h3 class="text-xs font-semibold text-gray-500 mb-1">ชื่อธนาคาร / ชื่อบัญชีผู้โอน</h3>
                            <p class="text-sm font-bold text-gray-900">
                                {{ request.bank_account_name || '-' }}
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-100">
                            <h3 class="text-xs font-semibold text-gray-500 mb-1">เลขที่บัญชีผู้โอน</h3>
                            <p class="text-sm font-bold text-gray-900">
                                {{ request.bank_account_number || '-' }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-semibold text-gray-500 mb-1">เวลาโอน / หมายเหตุจากลูกค้า</h3>
                        <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-100 text-sm text-gray-800 whitespace-pre-line">
                            {{ request.note || '-' }}
                        </div>
                    </div>

                    <div v-if="request.all_slips?.length || request.slip_url" class="border-t pt-4 mt-4">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <h3 class="text-sm font-bold text-gray-800">
                                หลักฐานสลิปการโอนเงิน ({{ request.all_slips?.length || 1 }} ใบ)
                            </h3>
                            <span
                                v-if="request.all_slips?.length > 1"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-purple-100 text-purple-900 border border-purple-300"
                            >
                                ลูกค้าโอนเพิ่มรวม {{ request.all_slips.length }} สลิป (ยอดรวมสุทธิ ฿{{ Number(request.amount || 0).toLocaleString('th-TH', { minimumFractionDigits: 2 }) }})
                            </span>
                        </div>

                        <div
                            v-if="request.all_slips?.length"
                            class="grid grid-cols-1 md:grid-cols-2 gap-4"
                        >
                            <div
                                v-for="slip in request.all_slips"
                                :key="slip.number"
                                class="rounded-2xl border-2 p-4 bg-gray-50 flex flex-col justify-between gap-3"
                                :class="slip.number > 1 ? 'border-purple-300 bg-purple-50/40' : 'border-gray-200'"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-extrabold px-2.5 py-1 rounded-lg bg-white border border-gray-200 text-gray-900">
                                        {{ slip.label }}
                                    </span>
                                    <span v-if="slip.amount !== null" class="text-sm font-black text-emerald-700">
                                        ฿{{ Number(slip.amount).toLocaleString('th-TH', { minimumFractionDigits: 2 }) }}
                                    </span>
                                </div>
                                <a :href="slip.url" target="_blank" rel="noopener" class="block text-center bg-white rounded-xl p-2 border border-gray-200">
                                    <img
                                        :src="slip.url"
                                        :alt="slip.label"
                                        class="max-w-full max-h-80 mx-auto rounded-lg object-contain"
                                    />
                                </a>
                                <div class="flex items-center justify-between text-[11px] text-gray-500">
                                    <span v-if="slip.created_at">
                                        ส่งเมื่อ: {{ new Date(slip.created_at).toLocaleString('th-TH', { dateStyle: 'short', timeStyle: 'short' }) }}
                                    </span>
                                    <a :href="slip.url" target="_blank" rel="noopener" class="text-indigo-600 font-bold hover:underline">
                                        คลิกเปิดรูปเต็ม ↗
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div v-else>
                            <a :href="request.slip_url" target="_blank" rel="noopener" class="inline-block">
                                <img
                                    :src="request.slip_url"
                                    alt="สลิปโอนเงิน"
                                    class="max-w-full max-h-96 rounded-lg border border-gray-200 shadow-sm object-contain"
                                />
                            </a>
                            <p class="mt-2 text-xs text-gray-500">คลิกที่รูปเพื่อเปิดขนาดเต็ม</p>
                        </div>
                    </div>

                    <!-- กรณีจัดการไปแล้ว -->
                    <div v-if="request.status !== 'pending'" class="border-t pt-4 mt-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-1">สถานะคำขอ</h3>
                        <p class="text-sm text-gray-800">
                            <strong>{{ statusLabel(request.status) }}</strong> โดย {{ request.approver?.name || '-' }} เมื่อ
                            {{ request.approved_at ? new Date(request.approved_at).toLocaleString('th-TH') : '-' }}
                        </p>
                    </div>

                    <!-- ฟอร์มอนุมัติ / ปฏิเสธ -->
                    <div v-if="request.status === 'pending'" class="border-t pt-5 mt-4 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <h3 class="text-base font-bold text-gray-900">อนุมัติสิทธิ์ VIP</h3>
                            <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full">
                                ✨ ระบบตั้งค่าเริ่มต้นให้ {{ calcInitialDays() }} วัน อัตโนมัติตามยอดโอน ({{ request.amount }}฿)
                            </span>
                        </div>

                        <form class="space-y-4" @submit.prevent="approve">
                            <div>
                                <InputLabel value="เลือกจำนวนวัน VIP ที่ต้องการอนุมัติ" />
                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                    <button
                                        type="button"
                                        @click="approveForm.days = 15"
                                        class="px-3.5 py-2 rounded-xl border text-xs font-bold transition"
                                        :class="Number(approveForm.days) === 15 ? 'bg-amber-500 text-white border-amber-600 shadow-sm' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                                    >
                                        15 วัน (รายงวด • 99฿)
                                    </button>
                                    <button
                                        type="button"
                                        @click="approveForm.days = 30"
                                        class="px-3.5 py-2 rounded-xl border text-xs font-bold transition"
                                        :class="Number(approveForm.days) === 30 ? 'bg-amber-500 text-white border-amber-600 shadow-sm' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                                    >
                                        30 วัน (1 เดือน • 179฿)
                                    </button>
                                    <button
                                        type="button"
                                        @click="approveForm.days = 90"
                                        class="px-3.5 py-2 rounded-xl border text-xs font-bold transition"
                                        :class="Number(approveForm.days) === 90 ? 'bg-amber-500 text-white border-amber-600 shadow-sm' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                                    >
                                        90 วัน (3 เดือน • 450฿)
                                    </button>
                                    <button
                                        type="button"
                                        @click="approveForm.days = 365"
                                        class="px-3.5 py-2 rounded-xl border text-xs font-bold transition"
                                        :class="Number(approveForm.days) === 365 ? 'bg-amber-500 text-white border-amber-600 shadow-sm' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                                    >
                                        365 วัน (1 ปี)
                                    </button>
                                </div>

                                <div class="flex items-center gap-2 mt-3">
                                    <TextInput
                                        v-model="approveForm.days"
                                        type="number"
                                        class="block w-32 font-bold text-center"
                                        min="1"
                                        max="365"
                                    />
                                    <span class="text-sm text-gray-600">วัน (สามารถแก้ไขตัวเลขเองได้ 1–365 วัน)</span>
                                </div>
                                <InputError class="mt-2" :message="approveForm.errors.days" />
                            </div>

                            <div class="flex flex-wrap items-center gap-3 pt-2">
                                <PrimaryButton type="submit" :disabled="approveForm.processing">
                                    ✅ ยืนยันอนุมัติ VIP ({{ approveForm.days }} วัน)
                                </PrimaryButton>
                                <SecondaryButton type="button" :disabled="rejectForm.processing" @click="reject">
                                    ❌ ปฏิเสธคำขอ
                                </SecondaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
