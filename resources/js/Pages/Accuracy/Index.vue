<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    scores: Array,
    histories: Array,
    trackRecords: Array,
    recommendedSourceIds: Array,
    lastDrawDates: Array,
    filters: Object,
});

const type = ref(props.filters?.type || 'two_digit');
const sortBy = ref(props.filters?.sort || 'accuracy');

const typeLabels = {
    two_digit: '2 ตัว',
    three_digit: '3 ตัว',
    running: 'เลขวิ่ง',
};

const applyFilters = () => {
    router.get(route('accuracy.index'), {
        type: type.value,
        sort: sortBy.value,
    }, { preserveState: true, replace: true });
};

const topScores = computed(() => {
    return props.scores.slice(0, 10);
});
</script>

<template>
    <Head>
        <title>ตารางคะแนนความแม่นยำ - แจกตัง | เปรียบเทียบความแม่นยำของสำนัก</title>
        <meta name="description" content="ตารางคะแนนความแม่นยำของสำนักหวย หวยเลขเด็ด เปรียบเทียบความแม่นยำในการทำนายเลข 2 ตัว 3 ตัว และเลขวิ่ง" />
        <meta name="keywords" content="ตารางคะแนน, ความแม่นยำ, สำนักหวย, หวยเลขเด็ด, เปรียบเทียบสำนัก, คะแนนสำนัก" />
        <meta property="og:title" content="ตารางคะแนนความแม่นยำ - แจกตัง" />
        <meta property="og:description" content="ตารางคะแนนความแม่นยำของสำนักหวย หวยเลขเด็ด เปรียบเทียบความแม่นยำในการทำนายเลข" />
        <meta name="twitter:title" content="ตารางคะแนนความแม่นยำ - แจกตัง" />
        <meta name="twitter:description" content="ตารางคะแนนความแม่นยำของสำนักหวย หวยเลขเด็ด เปรียบเทียบความแม่นยำในการทำนายเลข" />
    </Head>

    <PublicLayout>
        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">จัดอันดับความแม่น — สำนักไหนตัวจริง?</h1>
                        <p class="text-sm text-gray-600 mt-1">สถิติ 10 งวดล่าสุด: เข้าตรง / ตัวกลับ / เฉียด งวดนี้เชื่อใครดี มีตัวเลขยืนยัน</p>
                    </div>
                </div>

                <!-- Filters: ใช้กับตารางคะแนนความแม่นยำด้านล่าง -->
                <div class="bg-white shadow-sm rounded-lg p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ประเภทเลข</label>
                            <select v-model="type" @change="applyFilters" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="two_digit">2 ตัว</option>
                                <option value="three_digit">3 ตัว</option>
                                <option value="running">เลขวิ่ง</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">เรียงตาม</label>
                            <select v-model="sortBy" @change="applyFilters" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="accuracy">% เข้า (สูงสุด)</option>
                                <option value="total_draws">จำนวนงวด (มากสุด)</option>
                                <option value="consecutive">เข้าติดกัน (มากสุด)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Track Record: สถิติ 10 งวดล่าสุด -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-amber-50">
                        <h2 class="text-xl font-semibold text-gray-900">สถิติ — ใน 10 งวดที่ผ่านมา</h2>
                        <p class="text-sm text-gray-600 mt-1">จำนวนงวดที่สำนักให้เลขเข้า (เข้าตรง / ตัวกลับ / เฉียด) ไม่มโน มีสถิติยืนยัน</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สำนัก</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">เข้าตรง (งวด)</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">ตัวกลับ (งวด)</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">เฉียด (งวด)</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">สรุป 10 งวด</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="row in (trackRecords || [])" :key="row.source_id"
                                    :class="[
                                        'hover:bg-gray-50',
                                        row.is_recommended ? 'bg-amber-50/60' : ''
                                    ]">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-medium text-gray-900">{{ row.source_name }}</span>
                                        <span v-if="row.is_recommended"
                                            class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-200 text-amber-900">
                                            สำนักแนะนำประจำงวด
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        <span class="font-semibold text-green-700">{{ row.draws_direct }}</span>
                                        <span class="text-gray-500">/{{ row.last_10_draws_count }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-blue-700">
                                        {{ row.draws_reverse }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-600">
                                        {{ row.draws_near }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">
                                        ตรง {{ row.total_hit_direct }} · กลับ {{ row.total_hit_reverse }} · เฉียด {{ row.total_hit_near }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span v-if="row.is_recommended"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                            เข้าตรงติด {{ row.consecutive_direct_draws }} งวด
                                        </span>
                                        <span v-else class="text-xs text-gray-400">—</span>
                                    </td>
                                </tr>
                                <tr v-if="!trackRecords || trackRecords.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        ยังไม่มีข้อมูล Track Record (รันคำนวณหลังมีผลหวยและเลขสำนัก)
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ตารางเดิม: สถิติสะสมแยกประเภทเลข (สำหรับคนที่อยากดู % สะสม หรือแยก 2 ตัว/3 ตัว/เลขวิ่ง) -->
                <details class="bg-white shadow-sm rounded-lg overflow-hidden" :open="false">
                    <summary class="px-6 py-4 border-b border-gray-200 cursor-pointer list-none flex items-center justify-between hover:bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-800">ตารางคะแนนความแม่นยำ แยกตามประเภทเลข (สถิติสะสม)</h2>
                        <span class="text-sm text-gray-500">กดเพื่อขยาย/ย่อ</span>
                    </summary>
                    <div class="p-6 space-y-4">
                        <!-- Chart -->
                        <div v-if="histories && histories.length > 0">
                            <h3 class="text-base font-semibold text-gray-900 mb-3">กราฟความแม่นยำย้อนหลัง (10 งวดล่าสุด) — {{ typeLabels[type] }}</h3>
                            <div class="h-48 flex items-end gap-2">
                                <div v-for="(item, index) in histories" :key="index" class="flex-1 flex flex-col items-center">
                                    <div class="w-full bg-gray-200 rounded-t relative" :style="{ height: `${item.percentage}%` }">
                                        <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 text-xs text-gray-600 whitespace-nowrap">
                                            {{ item.percentage }}%
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-2 text-center" style="writing-mode: vertical-rl; text-orientation: mixed;">
                                        {{ item.draw_date }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">อันดับ</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สำนัก</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">% เข้า</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">เข้า</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">ทั้งหมด</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">เข้าติดกัน</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(score, index) in scores" :key="score.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ score.source_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <span class="font-semibold" :class="{
                                                'text-green-600': score.accuracy_percentage >= 50,
                                                'text-yellow-600': score.accuracy_percentage >= 30 && score.accuracy_percentage < 50,
                                                'text-red-600': score.accuracy_percentage < 30,
                                            }">
                                                {{ score.accuracy_percentage.toFixed(2) }}%
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">{{ score.correct_count }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">{{ score.total_draws }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="{
                                                'bg-green-100 text-green-800': score.consecutive_correct >= 3,
                                                'bg-yellow-100 text-yellow-800': score.consecutive_correct >= 1 && score.consecutive_correct < 3,
                                                'bg-gray-100 text-gray-800': score.consecutive_correct === 0,
                                            }">
                                                {{ score.consecutive_correct }} งวด
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="scores.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">ยังไม่มีข้อมูลคะแนน</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </PublicLayout>
</template>
