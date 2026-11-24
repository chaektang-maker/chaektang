<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import VoteButton from '@/Components/VoteButton.vue';
import ShareButtons from '@/Components/ShareButtons.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

const props = defineProps({
    filters: Object,
    availableDrawDates: Array,
    numbers: Object,
    hotNumbers: Array,
    canViewVip: {
        type: Boolean,
        default: false,
    },
});

const localFilters = reactive({
    draw_date: props.filters?.draw_date || '',
    sort: props.filters?.sort || 'latest',
});

const apply = (e) => {
    e?.preventDefault?.();
    router.get(route('lucky-numbers.index'), {
        draw_date: localFilters.draw_date || '',
        sort: localFilters.sort || 'latest',
    }, { preserveState: true, replace: true });
};

const fmtRunning = (arr) => {
    if (!arr) return '-';
    if (Array.isArray(arr)) return arr.join(', ');
    return String(arr);
};

// แปลงวันที่เป็นภาษาไทย
const formatThaiDate = (dateString) => {
    if (!dateString) return '';
    
    try {
        const date = new Date(dateString);
        const day = date.getDate();
        const month = date.getMonth();
        const year = date.getFullYear() + 543; // แปลงเป็น พ.ศ.
        
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

// แปลงวันที่และเวลาเป็นภาษาไทย
const formatThaiDateTime = (dateTimeString) => {
    if (!dateTimeString) return '';
    
    try {
        const date = new Date(dateTimeString);
        const day = date.getDate();
        const month = date.getMonth();
        const year = date.getFullYear() + 543;
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        
        const thaiMonths = [
            'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน',
            'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม',
            'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
        ];
        
        return `${day} ${thaiMonths[month]} ${year} ${hours}:${minutes} น.`;
    } catch (e) {
        return dateTimeString;
    }
};

// สีสำหรับการ์ดสำนัก (ใช้ index เพื่อให้สีแตกต่างกัน)
const getCardColor = (index) => {
    const colors = [
        { bg: 'from-red-50 to-red-100', border: 'border-red-200', accent: 'bg-red-600' },
        { bg: 'from-orange-50 to-orange-100', border: 'border-orange-200', accent: 'bg-orange-600' },
        { bg: 'from-amber-50 to-amber-100', border: 'border-amber-200', accent: 'bg-amber-600' },
        { bg: 'from-yellow-50 to-yellow-100', border: 'border-yellow-200', accent: 'bg-yellow-600' },
        { bg: 'from-pink-50 to-pink-100', border: 'border-pink-200', accent: 'bg-pink-600' },
        { bg: 'from-rose-50 to-rose-100', border: 'border-rose-200', accent: 'bg-rose-600' },
    ];
    return colors[index % colors.length];
};
</script>

<template>
    <Head>
        <title>เลขเด็ด เลขเด็ดงวดนี้ - แจกตัง | รวมเลขเด็ดจากหลายสำนัก</title>
        <meta name="description" content="เลขเด็ด เลขเด็ดงวดนี้ รวมเลขจากหลายสำนัก หวยเลขเด็ด อัปเดตรายงวด เลขเด็ด 2 ตัว 3 ตัว เลขวิ่ง เลือกดูตามงวดและการเรียง" />
        <meta name="keywords" content="เลขเด็ด, เลขเด็ดงวดนี้, หวยเลขเด็ด, เลขเด็ด 2 ตัว, เลขเด็ด 3 ตัว, เลขวิ่ง, สำนักหวย, รวมเลขเด็ด, แจกตัง" />
        <meta property="og:title" content="เลขเด็ด เลขเด็ดงวดนี้ - แจกตัง | รวมเลขเด็ดจากหลายสำนัก" />
        <meta property="og:description" content="เลขเด็ด เลขเด็ดงวดนี้ รวมเลขจากหลายสำนัก หวยเลขเด็ด อัปเดตรายงวด เลขเด็ด 2 ตัว 3 ตัว เลขวิ่ง" />
        <meta name="twitter:title" content="เลขเด็ด เลขเด็ดงวดนี้ - แจกตัง | รวมเลขเด็ด" />
        <meta name="twitter:description" content="เลขเด็ด เลขเด็ดงวดนี้ รวมเลขจากหลายสำนัก หวยเลขเด็ด อัปเดตรายงวด เลขเด็ด 2 ตัว 3 ตัว เลขวิ่ง" />
    </Head>

    <PublicLayout>
        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Header: SEO เลขเด็ด / เลขเด็ดงวดนี้ -->
                <div class="text-center mb-8">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                        <span class="text-red-600">เลขเด็ด งวดนี้</span> — รวมเลขเด็ดจากหลายสำนัก
                    </h1>
                    <p class="text-xl text-gray-600 mb-4">เลขเด็ด อัปเดตรายงวด เลขเด็ด 2 ตัว 3 ตัว เลขวิ่ง เลือกดูตามงวดและการเรียง</p>
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <Link 
                            :href="route('vote.leaderboard')" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-sm shadow-md"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                            ดูคะแนนโหวตรวม
                        </Link>
                        <ShareButtons
                            title="รวมเลขเด็ดงวดนี้จากทุกสำนักดัง - เว็บแจกตัง"
                            text="🔥 มัดรวมเลขเด็ดงวดนี้จากทุกสำนักดัง + 10 อันดับเลขมาแรงชนกันทุกสำนัก! เข้ามาส่องฟรีที่เว็บแจกตัง"
                            label="แชร์หน้าเลขเด็ด:"
                        />
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-2xl shadow-lg border border-red-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        กรองข้อมูล
                    </h2>
                    <form class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end" @submit="apply">
                        <div>
                            <InputLabel value="งวด" />
                            <select v-model="localFilters.draw_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                <option value="">เลือกงวด</option>
                                <option v-for="d in availableDrawDates" :key="d.value" :value="d.value">{{ d.label }}</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="เรียงตาม" />
                            <select v-model="localFilters.sort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                <option value="latest">อัปเดตล่าสุด</option>
                                <option value="popular">ความนิยม</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-all font-medium text-sm shadow-md">
                                แสดง
                            </button>
                            <button type="button" @click="router.get(route('lucky-numbers.index'))" class="px-6 py-2 bg-white text-red-600 rounded-md border-2 border-red-600 hover:bg-red-50 transition-all font-medium text-sm">
                                ค่าเริ่มต้น
                            </button>
                        </div>
                    </form>
                </div>

                <!-- แบนเนอร์เชิญชวนสมัคร VIP (เฉพาะผู้ที่ยังไม่ได้เป็น VIP) -->
                <div v-if="!canViewVip" class="bg-gradient-to-r from-amber-500 via-yellow-500 to-amber-600 rounded-2xl shadow-lg p-5 text-white flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <span class="w-11 h-11 rounded-xl bg-gray-900/15 text-gray-900 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">ปลดล็อกเลขเด็ดสำนัก VIP & เลขมาแรงอันดับ 1</h3>
                            <p class="text-sm text-gray-900/90">สมาชิก VIP ดูเลขเจาะลึก 2 ตัว / 3 ตัวของสำนักแม่นยำสูงสุด และทำนายฝันตีเลขมงคลได้ไม่จำกัด</p>
                        </div>
                    </div>
                    <Link
                        :href="route('vip-request.create')"
                        class="shrink-0 px-5 py-2.5 bg-red-600 text-white font-bold rounded-xl shadow-md hover:bg-red-700 transition-colors text-sm"
                    >
                        สมัครสมาชิก VIP →
                    </Link>
                </div>

                <!-- เลขเด็ดมาแรง -->
                <div v-if="hotNumbers && hotNumbers.length > 0" class="bg-gradient-to-r from-red-600 to-red-700 rounded-2xl shadow-xl border-2 border-red-500 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <svg class="w-10 h-10 text-yellow-300 animate-pulse" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.66 11.2C17.43 10.9 17 10.5 16.4 10c-.6-.5-1.1-.9-1.4-1.2-.3-.3-.6-.7-.8-1.1-.2-.4-.3-.8-.3-1.2 0-.4.1-.7.2-1 .1-.3.3-.5.5-.7.2-.2.5-.3.8-.4.3-.1.6-.1 1-.1.4 0 .7 0 1 .1.3.1.5.2.8.4.2.2.4.4.5.7.1.3.2.6.2 1 0 .4-.1.8-.3 1.2-.2.4-.5.8-.8 1.1-.3.3-.8.7-1.4 1.2-.6.5-1.03.9-1.26 1.2M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <h2 class="text-2xl md:text-3xl font-bold text-white">
                            เลขเด็ดมาแรง 2 ตัว
                        </h2>
                    </div>
                    <p class="text-red-100 mb-4 text-sm md:text-base">
                        เลขที่ออกบ่อยที่สุดจากทุกสำนักในงวดนี้ (อันดับ 1 สำหรับสมาชิก VIP)
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <div 
                            v-for="(hot, index) in hotNumbers" 
                            :key="`${index}-${hot.number}`"
                            class="bg-white rounded-xl px-6 py-4 shadow-lg border-2 border-yellow-300 hover:border-yellow-400 hover:scale-105 transition-all duration-200 relative"
                        >
                            <div class="flex items-center gap-3">
                                <div>
                                    <div v-if="hot.is_vip_locked" class="flex flex-col items-center">
                                        <span class="inline-flex items-center gap-1 text-xs font-bold bg-amber-100 text-amber-800 px-2.5 py-0.5 rounded-full mb-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            <span>อันดับ 1 (VIP)</span>
                                        </span>
                                        <Link :href="route('vip-request.create')" class="text-xs font-semibold text-red-600 hover:underline">
                                            ปลดล็อกเลข ({{ hot.count }} สำนัก)
                                        </Link>
                                    </div>
                                    <template v-else>
                                        <div class="text-3xl font-bold text-red-600">{{ hot.number }}</div>
                                        <div class="text-xs text-gray-600 mt-1">
                                            {{ hot.count }} สำนัก
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="(n, index) in numbers.data" 
                        :key="n.id" 
                        :class="[
                            `bg-gradient-to-br ${getCardColor(index).bg} rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 relative`,
                            n.is_currently_promoted ? 'border-4 border-yellow-400 ring-2 ring-yellow-300/60' : `border-2 ${getCardColor(index).border}`
                        ]"
                    >
                        <!-- Header -->
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                    <span class="text-xs font-medium text-gray-600">สำนัก</span>
                                    <span
                                        v-if="n.is_currently_promoted"
                                        class="inline-flex items-center gap-1 rounded-full bg-yellow-400 px-2.5 py-0.5 text-xs font-bold text-gray-900 shadow-sm"
                                    >
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                        </svg>
                                        <span>สำนักแนะนำ</span>
                                    </span>
                                    <span
                                        v-if="n.is_vip_source"
                                        class="inline-flex items-center gap-1 rounded-full bg-amber-600 px-2.5 py-0.5 text-xs font-bold text-white shadow-sm"
                                    >
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z" />
                                        </svg>
                                        <span>VIP</span>
                                    </span>
                                </div>
                                <div class="text-xl font-bold text-gray-900">{{ n.source?.name }}</div>
                                <p
                                    v-if="n.source?.description"
                                    class="mt-1 text-xs text-gray-700 bg-white/60 rounded-lg px-2.5 py-1.5 border border-white/80 line-clamp-2"
                                    :title="n.source.description"
                                >
                                    {{ n.source.description }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Date & View Count -->
                        <div class="mb-4 pb-4 border-b border-gray-300 flex items-center justify-between gap-2">
                            <div class="text-sm font-medium text-gray-700">
                                {{ formatThaiDate(n.draw_date) }}
                            </div>
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-gray-600 bg-white/70 px-2.5 py-0.5 rounded-full border border-white">
                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>{{ (n.source?.view_count || 0).toLocaleString() }} ครั้ง</span>
                            </span>
                        </div>

                        <!-- Numbers -->
                        <div class="grid grid-cols-3 gap-3 mb-4">
                            <div class="rounded-xl bg-white/80 backdrop-blur-sm p-4 border-2 border-white shadow-md text-center">
                                <div class="text-xs font-medium text-gray-600 mb-1">2 ตัว</div>
                                <div v-if="n.is_vip_locked" class="inline-flex items-center justify-center gap-1 text-xs font-bold text-amber-700 py-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <span>VIP</span>
                                </div>
                                <div v-else class="text-2xl font-bold text-red-600">{{ n.two_digit || '-' }}</div>
                            </div>
                            <div class="rounded-xl bg-white/80 backdrop-blur-sm p-4 border-2 border-white shadow-md text-center">
                                <div class="text-xs font-medium text-gray-600 mb-1">3 ตัว</div>
                                <div v-if="n.is_vip_locked" class="inline-flex items-center justify-center gap-1 text-xs font-bold text-amber-700 py-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <span>VIP</span>
                                </div>
                                <div v-else class="text-2xl font-bold text-red-600">{{ n.three_digit || '-' }}</div>
                            </div>
                            <div class="rounded-xl bg-white/80 backdrop-blur-sm p-4 border-2 border-white shadow-md text-center">
                                <div class="text-xs font-medium text-gray-600 mb-1">วิ่ง</div>
                                <div class="text-sm font-bold text-red-600 truncate" :title="fmtRunning(n.running_numbers)">
                                    {{ fmtRunning(n.running_numbers) }}
                                </div>
                            </div>
                        </div>

                        <!-- ปุ่มปลดล็อก VIP สำหรับการ์ดที่ถูกล็อก -->
                        <div v-if="n.is_vip_locked" class="mb-4">
                            <Link
                                :href="route('vip-request.create')"
                                class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-500 text-gray-900 font-bold text-xs shadow hover:from-amber-600 hover:to-yellow-600 transition-all"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                </svg>
                                <span>สมัคร VIP เพื่อเปิดดูเลข 2 ตัว / 3 ตัว</span>
                            </Link>
                        </div>

                        <!-- Vote Button (แสดงทุกสำนัก) -->
                        <div class="pt-4 border-t border-gray-300">
                            <div class="flex items-center justify-center">
                                <VoteButton 
                                    :source-id="n.source_id"
                                    :draw-date="n.draw_date"
                                    :initial-vote-count="n.vote_count || 0"
                                />
                            </div>
                        </div>

                        <!-- Footer & Share -->
                        <div class="pt-4 border-t border-gray-300 flex flex-col gap-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs text-gray-600 font-medium">ส่งต่อเลขสำนักนี้:</span>
                                <ShareButtons
                                    compact
                                    :title="`เลขเด็ดสำนัก ${n.source?.name}`"
                                    :text="`🎯 แนวทางเลขเด็ดสำนัก '${n.source?.name}' งวด ${formatThaiDate(n.draw_date)} | 2 ตัว: ${n.is_vip_locked ? '🔒VIP' : (n.two_digit || '-')} | 3 ตัว: ${n.is_vip_locked ? '🔒VIP' : (n.three_digit || '-')} | วิ่ง: ${fmtRunning(n.running_numbers)}`"
                                />
                            </div>
                            <div class="text-[11px] text-gray-500">
                                <span class="font-medium">อัปเดตล่าสุด:</span> {{ formatThaiDateTime(n.updated_at) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="flex flex-wrap gap-2 justify-center">
                    <Link
                        v-for="link in numbers.links"
                        :key="link.label"
                        :href="link.url || ''"
                        class="px-4 py-2 rounded-md border-2 text-sm font-medium transition-all"
                        :class="link.active 
                            ? 'bg-red-600 text-white border-red-600 shadow-md' 
                            : 'bg-white text-gray-700 border-red-200 hover:bg-red-50 hover:border-red-300'"
                        v-html="link.label"
                    />
                </div>

                <!-- แบนเนอร์เชิญชวนสำนักหวยลงโปรโมท -->
                <div class="mt-8 bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 rounded-2xl p-6 sm:p-8 border-2 border-yellow-400 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4 text-white">
                    <div>
                        <div class="inline-flex items-center gap-1 rounded-full bg-yellow-400 px-3 py-0.5 text-xs font-bold text-gray-900 mb-2">
                            ⭐ สำหรับเจ้าของสำนักหวย & เพจใบ้หวย
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold">
                            ต้องการปักหมุดสำนักของคุณขึ้นแถวแรกสุด + แสดงบนหน้าแรกของเว็บ?
                        </h3>
                        <p class="text-sm text-gray-300 mt-1">
                            เปิดโชว์เลขครบ 100% พร้อมใส่ชื่อเพจ / Line ID บนการ์ดเพื่อดึงคนเข้ากลุ่มของคุณโดยตรง
                        </p>
                    </div>
                    <Link
                        :href="route('advertise.index')"
                        class="shrink-0 px-6 py-3 rounded-xl bg-yellow-400 text-gray-900 font-extrabold text-sm shadow-lg hover:bg-yellow-300 transition-all"
                    >
                        ดูเรทราคาโปรโมทสำนัก →
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
