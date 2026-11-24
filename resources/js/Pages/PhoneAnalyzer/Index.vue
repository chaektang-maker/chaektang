<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ShareButtons from '@/Components/ShareButtons.vue';

const props = defineProps({
    analysisResult: {
        type: Object,
        default: null,
    },
    analysisError: {
        type: String,
        default: null,
    },
    initialPhone: {
        type: String,
        default: '',
    },
    affiliateProducts: {
        type: Array,
        default: () => [],
    },
});

const phoneInput = ref(props.initialPhone || '');
const localError = ref(props.analysisError || '');
const isSubmitting = ref(false);

const onPhoneInput = (e) => {
    const digitsOnly = String(e.target.value || '').replace(/\D/g, '').slice(0, 10);
    phoneInput.value = digitsOnly;
    localError.value = '';
};

const submitAnalyze = () => {
    const clean = String(phoneInput.value || '').replace(/\D/g, '');
    if (!/^0[689]\d{8}$/.test(clean)) {
        localError.value = 'กรุณากรอกเบอร์โทรศัพท์มือถือ 10 หลักให้ถูกต้อง (ขึ้นต้นด้วย 06, 08 หรือ 09)';
        return;
    }

    isSubmitting.value = true;
    router.get(
        route('phone-analyzer.index'),
        { phone: clean },
        {
            preserveScroll: false,
            onFinish: () => {
                isSubmitting.value = false;
            },
        }
    );
};

const gradeBadgeClass = computed(() => {
    const g = props.analysisResult?.grade || 'B';
    if (g === 'A+') return 'from-emerald-500 to-green-600 text-white border-yellow-300';
    if (g === 'A') return 'from-green-500 to-emerald-600 text-white border-green-300';
    if (g === 'B+') return 'from-blue-500 to-indigo-600 text-white border-blue-300';
    if (g === 'B') return 'from-amber-500 to-yellow-600 text-white border-amber-300';
    if (g === 'C') return 'from-orange-500 to-red-500 text-white border-orange-300';
    return 'from-red-600 to-rose-700 text-white border-red-300';
});

const pairCardClass = (level) => {
    if (level === 'excellent') return 'bg-gradient-to-br from-emerald-50 to-green-50 border-emerald-300';
    if (level === 'good') return 'bg-gradient-to-br from-blue-50 to-indigo-50 border-blue-200';
    if (level === 'neutral') return 'bg-gray-50 border-gray-200';
    return 'bg-gradient-to-br from-red-50 to-orange-50 border-red-300';
};

const pairBadgeClass = (level) => {
    if (level === 'excellent') return 'bg-emerald-600 text-white';
    if (level === 'good') return 'bg-blue-600 text-white';
    if (level === 'neutral') return 'bg-gray-600 text-white';
    return 'bg-red-600 text-white';
};
</script>

<template>
    <Head>
        <title>วิเคราะห์เบอร์มงคล & ถอดรหัสเลขนำโชคจากเบอร์มือถือ - แจกตัง</title>
        <meta
            name="description"
            content="เช็กเบอร์มงคลฟรี วิเคราะห์คู่เลข 7 ตัวท้าย ผลรวมเบอร์โทรศัพท์ ตัดเกรดเบอร์ A+ พร้อมถอดรหัสเลขเด็ดนำโชคประจำเบอร์มือถือของคุณตามตำราเลขศาสตร์ไทย"
        />
        <meta
            name="keywords"
            content="วิเคราะห์เบอร์มงคล, เช็กเบอร์มงคล, ทำนายเบอร์โทรศัพท์, ผลรวมเบอร์มงคล, เลขนำโชคจากเบอร์มือถือ, แจกตัง"
        />
    </Head>

    <PublicLayout>
        <div class="py-10 bg-gradient-to-b from-amber-50/50 via-white to-gray-50 min-h-screen">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-8">
                <!-- Header & ฟอร์มกรอกเบอร์มือถือ -->
                <div class="bg-gradient-to-br from-red-700 via-red-600 to-amber-600 rounded-3xl shadow-2xl p-6 sm:p-10 text-white text-center relative overflow-hidden">
                    <div class="relative z-10 max-w-2xl mx-auto">
                        <div class="inline-flex items-center gap-1.5 rounded-full bg-yellow-400 text-gray-900 px-3.5 py-1 text-xs font-extrabold shadow mb-4">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>ตำราเลขศาสตร์เบอร์มงคลมาตรฐานไทย</span>
                        </div>
                        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-2">
                            วิเคราะห์เบอร์มงคล & ถอดรหัสเลขให้โชค
                        </h1>
                        <p class="text-red-100 text-sm sm:text-base mb-6">
                            ผ่าดวงคู่เลข 7 ตัวท้าย + ผลรวมเบอร์ 10 หลัก เช็กพลังโชคลาภ การเงิน การงาน เสน่ห์
                            พร้อมสกัด <strong class="text-yellow-300">"เลขนำโชคประจำเบอร์ของคุณ"</strong> ฟรีทันที!
                        </p>

                        <form @submit.prevent="submitAnalyze" class="max-w-lg mx-auto space-y-3">
                            <div class="flex flex-col sm:flex-row gap-2.5">
                                <input
                                    :value="phoneInput"
                                    @input="onPhoneInput"
                                    type="tel"
                                    inputmode="numeric"
                                    maxlength="10"
                                    placeholder="กรอกเบอร์มือถือ 10 หลัก (เช่น 0891234567)"
                                    class="flex-1 rounded-2xl border-2 border-yellow-300 bg-white px-5 py-4 text-gray-900 text-xl sm:text-2xl font-extrabold text-center tracking-widest shadow-inner focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400"
                                />
                                <button
                                    type="submit"
                                    :disabled="isSubmitting"
                                    class="inline-flex items-center justify-center gap-2 px-7 py-4 rounded-2xl bg-yellow-400 hover:bg-yellow-300 text-gray-900 font-extrabold text-lg shadow-xl transition-all shrink-0 disabled:opacity-60"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <span>{{ isSubmitting ? 'กำลังวิเคราะห์...' : 'ทำนายเบอร์' }}</span>
                                </button>
                            </div>
                            <div
                                v-if="localError || analysisError"
                                class="rounded-xl bg-red-900/80 border border-red-300 px-4 py-2.5 text-xs sm:text-sm text-yellow-200 font-medium"
                            >
                                {{ localError || analysisError }}
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ผลลัพธ์การวิเคราะห์เบอร์มงคล -->
                <div v-if="analysisResult" class="space-y-8">
                    <!-- 1. การ์ดสรุปเกรดเบอร์ + กราฟพลัง 4 ด้าน -->
                    <div class="bg-white rounded-3xl shadow-xl border-2 border-amber-200 overflow-hidden">
                        <div class="p-6 sm:p-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                            <!-- ฝั่งซ้าย: เกรดและคะแนนรวม -->
                            <div class="lg:col-span-5 text-center space-y-4 lg:border-r lg:border-gray-100 lg:pr-6">
                                <div class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                    ผลการวิเคราะห์เบอร์โทรศัพท์
                                </div>
                                <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-wider">
                                    {{ analysisResult.formatted_phone }}
                                </div>

                                <div class="flex items-center justify-center gap-4 py-2">
                                    <div
                                        :class="[
                                            'w-28 h-28 rounded-3xl bg-gradient-to-br border-4 shadow-xl flex flex-col items-center justify-center',
                                            gradeBadgeClass,
                                        ]"
                                    >
                                        <span class="text-xs font-bold uppercase opacity-90">เกรดเบอร์</span>
                                        <span class="text-4xl font-black leading-none mt-1">{{ analysisResult.grade }}</span>
                                    </div>
                                    <div class="text-left">
                                        <div class="text-xs text-gray-500 font-semibold">คะแนนพลังมงคลรวม</div>
                                        <div class="text-4xl font-extrabold text-gray-900">
                                            {{ analysisResult.overall_score }}
                                            <span class="text-base font-normal text-gray-500">/ 100</span>
                                        </div>
                                        <div class="text-xs font-bold text-emerald-700 mt-1">
                                            ผลรวมเลข 10 หลัก = {{ analysisResult.total_sum?.number }}
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-amber-50 rounded-2xl p-4 border border-amber-200 text-left">
                                    <div class="font-extrabold text-gray-900 text-sm mb-1">
                                        {{ analysisResult.grade_title }}
                                    </div>
                                    <p class="text-xs text-gray-700 leading-relaxed">
                                        {{ analysisResult.grade_summary }}
                                    </p>
                                </div>
                            </div>

                            <!-- ฝั่งขวา: กราฟหลอดพลัง 4 ด้าน + ปุ่มแชร์ -->
                            <div class="lg:col-span-7 space-y-5">
                                <h2 class="text-lg font-extrabold text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    <span>กราฟพลังดวงชะตาทั้ง 4 ด้านของเบอร์นี้</span>
                                </h2>

                                <div class="space-y-3.5">
                                    <!-- โชคลาภและการเงิน -->
                                    <div>
                                        <div class="flex justify-between text-sm font-bold mb-1">
                                            <span class="text-gray-800">โชคลาภ การเงิน และการเสี่ยงดวง</span>
                                            <span class="text-emerald-600">{{ analysisResult.aspects.wealth }}%</span>
                                        </div>
                                        <div class="w-full h-3.5 bg-gray-100 rounded-full overflow-hidden">
                                            <div
                                                class="h-full bg-gradient-to-r from-emerald-500 to-green-500 rounded-full transition-all duration-500"
                                                :style="{ width: `${analysisResult.aspects.wealth}%` }"
                                            />
                                        </div>
                                    </div>

                                    <!-- การงานและบารมี -->
                                    <div>
                                        <div class="flex justify-between text-sm font-bold mb-1">
                                            <span class="text-gray-800">การงาน สติปัญญา และอำนาจบารมี</span>
                                            <span class="text-blue-600">{{ analysisResult.aspects.career }}%</span>
                                        </div>
                                        <div class="w-full h-3.5 bg-gray-100 rounded-full overflow-hidden">
                                            <div
                                                class="h-full bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full transition-all duration-500"
                                                :style="{ width: `${analysisResult.aspects.career}%` }"
                                            />
                                        </div>
                                    </div>

                                    <!-- เสน่ห์และเมตตา -->
                                    <div>
                                        <div class="flex justify-between text-sm font-bold mb-1">
                                            <span class="text-gray-800">เสน่ห์ เมตตามหานิยม และคนอุปถัมภ์</span>
                                            <span class="text-pink-600">{{ analysisResult.aspects.charm }}%</span>
                                        </div>
                                        <div class="w-full h-3.5 bg-gray-100 rounded-full overflow-hidden">
                                            <div
                                                class="h-full bg-gradient-to-r from-pink-500 to-rose-500 rounded-full transition-all duration-500"
                                                :style="{ width: `${analysisResult.aspects.charm}%` }"
                                            />
                                        </div>
                                    </div>

                                    <!-- สุขภาพและแคล้วคลาด -->
                                    <div>
                                        <div class="flex justify-between text-sm font-bold mb-1">
                                            <span class="text-gray-800">สุขภาพ ความสงบสุข และแคล้วคลาด</span>
                                            <span class="text-amber-600">{{ analysisResult.aspects.health }}%</span>
                                        </div>
                                        <div class="w-full h-3.5 bg-gray-100 rounded-full overflow-hidden">
                                            <div
                                                class="h-full bg-gradient-to-r from-amber-500 to-yellow-500 rounded-full transition-all duration-500"
                                                :style="{ width: `${analysisResult.aspects.health}%` }"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <!-- ความหมายผลรวม 10 หลัก -->
                                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200">
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <span class="font-extrabold text-sm text-gray-900">
                                            {{ analysisResult.total_sum.title }}
                                        </span>
                                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900">
                                            {{ analysisResult.total_sum.level_label }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600 leading-relaxed">
                                        {{ analysisResult.total_sum.meaning }}
                                    </p>
                                </div>

                                <!-- ปุ่มแชร์ผลวิเคราะห์เบอร์ -->
                                <div class="pt-2 flex flex-wrap items-center justify-between gap-2">
                                    <ShareButtons
                                        :title="`ผลวิเคราะห์เบอร์มงคล ${analysisResult.formatted_phone} ได้เกรด ${analysisResult.grade} (${analysisResult.overall_score}/100 คะแนน)`"
                                        :text="`📱 เช็กเบอร์มงคลเว็บแจกตัง! เบอร์ของฉันได้เกรด ${analysisResult.grade} (${analysisResult.overall_score}/100 คะแนน — ${analysisResult.grade_title}) ลองมาเช็กเบอร์และดูเลขนำโชคของคุณฟรีที่นี่`"
                                        label="อวดคะแนนเบอร์ให้เพื่อนดู:"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. เลขนำโชคประจำเบอร์มือถือของคุณ (เชื่อมไปหน้าเลขเด็ด & สมัคร VIP) -->
                    <div class="bg-gradient-to-r from-red-600 via-red-700 to-amber-600 rounded-3xl p-6 sm:p-8 text-white shadow-xl">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                            <div class="lg:col-span-7 space-y-2">
                                <div class="inline-flex items-center gap-1 rounded-full bg-yellow-400 text-gray-900 px-3 py-0.5 text-xs font-extrabold">
                                    ถอดรหัสเลขให้โชคเฉพาะเบอร์นี้
                                </div>
                                <h2 class="text-2xl sm:text-3xl font-extrabold">
                                    เลขนำโชคประจำเบอร์ {{ analysisResult.formatted_phone }}
                                </h2>
                                <p class="text-red-100 text-sm leading-relaxed">
                                    สกัดจากคู่เลขพลังงานดีและผลรวมมงคลในเบอร์มือถือของคุณ สำหรับใช้เป็นแนวทางเสี่ยงโชคสลากกินแบ่งรัฐบาล
                                </p>
                                <div
                                    v-if="analysisResult.lucky_numbers?.matching_sources_count > 0"
                                    class="inline-flex items-center gap-2 rounded-xl bg-white/15 backdrop-blur-sm border border-yellow-300 px-4 py-2 text-xs sm:text-sm font-bold text-yellow-300 mt-2"
                                >
                                    <span>ข่าวดี! เลขในเบอร์ของคุณตรงกับแนวทางของสำนักดังงวดนี้ถึง {{ analysisResult.lucky_numbers.matching_sources_count }} สำนัก!</span>
                                </div>
                            </div>

                            <div class="lg:col-span-5 space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-white rounded-2xl p-4 text-center shadow-lg border-2 border-yellow-300">
                                        <div class="text-xs font-bold text-gray-500 mb-1">เลข 2 ตัวประจำเบอร์</div>
                                        <div class="text-2xl sm:text-3xl font-black text-red-600 tracking-wider">
                                            {{ analysisResult.lucky_numbers?.two_digits?.join(' • ') }}
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-2xl p-4 text-center shadow-lg border-2 border-yellow-300">
                                        <div class="text-xs font-bold text-gray-500 mb-1">เลข 3 ตัวประจำเบอร์</div>
                                        <div class="text-2xl sm:text-3xl font-black text-amber-600 tracking-wider">
                                            {{ analysisResult.lucky_numbers?.three_digits?.join(' • ') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <Link
                                        :href="route('lucky-numbers.index')"
                                        class="flex-1 text-center py-3 px-4 rounded-xl bg-yellow-400 hover:bg-yellow-300 text-gray-900 font-extrabold text-xs sm:text-sm shadow-lg transition-all"
                                    >
                                        เทียบกับเลขเด็ดทุกสำนักงวดนี้ →
                                    </Link>
                                    <Link
                                        :href="route('vip-request.create')"
                                        class="inline-flex items-center gap-1.5 py-3 px-4 rounded-xl bg-gray-900 hover:bg-gray-800 text-yellow-400 font-extrabold text-xs sm:text-sm shadow-lg transition-all"
                                    >
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z" />
                                        </svg>
                                        <span>เปิดเลข VIP</span>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. ผ่าความหมายคู่เลข 7 ตัวท้าย (6 คู่สำคัญ) -->
                    <div class="bg-white rounded-3xl shadow-md border border-gray-200 p-6 sm:p-8 space-y-6">
                        <div>
                            <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900">
                                ผ่าความหมายคู่เลข 7 ตัวท้าย (6 คู่ลำดับสำคัญ)
                            </h2>
                            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                                ตามหลักเลขศาสตร์เบอร์มงคล ตัวเลข 3 ตัวหน้า (รหัสเครือข่าย) จะมีผลน้อย ส่วน 7 ตัวท้ายที่จับคู่กัน 6 คู่คือหัวใจสำคัญที่ส่งผลต่อผู้ใช้โดยตรง
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div
                                v-for="item in analysisResult.pairs"
                                :key="item.position"
                                :class="[
                                    'rounded-2xl p-5 border-2 transition-all flex flex-col justify-between',
                                    pairCardClass(item.level),
                                ]"
                            >
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-3">
                                        <span class="text-xs font-bold text-gray-600">{{ item.position_label }}</span>
                                        <span :class="['px-2.5 py-0.5 rounded-full text-[11px] font-bold', pairBadgeClass(item.level)]">
                                            {{ item.level_label }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="w-14 h-14 rounded-2xl bg-white border-2 border-gray-200 shadow-sm flex items-center justify-center text-2xl font-black text-gray-900 shrink-0">
                                            {{ item.pair }}
                                        </span>
                                        <div>
                                            <h3 class="font-extrabold text-gray-900 text-sm leading-snug">
                                                {{ item.title }}
                                            </h3>
                                            <div class="text-xs text-gray-500 font-semibold mt-0.5">
                                                พลังตัวเลข: {{ item.score }}/100
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-700 leading-relaxed mt-2">
                                        {{ item.meaning }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. คำแนะนำวิธีแก้เคล็ด / เสริมดวงประจำเบอร์ + สินค้าสายมู (Affiliate) -->
                    <div
                        :class="[
                            'rounded-3xl p-6 sm:p-8 border-2 shadow-md space-y-6',
                            analysisResult.remedy_advice?.has_bad_pairs
                                ? 'bg-gradient-to-br from-amber-50 to-orange-50 border-amber-300'
                                : 'bg-gradient-to-br from-emerald-50 to-teal-50 border-emerald-300',
                        ]"
                    >
                        <div class="space-y-2">
                            <h2 class="text-xl font-extrabold text-gray-900">
                                {{ analysisResult.remedy_advice?.headline }}
                            </h2>
                            <p class="text-sm text-gray-700 leading-relaxed">
                                {{ analysisResult.remedy_advice?.advice }}
                            </p>
                            <div class="inline-block rounded-xl bg-white/90 border border-amber-200 px-3.5 py-1.5 text-xs font-bold text-amber-900">
                                💡 ไอเทมสายมูที่แนะนำสำหรับเบอร์นี้: {{ analysisResult.remedy_advice?.recommended_item_type }}
                            </div>
                        </div>

                        <!-- สินค้าสายมู Affiliate -->
                        <div v-if="affiliateProducts && affiliateProducts.length > 0" class="pt-4 border-t border-black/10">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-extrabold text-gray-900 text-base">
                                    🧿 วัตถุมงคล & แผ่นทองแก้เคล็ดเสริมดวงการเงินแนะนำ
                                </h3>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <a
                                    v-for="product in affiliateProducts"
                                    :key="product.id"
                                    :href="product.affiliate_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="group block bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-lg hover:border-red-300 transition-all"
                                >
                                    <div class="aspect-square bg-gray-100 overflow-hidden">
                                        <img
                                            v-if="product.image_url"
                                            :src="product.image_url"
                                            :alt="product.title"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                        />
                                        <div v-else class="w-full h-full flex items-center justify-center text-3xl text-gray-400">
                                            🧿
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <div class="text-xs font-bold text-gray-900 line-clamp-2 group-hover:text-red-600">
                                            {{ product.title }}
                                        </div>
                                        <span class="inline-block mt-1.5 text-[11px] text-red-600 font-semibold">
                                            บูชาที่ {{ product.platform?.name || 'ร้านค้า' }} →
                                        </span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- กรณียังไม่ได้กรอกเบอร์: แสดงคู่มือคู่เลขมงคลยอดฮิตเพื่อดึงดูด SEO -->
                <div v-else class="bg-white rounded-3xl shadow-md border border-gray-200 p-6 sm:p-8 space-y-6">
                    <div class="text-center max-w-2xl mx-auto">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900">
                            ตัวอย่างคู่เลขมงคลยอดนิยมตามตำราเลขศาสตร์ไทย
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            ลองกรอกเบอร์มือถือ 10 หลักของคุณด้านบน เพื่อตรวจดูว่าในเบอร์ของคุณมีคู่เลขเศรษฐีเหล่านี้ซ่อนอยู่หรือไม่
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="rounded-2xl p-4 bg-emerald-50 border border-emerald-200">
                            <div class="text-lg font-black text-emerald-800 mb-1">24 / 42 / 56 / 65</div>
                            <div class="text-xs font-bold text-gray-900 mb-1">คู่เลขเศรษฐี & มหาเสน่ห์</div>
                            <p class="text-xs text-gray-600">ค้าขายคล่อง เงินทองไหลมาเทมา ผู้คนรักใคร่เมตตาและคอยช่วยเหลือ</p>
                        </div>
                        <div class="rounded-2xl p-4 bg-amber-50 border border-amber-200">
                            <div class="text-lg font-black text-amber-800 mb-1">78 / 87 / 89 / 98</div>
                            <div class="text-xs font-bold text-gray-900 mb-1">คู่เลขมังกร-หงส์ โชคก้อนโต</div>
                            <p class="text-xs text-gray-600">โดดเด่นเรื่องการเสี่ยงโชค ลางสังหรณ์แม่นยำ หมุนเงินก้อนใหญ่และมีบารมีสูง</p>
                        </div>
                        <div class="rounded-2xl p-4 bg-blue-50 border border-blue-200">
                            <div class="text-lg font-black text-blue-800 mb-1">14 / 41 / 15 / 51</div>
                            <div class="text-xs font-bold text-gray-900 mb-1">คู่เลขผู้บริหาร & ปัญญา</div>
                            <p class="text-xs text-gray-600">สติปัญญาเฉียบแหลม ผู้ใหญ่อุปถัมภ์ เลื่อนขั้นเลื่อนตำแหน่งและการงานมั่นคง</p>
                        </div>
                        <div class="rounded-2xl p-4 bg-red-50 border border-red-200">
                            <div class="text-lg font-black text-red-700 mb-1">13 / 37 / 67 / 76</div>
                            <div class="text-xs font-bold text-gray-900 mb-1">คู่เลขที่ควรระวัง & แก้เคล็ด</div>
                            <p class="text-xs text-gray-600">ระวังเรื่องเก็บเงินไม่อยู่ อารมณ์ร้อน หรือความรักสะดุด ควรเสริมดวงแก้เคล็ด</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
