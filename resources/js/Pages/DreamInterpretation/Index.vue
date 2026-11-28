<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ShareButtons from '@/Components/ShareButtons.vue';

const props = defineProps({
    searchQuery: { type: String, default: '' },
    selectedLetter: { type: String, default: 'all' },
    availableLetters: { type: Array, default: () => [] },
    popularKeywords: { type: Array, default: () => [] },
    dreamItems: { type: Array, default: () => [] },
    pagination: {
        type: Object,
        default: () => ({ current_page: 1, last_page: 1, per_page: 20, total: 0 }),
    },
    totalDictionaryCount: { type: Number, default: 0 },
    canViewVip: { type: Boolean, default: false },
    affiliateProducts: { type: Array, default: () => [] },
});

const q = ref(props.searchQuery || '');
const isSearching = ref(false);

const submitSearch = () => {
    isSearching.value = true;
    router.get(
        route('dream-interpretation.index'),
        q.value.trim() ? { q: q.value.trim() } : {},
        {
            preserveScroll: false,
            onFinish: () => {
                isSearching.value = false;
            },
        }
    );
};

const selectKeyword = (kw) => {
    q.value = kw;
    submitSearch();
};

const selectLetter = (letter) => {
    q.value = '';
    router.get(
        route('dream-interpretation.index'),
        letter && letter !== 'all' ? { letter } : {},
        { preserveScroll: true }
    );
};

const clearSearch = () => {
    q.value = '';
    router.get(route('dream-interpretation.index'), {}, { preserveScroll: true });
};

const goToPage = (pageNum) => {
    if (pageNum < 1 || pageNum > (props.pagination?.last_page || 1)) return;
    const params = {};
    if (q.value.trim()) {
        params.q = q.value.trim();
    } else if (props.selectedLetter && props.selectedLetter !== 'all') {
        params.letter = props.selectedLetter;
    }
    if (pageNum > 1) {
        params.page = pageNum;
    }
    router.get(route('dream-interpretation.index'), params, { preserveScroll: false });
};
</script>

<template>
    <Head>
        <title>ตำราทำนายฝัน & ตีเลขมงคลโบราณ (ก-ฮ) - แจกตัง</title>
        <meta
            name="description"
            content="ตำราทำนายฝันโบราณ ค้นหาความหมายความฝันตามหมวดอักษร ก-ฮ พร้อมถอดรหัสเลขมงคล 2 ตัว 3 ตัว และเทียบเลขฝันชนสำนักดังฟรี"
        />
    </Head>

    <PublicLayout>
        <div class="py-10 bg-gradient-to-b from-amber-50/50 via-white to-gray-50 min-h-screen">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-8">
                <!-- Hero & Search Box -->
                <div class="bg-gradient-to-br from-red-700 via-red-600 to-amber-600 rounded-3xl shadow-2xl p-6 sm:p-10 text-white text-center relative overflow-hidden">
                    <div class="relative z-10 max-w-2xl mx-auto space-y-4">
                        <div class="inline-flex items-center gap-1.5 rounded-full bg-yellow-400 text-gray-900 px-3.5 py-1 text-xs font-extrabold shadow">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>ตำราโหราจารย์ทำนายฝันแต่โบราณ ({{ totalDictionaryCount }} หมวดคำฝัน)</span>
                        </div>

                        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
                            ทำนายฝัน & ถอดรหัสเลขมงคล
                        </h1>
                        <p class="text-red-100 text-sm sm:text-base">
                            พิมพ์สิ่งที่คุณฝันเห็น หรือเลือกตามหมวดอักษร <strong>ก - ฮ</strong> เพื่อเปิดอ่านคำทำนายโบราณและดูเลขมงคลได้ฟรีไม่จำกัดครั้ง
                        </p>

                        <!-- Search Form -->
                        <form @submit.prevent="submitSearch" class="max-w-xl mx-auto pt-2">
                            <div class="flex flex-col sm:flex-row gap-2.5">
                                <div class="relative flex-1">
                                    <input
                                        v-model="q"
                                        type="text"
                                        placeholder="พิมพ์สิ่งที่ฝันเห็น เช่น กุญแจ, กระต่ายน้อย, กฐิน..."
                                        class="w-full rounded-2xl border-2 border-yellow-300 bg-white px-5 py-4 text-gray-900 text-base sm:text-lg font-bold shadow-inner focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400"
                                    />
                                    <button
                                        v-if="q"
                                        type="button"
                                        @click="clearSearch"
                                        class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 font-bold text-sm px-1.5"
                                    >
                                        ✕
                                    </button>
                                </div>
                                <button
                                    type="submit"
                                    :disabled="isSearching"
                                    class="inline-flex items-center justify-center gap-2 px-7 py-4 rounded-2xl bg-yellow-400 hover:bg-yellow-300 text-gray-900 font-extrabold text-base sm:text-lg shadow-xl transition-all shrink-0 disabled:opacity-60"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <span>{{ isSearching ? 'กำลังค้นหา...' : 'ค้นหาคำทำนาย' }}</span>
                                </button>
                            </div>
                        </form>

                        <!-- คำฝันยอดนิยมคลิกด่วน -->
                        <div v-if="popularKeywords.length > 0" class="pt-2 flex flex-wrap items-center justify-center gap-1.5">
                            <span class="text-xs text-yellow-200 font-semibold mr-1">คำฝันในตำรา:</span>
                            <button
                                v-for="kw in popularKeywords"
                                :key="kw"
                                type="button"
                                @click="selectKeyword(kw)"
                                class="px-3 py-1 rounded-full bg-white/15 hover:bg-yellow-400 hover:text-gray-900 text-white border border-white/25 text-xs font-bold transition-all"
                            >
                                {{ kw }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- สารบัญหมวดอักษร ก - ฮ -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-4 sm:p-5">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <div class="text-sm font-extrabold text-gray-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                            </svg>
                            <span>เลือกเปิดตำราตามหมวดอักษร (ก - ฮ)</span>
                        </div>
                        <span v-if="searchQuery" class="text-xs font-semibold text-red-600">
                            ผลการค้นหาสำหรับ "{{ searchQuery }}" ({{ dreamItems.length }} รายการ)
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-1.5">
                        <button
                            type="button"
                            @click="selectLetter('all')"
                            :class="[
                                'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all',
                                selectedLetter === 'all' && !searchQuery
                                    ? 'bg-red-600 text-white shadow'
                                    : 'bg-gray-100 text-gray-700 hover:bg-red-50 hover:text-red-600',
                            ]"
                        >
                            ทั้งหมด ({{ totalDictionaryCount }})
                        </button>
                        <button
                            v-for="letter in availableLetters"
                            :key="letter"
                            type="button"
                            @click="selectLetter(letter)"
                            :class="[
                                'w-9 h-9 rounded-xl text-sm font-extrabold flex items-center justify-center transition-all',
                                selectedLetter === letter && !searchQuery
                                    ? 'bg-red-600 text-white shadow'
                                    : 'bg-gray-100 text-gray-800 hover:bg-red-50 hover:text-red-600',
                            ]"
                        >
                            {{ letter }}
                        </button>
                    </div>
                </div>

                <!-- แบนเนอร์สิทธิพิเศษ VIP สำหรับหน้าทำนายฝัน (แสดงเฉพาะผู้ที่ยังไม่ได้เป็น VIP) -->
                <div
                    v-if="!canViewVip"
                    class="bg-gradient-to-r from-amber-500 via-yellow-500 to-amber-600 rounded-2xl shadow-lg p-5 text-gray-900 flex flex-col md:flex-row items-center justify-between gap-4"
                >
                    <div class="flex items-center gap-3.5">
                        <span class="w-11 h-11 rounded-xl bg-gray-900/15 text-gray-900 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base sm:text-lg font-extrabold text-gray-950">
                                สิทธิพิเศษสมาชิก VIP: ปลดล็อกเลขฝัน 3 ตัวตรง & เช็กเลขฝันชนสำนักดังงวดนี้
                            </h3>
                            <p class="text-xs sm:text-sm text-gray-900/90">
                                อ่านคำทำนายและดูเลขมงคล 2 ตัวได้ฟรีทุกคำ! หากต้องการเจาะลึกชุดเลข 3 ตัวตรงและดูว่าเลขฝันตรงกับสำนักไหนในงวดนี้ อัปเกรดเป็น VIP ได้ทันที
                            </p>
                        </div>
                    </div>
                    <Link
                        :href="route('vip-request.create')"
                        class="shrink-0 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-extrabold rounded-xl shadow-md transition-colors text-xs sm:text-sm"
                    >
                        ดูข้อเสนอ VIP →
                    </Link>
                </div>

                <!-- รายการคำทำนายฝัน -->
                <div v-if="dreamItems.length > 0" class="space-y-5">
                    <div
                        v-for="item in dreamItems"
                        :key="item.id"
                        class="bg-white rounded-3xl shadow-lg border-2 border-amber-200/80 overflow-hidden transition hover:shadow-xl"
                    >
                        <!-- หัวข้อสิ่งที่ฝัน -->
                        <div class="bg-gradient-to-r from-amber-50 via-yellow-50/70 to-white px-6 py-4 border-b border-amber-200/70 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="w-11 h-11 rounded-2xl bg-red-600 text-white font-black text-xl flex items-center justify-center shadow-sm shrink-0">
                                    {{ item.category_letter }}
                                </span>
                                <div>
                                    <div class="text-xs font-bold text-amber-800">ตำราทำนายฝันหมวดอักษร {{ item.category_letter }}</div>
                                    <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900">
                                        ฝันเห็น "{{ item.keyword }}"
                                    </h2>
                                </div>
                            </div>

                            <ShareButtons
                                compact
                                :title="`ทำนายฝัน: ฝันเห็น${item.keyword} - เว็บแจกตัง`"
                                :text="`ทำนายฝัน 'ฝันเห็น${item.keyword}': ${item.meaning}\nเลขมงคล 2 ตัว: ${item.two_digit_list?.join(', ') || '-'}`"
                            />
                        </div>

                        <!-- เนื้อหาคำทำนาย & กล่องเลขมงคล -->
                        <div class="p-6 sm:p-7 space-y-6">
                            <!-- คำทำนายโบราณ (เปิดให้อ่านฟรี 100%) -->
                            <div class="space-y-2">
                                <div class="text-xs font-extrabold uppercase tracking-wider text-gray-500">
                                    คำทำนายตามตำราโหราจารย์โบราณ
                                </div>
                                <div class="text-gray-800 text-sm sm:text-base whitespace-pre-line leading-relaxed bg-gray-50/80 rounded-2xl p-4 sm:p-5 border border-gray-200/80">
                                    {{ item.meaning }}
                                </div>
                            </div>

                            <!-- กรณีเป็นความฝันที่ตำราระบุว่า "ไม่มีเลขเสี่ยงโชค / งดเสี่ยงโชค" (เช่น กอดอก) -->
                            <div
                                v-if="item.no_number_note && item.two_digit_list.length === 0 && item.three_digit_count === 0"
                                class="rounded-2xl bg-rose-50 border-2 border-rose-200 p-4 sm:p-5 flex items-start gap-3"
                            >
                                <span class="w-9 h-9 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </span>
                                <div>
                                    <div class="font-extrabold text-rose-900 text-sm sm:text-base">
                                        คำแนะนำด้านตัวเลขมงคล
                                    </div>
                                    <p class="text-xs sm:text-sm text-rose-800 mt-0.5 leading-relaxed">
                                        {{ item.no_number_note }}
                                    </p>
                                </div>
                            </div>

                            <!-- กรณีมีเลขมงคล: แบ่งเป็น เลข 2 ตัว (ดูฟรี) และ เลข 3 ตัว (แสดงเฉพาะเมื่อตำรามีเลข 3 ตัวจริงเท่านั้น) -->
                            <div
                                v-else
                                :class="[
                                    'grid grid-cols-1 gap-4',
                                    item.three_digit_count > 0 || item.matching_sources_count > 0 ? 'md:grid-cols-2' : ''
                                ]"
                            >
                                <!-- กล่องที่ 1: เลขมงคล 2 ตัว (เปิดดูฟรีทุกคน) -->
                                <div class="rounded-2xl bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 p-5 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between gap-2 mb-3">
                                            <span class="text-xs font-extrabold text-emerald-900 uppercase tracking-wide">
                                                เลขมงคล 2 ตัว
                                            </span>
                                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-600 text-white text-[11px] font-bold">
                                                เปิดดูฟรี
                                            </span>
                                        </div>

                                        <div v-if="item.two_digit_list.length > 0" class="flex flex-wrap gap-2">
                                            <span
                                                v-for="num in item.two_digit_list"
                                                :key="num"
                                                class="px-4 py-2 rounded-xl bg-white border-2 border-emerald-300 text-2xl font-black text-emerald-800 shadow-2xs"
                                            >
                                                {{ num }}
                                            </span>
                                        </div>
                                        <div v-else class="text-sm text-gray-500 py-2">
                                            ไม่มีชุดเลข 2 ตัวสำหรับความฝันนี้
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-3 border-t border-emerald-200/70 text-xs text-emerald-800">
                                        เลขเด่น 2 ตัวตามตำราทำนายฝันสำหรับใช้เป็นแนวทางสลากกินแบ่งรัฐบาล
                                    </div>
                                </div>

                                <!-- กล่องที่ 2: เลขมงคล 3 ตัวตรง & เลขฝันชนสำนักดังงวดนี้ (แสดงเฉพาะเมื่อมีเลข 3 ตัวในตำรา หรือมีเลขชนสำนักจริงเท่านั้น ไม่หลอกลูกค้า) -->
                                <div
                                    v-if="item.three_digit_count > 0 || item.matching_sources_count > 0"
                                    class="rounded-2xl bg-gradient-to-br from-amber-50 via-yellow-50 to-orange-50 border-2 border-amber-300 p-5 flex flex-col justify-between"
                                >
                                    <div>
                                        <div class="flex items-center justify-between gap-2 mb-3">
                                            <span class="text-xs font-extrabold text-amber-950 uppercase tracking-wide flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z" />
                                                </svg>
                                                <span>{{ item.three_digit_count > 0 ? 'ชุดเลขมงคล 3 ตัวตรง' : 'เลขฝันชนสำนักดังงวดนี้' }}</span>
                                            </span>
                                            <span class="px-2.5 py-0.5 rounded-full bg-amber-500 text-gray-950 text-[11px] font-extrabold">
                                                สิทธิพิเศษ VIP
                                            </span>
                                        </div>

                                        <!-- กรณีเป็น VIP: เปิดโชว์เลข 3 ตัวครบทุกชุด -->
                                        <template v-if="canViewVip">
                                            <div v-if="item.three_digit_list.length > 0" class="flex flex-wrap gap-2">
                                                <span
                                                    v-for="num in item.three_digit_list"
                                                    :key="num"
                                                    class="px-4 py-2 rounded-xl bg-white border-2 border-amber-400 text-2xl font-black text-red-600 shadow-2xs"
                                                >
                                                    {{ num }}
                                                </span>
                                            </div>

                                            <!-- แสดงสำนักดังที่ให้เลขตรงกับความฝันนี้ในงวดปัจจุบัน -->
                                            <div v-if="item.matching_sources.length > 0" class="mt-4 p-3 rounded-xl bg-white/90 border border-amber-300 space-y-1.5">
                                                <div class="text-xs font-extrabold text-red-600">
                                                    เลขฝันนี้ตรงกับสำนักดังงวดล่าสุด ({{ item.matching_sources.length }} สำนัก):
                                                </div>
                                                <div class="flex flex-wrap gap-1.5">
                                                    <span
                                                        v-for="(ms, idx) in item.matching_sources"
                                                        :key="idx"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-red-50 border border-red-200 text-xs font-bold text-gray-900"
                                                    >
                                                        <span>{{ ms.source_name }}:</span>
                                                        <strong class="text-red-600">{{ ms.matched_numbers }}</strong>
                                                    </span>
                                                </div>
                                            </div>
                                        </template>

                                        <!-- กรณียังไม่ได้เป็น VIP: แสดงป้ายล็อกเฉพาะเมื่อมีเลข 3 ตัวจริง -->
                                        <template v-else>
                                            <div class="space-y-3">
                                                <div v-if="item.three_digit_count > 0" class="flex flex-wrap gap-2">
                                                    <span
                                                        v-for="n in item.three_digit_count"
                                                        :key="n"
                                                        class="px-4 py-2 rounded-xl bg-white/80 border-2 border-dashed border-amber-400 text-lg font-black text-amber-700 select-none flex items-center gap-1"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                        </svg>
                                                        <span>XXX</span>
                                                    </span>
                                                </div>

                                                <div
                                                    v-if="item.matching_sources_count > 0"
                                                    class="p-2.5 rounded-xl bg-red-600 text-white text-xs font-bold"
                                                >
                                                    เลขจากความฝันนี้ ตรงกับแนวทางของสำนักดังงวดนี้ถึง {{ item.matching_sources_count }} สำนัก!
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- ปุ่มปลดล็อกเลข 3 ตัว แสดงเฉพาะเมื่อการ์ดนั้นมีเลข 3 ตัวในตำราจริงๆ เท่านั้น -->
                                    <div v-if="!canViewVip && item.three_digit_count > 0" class="mt-4 pt-3 border-t border-amber-200/80 flex items-center justify-between gap-2">
                                        <span class="text-xs font-bold text-amber-900">
                                            มีเลข 3 ตัวตรง {{ item.three_digit_count }} ชุดในตำรา
                                        </span>
                                        <Link
                                            :href="route('vip-request.create')"
                                            class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-extrabold shadow transition"
                                        >
                                            <span>ปลดล็อกเลข 3 ตัว (VIP) →</span>
                                        </Link>
                                    </div>
                                    <div v-else-if="!canViewVip && item.matching_sources_count > 0" class="mt-4 pt-3 border-t border-amber-200/80 flex items-center justify-between gap-2">
                                        <span class="text-xs font-bold text-amber-900">
                                            ดูรายชื่อสำนักที่ให้เลขตรงกับฝันนี้
                                        </span>
                                        <Link
                                            :href="route('vip-request.create')"
                                            class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-extrabold shadow transition"
                                        >
                                            <span>เปิดดูสำนักที่ชน (VIP) →</span>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ปุ่มเปลี่ยนหน้า (Pagination) รองรับกรณีมีคำทำนายฝันหลักร้อย/หลักพันคำ -->
                    <div
                        v-if="pagination && pagination.last_page > 1"
                        class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 flex flex-col sm:flex-row items-center justify-between gap-3"
                    >
                        <div class="text-xs sm:text-sm text-gray-600">
                            แสดงหน้าที่ <strong>{{ pagination.current_page }}</strong> จาก <strong>{{ pagination.last_page }}</strong>
                            (ทั้งหมด {{ pagination.total }} คำทำนาย)
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <button
                                type="button"
                                :disabled="pagination.current_page <= 1"
                                @click="goToPage(pagination.current_page - 1)"
                                class="px-3.5 py-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 disabled:opacity-40 transition"
                            >
                                ← ก่อนหน้า
                            </button>
                            <button
                                v-for="p in pagination.last_page"
                                :key="p"
                                v-show="Math.abs(p - pagination.current_page) <= 2 || p === 1 || p === pagination.last_page"
                                type="button"
                                @click="goToPage(p)"
                                :class="p === pagination.current_page ? 'bg-red-600 text-white border-red-600 shadow' : 'bg-white text-gray-700 border-gray-200 hover:bg-red-50'"
                                class="w-9 h-9 rounded-xl border text-xs font-extrabold transition"
                            >
                                {{ p }}
                            </button>
                            <button
                                type="button"
                                :disabled="pagination.current_page >= pagination.last_page"
                                @click="goToPage(pagination.current_page + 1)"
                                class="px-3.5 py-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 disabled:opacity-40 transition"
                            >
                                ถัดไป →
                            </button>
                        </div>
                    </div>
                </div>

                <!-- กรณีค้นหาแล้วยังไม่พบคำนั้นในตำรา -->
                <div v-else class="bg-white rounded-3xl shadow-md border border-gray-200 p-8 text-center space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-extrabold text-gray-900">
                        ไม่พบคำว่า "{{ searchQuery }}" ในหมวดที่เลือก
                    </h3>
                    <p class="text-sm text-gray-600 max-w-md mx-auto">
                        ลองพิมพ์เฉพาะคำนามหลักสั้นๆ ที่เห็นในฝัน หรือกดดูรายการคำฝันทั้งหมดในตำราด้านล่างครับ
                    </p>
                    <div>
                        <button
                            type="button"
                            @click="clearSearch"
                            class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm font-extrabold shadow transition"
                        >
                            แสดงคำทำนายฝันทั้งหมดในตำรา
                        </button>
                    </div>
                </div>

                <!-- สินค้าสายมูเสริมโชค (Affiliate Section) -->
                <div v-if="affiliateProducts && affiliateProducts.length > 0" class="bg-white rounded-3xl shadow-md border border-gray-200 p-6 sm:p-8">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900">วัตถุมงคล & ของนำโชคเสริมดวง</h2>
                            <p class="text-xs text-gray-500">เสริมสิริมงคลและโชคลาภควบคู่กับเลขมงคลจากความฝัน</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <a
                            v-for="product in affiliateProducts"
                            :key="product.id"
                            :href="product.affiliate_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="group block bg-gray-50 rounded-2xl border border-gray-200 overflow-hidden hover:shadow-md hover:border-red-200 transition-all"
                        >
                            <div class="aspect-square bg-gray-100 overflow-hidden">
                                <img
                                    v-if="product.image_url"
                                    :src="product.image_url"
                                    :alt="product.title"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                />
                            </div>
                            <div class="p-3">
                                <div class="text-xs font-bold text-gray-900 line-clamp-2 group-hover:text-red-600">
                                    {{ product.title }}
                                </div>
                                <span class="inline-block mt-1.5 text-[11px] text-red-600 font-semibold">
                                    ดูที่ {{ product.platform?.name || 'ร้านค้า' }} →
                                </span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
