<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    allStatistics: Object,
    yearRangeStatistics: Array,
    filters: Object,
});

const startYear = ref(props.filters?.start_year || 2552);
const endYear = ref(props.filters?.end_year || (new Date().getFullYear() + 543));
const type = ref(props.filters?.type || 'last_two');
const limit = ref(props.filters?.limit || 20);

const applyFilters = () => {
    router.get(route('statistics.index'), {
        start_year: startYear.value,
        end_year: endYear.value,
        type: type.value,
        limit: limit.value,
    }, { preserveState: true, replace: true });
};

const resetFilters = () => {
    startYear.value = 2552;
    endYear.value = new Date().getFullYear() + 543;
    type.value = 'last_two';
    limit.value = 20;
    applyFilters();
};
</script>

<template>
    <Head title="สถิติหวยย้อนหลัง" />
    
    <PublicLayout>
        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                        <span class="text-red-600">สถิติหวยย้อนหลัง</span>
                    </h1>
                    <p class="text-xl text-gray-600">วิเคราะห์ข้อมูลหวยย้อนหลังตั้งแต่ปี 2552</p>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl shadow-lg border border-red-100 p-6 hover:shadow-xl transition-shadow">
                        <div class="text-sm text-gray-600 mb-2">จำนวนงวดหวยทั้งหมด</div>
                        <div class="text-4xl font-bold text-red-600">{{ allStatistics?.total_lottos || 0 }}</div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-lg border border-red-100 p-6 hover:shadow-xl transition-shadow">
                        <div class="text-sm text-gray-600 mb-2">จำนวนรางวัลที่ 1</div>
                        <div class="text-4xl font-bold text-red-600">{{ allStatistics?.total_prizes || 0 }}</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-2xl shadow-lg border border-red-100 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        กรองข้อมูลตามช่วงปี
                    </h2>
                    <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <InputLabel value="ปีเริ่มต้น (พ.ศ.)" />
                            <TextInput v-model.number="startYear" type="number" class="mt-1 block w-full" min="2552" />
                        </div>
                        <div>
                            <InputLabel value="ปีสิ้นสุด (พ.ศ.)" />
                            <TextInput v-model.number="endYear" type="number" class="mt-1 block w-full" min="2552" />
                        </div>
                        <div>
                            <InputLabel value="ประเภท" />
                            <select v-model="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                <option value="last_two">เลขท้าย 2 ตัว</option>
                                <option value="last_three">เลขท้าย 3 ตัว</option>
                                <option value="front_three">เลขหน้า 3 ตัว</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-all font-medium text-sm shadow-md">
                                ค้นหา
                            </button>
                            <button type="button" @click="resetFilters" class="px-6 py-2 bg-white text-red-600 rounded-md border-2 border-red-600 hover:bg-red-50 transition-all font-medium text-sm">
                                รีเซ็ต
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Statistics Tables -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- เลขท้าย 2 ตัวที่ออกบ่อยที่สุด -->
                    <div class="bg-white rounded-2xl shadow-lg border border-red-100 p-6 hover:shadow-xl transition-shadow">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-red-600 rounded-full"></span>
                            เลขท้าย 2 ตัวที่ออกบ่อยที่สุด
                        </h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-red-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">อันดับ</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">เลข</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">จำนวนครั้ง</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(item, index) in allStatistics?.top_last_two_digits?.slice(0, 10)" :key="item.number" class="hover:bg-red-50 transition-colors">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ index + 1 }}</td>
                                        <td class="px-4 py-3 text-sm font-bold text-red-600 text-lg">{{ item.number }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ item.count }} ครั้ง</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- เลขท้าย 3 ตัวที่ออกบ่อยที่สุด -->
                    <div class="bg-white rounded-2xl shadow-lg border border-red-100 p-6 hover:shadow-xl transition-shadow">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-red-600 rounded-full"></span>
                            เลขท้าย 3 ตัวที่ออกบ่อยที่สุด
                        </h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-red-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">อันดับ</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">เลข</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">จำนวนครั้ง</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(item, index) in allStatistics?.top_last_three_digits?.slice(0, 10)" :key="item.number" class="hover:bg-red-50 transition-colors">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ index + 1 }}</td>
                                        <td class="px-4 py-3 text-sm font-bold text-red-600 text-lg">{{ item.number }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ item.count }} ครั้ง</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- เลขหน้า 3 ตัวที่ออกบ่อยที่สุด -->
                    <div class="bg-white rounded-2xl shadow-lg border border-red-100 p-6 hover:shadow-xl transition-shadow">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-red-600 rounded-full"></span>
                            เลขหน้า 3 ตัวที่ออกบ่อยที่สุด
                        </h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-red-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">อันดับ</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">เลข</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">จำนวนครั้ง</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(item, index) in allStatistics?.top_front_three_digits?.slice(0, 10)" :key="item.number" class="hover:bg-red-50 transition-colors">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ index + 1 }}</td>
                                        <td class="px-4 py-3 text-sm font-bold text-red-600 text-lg">{{ item.number }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ item.count }} ครั้ง</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- เลข 2 ตัวสุดท้ายของรางวัลที่ 1 ที่ออกบ่อยที่สุด -->
                    <div class="bg-white rounded-2xl shadow-lg border border-red-100 p-6 hover:shadow-xl transition-shadow">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-red-600 rounded-full"></span>
                            เลข 2 ตัวสุดท้ายของรางวัลที่ 1 ที่ออกบ่อยที่สุด
                        </h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-red-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">อันดับ</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">เลข</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">จำนวนครั้ง</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(item, index) in allStatistics?.top_first_prize_last_two_digits?.slice(0, 10)" :key="item.number" class="hover:bg-red-50 transition-colors">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ index + 1 }}</td>
                                        <td class="px-4 py-3 text-sm font-bold text-red-600 text-lg">{{ item.number }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ item.count }} ครั้ง</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Year Range Statistics -->
                <div v-if="yearRangeStatistics && yearRangeStatistics.length > 0" class="bg-white rounded-2xl shadow-lg border border-red-100 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 bg-red-600 rounded-full"></span>
                        สถิติ{{ type === 'last_two' ? 'เลขท้าย 2 ตัว' : type === 'last_three' ? 'เลขท้าย 3 ตัว' : 'เลขหน้า 3 ตัว' }} 
                        ที่ออกบ่อยที่สุด (ปี {{ startYear }} - {{ endYear }})
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">อันดับ</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">เลข</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">จำนวนครั้ง</th>
                                </tr>
                            </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(item, index) in yearRangeStatistics" :key="item.number" class="hover:bg-red-50 transition-colors">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ index + 1 }}</td>
                                        <td class="px-4 py-3 text-sm font-bold text-red-600 text-lg">{{ item.number }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ item.count }} ครั้ง</td>
                                    </tr>
                                </tbody>
                        </table>
                    </div>
                </div>

                <!-- Additional Statistics -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- เลขตัวแรกของรางวัลที่ 1 -->
                    <div class="bg-white rounded-2xl shadow-lg border border-red-100 p-6 hover:shadow-xl transition-shadow">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-red-600 rounded-full"></span>
                            เลขตัวแรกของรางวัลที่ 1 ที่ออกบ่อยที่สุด
                        </h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-red-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">อันดับ</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">เลข</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">จำนวนครั้ง</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(item, index) in allStatistics?.top_first_prize_first_digits" :key="item.digit" class="hover:bg-red-50 transition-colors">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ index + 1 }}</td>
                                        <td class="px-4 py-3 text-sm font-bold text-red-600 text-lg">{{ item.digit }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ item.count }} ครั้ง</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- เลขตัวสุดท้ายของรางวัลที่ 1 -->
                    <div class="bg-white rounded-2xl shadow-lg border border-red-100 p-6 hover:shadow-xl transition-shadow">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-red-600 rounded-full"></span>
                            เลขตัวสุดท้ายของรางวัลที่ 1 ที่ออกบ่อยที่สุด
                        </h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-red-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">อันดับ</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">เลข</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">จำนวนครั้ง</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(item, index) in allStatistics?.top_first_prize_last_digits" :key="item.digit" class="hover:bg-red-50 transition-colors">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ index + 1 }}</td>
                                        <td class="px-4 py-3 text-sm font-bold text-red-600 text-lg">{{ item.digit }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ item.count }} ครั้ง</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
