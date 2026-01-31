<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    availableDraws: {
        type: Array,
        default: () => [],
    },
    selectedLottoId: {
        type: String,
        default: '',
    },
    result: {
        type: Object,
        default: null,
    },
});

const selectedDraw = ref(props.selectedLottoId);

watch(selectedDraw, (newValue) => {
    if (newValue) {
        router.get(route('results.index'), { lotto_id: newValue }, { preserveState: true, replace: true });
    }
});

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
</script>

<template>
    <Head>
        <title>ตรวจผลรางวัลย้อนหลัง - แจกตัง | ผลหวยย้อนหลัง</title>
        <meta name="description" content="ตรวจผลรางวัลย้อนหลัง ผลหวยย้อนหลัง ผลสลากกินแบ่งรัฐบาลย้อนหลัง หวยเลขเด็ด ตรวจหวยงวดที่ผ่านมา" />
        <meta name="keywords" content="ตรวจหวยย้อนหลัง, ผลหวยย้อนหลัง, ตรวจผลรางวัล, ผลสลากกินแบ่ง, หวยเลขเด็ด, ผลหวยงวดที่ผ่านมา, ผลรางวัลย้อนหลัง" />
        <meta property="og:title" content="ตรวจหวยย้อนหลัง - แจกตัง" />
        <meta property="og:description" content="ตรวจผลรางวัลย้อนหลัง ผลหวยย้อนหลัง ผลสลากกินแบ่งรัฐบาลย้อนหลัง หวยเลขเด็ด" />
        <meta name="twitter:title" content="ตรวจผลรางวัลย้อนหลัง - แจกตัง" />
        <meta name="twitter:description" content="ตรวจผลรางวัลย้อนหลัง ผลหวยย้อนหลัง ผลสลากกินแบ่งรัฐบาลย้อนหลัง หวยเลขเด็ด" />
    </Head>

    <PublicLayout>
        <div class="py-6">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-4">
                <!-- Header + เลือกงวด -->
                <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-xl p-4 shadow-lg">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="text-center md:text-left">
                            <h1 class="text-xl md:text-2xl font-bold text-white">🏆 ผลการออกรางวัล</h1>
                            <p class="text-red-100 text-sm">สลากกินแบ่งรัฐบาล</p>
                        </div>
                        <select 
                            v-model="selectedDraw" 
                            class="w-full md:w-auto md:min-w-[250px] rounded-lg border-0 shadow-sm focus:ring-2 focus:ring-yellow-400 text-sm"
                        >
                            <option value="">-- เลือกงวด --</option>
                            <option v-for="draw in availableDraws" :key="draw.value" :value="draw.value">
                                {{ draw.label }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- ผลรางวัล -->
                <div v-if="result" class="space-y-4">
                    <!-- รางวัลหลัก -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <!-- รางวัลที่ 1 -->
                        <div v-if="result.prizes?.first" class="md:col-span-3 bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl p-4 text-center shadow-lg">
                            <div class="text-yellow-900 font-medium text-sm">{{ result.prizes.first.name }}</div>
                            <div class="text-4xl md:text-5xl font-bold text-white tracking-widest drop-shadow-lg my-1">
                                {{ result.prizes.first.numbers[0] || '------' }}
                            </div>
                            <div class="text-yellow-100 text-xs">รางวัลละ {{ result.prizes.first.reward }} บาท</div>
                        </div>

                        <!-- เลขท้าย 2 ตัว -->
                        <div v-if="result.running_numbers?.back_two" class="bg-blue-600 rounded-xl p-3 text-center">
                            <div class="text-blue-100 text-xs">{{ result.running_numbers.back_two.name }}</div>
                            <div class="text-3xl font-bold text-white tracking-widest">
                                {{ result.running_numbers.back_two.numbers[0] || '--' }}
                            </div>
                            <div class="text-blue-200 text-xs">{{ result.running_numbers.back_two.reward }} บาท</div>
                        </div>

                        <!-- เลขหน้า 3 ตัว -->
                        <div v-if="result.running_numbers?.front_three" class="bg-green-600 rounded-xl p-3 text-center">
                            <div class="text-green-100 text-xs">{{ result.running_numbers.front_three.name }}</div>
                            <div class="flex justify-center gap-2 flex-wrap">
                                <span v-for="(num, idx) in result.running_numbers.front_three.numbers" :key="idx" class="text-xl font-bold text-white">
                                    {{ num }}
                                </span>
                            </div>
                            <div class="text-green-200 text-xs">{{ result.running_numbers.front_three.reward }} บาท</div>
                        </div>

                        <!-- เลขท้าย 3 ตัว -->
                        <div v-if="result.running_numbers?.back_three" class="bg-purple-600 rounded-xl p-3 text-center">
                            <div class="text-purple-100 text-xs">{{ result.running_numbers.back_three.name }}</div>
                            <div class="flex justify-center gap-2 flex-wrap">
                                <span v-for="(num, idx) in result.running_numbers.back_three.numbers" :key="idx" class="text-xl font-bold text-white">
                                    {{ num }}
                                </span>
                            </div>
                            <div class="text-purple-200 text-xs">{{ result.running_numbers.back_three.reward }} บาท</div>
                        </div>
                    </div>

                    <!-- รางวัลข้างเคียงรางวัลที่ 1 -->
                    <div v-if="result.prizes?.nearby" class="bg-white rounded-xl p-4 border border-orange-200 shadow">
                        <div class="flex flex-col md:flex-row items-center justify-between gap-2">
                            <div class="text-center md:text-left">
                                <div class="text-orange-600 font-bold text-sm">{{ result.prizes.nearby.name }}</div>
                                <div class="text-gray-500 text-xs">รางวัลละ {{ result.prizes.nearby.reward }} บาท</div>
                            </div>
                            <div class="flex gap-4">
                                <span v-for="(num, idx) in result.prizes.nearby.numbers" :key="idx" class="text-2xl font-bold text-orange-600">
                                    {{ num }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- รางวัลที่ 2 -->
                    <div v-if="result.prizes?.second" class="bg-white rounded-xl p-4 border border-red-200 shadow">
                        <div class="text-center mb-3">
                            <span class="text-red-600 font-bold text-sm">{{ result.prizes.second.name }}</span>
                            <span class="text-gray-500 text-xs ml-2">รางวัลละ {{ result.prizes.second.reward }} บาท</span>
                        </div>
                        <div class="grid grid-cols-3 md:grid-cols-5 gap-2">
                            <span v-for="(num, idx) in result.prizes.second.numbers" :key="idx" class="text-base font-bold text-gray-800 bg-red-50 rounded-lg py-1.5 px-2 text-center">
                                {{ num }}
                            </span>
                        </div>
                    </div>

                    <!-- รางวัลที่ 3 -->
                    <div v-if="result.prizes?.third" class="bg-white rounded-xl p-4 border border-blue-200 shadow">
                        <div class="text-center mb-3">
                            <span class="text-blue-600 font-bold text-sm">{{ result.prizes.third.name }}</span>
                            <span class="text-gray-500 text-xs ml-2">รางวัลละ {{ result.prizes.third.reward }} บาท</span>
                        </div>
                        <div class="grid grid-cols-3 md:grid-cols-5 gap-2">
                            <span v-for="(num, idx) in result.prizes.third.numbers" :key="idx" class="text-base font-bold text-gray-800 bg-blue-50 rounded-lg py-1.5 px-2 text-center">
                                {{ num }}
                            </span>
                        </div>
                    </div>

                    <!-- รางวัลที่ 4 -->
                    <div v-if="result.prizes?.fourth" class="bg-white rounded-xl p-4 border border-green-200 shadow">
                        <div class="text-center mb-3">
                            <span class="text-green-600 font-bold text-sm">{{ result.prizes.fourth.name }}</span>
                            <span class="text-gray-500 text-xs ml-2">รางวัลละ {{ result.prizes.fourth.reward }} บาท</span>
                        </div>
                        <div class="grid grid-cols-3 md:grid-cols-5 gap-2">
                            <span v-for="(num, idx) in result.prizes.fourth.numbers" :key="idx" class="text-sm font-bold text-gray-800 bg-green-50 rounded-lg py-1.5 px-2 text-center">
                                {{ num }}
                            </span>
                        </div>
                    </div>

                    <!-- รางวัลที่ 5 -->
                    <div v-if="result.prizes?.fifth" class="bg-white rounded-xl p-4 border border-purple-200 shadow">
                        <div class="text-center mb-3">
                            <span class="text-purple-600 font-bold text-sm">{{ result.prizes.fifth.name }}</span>
                            <span class="text-gray-500 text-xs ml-2">รางวัลละ {{ result.prizes.fifth.reward }} บาท</span>
                        </div>
                        <div class="grid grid-cols-3 md:grid-cols-5 gap-2">
                            <span v-for="(num, idx) in result.prizes.fifth.numbers" :key="idx" class="text-sm font-bold text-gray-800 bg-purple-50 rounded-lg py-1.5 px-2 text-center">
                                {{ num }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- ไม่มีข้อมูล -->
                <div v-else class="bg-white rounded-xl p-8 text-center shadow">
                    <div class="text-4xl mb-2">📋</div>
                    <div class="text-gray-600">กรุณาเลือกงวดเพื่อดูผลรางวัล</div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
