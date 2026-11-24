<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ShareButtons from '@/Components/ShareButtons.vue';
import axios from 'axios';

const props = defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
        default: true,
    },
    latestResult: {
        type: Object,
        default: null,
    },
    availableDraws: {
        type: Array,
        default: () => [],
    },
    affiliateSections: {
        type: Array,
        default: () => [],
    },
    promotedNumbers: {
        type: Array,
        default: () => [],
    },
    latestPosts: {
        type: Array,
        default: () => [],
    },
});

const fmtRunning = (arr) => {
    if (!arr || !Array.isArray(arr) || arr.length === 0) return '-';
    return arr.join(', ');
};

const scrollY = ref(0);
const selectedLottoId = ref(props.latestResult?.lotto_id || '');
const numberToCheck = ref('');
const checkResult = ref(null);
const isChecking = ref(false);
const errorMessage = ref('');

onMounted(() => {
    const handleScroll = () => {
        scrollY.value = window.scrollY;
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
});

const checkLottery = async () => {
    if (!selectedLottoId.value || !numberToCheck.value) {
        errorMessage.value = 'กรุณาเลือกงวดและกรอกหมายเลขสลาก';
        return;
    }

    isChecking.value = true;
    errorMessage.value = '';
    checkResult.value = null;

    try {
        const response = await axios.post(route('check-lottery'), {
            lotto_id: selectedLottoId.value,
            number: numberToCheck.value,
        });
        checkResult.value = response.data;
    } catch (error) {
        errorMessage.value = error.response?.data?.error || 'เกิดข้อผิดพลาดในการตรวจสอบ';
    } finally {
        isChecking.value = false;
    }
};

const formatThaiDate = (dateString) => {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        const day = date.getDate();
        const month = date.getMonth();
        const year = date.getFullYear() + 543;
        const thaiMonths = [
            'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน',
            'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม',
            'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
        ];
        return `${day} ${thaiMonths[month]} ${year}`;
    } catch (e) {
        return dateString;
    }
};

const formatNumber = (num) => {
    if (!num) return '0';
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};
</script>

<template>
    <Head>
        <title>เลขเด็ด เลขเด็ดงวดนี้ - แจกตัง | ตรวจหวย ตรวจผลสลากกินแบ่งรัฐบาล</title>
        <meta name="description" content="เลขเด็ด เลขเด็ดงวดนี้ อัปเดตผลหวยล่าสุด ตรวจหวยออนไลน์ ตรวจผลสลากกินแบ่งรัฐบาล หวยเลขเด็ด สินค้าสายมู สินค้ามงคล ของนำโชค สถิติหวยย้อนหลัง ตารางคะแนนความแม่นยำ" />
        <meta name="keywords" content="เลขเด็ด, เลขเด็ดงวดนี้, หวยเลขเด็ด, สินค้าสายมู, สินค้ามงคล, ของนำโชค, ตรวจหวย, ผลหวย, สลากกินแบ่งรัฐบาล, สถิติหวย, ตารางคะแนน, ตรวจหวยออนไลน์, แจกตัง" />
        <meta property="og:title" content="เลขเด็ด เลขเด็ดงวดนี้ - แจกตัง | ตรวจหวย ตรวจผลหวย" />
        <meta property="og:description" content="เลขเด็ด เลขเด็ดงวดนี้ ตรวจหวยออนไลน์ สินค้าสายมู สินค้ามงคล ของนำโชค หวยเลขเด็ด สถิติหวยย้อนหลัง" />
        <meta property="og:type" content="website" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="เลขเด็ด เลขเด็ดงวดนี้ - แจกตัง | ตรวจหวย" />
        <meta name="twitter:description" content="เลขเด็ด เลขเด็ดงวดนี้ ตรวจหวยออนไลน์ สินค้าสายมู สินค้ามงคล ของนำโชค หวยเลขเด็ด" />
    </Head>
    
    <PublicLayout :can-login="canLogin" :can-register="canRegister">

    <!-- ตรวจหวย Section -->
    <section class="pt-24 pb-12 bg-gradient-to-b from-red-600 to-red-700">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header: H1 สำหรับ SEO เลขเด็ด / เลขเด็ดงวดนี้ -->
            <div class="text-center mb-8">
                <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">
                    เลขเด็ด งวดนี้ — ตรวจหวย
                </h1>
                <p class="text-red-100">เลขเด็ด อัปเดตผลหวยล่าสุด ตรวจผลสลากกินแบ่งรัฐบาลออนไลน์</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- ผลหวยล่าสุด -->
                <div class="bg-white rounded-2xl shadow-2xl p-6 border-4 border-yellow-400">
                    <div class="flex items-center gap-2.5 mb-4">
                        <span class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </span>
                        <h3 class="text-xl font-bold text-gray-900">ผลหวยล่าสุด</h3>
                    </div>
                    
                    <div v-if="latestResult" class="space-y-4">
                        <div class="text-center bg-red-50 rounded-xl p-4">
                            <div class="text-sm text-gray-600 mb-1">งวดวันที่</div>
                            <div class="text-lg font-bold text-red-600">{{ latestResult.date_text }}</div>
                        </div>

                        <!-- รางวัลที่ 1 -->
                        <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl p-4 text-center shadow-lg">
                            <div class="text-sm text-yellow-900 font-medium mb-1">รางวัลที่ 1</div>
                            <div class="text-4xl md:text-5xl font-bold text-white tracking-widest drop-shadow-lg">
                                {{ latestResult.first_prize || '------' }}
                            </div>
                            <div class="text-yellow-100 text-sm mt-1">รางวัลละ 6,000,000 บาท</div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- เลขท้าย 2 ตัว -->
                            <div class="bg-blue-600 rounded-xl p-4 text-center">
                                <div class="text-xs text-blue-100 mb-1">เลขท้าย 2 ตัว</div>
                                <div class="text-3xl font-bold text-white">
                                    {{ latestResult.last_two_digit || '--' }}
                                </div>
                            </div>

                            <!-- เลขหน้า 3 ตัว -->
                            <div class="bg-green-600 rounded-xl p-4 text-center">
                                <div class="text-xs text-green-100 mb-1">เลขหน้า 3 ตัว</div>
                                <div class="text-xl font-bold text-white">
                                    {{ latestResult.front_three_digit?.join(' ') || '---' }}
                                </div>
                            </div>
                        </div>

                        <!-- เลขท้าย 3 ตัว -->
                        <div class="bg-purple-600 rounded-xl p-4 text-center">
                            <div class="text-xs text-purple-100 mb-1">เลขท้าย 3 ตัว</div>
                            <div class="text-xl font-bold text-white">
                                {{ latestResult.last_three_digit?.join(' ') || '---' }}
                            </div>
                        </div>

                        <!-- ปุ่มแชร์ผลหวยงวดล่าสุด -->
                        <div class="pt-3 border-t border-gray-100 flex items-center justify-between flex-wrap gap-2">
                            <span class="text-xs font-semibold text-gray-600">แชร์ผลหวยงวดนี้:</span>
                            <ShareButtons
                                compact
                                :title="`ผลสลากกินแบ่งรัฐบาล ${latestResult.date_text}`"
                                :text="`🏆 ผลหวยงวด ${latestResult.date_text} | รางวัลที่ 1: ${latestResult.first_prize || 'xxxxxx'} | เลขท้าย 2 ตัว: ${latestResult.last_two_digit || 'xx'} | ตรวจหวยและดูเลขเด็ดฟรีที่เว็บแจกตัง`"
                            />
                        </div>
                    </div>
                    <div v-else class="text-center text-gray-500 py-8">
                        ไม่พบข้อมูลผลหวย
                    </div>
                </div>

                <!-- ฟอร์มตรวจหวย -->
                <div class="bg-white rounded-2xl shadow-2xl p-6">
                    <div class="flex items-center gap-2.5 mb-4">
                        <span class="w-9 h-9 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <h3 class="text-xl font-bold text-gray-900">ตรวจสลากของคุณ</h3>
                    </div>

                    <div class="space-y-4">
                        <!-- เลือกงวด -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">เลือกงวด</label>
                            <select 
                                v-model="selectedLottoId" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-lg"
                            >
                                <option value="">-- เลือกงวด --</option>
                                <option v-for="draw in availableDraws" :key="draw.value" :value="draw.value">
                                    {{ draw.label }}
                                </option>
                            </select>
                        </div>

                        <!-- กรอกเลข -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">หมายเลขสลาก</label>
                            <input 
                                v-model="numberToCheck"
                                type="text"
                                maxlength="6"
                                placeholder="กรอกเลข 2-6 หลัก"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-2xl text-center tracking-widest font-bold"
                                @keyup.enter="checkLottery"
                            />
                            <p class="text-xs text-gray-500 mt-1">กรอกเลข 6 หลักเพื่อตรวจรางวัลใหญ่ หรือ 2-3 หลักเพื่อตรวจรางวัลเลขท้าย</p>
                        </div>

                        <!-- ปุ่มตรวจ -->
                        <button 
                            @click="checkLottery"
                            :disabled="isChecking"
                            class="w-full py-4 bg-gradient-to-r from-red-600 to-red-700 text-white text-xl font-bold rounded-xl hover:from-red-700 hover:to-red-800 transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="isChecking" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                กำลังตรวจสอบ...
                            </span>
                            <span v-else class="inline-flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>ตรวจหวย</span>
                            </span>
                        </button>

                        <!-- Error Message -->
                        <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                            {{ errorMessage }}
                        </div>

                        <!-- ผลการตรวจ -->
                        <div v-if="checkResult" class="mt-4">
                            <div v-if="checkResult.is_winner" class="bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl p-6 text-center">
                                <div class="w-14 h-14 rounded-full bg-white/90 text-amber-600 flex items-center justify-center mx-auto mb-3 shadow">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="text-2xl font-bold text-yellow-900 mb-2">ยินดีด้วย! คุณถูกรางวัล!</div>
                                <div class="text-lg text-yellow-800 mb-4">หมายเลข {{ checkResult.number }} งวด {{ checkResult.draw_date }}</div>
                                
                                <div class="space-y-2 text-left bg-white/50 rounded-lg p-4">
                                    <div v-for="(result, index) in checkResult.results" :key="index" class="flex justify-between items-center">
                                        <span class="font-medium text-yellow-900">{{ result.prize_name }}</span>
                                        <span class="text-yellow-900 font-bold">{{ result.reward }} บาท</span>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-t border-yellow-600">
                                    <div class="text-sm text-yellow-800">รวมเงินรางวัล</div>
                                    <div class="text-3xl font-bold text-yellow-900">{{ formatNumber(checkResult.total_winnings) }} บาท</div>
                                </div>
                            </div>

                            <div v-else class="bg-gray-100 rounded-xl p-6 text-center">
                                <div class="w-12 h-12 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="text-xl font-bold text-gray-700 mb-2">ไม่ถูกรางวัล</div>
                                <div class="text-gray-500">หมายเลข {{ checkResult.number }} งวด {{ checkResult.draw_date }}</div>
                                <div class="text-sm text-gray-400 mt-2">ขอให้โชคดีในงวดหน้านะครับ!</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- เลขเด็ดสำนักแนะนำประจำงวดนี้ (Sponsored Sources) -->
    <section v-if="promotedNumbers.length" class="py-12 bg-gradient-to-b from-amber-50 to-white border-t border-amber-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
                <div>
                    <div class="inline-flex items-center gap-1.5 rounded-full bg-yellow-400 px-3 py-1 text-xs font-bold text-gray-900 shadow-sm mb-2">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                        </svg>
                        <span>สำนักแนะนำพิเศษ</span>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900">
                        เลขเด็ดสำนักแนะนำประจำงวดนี้
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        คัดสรรแนวทางเลขเด็ดจากสำนักดัง เปิดให้ดูครบทั้งเลข 2 ตัว 3 ตัว และเลขวิ่ง
                    </p>
                </div>
                <Link
                    :href="route('lucky-numbers.index')"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-red-600 text-white text-sm font-bold shadow hover:bg-red-700 transition-colors"
                >
                    ดูเลขเด็ดทุกสำนัก →
                </Link>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="item in promotedNumbers"
                    :key="item.id"
                    class="bg-gradient-to-br from-amber-50 via-yellow-50 to-orange-50 rounded-2xl shadow-lg p-6 border-4 border-yellow-400 ring-2 ring-yellow-300/60 hover:shadow-xl transition-all duration-300 hover:-translate-y-1"
                >
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="text-xs font-medium text-gray-600">สำนัก</span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-yellow-400 px-2.5 py-0.5 text-xs font-bold text-gray-900 shadow-sm">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                    </svg>
                                    <span>สำนักแนะนำ</span>
                                </span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">{{ item.source?.name }}</h3>
                            <p
                                v-if="item.source?.description"
                                class="mt-1 text-xs text-gray-700 bg-white/70 rounded-lg px-2.5 py-1.5 border border-yellow-200 line-clamp-2"
                                :title="item.source.description"
                            >
                                {{ item.source.description }}
                            </p>
                        </div>
                    </div>

                    <div class="text-xs font-medium text-gray-600 mb-3 pb-3 border-b border-yellow-200 flex items-center justify-between gap-2">
                        <span>งวดวันที่ {{ formatThaiDate(item.draw_date) }}</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white/80 text-gray-700 font-semibold border border-yellow-200">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>{{ formatNumber(item.source?.view_count || 0) }} ครั้ง</span>
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-xl bg-white/90 p-3 border border-yellow-200 shadow-sm text-center">
                            <div class="text-xs font-medium text-gray-600 mb-1">2 ตัว</div>
                            <div class="text-2xl font-bold text-red-600">{{ item.two_digit || '-' }}</div>
                        </div>
                        <div class="rounded-xl bg-white/90 p-3 border border-yellow-200 shadow-sm text-center">
                            <div class="text-xs font-medium text-gray-600 mb-1">3 ตัว</div>
                            <div class="text-2xl font-bold text-red-600">{{ item.three_digit || '-' }}</div>
                        </div>
                        <div class="rounded-xl bg-white/90 p-3 border border-yellow-200 shadow-sm text-center">
                            <div class="text-xs font-medium text-gray-600 mb-1">วิ่ง</div>
                            <div class="text-sm font-bold text-red-600 truncate" :title="fmtRunning(item.running_numbers)">
                                {{ fmtRunning(item.running_numbers) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- สินค้าสายมู สินค้ามงคล ของนำโชค — แยก section ตามแพลตฟอร์ม -->
    <section v-if="affiliateSections.length" class="py-12 bg-gray-50 border-t border-gray-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">
                    สินค้าสายมู สินค้ามงคล ของนำโชค
                </h2>
                <p class="text-gray-600">วัตถุมงคล ของนำโชค จากร้านออนไลน์ที่น่าเชื่อถือ</p>
            </div>
        </div>
    </section>
    <section
        v-for="section in affiliateSections"
        :key="section.platform.id"
        class="py-12 bg-white border-t border-gray-100"
    >
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3 mb-6">
                <img
                    v-if="section.platform.logo_url"
                    :src="section.platform.logo_url"
                    :alt="section.platform.name"
                    class="h-10 w-10 object-contain rounded"
                />
                <h2 class="text-2xl font-bold text-gray-900">
                    สินค้าสายมู สินค้ามงคล จาก {{ section.platform.name }}
                </h2>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <a
                    v-for="product in section.products"
                    :key="product.id"
                    :href="product.affiliate_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="group block bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg hover:border-red-200 transition-all duration-200"
                >
                    <div class="aspect-square bg-gray-100 overflow-hidden">
                        <img
                            v-if="product.image_url"
                            :src="product.image_url"
                            :alt="product.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                        />
                        <div
                            v-else
                            class="w-full h-full flex items-center justify-center text-gray-400 text-4xl"
                        >
                            🧿
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 line-clamp-2 group-hover:text-red-600 transition-colors">
                            {{ product.title }}
                        </h3>
                        <p
                            v-if="product.description"
                            class="text-sm text-gray-500 mt-1 line-clamp-2"
                        >
                            {{ product.description }}
                        </p>
                        <span class="inline-block mt-2 text-sm text-red-600 font-medium">
                            ดูที่ {{ section.platform.name }} →
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- บทความหวย & คลังความรู้สายมู (SEO Articles Section บนหน้าแรก) -->
    <section v-if="latestPosts.length" class="py-12 bg-gray-50 border-t border-gray-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
                <div>
                    <div class="inline-flex items-center gap-1.5 rounded-full bg-red-100 text-red-700 px-3 py-1 text-xs font-bold mb-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                        <span>บทความ & เคล็ดลับสายมู</span>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900">
                        บทความหวย & วิเคราะห์สถิติน่ารู้
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        อัปเดตเรื่องราวสถิติหวย ตำราเลขมงคล และแนวทางเสริมโชคลาภ
                    </p>
                </div>
                <Link
                    :href="route('blog.index')"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-white border-2 border-red-600 text-red-600 text-sm font-bold hover:bg-red-50 transition-colors"
                >
                    อ่านบทความทั้งหมด →
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Link
                    v-for="post in latestPosts"
                    :key="post.id"
                    :href="route('blog.show', post.slug)"
                    class="group bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl hover:border-red-200 transition-all duration-300 flex flex-col justify-between"
                >
                    <div>
                        <div class="aspect-video bg-gray-100 overflow-hidden relative">
                            <img
                                v-if="post.cover_image_url"
                                :src="post.cover_image_url"
                                :alt="post.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div
                                v-else
                                class="w-full h-full bg-gradient-to-br from-red-50 to-amber-50 flex items-center justify-center text-red-400"
                            >
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <span class="absolute bottom-2.5 right-2.5 inline-flex items-center gap-1 rounded-full bg-black/70 text-white px-2.5 py-0.5 text-[11px] font-semibold">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>{{ formatNumber(post.view_count || 0) }}</span>
                            </span>
                        </div>
                        <div class="p-5">
                            <div class="text-xs text-gray-400 mb-1.5">
                                {{ post.published_at }} · {{ post.author_name }}
                            </div>
                            <h3 class="font-bold text-gray-900 line-clamp-2 group-hover:text-red-600 transition-colors">
                                {{ post.title }}
                            </h3>
                            <p v-if="post.excerpt" class="text-sm text-gray-500 mt-2 line-clamp-2">
                                {{ post.excerpt }}
                            </p>
                        </div>
                    </div>
                    <div class="px-5 pb-4 pt-3 border-t border-gray-100 text-right">
                        <span class="text-xs font-bold text-red-600 group-hover:underline">อ่านต่อ →</span>
                    </div>
                </Link>
            </div>
        </div>
    </section>

    <!-- Hero Section -->
    <!--<section class="relative min-h-[60vh] flex items-center justify-center overflow-hidden bg-gradient-to-b from-red-50 to-white">-->
        <!-- Background Pattern -->
        <!---<div class="absolute inset-0 opacity-5">
            <div class="absolute top-0 left-0 w-full h-full" style="background-image: radial-gradient(circle, #dc2626 1px, transparent 1px); background-size: 50px 50px;"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center py-16">
            <div class="space-y-8">
                <h1 class="text-5xl md:text-7xl font-bold text-gray-900 leading-tight">
                    <span class="text-red-600">
                        ยินดีต้อนรับสู่
                    </span>
                    <br>
                    <span class="text-gray-800">ระบบลอตเตอรี่ออนไลน์</span>
                </h1>
                
                <p class="text-xl md:text-2xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    ระบบที่ทันสมัย ปลอดภัย และใช้งานง่าย 
                    <br class="hidden md:block">
                    พร้อมให้คุณสัมผัสประสบการณ์ใหม่ในการซื้อลอตเตอรี่
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center pt-8">
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="px-8 py-4 bg-red-600 text-white text-lg font-semibold rounded-md hover:bg-red-700 hover:shadow-2xl hover:scale-105 transition-all duration-300"
                    >
                        เริ่มต้นใช้งานฟรี
                    </Link>
                    <Link
                        v-if="canLogin && !$page.props.auth?.user"
                        :href="route('login')"
                        class="px-8 py-4 bg-white text-red-600 text-lg font-semibold rounded-md border-2 border-red-600 hover:bg-red-50 transition-all duration-300"
                    >
                        เข้าสู่ระบบ
                    </Link>
                </div> -->

                <!-- Stats -->
               <!--- <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-16 max-w-4xl mx-auto">
                    <div class="bg-white rounded-2xl p-6 shadow-lg border border-red-100">
                        <div class="text-4xl font-bold text-red-600 mb-2">10,000+</div>
                        <div class="text-gray-600">ผู้ใช้งาน</div>
                    </div>
                    <div class="bg-white rounded-2xl p-6 shadow-lg border border-red-100">
                        <div class="text-4xl font-bold text-red-600 mb-2">99.9%</div>
                        <div class="text-gray-600">ความปลอดภัย</div>
                    </div>
                    <div class="bg-white rounded-2xl p-6 shadow-lg border border-red-100">
                        <div class="text-4xl font-bold text-red-600 mb-2">24/7</div>
                        <div class="text-gray-600">บริการตลอดเวลา</div>
                    </div>
                </div>
            </div>
        </div>
    </section> -->

    <!-- Features Section -->
   <!--- <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    ทำไมต้องเลือกเรา?
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    ระบบที่ออกแบบมาเพื่อความสะดวกและความปลอดภัยของคุณ
                </p>
            </div> -->

           <!-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">-->
                <!-- Feature 1 -->
               <!--- <div class="group bg-gradient-to-br from-red-50 to-white rounded-2xl p-8 hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-red-100">
                    <div class="w-16 h-16 bg-red-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">ปลอดภัย 100%</h3>
                    <p class="text-gray-600 leading-relaxed">
                        ระบบรักษาความปลอดภัยระดับสูงด้วยเทคโนโลยีเข้ารหัสข้อมูล 
                        เพื่อปกป้องข้อมูลส่วนตัวของคุณ
                    </p>
                </div> -->

                <!-- Feature 2 -->
               <!--- <div class="group bg-gradient-to-br from-red-50 to-white rounded-2xl p-8 hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-red-100">
                    <div class="w-16 h-16 bg-red-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">รวดเร็วทันใจ</h3>
                    <p class="text-gray-600 leading-relaxed">
                        ระบบที่ทำงานเร็วและเสถียร พร้อมให้บริการทุกที่ทุกเวลา 
                        ไม่ต้องรอคอยนาน
                    </p>
                </div> -->

                <!-- Feature 3 -->
               <!-- <div class="group bg-gradient-to-br from-red-50 to-white rounded-2xl p-8 hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-red-100">
                    <div class="w-16 h-16 bg-red-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">ใช้งานง่าย</h3>
                    <p class="text-gray-600 leading-relaxed">
                        อินเทอร์เฟซที่เข้าใจง่าย ใช้งานสะดวก 
                        เหมาะสำหรับทุกเพศทุกวัย
                    </p>
                </div>  -->

                <!-- Feature 4 -->
               <!--- <div class="group bg-gradient-to-br from-red-50 to-white rounded-2xl p-8 hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-red-100">
                    <div class="w-16 h-16 bg-red-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">เชื่อถือได้</h3>
                    <p class="text-gray-600 leading-relaxed">
                        ได้รับการรับรองจากหน่วยงานที่เกี่ยวข้อง 
                        มั่นใจได้ในความน่าเชื่อถือ
                    </p>
                </div> -->

                <!-- Feature 5 -->
               <!-- <div class="group bg-gradient-to-br from-red-50 to-white rounded-2xl p-8 hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-red-100">
                    <div class="w-16 h-16 bg-red-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">ทีมงานพร้อมช่วยเหลือ</h3>
                    <p class="text-gray-600 leading-relaxed">
                        มีทีมงานคอยให้คำปรึกษาและช่วยเหลือตลอด 24 ชั่วโมง 
                        พร้อมตอบคำถามทุกข้อสงสัย
                    </p>
                </div> -->

                <!-- Feature 6 -->
               <!--- <div class="group bg-gradient-to-br from-red-50 to-white rounded-2xl p-8 hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-red-100">
                    <div class="w-16 h-16 bg-red-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">ราคาเป็นธรรม</h3>
                    <p class="text-gray-600 leading-relaxed">
                        ราคาที่เหมาะสมและโปร่งใส ไม่มีค่าใช้จ่ายแอบแฝง 
                        คุ้มค่ากับทุกบาทที่จ่าย
                    </p>
                </div>
            </div>
        </div>
    </section> -->

    <!-- CTA Section -->
   <!--- <section class="py-24 bg-gradient-to-r from-red-600 to-red-700">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                พร้อมเริ่มต้นแล้วหรือยัง?
            </h2>
            <p class="text-xl text-white/90 mb-8">
                สมัครสมาชิกวันนี้และรับสิทธิพิเศษมากมาย
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="px-8 py-4 bg-white text-red-600 text-lg font-semibold rounded-md hover:shadow-2xl hover:scale-105 transition-all duration-300"
                >
                    สมัครสมาชิกฟรี
                </Link>
                <Link
                    v-if="canLogin && !$page.props.auth?.user"
                    :href="route('login')"
                    class="px-8 py-4 bg-white/20 backdrop-blur-sm text-white text-lg font-semibold rounded-md border-2 border-white hover:bg-white/30 transition-all duration-300"
                >
                    เข้าสู่ระบบ
                </Link>
            </div>
        </div>
    </section> -->
    </PublicLayout>
</template>

<style scoped>
@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
