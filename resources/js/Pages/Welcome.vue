<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import axios from 'axios';

const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
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
});

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
        const response = await axios.post('/check-lottery', {
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
        <title>ตรวจหวยย้อนหลัง - แจกตัง | ตรวจหวยออนไลน์ ตรวจผลสลากกินแบ่งรัฐบาล</title>
        <meta name="description" content="แจกตัง - ระบบตรวจหวยออนไลน์ ตรวจผลสลากกินแบ่งรัฐบาล เลขเด็ด สถิติหวยย้อนหลัง ตารางคะแนนความแม่นยำ ตรวจหวยง่าย รวดเร็ว ปลอดภัย" />
        <meta name="keywords" content="ตรวจหวย, ผลหวย, สลากกินแบ่ง, เลขเด็ด, สถิติหวย, ตารางคะแนน, ตรวจผลรางวัล, หวยออนไลน์, แจกตัง" />
        <meta property="og:title" content="ยินดีต้อนรับ - แจกตัง | ตรวจหวยออนไลน์" />
        <meta property="og:description" content="ตรวจหวยออนไลน์ ตรวจผลสลากกินแบ่งรัฐบาล เลขเด็ด สถิติหวยย้อนหลัง ตารางคะแนนความแม่นยำ" />
        <meta property="og:type" content="website" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="ยินดีต้อนรับ - แจกตัง | ตรวจหวยออนไลน์" />
        <meta name="twitter:description" content="ตรวจหวยออนไลน์ ตรวจผลสลากกินแบ่งรัฐบาล เลขเด็ด สถิติหวยย้อนหลัง" />
    </Head>
    
    <PublicLayout :can-login="canLogin" :can-register="canRegister">

    <!-- ตรวจหวย Section -->
    <section class="pt-24 pb-12 bg-gradient-to-b from-red-600 to-red-700">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-2">
                    🎯 ตรวจหวย
                </h2>
                <p class="text-red-100">ตรวจผลสลากกินแบ่งรัฐบาลออนไลน์</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- ผลหวยล่าสุด -->
                <div class="bg-white rounded-2xl shadow-2xl p-6 border-4 border-yellow-400">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-2xl">🏆</span>
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
                    </div>
                    <div v-else class="text-center text-gray-500 py-8">
                        ไม่พบข้อมูลผลหวย
                    </div>
                </div>

                <!-- ฟอร์มตรวจหวย -->
                <div class="bg-white rounded-2xl shadow-2xl p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-2xl">🔍</span>
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
                            <span v-else>🎰 ตรวจหวย</span>
                        </button>

                        <!-- Error Message -->
                        <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                            {{ errorMessage }}
                        </div>

                        <!-- ผลการตรวจ -->
                        <div v-if="checkResult" class="mt-4">
                            <div v-if="checkResult.is_winner" class="bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl p-6 text-center">
                                <div class="text-5xl mb-2">🎉</div>
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
                                <div class="text-5xl mb-2">😔</div>
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

    <!-- วัตถุมงคล แยก section ตามแพลตฟอร์ม -->
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
                    สินค้าวัตถุมงคลจาก {{ section.platform.name }}
                </h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
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
