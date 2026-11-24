<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    analysisResult: Object,
    analysisError: String,
    oldNumber: String,
});

const form = useForm({
    number: props.oldNumber || '',
});

// ตรวจจับการกดตัวเลขเท่านั้นในช่องกรอก
const handleInput = (e) => {
    form.number = e.target.value.replace(/[^0-9]/g, '');
};

const submit = () => {
    if (form.number.length !== 6) {
        form.errors.number = 'กรุณากรอกตัวเลขให้ครบ 6 หลัก';
        return;
    }
    form.post(route('lotto-analyzer.analyze'), {
        preserveState: true,
        onSuccess: () => {
            // สำเร็จ
        }
    });
};

// คำนวณ SVG Gauge Semi-Circle
const dashArray = 125.6;
const dashOffset = computed(() => {
    const scoreVal = props.analysisResult?.score ?? 0;
    return dashArray - (dashArray * scoreVal) / 100;
});

// คำนวณระดับความน่าจะเป็น
const ratingTier = computed(() => {
    const scoreVal = props.analysisResult?.score ?? 0;
    if (scoreVal >= 80) {
        return {
            text: 'ดีเยี่ยม (Excellent)',
            colorClass: 'text-emerald-600 bg-emerald-50 border-emerald-200',
            bgGlow: 'from-emerald-500/10 to-transparent'
        };
    } else if (scoreVal >= 60) {
        return {
            text: 'ค่อนข้างดี (Good)',
            colorClass: 'text-teal-600 bg-teal-50 border-teal-200',
            bgGlow: 'from-teal-500/10 to-transparent'
        };
    } else if (scoreVal >= 40) {
        return {
            text: 'ปานกลาง (Fair)',
            colorClass: 'text-amber-600 bg-amber-50 border-amber-200',
            bgGlow: 'from-amber-500/10 to-transparent'
        };
    } else {
        return {
            text: 'ค่อนข้างต่ำ (Unfavorable)',
            colorClass: 'text-rose-600 bg-rose-50 border-rose-200',
            bgGlow: 'from-rose-500/10 to-transparent'
        };
    }
});
</script>

<template>
    <Head>
        <title>วิเคราะห์เลขนำโชค - แจกตัง | ตรวจสอบสถิติความน่าจะเป็นตัวเลข</title>
        <meta name="description" content="วิเคราะห์เลขสลากกินแบ่ง 6 หลักของคุณ ตรวจสอบสถิติดิจิตคู่คี่ ผลรวมหลัก ความถี่การออกรางวัลย้อนหลัง และความน่าจะเป็นทางคณิตศาสตร์" />
        <meta name="keywords" content="วิเคราะห์หวย, วิเคราะห์เลขนำโชค, สถิติหวย, คำนวณความน่าจะเป็นหวย, เลขเด็ด" />
    </Head>

    <PublicLayout>
        <div class="py-12">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-8">
                
                <!-- Header -->
                <div class="text-center">
                    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-3 tracking-tight">
                        วิเคราะห์ <span class="text-red-600">เลขนำโชค</span>
                    </h1>
                    <p class="text-lg text-gray-600 max-w-xl mx-auto">
                        กรอกตัวเลขสลาก 6 หลักเพื่อตรวจสอบสมดุลตัวเลข สถิติการออกรางวัลย้อนหลัง และวิเคราะห์ความน่าจะเป็นทางสถิติศาสตร์
                    </p>
                </div>

                <!-- Input Card -->
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6 md:p-8 max-w-xl mx-auto">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="number" value="กรอกหมายเลข 6 หลักที่คุณต้องการวิเคราะห์" class="text-gray-700 font-semibold mb-2" />
                            <div class="relative rounded-2xl shadow-inner bg-gray-50 border border-gray-200 focus-within:ring-2 focus-within:ring-red-500 focus-within:border-red-500 transition-all">
                                <input
                                    id="number"
                                    type="text"
                                    maxlength="6"
                                    v-model="form.number"
                                    @input="handleInput"
                                    placeholder="เช่น 987654"
                                    class="w-full bg-transparent border-0 text-center font-mono text-4xl md:text-5xl font-bold tracking-widest text-gray-800 placeholder-gray-300 py-4 focus:ring-0 focus:outline-none"
                                    required
                                />
                            </div>
                            <InputError class="mt-2 text-center" :message="form.errors.number || analysisError" />
                        </div>

                        <div class="flex justify-center">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full md:w-auto px-8 py-3 bg-red-600 text-white font-bold rounded-2xl hover:bg-red-700 active:scale-95 disabled:opacity-50 transition-all shadow-lg hover:shadow-red-600/30 flex items-center justify-center gap-2"
                            >
                                <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ form.processing ? 'กำลังคำนวณวิเคราะห์...' : 'เริ่มต้นวิเคราะห์สถิติ' }}</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Simulation Processing Screen -->
                <div v-if="form.processing" class="text-center py-12 space-y-4">
                    <div class="relative flex justify-center">
                        <div class="absolute h-16 w-16 rounded-full border-4 border-red-200 animate-ping"></div>
                        <div class="h-16 w-16 rounded-full border-4 border-red-600 border-t-transparent animate-spin"></div>
                    </div>
                    <p class="text-gray-500 font-medium animate-pulse">กำลังประมวลผล เปรียบเทียบข้อมูลสถิติกับฐานข้อมูลแจกตัง...</p>
                </div>

                <!-- Analysis Results Section -->
                <div v-else-if="analysisResult && analysisResult.success" class="space-y-6 animate-fade-in">
                    
                    <!-- Main Score Card -->
                    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 text-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-b opacity-5 pointer-events-none" :class="ratingTier.bgGlow"></div>
                        
                        <div class="text-gray-500 font-bold text-sm uppercase tracking-wider mb-2">ดัชนีโอกาสถูกรางวัลเชิงสถิติ</div>
                        
                        <!-- SVG Gauge Chart -->
                        <div class="relative w-full max-w-[240px] mx-auto mb-4">
                            <svg viewBox="0 0 100 55" class="w-full">
                                <!-- Background Arc -->
                                <path
                                    d="M 10 50 A 40 40 0 0 1 90 50"
                                    fill="none"
                                    stroke="#E5E7EB"
                                    stroke-width="8"
                                    stroke-linecap="round"
                                />
                                <!-- Value Arc -->
                                <path
                                    d="M 10 50 A 40 40 0 0 1 90 50"
                                    fill="none"
                                    stroke="url(#scoreGradient)"
                                    stroke-width="8"
                                    stroke-linecap="round"
                                    :stroke-dasharray="dashArray"
                                    :stroke-dashoffset="dashOffset"
                                    class="transition-all duration-1000 ease-out"
                                />
                                <defs>
                                    <linearGradient id="scoreGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" stop-color="#F43F5E" />
                                        <stop offset="50%" stop-color="#F59E0B" />
                                        <stop offset="100%" stop-color="#10B981" />
                                    </linearGradient>
                                </defs>
                            </svg>
                            <!-- Score text overlay -->
                            <div class="absolute bottom-1 left-0 right-0 text-center">
                                <div class="text-5xl font-black text-gray-800 tracking-tight">{{ analysisResult.score }}<span class="text-xl font-normal text-gray-500">%</span></div>
                            </div>
                        </div>

                        <!-- Badge Rating -->
                        <div class="inline-flex items-center px-4 py-1.5 rounded-full border text-sm font-bold shadow-sm" :class="ratingTier.colorClass">
                            เกณฑ์ความน่าจะเป็น: {{ ratingTier.text }}
                        </div>

                        <div class="text-center max-w-xl mx-auto mt-6">
                            <div class="font-mono text-3xl font-extrabold text-gray-800 tracking-widest bg-gray-50 rounded-2xl py-3 border border-gray-100">
                                {{ analysisResult.number }}
                            </div>
                        </div>
                    </div>

                    <!-- Details Metric Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        <!-- Metric 1: Odd/Even -->
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-md p-6 flex flex-col justify-between">
                            <div>
                                <div class="text-sm font-bold text-gray-400 uppercase mb-1">สมดุลคู่ / คี่</div>
                                <div class="text-2xl font-black text-gray-800">{{ analysisResult.metrics.odd_even }}</div>
                            </div>
                            <div class="text-xs text-gray-500 mt-4 pt-4 border-t border-gray-50">
                                อัตราส่วนที่เหมาะสมเชิงสถิติคือ 3:3 (สลากทั่วไปส่วนใหญ่จะมีสัดส่วนนี้)
                            </div>
                        </div>

                        <!-- Metric 2: High/Low -->
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-md p-6 flex flex-col justify-between">
                            <div>
                                <div class="text-sm font-bold text-gray-400 uppercase mb-1">สมดุลสูง / ต่ำ</div>
                                <div class="text-2xl font-black text-gray-800">{{ analysisResult.metrics.high_low }}</div>
                            </div>
                            <div class="text-xs text-gray-500 mt-4 pt-4 border-t border-gray-50">
                                เลขต่ำ (0-4) และสูง (5-9) ที่กระจายตัวกันมักมีแนวโน้มถูกสุ่มจับได้ง่ายกว่า
                            </div>
                        </div>

                        <!-- Metric 3: Digit Sum -->
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-md p-6 flex flex-col justify-between">
                            <div>
                                <div class="text-sm font-bold text-gray-400 uppercase mb-1">ผลรวมเลขทุกหลัก</div>
                                <div class="text-2xl font-black text-gray-800">{{ analysisResult.metrics.sum }}</div>
                            </div>
                            <div class="text-xs text-gray-500 mt-4 pt-4 border-t border-gray-50">
                                ผลรวมที่เกาะกลุ่มในช่วง 20 - 40 มีอัตราการออกรางวัลสูงที่สุดถึง 70%
                            </div>
                        </div>

                    </div>

                    <!-- History Statistics Card -->
                    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6 md:p-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2 border-b border-gray-100 pb-4">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            ความถี่การออกรางวัลในอดีต (ประวัติย้อนหลัง)
                        </h2>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                <div class="text-xs text-gray-500 font-bold mb-1">เลขหน้า 3 ตัว ({{ analysisResult.number.substring(0, 3) }})</div>
                                <div class="text-2xl font-black text-red-600">{{ analysisResult.metrics.front_three_count }} <span class="text-xs font-normal text-gray-500">ครั้ง</span></div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                <div class="text-xs text-gray-500 font-bold mb-1">เลขท้าย 3 ตัว ({{ analysisResult.number.substring(3) }})</div>
                                <div class="text-2xl font-black text-red-600">{{ analysisResult.metrics.last_three_count }} <span class="text-xs font-normal text-gray-500">ครั้ง</span></div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                <div class="text-xs text-gray-500 font-bold mb-1">เลขท้าย 2 ตัว ({{ analysisResult.number.substring(4) }})</div>
                                <div class="text-2xl font-black text-red-600">{{ analysisResult.metrics.last_two_count }} <span class="text-xs font-normal text-gray-500">ครั้ง</span></div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                <div class="text-xs text-gray-500 font-bold mb-1">รางวัลที่ 1 ({{ analysisResult.number }})</div>
                                <div class="text-lg font-black" :class="analysisResult.metrics.first_prize_drawn ? 'text-red-600' : 'text-emerald-600'">
                                    {{ analysisResult.metrics.first_prize_drawn ? 'เคยถูกออกแล้ว' : 'ไม่เคยออก' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Analysis Bullet Points -->
                    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6 md:p-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2 border-b border-gray-100 pb-4">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            บทวิเคราะห์สรุปตัวเลขนำโชค
                        </h2>

                        <ul class="space-y-4">
                            <li
                                v-for="(comment, index) in analysisResult.analysis"
                                :key="index"
                                class="flex items-start gap-3 text-gray-700"
                            >
                                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm md:text-base leading-relaxed">{{ comment }}</span>
                            </li>
                        </ul>
                    </div>

                </div>

            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
