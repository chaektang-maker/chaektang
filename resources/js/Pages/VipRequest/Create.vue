<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    canLogin: { type: Boolean, default: false },
    canRegister: { type: Boolean, default: false },
    isLoggedIn: { type: Boolean, default: false },
    paymentConfig: { type: Object, default: () => ({}) },
    activeSubscription: { type: Object, default: null },
    recentRequests: { type: Array, default: () => [] },
});

const packages = props.paymentConfig?.packages || [
    { id: 'draw_15d', name: 'แพ็กเกจรายงวด', days: 15, price: 99, badge: 'เริ่มต้น', popular: false, description: 'ปลดล็อกเลขเด็ด VIP + เลขมาแรงอันดับ 1 + เลขฝัน 3 ตัวตรง นาน 15 วัน' },
    { id: 'monthly_30d', name: 'แพ็กเกจรายเดือน', days: 30, price: 179, badge: 'ขายดีที่สุด', popular: true, description: 'คุ้มค่า! ครอบคลุม 2 งวดเต็ม ปลดล็อกทุกฟีเจอร์ VIP นาน 30 วัน' },
    { id: 'quarterly_90d', name: 'แพ็กเกจ 3 เดือน', days: 90, price: 450, badge: 'ประหยัด 25%', popular: false, description: 'ครอบคลุม 6 งวดเต็ม เฉลี่ยงวดละ 75 บาท นาน 90 วัน' },
];

const defaultPkg = packages.find((p) => p.popular) || packages[0];
const selectedPackageId = ref(defaultPkg?.id || '');
const selectedPackage = computed(() => packages.find((p) => p.id === selectedPackageId.value) || defaultPkg);
const copied = ref(false);
const showSuccessModal = ref(false);
const lastSubmittedAmount = ref('');
const pendingActionMode = ref(''); // '' | 'append' | 'replace'

const pendingRequest = computed(() => {
    return (props.recentRequests || []).find((r) => r.status === 'pending') || null;
});

const form = useForm({
    slip: null,
    amount: defaultPkg ? String(defaultPkg.price) : '',
    bank_account_name: '',
    bank_account_number: '',
    note: defaultPkg ? `สมัคร ${defaultPkg.name} (${defaultPkg.days} วัน)` : '',
    submit_mode: 'new',
});

const selectPackage = (pkg) => {
    selectedPackageId.value = pkg.id;
    if (pendingActionMode.value === 'append' && pendingRequest.value) {
        const paid = Number(pendingRequest.value.amount || 0);
        const diff = Math.max(0, Number(pkg.price) - paid);
        form.amount = diff > 0 ? String(diff) : String(pkg.price);
        form.note = `โอนเพิ่มรอบที่ ${(pendingRequest.value?.slip_count || 1) + 1} เพื่อให้ครบ ${pkg.name} (${pkg.days} วัน)`;
    } else if (pendingActionMode.value === 'replace') {
        form.amount = String(pkg.price);
        form.note = `เปลี่ยนรูปสลิปใหม่ (${pkg.name} • ${pkg.days} วัน)`;
    } else {
        form.amount = String(pkg.price);
        form.note = `สมัคร ${pkg.name} (${pkg.days} วัน)`;
    }
};

const openAppendSlipMode = () => {
    pendingActionMode.value = 'append';
    form.submit_mode = 'append';
    form.clearErrors();
    const pkgPrice = Number(selectedPackage.value?.price || 179);
    const paid = Number(pendingRequest.value?.amount || 0);
    const diff = Math.max(0, pkgPrice - paid);
    form.amount = diff > 0 ? String(diff) : '';
    form.note = `โอนเพิ่มรอบที่ ${(pendingRequest.value?.slip_count || 1) + 1} เพื่อให้ครบแพ็กเกจ ${selectedPackage.value?.name || 'VIP'}`;
};

const openReplaceSlipMode = () => {
    pendingActionMode.value = 'replace';
    form.submit_mode = 'replace';
    form.clearErrors();
    form.amount = String(pendingRequest.value?.amount || selectedPackage.value?.price || 179);
    form.note = `เปลี่ยนรูปสลิปใหม่ (${selectedPackage.value?.name || 'VIP'})`;
};

const cancelPendingAction = () => {
    pendingActionMode.value = '';
    form.submit_mode = 'new';
    form.clearErrors();
};

/**
 * สร้างรหัสมาตรฐาน EMVCo สำหรับ Thai PromptPay QR Code พร้อมคำนวณ CRC16-CCITT
 */
const formatTlv = (id, value) => {
    const len = String(value.length).padStart(2, '0');
    return `${id}${len}${value}`;
};

const crc16Ccitt = (str) => {
    let crc = 0xffff;
    for (let i = 0; i < str.length; i++) {
        crc ^= str.charCodeAt(i) << 8;
        for (let j = 0; j < 8; j++) {
            if ((crc & 0x8000) !== 0) {
                crc = ((crc << 1) ^ 0x1021) & 0xffff;
            } else {
                crc = (crc << 1) & 0xffff;
            }
        }
    }
    return crc.toString(16).toUpperCase().padStart(4, '0');
};

const buildPromptPayPayload = (rawTarget, rawAmount) => {
    const target = String(rawTarget || '').replace(/[^0-9]/g, '');
    if (!target) return '';

    let targetTag = '';
    if (target.length >= 15) {
        targetTag = formatTlv('03', target);
    } else if (target.length >= 13) {
        targetTag = formatTlv('02', target);
    } else {
        const phone = target.replace(/^0/, '');
        targetTag = formatTlv('01', `0066${phone}`);
    }

    const merchantInfo = formatTlv('00', 'A000000677010111') + targetTag;
    const numAmount = parseFloat(rawAmount);
    const hasValidAmount = !isNaN(numAmount) && numAmount > 0;

    const parts = [
        formatTlv('00', '01'),
        formatTlv('01', hasValidAmount ? '12' : '11'),
        formatTlv('29', merchantInfo),
        formatTlv('53', '764'),
        ...(hasValidAmount ? [formatTlv('54', numAmount.toFixed(2))] : []),
        formatTlv('58', 'TH'),
    ];

    const dataForCrc = parts.join('') + '6304';
    return dataForCrc + crc16Ccitt(dataForCrc);
};

const promptPayId = computed(() => {
    return String(props.paymentConfig?.promptpay_id || '').trim();
});

const selectedAmountNumber = computed(() => {
    const n = parseFloat(form.amount);
    return !isNaN(n) && n > 0 ? n : (defaultPkg?.price || 179);
});

const promptPayQrUrl = computed(() => {
    if (!promptPayId.value) return '';
    const payload = buildPromptPayPayload(promptPayId.value, selectedAmountNumber.value);
    if (!payload) return '';
    return `https://api.qrserver.com/v1/create-qr-code/?size=280x280&margin=10&data=${encodeURIComponent(payload)}`;
});

const copyAccountNumber = async () => {
    const acc = props.paymentConfig?.account_number || '';
    if (!acc) return;
    try {
        await navigator.clipboard.writeText(acc);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (e) {
        // ignore clipboard error
    }
};

const formatThaiDate = (dateStr) => {
    if (!dateStr) return '-';
    try {
        return new Date(dateStr).toLocaleDateString('th-TH', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch (e) {
        return dateStr;
    }
};

const submit = () => {
    if (pendingRequest.value && !pendingActionMode.value) {
        return;
    }
    if (!pendingRequest.value) {
        form.submit_mode = 'new';
    }
    const submittedAmt = form.amount;
    form.post(route('vip-request.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            lastSubmittedAmount.value = submittedAmt;
            showSuccessModal.value = true;
            pendingActionMode.value = '';
            form.reset('slip', 'bank_account_name', 'bank_account_number');
        },
    });
};
</script>

<template>
    <Head title="สมัครสมาชิก VIP - แจกตัง" />
    <PublicLayout :can-login="canLogin" :can-register="canRegister">
        <section class="py-10 bg-gray-50 min-h-screen">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <!-- แบนเนอร์เด่นด้านบนสุด เมื่อส่งสลิปสำเร็จ หรือมีรายการรอตรวจสอบ -->
                <div
                    v-if="$page.props.flash?.success || form.recentlySuccessful || pendingRequest"
                    class="rounded-2xl bg-gradient-to-r from-emerald-600 to-green-600 p-5 sm:p-6 text-white shadow-xl border-2 border-emerald-300 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
                >
                    <div class="flex items-start sm:items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-white text-emerald-600 flex items-center justify-center shrink-0 shadow-md">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-yellow-300 text-gray-950 text-xs font-extrabold mb-1">
                                <span>สถานะ: ได้รับสลิปแล้ว รอแอดมินอนุมัติ</span>
                            </div>
                            <h2 class="text-lg sm:text-xl font-extrabold">
                                {{ $page.props.flash?.success || 'เราได้รับหลักฐานการโอนเงินของคุณเรียบร้อยแล้ว!' }}
                            </h2>
                            <p class="text-xs sm:text-sm text-emerald-50 mt-0.5">
                                ระบบได้ส่งสลิปแจ้งเตือนไปยังทีมงานแอดมินแล้ว
                                <span v-if="pendingRequest">
                                    (ยอดแจ้งโอน {{ pendingRequest.amount ? `${pendingRequest.amount} บาท` : 'ตามสลิป' }} • ส่งเมื่อ {{ formatThaiDate(pendingRequest.created_at) }})
                                </span>
                                คุณไม่ต้องส่งสลิปซ้ำนะครับ
                            </p>
                        </div>
                    </div>
                    <Link
                        :href="route('lucky-numbers.index')"
                        class="shrink-0 px-4 py-2.5 rounded-xl bg-white text-emerald-800 hover:bg-emerald-50 font-extrabold text-xs sm:text-sm shadow transition"
                    >
                        ไปหน้าเลขเด็ด →
                    </Link>
                </div>

                <!-- Header & VIP Benefits -->
                <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-2xl shadow-xl p-6 md:p-8 text-white">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-yellow-400 text-gray-900 text-xs font-bold mb-3">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z" />
                                </svg>
                                <span>สิทธิพิเศษระดับ VIP</span>
                            </span>
                            <h1 class="text-2xl md:text-3xl font-bold mb-2">สมัครสมาชิก VIP แจกตัง</h1>
                            <p class="text-red-100 text-sm md:text-base">
                                ปลดล็อกเลขเด็ดสำนักแม่นยำสูงสุด เลขมาแรงอันดับ 1 และชุดเลขฝัน 3 ตัวตรงพร้อมเทียบเลขชนสำนักดัง
                            </p>
                        </div>
                        <div v-if="activeSubscription" class="bg-white/15 backdrop-blur rounded-xl p-4 border border-white/30 text-sm shrink-0">
                            <div class="font-bold text-yellow-300">สถานะปัจจุบัน: สมาชิก VIP</div>
                            <div class="text-white/90 mt-1">หมดอายุ: {{ formatThaiDate(activeSubscription.ends_at) }}</div>
                            <div class="text-xs text-red-100 mt-1">(ส่งสลิปเพื่อต่ออายุเพิ่มวันได้ทันที)</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-6 pt-6 border-t border-red-500/60 text-sm">
                        <div class="bg-white/10 rounded-xl p-3.5">
                            <div class="font-bold text-yellow-300 mb-1 flex items-center gap-1.5">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                </svg>
                                <span>ปลดล็อกเลขสำนัก VIP</span>
                            </div>
                            <div class="text-red-100 text-xs">เปิดดูเลข 2 ตัว / 3 ตัว ของสำนักแม่นยำสูงสุดและสำนักยอดนิยมครบทุกสำนัก</div>
                        </div>
                        <div class="bg-white/10 rounded-xl p-3.5">
                            <div class="font-bold text-yellow-300 mb-1 flex items-center gap-1.5">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                                <span>เลขมาแรงอันดับ 1</span>
                            </div>
                            <div class="text-red-100 text-xs">เปิดดูเลขเด็ด 2 ตัวที่ทุกสำนักให้ตรงกันมากที่สุดอันดับ 1 ประจำงวด</div>
                        </div>
                        <div class="bg-white/10 rounded-xl p-3.5">
                            <div class="font-bold text-yellow-300 mb-1 flex items-center gap-1.5">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span>เลขฝัน 3 ตัว & เทียบสำนัก</span>
                            </div>
                            <div class="text-red-100 text-xs">ปลดล็อกชุดเลขมงคล 3 ตัวตรงในตำราทำนายฝัน และดูสำนักดังที่ให้เลขตรงกับฝัน</div>
                        </div>
                        <div class="bg-white/10 rounded-xl p-3.5">
                            <div class="font-bold text-yellow-300 mb-1 flex items-center gap-1.5">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <span>เทียบสถิติ 10 งวดติด</span>
                            </div>
                            <div class="text-red-100 text-xs">คลิกดูเลขงวดปัจจุบันของสำนักที่เข้าเป้าต่อเนื่องจากตารางความแม่นได้ทันที</div>
                        </div>
                    </div>
                </div>

                <!-- Step 1: เลือกแพ็กเกจ -->
                <div>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">1. เลือกแพ็กเกจสมาชิก VIP</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <button
                            v-for="pkg in packages"
                            :key="pkg.id"
                            type="button"
                            @click="selectPackage(pkg)"
                            :class="[
                                'text-left rounded-2xl p-5 border-2 transition-all relative',
                                selectedPackageId === pkg.id
                                    ? 'bg-red-50/70 border-red-600 shadow-lg ring-2 ring-red-500/30'
                                    : 'bg-white border-gray-200 hover:border-red-300 shadow-sm'
                            ]"
                        >
                            <span
                                v-if="pkg.badge"
                                :class="[
                                    'inline-block text-xs font-bold px-2.5 py-0.5 rounded-full mb-2',
                                    pkg.popular ? 'bg-red-600 text-white' : 'bg-amber-100 text-amber-800'
                                ]"
                            >
                                {{ pkg.badge }}
                            </span>
                            <div class="text-base font-bold text-gray-900">{{ pkg.name }}</div>
                            <div class="mt-2 flex items-baseline gap-1">
                                <span class="text-3xl font-extrabold text-red-600">{{ pkg.price }}</span>
                                <span class="text-sm text-gray-600">บาท / {{ pkg.days }} วัน</span>
                            </div>
                            <p class="mt-2 text-xs text-gray-600 leading-relaxed">{{ pkg.description }}</p>
                        </button>
                    </div>
                </div>

                <!-- Step 2: กรณีที่ยังไม่ได้เข้าสู่ระบบ (แสดงการ์ดชวนล็อกอิน/สมัครสมาชิกก่อนโอนเงิน) -->
                <div
                    v-if="!isLoggedIn && !$page.props.auth?.customer"
                    class="bg-white rounded-2xl shadow-xl border-2 border-amber-300 overflow-hidden"
                >
                    <div class="bg-gradient-to-r from-amber-50 via-yellow-50 to-orange-50 px-6 py-5 border-b border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="text-xs font-extrabold text-amber-800 uppercase tracking-wider">
                                ขั้นตอนที่ 2 • ยืนยันบัญชีผู้รับสิทธิ์ VIP
                            </div>
                            <div class="text-lg font-extrabold text-gray-900 mt-0.5">
                                คุณกำลังเลือก: <span class="text-red-600">{{ selectedPackage?.name }} ({{ selectedPackage?.days }} วัน)</span>
                            </div>
                        </div>
                        <div class="bg-white px-4 py-2 rounded-xl border border-amber-200 shadow-2xs text-right shrink-0">
                            <div class="text-[11px] text-gray-500">ยอดชำระ</div>
                            <div class="text-2xl font-black text-red-600">฿{{ selectedAmountNumber.toFixed(2) }}</div>
                        </div>
                    </div>

                    <div class="p-6 md:p-8 text-center max-w-2xl mx-auto space-y-5">
                        <div class="w-16 h-16 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto shadow-inner">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-xl font-extrabold text-gray-900">
                                เข้าสู่ระบบหรือสมัครสมาชิกฟรี เพื่อรับ QR Code ชำระเงิน
                            </h3>
                            <p class="text-sm text-gray-600 leading-relaxed">
                                เพื่อป้องกันการโอนเงินตกหล่น ระบบจะผูกสิทธิ์ <strong>สมาชิก VIP</strong> เข้ากับบัญชีของคุณโดยตรง<br class="hidden sm:inline" />
                                หากยังไม่มีบัญชี สามารถสมัครสมาชิกใหม่ได้ฟรี แล้วระบบจะพากลับมาหน้านี้อัตโนมัติครับ
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
                            <Link
                                :href="route('login')"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-extrabold text-sm shadow-lg transition-all"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                <span>เข้าสู่ระบบเพื่อสมัคร VIP</span>
                            </Link>
                            <Link
                                :href="route('register')"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl bg-gradient-to-r from-amber-400 to-yellow-500 hover:from-amber-500 hover:to-yellow-600 text-gray-950 font-extrabold text-sm shadow-md transition-all"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                <span>สมัครสมาชิกใหม่ฟรี</span>
                            </Link>
                        </div>

                        <div class="pt-4 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-gray-500">
                            <div class="flex items-center justify-center gap-1.5">
                                <span class="text-green-600 font-bold">✓</span> สมัครง่าย ไม่เสียค่าแรกเข้า
                            </div>
                            <div class="flex items-center justify-center gap-1.5">
                                <span class="text-green-600 font-bold">✓</span> รองรับสแกน QR ทุกธนาคาร
                            </div>
                            <div class="flex items-center justify-center gap-1.5">
                                <span class="text-green-600 font-bold">✓</span> เปิดสิทธิ์ไว แจ้งเตือนแอดมินทันที
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: ช่องทางโอนเงิน & ฟอร์มแจ้งสลิป (แสดงเฉพาะเมื่อล็อกอินแล้ว) -->
                <div v-else class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                    <!-- ข้อมูลบัญชีธนาคาร & QR Code พร้อมเพย์อัตโนมัติ -->
                    <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg border border-gray-100 p-6 space-y-4 h-fit">
                        <h2 class="text-lg font-bold text-gray-900">2. ช่องทางชำระเงิน</h2>

                        <!-- กล่องสแกน QR Code พร้อมเพย์อัตโนมัติตามยอดแพ็กเกจ -->
                        <div
                            v-if="promptPayQrUrl"
                            class="rounded-2xl overflow-hidden border-2 border-[#113566] shadow-md bg-white text-center"
                        >
                            <div class="bg-[#113566] text-white py-2.5 px-4 flex items-center justify-between">
                                <span class="font-extrabold text-xs tracking-wide">THAI QR PAYMENT</span>
                                <span class="bg-white text-[#113566] text-[11px] font-extrabold px-2 py-0.5 rounded">
                                    PromptPay พร้อมเพย์
                                </span>
                            </div>
                            <div class="p-4 space-y-2">
                                <div class="text-xs text-gray-500">สแกนด้วยแอปธนาคารได้ทุกธนาคาร</div>
                                <div class="inline-block p-2 bg-white rounded-xl border border-gray-200 shadow-2xs">
                                    <img
                                        :src="promptPayQrUrl"
                                        :alt="`PromptPay QR ${selectedAmountNumber} บาท`"
                                        class="w-48 h-48 mx-auto object-contain"
                                    />
                                </div>
                                <div class="pt-1">
                                    <div class="text-xs text-gray-500">ยอดชำระตามแพ็กเกจที่เลือก</div>
                                    <div class="text-2xl font-extrabold text-red-600">
                                        ฿{{ selectedAmountNumber.toFixed(2) }}
                                    </div>
                                    <div class="text-xs font-semibold text-gray-800 mt-1">
                                        ชื่อบัญชี: {{ paymentConfig?.account_name || 'อธิวัฒน์ อ้นโตน' }}
                                    </div>
                                    <div class="text-[11px] text-gray-500">
                                        พร้อมเพย์: {{ promptPayId }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 space-y-3">
                            <div class="text-xs font-bold text-amber-900 uppercase tracking-wider">
                                หรือโอนผ่านเลขบัญชีธนาคาร
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">ธนาคาร</div>
                                <div class="font-bold text-gray-900">{{ paymentConfig?.bank_name || 'ธนาคารไทยพาณิชย์ (SCB)' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">เลขที่บัญชี</div>
                                <div class="flex items-center justify-between gap-2 mt-0.5">
                                    <span class="text-lg font-extrabold text-red-600 tracking-wider">
                                        {{ paymentConfig?.account_number || 'xxx-x-xxxxx-x' }}
                                    </span>
                                    <button
                                        type="button"
                                        @click="copyAccountNumber"
                                        class="px-2.5 py-1 text-xs font-medium rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50"
                                    >
                                        {{ copied ? 'คัดลอกแล้ว ✓' : 'คัดลอก' }}
                                    </button>
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">ชื่อบัญชี</div>
                                <div class="font-semibold text-gray-800">{{ paymentConfig?.account_name || 'แจกตังค์ (Chaektang Official)' }}</div>
                            </div>
                        </div>

                        <div class="text-xs text-gray-500 space-y-1.5 leading-relaxed">
                            <p>• โอนเงินตามยอดแพ็กเกจที่เลือก แล้วแนบสลิปในฟอร์มด้านขวา</p>
                            <p>• ทีมงานจะตรวจสอบสลิปและเปิดสิทธิ์ VIP ตามจำนวนวันของแพ็กเกจโดยเร็วที่สุด</p>
                        </div>

                        <!-- ประวัติคำขอ VIP ล่าสุดของลูกค้า -->
                        <div v-if="recentRequests.length > 0" class="pt-4 border-t border-gray-200">
                            <h3 class="text-sm font-bold text-gray-900 mb-2">ประวัติการแจ้งโอนล่าสุด</h3>
                            <div class="space-y-2">
                                <div
                                    v-for="req in recentRequests"
                                    :key="req.id"
                                    class="flex items-center justify-between text-xs p-2.5 rounded-lg bg-gray-50 border border-gray-200"
                                >
                                    <div>
                                        <div class="font-semibold text-gray-800">{{ req.amount ? `${req.amount} บาท` : 'ไม่ระบุยอด' }}</div>
                                        <div class="text-gray-500">{{ formatThaiDate(req.created_at) }}</div>
                                    </div>
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded-full font-bold',
                                            req.status === 'approved'
                                                ? 'bg-green-100 text-green-800'
                                                : req.status === 'rejected'
                                                ? 'bg-red-100 text-red-800'
                                                : 'bg-yellow-100 text-yellow-800'
                                        ]"
                                    >
                                        {{ req.status === 'approved' ? 'อนุมัติแล้ว' : req.status === 'rejected' ? 'ปฏิเสธ' : 'รอตรวจสอบ' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ฟอร์มแนบสลิป หรือ การ์ดสถานะรอตรวจสอบ (ป้องกันการส่งซ้ำ + รองรับการแนบสลิปโอนเพิ่มรอบ 2) -->
                    <div class="lg:col-span-3 bg-white rounded-2xl shadow-lg border border-gray-100 p-6 md:p-8">
                        <h2 class="text-lg font-bold text-gray-900 mb-1">3. แจ้งหลักฐานการโอนเงิน</h2>
                        <p class="text-gray-600 text-sm mb-6">
                            แนบสลิปการโอนเงินและตรวจสอบยอดเงินด้านล่าง
                        </p>

                        <!-- กรณีมีคำขอที่กำลังรอแอดมินตรวจสอบอยู่แล้ว และยังไม่ได้กดปุ่ม "โอนเพิ่ม" หรือ "เปลี่ยนสลิป" -->
                        <div
                            v-if="pendingRequest && !pendingActionMode"
                            class="rounded-2xl bg-gradient-to-br from-emerald-50 via-green-50 to-teal-50 border-2 border-emerald-400 p-6 space-y-5"
                        >
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-amber-200 text-amber-900 text-xs font-extrabold">
                                        กำลังรอแอดมินตรวจสอบและเปิดสิทธิ์ VIP
                                    </span>
                                    <h3 class="text-lg font-extrabold text-gray-900">
                                        ระบบได้รับสลิปการโอนเงินของคุณแล้ว
                                    </h3>
                                    <p class="text-xs sm:text-sm text-gray-700 leading-relaxed">
                                        เพื่อป้องกันรายการซ้ำซ้อน ระบบได้ล็อกการส่งซ้ำไว้ให้อัตโนมัติ ทีมงานกำลังตรวจสอบสลิปและเปิดสิทธิ์ VIP ให้คุณครับ
                                    </p>
                                </div>
                            </div>

                            <!-- สรุปยอดที่แจ้งโอนไว้ -->
                            <div class="rounded-xl bg-white p-4 border border-emerald-200 grid grid-cols-2 sm:grid-cols-3 gap-3 text-center">
                                <div>
                                    <div class="text-[11px] text-gray-500">ยอดโอนรวมที่แจ้ง</div>
                                    <div class="text-lg font-black text-emerald-700">
                                        ฿{{ Number(pendingRequest.amount || 0).toFixed(2) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-[11px] text-gray-500">จำนวนสลิปที่แนบ</div>
                                    <div class="text-lg font-black text-gray-900">
                                        {{ pendingRequest.slip_count || 1 }} ใบ
                                    </div>
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <div class="text-[11px] text-gray-500">เวลาที่ส่งล่าสุด</div>
                                    <div class="text-xs font-bold text-gray-800 mt-1">
                                        {{ formatThaiDate(pendingRequest.created_at) }}
                                    </div>
                                </div>
                            </div>

                            <!-- ทางเลือกสำหรับลูกค้า: โอนเพิ่มให้ครบยอด หรือ เปลี่ยนรูปสลิปที่ส่งผิด -->
                            <div class="pt-2 border-t border-emerald-200/80 space-y-2.5">
                                <div class="text-xs font-bold text-gray-700">
                                    ต้องการแก้ไขหรือโอนเงินเพิ่มใช่หรือไม่? (ระบบจะรวมไว้ในคำขอเดิม ไม่สร้างรายการซ้ำ)
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    <button
                                        type="button"
                                        @click="openAppendSlipMode"
                                        class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-gray-950 font-extrabold text-xs sm:text-sm shadow transition"
                                    >
                                        <span>+ แนบสลิปโอนเพิ่ม (กรณีโอนไม่ครบยอด)</span>
                                    </button>
                                    <button
                                        type="button"
                                        @click="openReplaceSlipMode"
                                        class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-white hover:bg-gray-50 text-gray-800 border border-gray-300 font-bold text-xs sm:text-sm transition"
                                    >
                                        <span>เปลี่ยนรูปสลิปใหม่ (กรณีแนบรูปผิด)</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- ฟอร์มแนบสลิป (แสดงเมื่อยังไม่มีคำขอรอตรวจ หรือเมื่อลูกค้ากดปุ่ม "แนบสลิปโอนเพิ่ม" / "เปลี่ยนรูปสลิป") -->
                        <template v-else>
                            <!-- แถบแจ้งโหมดพิเศษ (โอนเพิ่มรอบ 2 หรือ เปลี่ยนสลิปเดิม) -->
                            <div
                                v-if="pendingRequest && pendingActionMode === 'append'"
                                class="mb-6 p-4 rounded-xl bg-amber-50 border-2 border-amber-400 text-amber-950 space-y-1.5"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-extrabold text-sm text-amber-900">
                                        โหมดแนบสลิปโอนเพิ่ม (ใบที่ {{ (pendingRequest.slip_count || 1) + 1 }})
                                    </span>
                                    <button
                                        type="button"
                                        @click="cancelPendingAction"
                                        class="text-xs font-bold text-gray-600 hover:text-red-600 underline"
                                    >
                                        ยกเลิก
                                    </button>
                                </div>
                                <p class="text-xs text-amber-800 leading-relaxed">
                                    ยอดโอนเดิมของคุณคือ <strong>฿{{ Number(pendingRequest.amount || 0).toFixed(2) }}</strong> •
                                    เมื่อแนบสลิปใบนี้ ระบบจะนำยอดที่โอนเพิ่มไป<strong>บวกรวมกับยอดเดิมในคำขอเดียวกันให้อัตโนมัติ</strong> (QR พร้อมเพย์ด้านซ้ายปรับตามยอดส่วนต่างให้แล้วครับ)
                                </p>
                            </div>

                            <div
                                v-else-if="pendingRequest && pendingActionMode === 'replace'"
                                class="mb-6 p-4 rounded-xl bg-blue-50 border-2 border-blue-300 text-blue-950 space-y-1.5"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-extrabold text-sm text-blue-900">
                                        โหมดเปลี่ยนรูปสลิปใหม่แทนใบเดิม
                                    </span>
                                    <button
                                        type="button"
                                        @click="cancelPendingAction"
                                        class="text-xs font-bold text-gray-600 hover:text-red-600 underline"
                                    >
                                        ยกเลิก
                                    </button>
                                </div>
                                <p class="text-xs text-blue-800 leading-relaxed">
                                    รูปสลิปใหม่จะถูกบันทึกทับรายการเดิมของคุณที่กำลังรอตรวจ โดยไม่สร้างคำขอซ้ำซ้อนครับ
                                </p>
                            </div>

                            <div v-if="form.hasErrors" class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                                กรุณาตรวจสอบข้อมูลอีกครั้ง
                            </div>

                            <form @submit.prevent="submit" class="space-y-5">
                                <div>
                                    <InputLabel
                                        for="slip"
                                        :value="pendingActionMode === 'append' ? `รูปสลิปที่โอนเพิ่ม (ใบที่ ${(pendingRequest?.slip_count || 1) + 1}) *` : 'สลิปการโอนเงิน *'"
                                        class="font-medium text-gray-700"
                                    />
                                    <input
                                        id="slip"
                                        type="file"
                                        accept="image/*"
                                        class="mt-2 block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-red-50 file:text-red-700 file:font-medium hover:file:bg-red-100"
                                        @change="form.slip = $event.target.files[0]"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">รองรับไฟล์รูป (JPG, PNG ฯลฯ ไม่เกิน 5 MB)</p>
                                    <InputError class="mt-1" :message="form.errors.slip" />
                                </div>

                                <div>
                                    <InputLabel
                                        for="amount"
                                        :value="pendingActionMode === 'append' ? 'ยอดที่โอนเพิ่มในสลิปใบนี้ (บาท)' : 'ยอดที่โอน (บาท)'"
                                        class="font-medium text-gray-700"
                                    />
                                    <TextInput
                                        id="amount"
                                        v-model="form.amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="เช่น 179"
                                        class="mt-2 block w-full"
                                    />
                                    <p v-if="pendingActionMode === 'append' && pendingRequest" class="mt-1 text-xs font-bold text-emerald-700">
                                        ยอดเดิม ฿{{ Number(pendingRequest.amount || 0).toFixed(2) }} + โอนเพิ่ม ฿{{ Number(form.amount || 0).toFixed(2) }} = ยอดโอนรวมใหม่ ฿{{ (Number(pendingRequest.amount || 0) + Number(form.amount || 0)).toFixed(2) }}
                                    </p>
                                    <InputError class="mt-1" :message="form.errors.amount" />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="bank_account_name" value="ชื่อบัญชีผู้โอน" class="font-medium text-gray-700" />
                                        <TextInput
                                            id="bank_account_name"
                                            v-model="form.bank_account_name"
                                            type="text"
                                            placeholder="ชื่อบัญชีของคุณ"
                                            class="mt-2 block w-full"
                                        />
                                        <InputError class="mt-1" :message="form.errors.bank_account_name" />
                                    </div>
                                    <div>
                                        <InputLabel for="bank_account_number" value="เลขท้ายบัญชีผู้โอน (4 ตัวท้าย)" class="font-medium text-gray-700" />
                                        <TextInput
                                            id="bank_account_number"
                                            v-model="form.bank_account_number"
                                            type="text"
                                            placeholder="เช่น 1234"
                                            class="mt-2 block w-full"
                                        />
                                        <InputError class="mt-1" :message="form.errors.bank_account_number" />
                                    </div>
                                </div>

                                <div>
                                    <InputLabel for="note" value="แพ็กเกจที่เลือก / หมายเหตุ" class="font-medium text-gray-700" />
                                    <textarea
                                        id="note"
                                        v-model="form.note"
                                        rows="3"
                                        class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                                        placeholder="ระบุแพ็กเกจ เวลาที่โอน หรือข้อมูลเพิ่มเติม"
                                    />
                                    <InputError class="mt-1" :message="form.errors.note" />
                                </div>

                                <!-- ข้อตกลงการใช้บริการและคุ้มครองข้อมูลส่วนบุคคล (Disclaimer & PDPA) -->
                                <div class="rounded-xl bg-amber-50/80 border border-amber-200 p-4 text-xs text-gray-700 space-y-1.5 leading-relaxed">
                                    <div class="font-bold text-gray-900">เงื่อนไขการให้บริการและการคุ้มครองข้อมูลส่วนบุคคล (PDPA)</div>
                                    <p>
                                        • <strong>ขอบเขตบริการ:</strong> ค่าสมาชิก VIP เป็นค่าบริการในการเข้าถึงระบบฐานข้อมูลสถิติและการจัดอันดับเชิงลึกของเว็บไซต์เท่านั้น ข้อมูลตัวเลขที่แสดงเป็นเพียงการวิเคราะห์เชิงสถิติและความเชื่อส่วนบุคคล ไม่มีการการันตีผลรางวัลสลากกินแบ่งรัฐบาล
                                    </p>
                                    <p>
                                        • <strong>การคุ้มครองข้อมูล (PDPA):</strong> ข้อมูลการโอนเงินและรูปภาพสลิปของท่านถูกจัดเก็บอย่างปลอดภัย เพื่อใช้ตรวจสอบยืนยันสิทธิ์สมาชิก VIP เท่านั้น ไม่มีการนำไปเผยแพร่หรือใช้ในวัตถุประสงค์อื่น
                                    </p>
                                </div>

                                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                                    <PrimaryButton
                                        type="submit"
                                        class="w-full sm:w-auto bg-red-600 hover:bg-red-700"
                                        :disabled="form.processing"
                                    >
                                        <span v-if="!form.processing">
                                            {{ pendingActionMode === 'append' ? 'บันทึกสลิปโอนเพิ่ม' : pendingActionMode === 'replace' ? 'อัปเดตรูปสลิปใหม่' : 'ส่งคำขอเปิดสิทธิ์ VIP' }}
                                        </span>
                                        <span v-else>กำลังส่ง...</span>
                                    </PrimaryButton>
                                    <button
                                        v-if="pendingActionMode"
                                        type="button"
                                        @click="cancelPendingAction"
                                        class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        ยกเลิก
                                    </button>
                                    <Link
                                        v-else
                                        :href="route('home')"
                                        class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        กลับหน้าแรก
                                    </Link>
                                </div>
                            </form>
                        </template>
                    </div>
                </div>
            </div>
        </section>

        <!-- Popup Modal แจ้งเตือนเมื่อส่งสลิปสำเร็จ (เด้งกลางหน้าจอชัดเจน 100%) -->
        <div
            v-if="showSuccessModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-xs"
        >
            <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border-2 border-emerald-400 overflow-hidden text-center animate-in fade-in zoom-in duration-200">
                <div class="bg-gradient-to-r from-emerald-600 to-green-600 px-6 py-7 text-white">
                    <div class="w-20 h-20 rounded-full bg-white text-emerald-600 flex items-center justify-center mx-auto shadow-lg mb-4">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span class="inline-block px-3 py-0.5 rounded-full bg-yellow-300 text-gray-950 text-xs font-extrabold mb-2">
                        ดำเนินการเสร็จสิ้น
                    </span>
                    <h3 class="text-2xl font-extrabold">
                        ส่งสลิปสมัคร VIP สำเร็จ!
                    </h3>
                </div>

                <div class="p-6 space-y-5">
                    <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-sm space-y-1.5">
                        <div class="flex items-center justify-between text-gray-600">
                            <span>รายการที่เลือก:</span>
                            <strong class="text-gray-900">{{ selectedPackage?.name }} ({{ selectedPackage?.days }} วัน)</strong>
                        </div>
                        <div v-if="lastSubmittedAmount" class="flex items-center justify-between text-gray-600">
                            <span>ยอดเงินที่แจ้งโอน:</span>
                            <strong class="text-emerald-700 text-base">฿{{ Number(lastSubmittedAmount).toFixed(2) }}</strong>
                        </div>
                        <div class="flex items-center justify-between text-gray-600 pt-1 border-t border-emerald-200/70">
                            <span>สถานะปัจจุบัน:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-xs font-extrabold">
                                รอแอดมินตรวจสอบอนุมัติ
                            </span>
                        </div>
                    </div>

                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                        ระบบได้ส่งรูปสลิปแจ้งเตือนไปยังแอดมินเรียบร้อยแล้ว เมื่อแอดมินกดอนุมัติ ป้ายสถานะด้านบนเมนูของคุณจะเปลี่ยนเป็น <strong>VIP</strong> ทันทีครับ
                    </p>

                    <div class="flex flex-col sm:flex-row gap-2.5 pt-1">
                        <button
                            type="button"
                            @click="showSuccessModal = false"
                            class="flex-1 px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm shadow-lg transition"
                        >
                            รับทราบ
                        </button>
                        <Link
                            :href="route('lucky-numbers.index')"
                            class="flex-1 px-5 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold text-sm transition"
                        >
                            ไปหน้าเลขเด็ด →
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
