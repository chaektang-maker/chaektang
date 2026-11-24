<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
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

const refreshResults = () => {
    router.reload({ only: ['result', 'availableDraws'] });
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
                                {{ draw.label }}{{ draw.is_fetched === false ? ' (รอผลรางวัล)' : '' }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- 1. กรณีมีผลรางวัลครบแล้ว (status === 'ready') -->
                <div v-if="result && result.status === 'ready'" class="space-y-4">
                    <div class="bg-white rounded-xl px-4 py-3 border border-gray-200 shadow-sm flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-700">งวดประจำวันที่ {{ result.date_text }}</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                            ✓ ออกรางวัลครบแล้ว
                        </span>
                    </div>

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
                            <div class="flex flex-wrap justify-center gap-4">
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
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2">
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
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2">
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
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2">
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
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2">
                            <span v-for="(num, idx) in result.prizes.fifth.numbers" :key="idx" class="text-sm font-bold text-gray-800 bg-purple-50 rounded-lg py-1.5 px-2 text-center">
                                {{ num }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 2. กรณีตรงกับวันหวยออก (status === 'drawing_today') -->
                <div v-else-if="result && result.status === 'drawing_today'" class="space-y-4">
                    <div class="bg-gradient-to-br from-amber-50 via-yellow-50 to-orange-50 rounded-2xl p-6 sm:p-8 border-2 border-yellow-400 shadow-lg text-center">
                        <div class="inline-flex items-center gap-2 rounded-full bg-red-600 text-white px-4 py-1 text-xs font-bold shadow animate-pulse mb-4">
                            ⏳ กำลังอยู่ในช่วงออกรางวัลสลากกินแบ่งรัฐบาล
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-2">
                            งวดประจำวันที่ {{ result.date_text }}
                        </h2>
                        <p class="text-gray-700 max-w-xl mx-auto text-sm sm:text-base mb-6">
                            ขณะนี้อยู่ระหว่างการออกรางวัล (เวลาประมาณ 14:30 – 16:00 น.)
                            ระบบจะอัปเดตผลรางวัลครบทุกรางวัลทันทีที่สำนักงานสลากกินแบ่งรัฐบาลประกาศผลเสร็จสิ้น
                        </p>

                        <!-- กรอบจำลองรอผลรางวัลหลัก -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 max-w-3xl mx-auto mb-6">
                            <div class="md:col-span-3 bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl p-4 text-center shadow">
                                <div class="text-yellow-900 font-semibold text-sm">รางวัลที่ 1 (รอผลรางวัล)</div>
                                <div class="text-4xl font-extrabold text-white tracking-widest my-1">xxxxxx</div>
                                <div class="text-yellow-100 text-xs">รางวัลละ 6,000,000 บาท</div>
                            </div>
                            <div class="bg-blue-600 rounded-xl p-3 text-center">
                                <div class="text-blue-100 text-xs">เลขท้าย 2 ตัว</div>
                                <div class="text-2xl font-bold text-white tracking-widest my-0.5">xx</div>
                                <div class="text-blue-200 text-xs">2,000 บาท</div>
                            </div>
                            <div class="bg-green-600 rounded-xl p-3 text-center">
                                <div class="text-green-100 text-xs">เลขหน้า 3 ตัว</div>
                                <div class="text-xl font-bold text-white tracking-widest my-1">xxx &nbsp; xxx</div>
                                <div class="text-green-200 text-xs">4,000 บาท</div>
                            </div>
                            <div class="bg-purple-600 rounded-xl p-3 text-center">
                                <div class="text-purple-100 text-xs">เลขท้าย 3 ตัว</div>
                                <div class="text-xl font-bold text-white tracking-widest my-1">xxx &nbsp; xxx</div>
                                <div class="text-purple-200 text-xs">4,000 บาท</div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-center gap-3">
                            <button
                                type="button"
                                @click="refreshResults"
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-red-600 text-white font-bold text-sm shadow-md hover:bg-red-700 transition-all"
                            >
                                🔄 กดรีเฟรชอัปเดตผลรางวัล
                            </button>
                            <Link
                                :href="route('lucky-numbers.index')"
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white border-2 border-red-600 text-red-600 font-bold text-sm hover:bg-red-50 transition-all"
                            >
                                🔥 ดูแนวทางเลขเด็ดงวดนี้
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- 3. กรณียังไม่ถึงวันออกรางวัล (status === 'upcoming') -->
                <div v-else-if="result && result.status === 'upcoming'" class="space-y-4">
                    <div class="bg-white rounded-2xl p-6 sm:p-10 border-2 border-amber-200 shadow-lg text-center">
                        <div class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300 px-4 py-1 text-xs font-bold mb-4">
                            📅 รอถึงกำหนดออกรางวัล (อีก {{ result.days_until_draw }} วัน)
                        </div>
                        <div class="text-5xl mb-3">🎰</div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-2">
                            งวดประจำวันที่ {{ result.date_text }} ยังไม่ถึงวันออกรางวัล
                        </h2>
                        <p class="text-gray-600 max-w-xl mx-auto text-sm sm:text-base mb-8 leading-relaxed">
                            สลากกินแบ่งรัฐบาลงวดนี้จะทำการออกรางวัลในวันที่ <strong>{{ result.date_text }}</strong> (เวลาประมาณ 14:30 – 16:00 น.)
                            ระหว่างรอวันออกรางวัล คุณสามารถเข้ามาส่องแนวทางเลขเด็ดจากทุกสำนักดัง หรือดูตารางสถิติความแม่นยำได้ก่อนใคร!
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-2xl mx-auto">
                            <Link
                                :href="route('lucky-numbers.index')"
                                class="flex flex-col items-center justify-center p-4 rounded-xl bg-gradient-to-br from-red-600 to-red-700 text-white font-bold shadow-md hover:from-red-700 hover:to-red-800 transition-all"
                            >
                                <span class="text-2xl mb-1">🔥</span>
                                <span class="text-sm">รวมเลขเด็ดงวดนี้</span>
                                <span class="text-[11px] text-red-100 font-normal mt-0.5">ส่องเลขดัง & เลขชนทุกสำนัก</span>
                            </Link>
                            <Link
                                :href="route('accuracy.index')"
                                class="flex flex-col items-center justify-center p-4 rounded-xl bg-amber-50 border-2 border-amber-300 text-amber-900 font-bold hover:bg-amber-100 transition-all"
                            >
                                <span class="text-2xl mb-1">📊</span>
                                <span class="text-sm">ตารางสถิติความแม่น</span>
                                <span class="text-[11px] text-amber-700 font-normal mt-0.5">เช็กสำนักที่เข้า 3 งวดติด</span>
                            </Link>
                            <Link
                                :href="route('dream-interpretation.index')"
                                class="flex flex-col items-center justify-center p-4 rounded-xl bg-purple-50 border-2 border-purple-200 text-purple-900 font-bold hover:bg-purple-100 transition-all"
                            >
                                <span class="text-2xl mb-1">🔮</span>
                                <span class="text-sm">ทำนายฝันตีเลข AI</span>
                                <span class="text-[11px] text-purple-700 font-normal mt-0.5">วิเคราะห์เลขมงคลจากความฝัน</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- 4. กรณีเลยวันออกรางวัลแล้วแต่ระบบยังไม่ได้ดึงข้อมูล (status === 'pending_update') -->
                <div v-else-if="result && result.status === 'pending_update'" class="bg-white rounded-2xl p-8 text-center shadow border border-gray-200">
                    <div class="text-5xl mb-3">🔄</div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">
                        งวดประจำวันที่ {{ result.date_text }} อยู่ระหว่างรออัปเดตข้อมูลเข้าสู่ระบบ
                    </h2>
                    <p class="text-sm text-gray-600 max-w-md mx-auto mb-6">
                        ระบบกำลังซิงค์ข้อมูลผลการออกรางวัลของงวดนี้ กรุณารอสักครู่แล้วกดรีเฟรช หรือเลือกดูผลรางวัลของงวดอื่นๆ จากเมนูด้านบน
                    </p>
                    <div class="flex justify-center gap-3">
                        <button
                            type="button"
                            @click="refreshResults"
                            class="px-5 py-2.5 rounded-xl bg-red-600 text-white font-bold text-sm hover:bg-red-700 transition-colors"
                        >
                            🔄 ลองรีเฟรชอีกครั้ง
                        </button>
                        <Link
                            :href="route('lucky-numbers.index')"
                            class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-bold text-sm hover:bg-gray-200 transition-colors"
                        >
                            ดูหน้าเลขเด็ด
                        </Link>
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
