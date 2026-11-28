<script setup>
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: Number,
        default: 500,
    },
    title: {
        type: String,
        default: 'ระบบขัดข้องชั่วคราว',
    },
    message: {
        type: String,
        default: 'ขออภัย เกิดข้อผิดพลาดในการประมวลผล ระบบได้บันทึกรายงานปัญหานี้เรียบร้อยแล้ว กรุณาลองใหม่อีกครั้ง',
    },
    isBackoffice: {
        type: Boolean,
        default: false,
    },
});

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(props.isBackoffice ? '/backoffice/dashboard' : '/');
    }
};

const reloadPage = () => {
    window.location.reload();
};
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100 flex items-center justify-center px-4 py-12">
        <div class="max-w-lg w-full bg-white rounded-2xl shadow-xl border border-slate-200 p-8 text-center">
            <!-- Icon -->
            <div class="mx-auto w-20 h-20 rounded-full bg-red-50 border border-red-100 flex items-center justify-center mb-6">
                <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <!-- Status Badge -->
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 mb-3">
                <span>รหัสสถานะ {{ status }}</span>
            </div>

            <!-- Title & Friendly Message -->
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-3">
                {{ title }}
            </h1>
            <p class="text-slate-600 text-base leading-relaxed mb-8">
                {{ message }}
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button
                    type="button"
                    @click="goBack"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    ย้อนกลับหน้าเดิม
                </button>

                <button
                    type="button"
                    @click="reloadPage"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    ลองใหม่อีกครั้ง
                </button>

                <Link
                    :href="isBackoffice ? '/backoffice/dashboard' : '/'"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700 transition shadow-sm"
                >
                    {{ isBackoffice ? 'กลับหน้าแดชบอร์ด' : 'กลับหน้าแรก' }}
                </Link>
            </div>
        </div>
    </div>
</template>
