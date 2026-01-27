<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

const props = defineProps({
    filters: Object,
    availableDrawDates: Array,
    numbers: Object,
    hotNumbers: Array,
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
    <Head title="รวมเลขเด็ดงวดนี้" />

    <PublicLayout>
        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                        <span class="text-red-600">รวมเลขเด็ดงวดนี้</span>
                    </h1>
                    <p class="text-xl text-gray-600">รวมเลขจากหลายสำนัก เลือกดูตามงวดและการเรียง</p>
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
                                <option v-for="(label, value) in availableDrawDates" :key="value" :value="value">{{ label }}</option>
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
                        เลขที่ออกบ่อยที่สุดจากทุกสำนักในงวดนี้
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <div 
                            v-for="(hot, index) in hotNumbers" 
                            :key="hot.number"
                            class="bg-white rounded-xl px-6 py-4 shadow-lg border-2 border-yellow-300 hover:border-yellow-400 hover:scale-105 transition-all duration-200"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-600 text-white font-bold text-lg">
                                    {{ index + 1 }}
                                </div>
                                <div>
                                    <div class="text-3xl font-bold text-red-600">{{ hot.number }}</div>
                                    <div class="text-xs text-gray-600 mt-1">
                                        {{ hot.count }} สำนัก
                                    </div>
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
                        :class="`bg-gradient-to-br ${getCardColor(index).bg} rounded-2xl shadow-lg border-2 ${getCardColor(index).border} p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-1`"
                    >
                        <!-- Header -->
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="flex-1">
                                <div class="text-xs font-medium text-gray-600 mb-1">สำนัก</div>
                                <div class="text-xl font-bold text-gray-900">{{ n.source?.name }}</div>
                            </div>
                            <div :class="`${getCardColor(index).accent} text-white rounded-lg px-3 py-1 text-xs font-semibold`">
                                งวด
                            </div>
                        </div>
                        
                        <!-- Date -->
                        <div class="mb-4 pb-4 border-b border-gray-300">
                            <div class="text-sm font-medium text-gray-700">
                                {{ formatThaiDate(n.draw_date) }}
                            </div>
                        </div>

                        <!-- Numbers -->
                        <div class="grid grid-cols-3 gap-3 mb-4">
                            <div class="rounded-xl bg-white/80 backdrop-blur-sm p-4 border-2 border-white shadow-md text-center">
                                <div class="text-xs font-medium text-gray-600 mb-1">2 ตัว</div>
                                <div class="text-2xl font-bold text-red-600">{{ n.two_digit || '-' }}</div>
                            </div>
                            <div class="rounded-xl bg-white/80 backdrop-blur-sm p-4 border-2 border-white shadow-md text-center">
                                <div class="text-xs font-medium text-gray-600 mb-1">3 ตัว</div>
                                <div class="text-2xl font-bold text-red-600">{{ n.three_digit || '-' }}</div>
                            </div>
                            <div class="rounded-xl bg-white/80 backdrop-blur-sm p-4 border-2 border-white shadow-md text-center">
                                <div class="text-xs font-medium text-gray-600 mb-1">วิ่ง</div>
                                <div class="text-sm font-bold text-red-600 truncate" :title="fmtRunning(n.running_numbers)">
                                    {{ fmtRunning(n.running_numbers) }}
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="pt-4 border-t border-gray-300">
                            <div class="text-xs text-gray-600">
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
            </div>
        </div>
    </PublicLayout>
</template>
