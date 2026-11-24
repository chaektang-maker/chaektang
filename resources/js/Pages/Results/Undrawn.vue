<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    numbers: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const searchStr = ref(props.filters.search || '');

let searchTimeout = null;
watch(searchStr, (newValue) => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('undrawn-first-prizes.index'),
            { search: newValue, page: 1 },
            { preserveState: true, replace: true }
        );
    }, 500);
});

const goToPage = (url) => {
    if (url) {
        router.get(url, { search: searchStr.value }, { preserveState: true, replace: true });
    }
};
</script>

<template>
    <Head>
        <title>เลข 6 หลักทุกรางวัลที่ยังไม่เคยออก - แจกตัง</title>
        <meta name="description" content="สถิติเลข 6 หลักของทุกรางวัล (รางวัลที่ 1-5) สลากกินแบ่งรัฐบาล ที่ยังไม่เคยออกเลย" />
    </Head>

    <PublicLayout>
        <div class="py-6">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Header -->
                <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-xl p-6 shadow-lg text-center">
                    <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">🎰 เลข 6 หลักทุกรางวัลที่ยังไม่เคยออก</h1>
                    <p class="text-red-100 text-sm md:text-base">จากข้อมูลสถิติผลสลากกินแบ่งรัฐบาลย้อนหลังทั้งหมด</p>
                </div>

                <!-- Search and Stats Box -->
                <div class="bg-white rounded-xl shadow-md p-4 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="w-full md:w-1/2">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">ค้นหาตัวเลข (เช่น 12 หรือ 1234)</label>
                        <input 
                            type="text" 
                            id="search" 
                            v-model="searchStr"
                            placeholder="พิมพ์เลขที่ต้องการค้นหา..." 
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                        >
                    </div>
                    
                    <div class="w-full md:w-auto text-center md:text-right">
                        <div class="text-sm text-gray-500">พบหมายเลขที่ยังไม่เคยออกทั้งหมด</div>
                        <div class="text-2xl font-bold text-red-600">{{ new Intl.NumberFormat().format(numbers.total) }} <span class="text-base font-normal text-gray-600">หมายเลข</span></div>
                    </div>
                </div>

                <!-- Numbers Grid -->
                <div v-if="numbers.data.length > 0" class="bg-white rounded-xl shadow-md overflow-hidden p-6 text-center">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                        <div 
                            v-for="(number, index) in numbers.data" 
                            :key="index"
                            class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-lg font-bold tracking-widest text-gray-800 hover:bg-red-50 hover:border-red-200 transition-colors cursor-default"
                        >
                            {{ number }}
                        </div>
                    </div>
                </div>
                <div v-else class="bg-white rounded-xl shadow-md p-10 text-center">
                    <div class="text-4xl mb-4">🔍</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">ไม่พบหมายเลข</h3>
                    <p class="text-gray-500">
                        {{ searchStr ? 'เลขนี้อาจจะเคยออกไปแล้ว หรือรูปแบบการค้นหาไม่ถูกต้อง' : 'ไม่มีข้อมูลให้แสดง' }}
                    </p>
                </div>

                <!-- Pagination UI -->
                <div v-if="numbers.last_page > 1" class="flex flex-col items-center gap-4 py-4">
                    <div class="text-sm text-gray-600">
                        แสดงหน้า 
                        <span class="font-bold">{{ numbers.current_page }}</span> 
                        จาก 
                        <span class="font-bold">{{ numbers.last_page }}</span>
                    </div>
                    
                    <div class="flex flex-wrap items-center justify-center gap-1">
                        <!-- ปุ่มหน้าแรก -->
                        <button 
                            @click="goToPage(numbers.first_page_url)" 
                            :disabled="numbers.current_page === 1"
                            class="px-3 py-1.5 rounded border text-sm font-medium transition-colors"
                            :class="numbers.current_page === 1 ? 'border-gray-200 text-gray-400 bg-gray-50 cursor-not-allowed' : 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50'"
                        >
                            &laquo; หน้าแรก
                        </button>

                        <!-- ปุ่มก่อนหน้า -->
                        <button 
                            @click="goToPage(numbers.prev_page_url)" 
                            :disabled="!numbers.prev_page_url"
                            class="px-3 py-1.5 rounded border text-sm font-medium transition-colors"
                            :class="!numbers.prev_page_url ? 'border-gray-200 text-gray-400 bg-gray-50 cursor-not-allowed' : 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50'"
                        >
                            &lt;
                        </button>

                        <!-- Links ตัวเลขบางส่วน (แสดงแบบง่าย ๆ ถัดไป 2 หน้าและก่อน 2 หน้า) -->
                        <template v-for="link in numbers.links.slice(1, -1)" :key="link.label">
                            <button 
                                v-if="(parseInt(link.label) >= numbers.current_page - 2) && (parseInt(link.label) <= numbers.current_page + 2)"
                                @click="goToPage(link.url)" 
                                class="px-3 py-1.5 rounded border text-sm font-medium transition-colors"
                                :class="link.active ? 'border-red-500 text-white bg-red-600' : 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50'"
                                v-html="link.label"
                            ></button>
                        </template>

                        <!-- ปุ่มถัดไป -->
                        <button 
                            @click="goToPage(numbers.next_page_url)" 
                            :disabled="!numbers.next_page_url"
                            class="px-3 py-1.5 rounded border text-sm font-medium transition-colors"
                            :class="!numbers.next_page_url ? 'border-gray-200 text-gray-400 bg-gray-50 cursor-not-allowed' : 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50'"
                        >
                            &gt;
                        </button>
                        
                        <!-- ปุ่มหน้าสุดท้าย -->
                        <button 
                            @click="goToPage(numbers.last_page_url)" 
                            :disabled="numbers.current_page === numbers.last_page"
                            class="px-3 py-1.5 rounded border text-sm font-medium transition-colors"
                            :class="numbers.current_page === numbers.last_page ? 'border-gray-200 text-gray-400 bg-gray-50 cursor-not-allowed' : 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50'"
                        >
                            หน้าสุดท้าย &raquo;
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </PublicLayout>
</template>
