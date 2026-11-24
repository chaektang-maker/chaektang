<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    pairs: {
        type: Array,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
});

const searchQuery = ref('');
const activeFilter = ref('all'); // all, custom, excellent, good, neutral, bad

const filteredPairs = computed(() => {
    return props.pairs.filter((item) => {
        if (activeFilter.value === 'custom' && !item.is_custom) return false;
        if (['excellent', 'good', 'neutral', 'bad'].includes(activeFilter.value) && item.level !== activeFilter.value) {
            return false;
        }

        if (searchQuery.value.trim()) {
            const q = searchQuery.value.trim().toLowerCase();
            return (
                item.pair.includes(q) ||
                (item.title && item.title.toLowerCase().includes(q)) ||
                (item.meaning && item.meaning.toLowerCase().includes(q))
            );
        }

        return true;
    });
});

const isEditModalOpen = ref(false);
const editingPair = ref(null);

const form = useForm({
    level: 'good',
    score: 80,
    title: '',
    meaning: '',
    wealth_score: 80,
    career_score: 80,
    charm_score: 80,
    health_score: 80,
});

const openEditModal = (item) => {
    editingPair.value = item;
    form.level = item.level;
    form.score = item.score;
    form.title = item.title;
    form.meaning = item.meaning;
    form.wealth_score = item.wealth_score;
    form.career_score = item.career_score;
    form.charm_score = item.charm_score;
    form.health_score = item.health_score;
    form.clearErrors();
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    editingPair.value = null;
};

const fillDefaultValues = () => {
    if (!editingPair.value || !editingPair.value.default) return;
    const d = editingPair.value.default;
    form.level = d.level;
    form.score = d.score;
    form.title = d.title;
    form.meaning = d.meaning;
    form.wealth_score = d.wealth;
    form.career_score = d.career;
    form.charm_score = d.charm;
    form.health_score = d.health;
};

const submitEdit = () => {
    if (!editingPair.value) return;
    form.put(route('backoffice.phone-pairs.update', editingPair.value.pair), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
        },
    });
};

const resetToDefault = (item) => {
    if (!confirm(`ต้องการรีเซ็ตคู่เลข "${item.pair}" กลับไปใช้ตำรามาตรฐานในระบบใช่หรือไม่?`)) {
        return;
    }
    router.delete(route('backoffice.phone-pairs.reset', item.pair), {
        preserveScroll: true,
    });
};

const getLevelBadge = (level) => {
    switch (level) {
        case 'excellent':
            return {
                label: '⭐ มงคลสูงสุด',
                bg: 'bg-amber-500/20 text-amber-300 border-amber-500/40',
                pairBg: 'bg-gradient-to-br from-amber-500/30 to-yellow-600/20 border-amber-400/50 text-amber-300',
            };
        case 'good':
            return {
                label: '✅ คู่เลขมงคล',
                bg: 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
                pairBg: 'bg-emerald-500/20 border-emerald-500/40 text-emerald-300',
            };
        case 'neutral':
            return {
                label: '⚖️ ปานกลาง',
                bg: 'bg-sky-500/20 text-sky-300 border-sky-500/40',
                pairBg: 'bg-sky-500/20 border-sky-500/40 text-sky-300',
            };
        default:
            return {
                label: '⚠️ ควรระวัง/แก้เคล็ด',
                bg: 'bg-rose-500/20 text-rose-300 border-rose-500/40',
                pairBg: 'bg-rose-500/20 border-rose-500/40 text-rose-300',
            };
    }
};
</script>

<template>
    <Head title="จัดการตำราเบอร์มงคล (00-99) - Backoffice" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-bold text-xl text-yellow-400 leading-tight flex items-center gap-2">
                        <span>📱</span> จัดการตำราเบอร์มงคล (คู่เลข 00 - 99)
                    </h2>
                    <p class="text-xs text-gray-400 mt-1">
                        ปรับแต่งความหมาย คะแนนความมงคล และคะแนน 4 ด้าน (การเงิน การงาน เสน่ห์ สุขภาพ) ได้ตามตำราของคุณ
                    </p>
                </div>
                <a
                    :href="route('phone-analyzer.index')"
                    target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500/20 border border-amber-400/40 text-amber-300 hover:bg-amber-500/30 text-sm font-bold transition"
                >
                    <span>🔮</span> เปิดหน้าวิเคราะห์เบอร์มงคล
                </a>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Flash Success Message -->
                <div
                    v-if="$page.props.flash?.success"
                    class="p-4 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-200 text-sm font-medium flex items-center justify-between"
                >
                    <span>✅ {{ $page.props.flash.success }}</span>
                </div>

                <!-- Hybrid Mode Explanation Banner -->
                <div class="p-5 rounded-2xl bg-gradient-to-r from-indigo-950/90 via-slate-900 to-purple-950/90 border border-indigo-500/30 shadow-lg">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="inline-flex items-center gap-2 text-indigo-300 font-bold text-sm">
                                <span>💡</span> ระบบทำงานแบบ Hybrid (ตำรามาตรฐานในโค้ด + ฐานข้อมูลทำงานคู่กันอัตโนมัติ)
                            </div>
                            <p class="text-xs text-gray-300 leading-relaxed">
                                • <strong>คู่เลขที่ยังไม่ได้แก้ไข:</strong> ระบบจะดึงคำทำนายจาก <strong>"ตำรามาตรฐานในระบบ"</strong> ให้อัตโนมัติครบทั้ง 100 คู่เลข (00-99) โดยไม่ต้องบันทึกข้อมูลลง Database ก่อน<br />
                                • <strong>คู่เลขที่คุณกดแก้ไขและบันทึก:</strong> ระบบจะบันทึกทับลงใน Database และนำไปใช้คำนวณหน้าเว็บทันที พร้อมปุ่ม <strong>"รีเซ็ตกลับเป็นค่าเริ่มต้น"</strong> ได้ตลอดเวลา
                            </p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <div class="px-4 py-2.5 rounded-xl bg-gray-900/80 border border-gray-700 text-center">
                                <div class="text-xs text-gray-400">คู่เลขทั้งหมด</div>
                                <div class="text-lg font-black text-white">{{ stats.total }} คู่</div>
                            </div>
                            <div class="px-4 py-2.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-center">
                                <div class="text-xs text-amber-300/80">ปรับแต่งเองแล้ว</div>
                                <div class="text-lg font-black text-amber-400">{{ stats.custom_count }} คู่</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search & Filter Controls -->
                <div class="p-5 rounded-2xl bg-gray-900/90 border border-gray-800 space-y-4">
                    <div class="flex flex-col md:flex-row gap-4 justify-between">
                        <!-- Filter Tabs -->
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                @click="activeFilter = 'all'"
                                :class="activeFilter === 'all' ? 'bg-yellow-500 text-gray-950 font-bold' : 'bg-gray-800 text-gray-300 hover:bg-gray-700'"
                                class="px-3.5 py-2 rounded-xl text-xs transition"
                            >
                                ทั้งหมด ({{ stats.total }})
                            </button>
                            <button
                                type="button"
                                @click="activeFilter = 'custom'"
                                :class="activeFilter === 'custom' ? 'bg-purple-500 text-white font-bold' : 'bg-gray-800 text-purple-300 hover:bg-gray-700'"
                                class="px-3.5 py-2 rounded-xl text-xs transition"
                            >
                                ✨ ปรับแต่งเองแล้ว ({{ stats.custom_count }})
                            </button>
                            <button
                                type="button"
                                @click="activeFilter = 'excellent'"
                                :class="activeFilter === 'excellent' ? 'bg-amber-500 text-gray-950 font-bold' : 'bg-gray-800 text-amber-300 hover:bg-gray-700'"
                                class="px-3.5 py-2 rounded-xl text-xs transition"
                            >
                                ⭐ มงคลสูงสุด ({{ stats.excellent_count }})
                            </button>
                            <button
                                type="button"
                                @click="activeFilter = 'good'"
                                :class="activeFilter === 'good' ? 'bg-emerald-500 text-gray-950 font-bold' : 'bg-gray-800 text-emerald-300 hover:bg-gray-700'"
                                class="px-3.5 py-2 rounded-xl text-xs transition"
                            >
                                ✅ คู่เลขมงคล ({{ stats.good_count }})
                            </button>
                            <button
                                type="button"
                                @click="activeFilter = 'neutral'"
                                :class="activeFilter === 'neutral' ? 'bg-sky-500 text-gray-950 font-bold' : 'bg-gray-800 text-sky-300 hover:bg-gray-700'"
                                class="px-3.5 py-2 rounded-xl text-xs transition"
                            >
                                ⚖️ ปานกลาง ({{ stats.neutral_count }})
                            </button>
                            <button
                                type="button"
                                @click="activeFilter = 'bad'"
                                :class="activeFilter === 'bad' ? 'bg-rose-500 text-white font-bold' : 'bg-gray-800 text-rose-300 hover:bg-gray-700'"
                                class="px-3.5 py-2 rounded-xl text-xs transition"
                            >
                                ⚠️ ควรระวัง ({{ stats.bad_count }})
                            </button>
                        </div>

                        <!-- Search Input -->
                        <div class="relative w-full md:w-72">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="ค้นหาคู่เลข (เช่น 24, 78) หรือคำทำนาย..."
                                class="w-full px-4 py-2 rounded-xl bg-gray-950 border border-gray-700 text-sm text-white placeholder-gray-500 focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Grid of Pairs -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="item in filteredPairs"
                        :key="item.pair"
                        class="p-5 rounded-2xl bg-gray-900/90 border transition flex flex-col justify-between gap-4"
                        :class="item.is_custom ? 'border-purple-500/60 shadow-lg shadow-purple-500/5' : 'border-gray-800 hover:border-gray-700'"
                    >
                        <div class="flex items-start gap-4">
                            <!-- Pair Number Box -->
                            <div
                                :class="getLevelBadge(item.level).pairBg"
                                class="w-16 h-16 rounded-2xl border flex flex-col items-center justify-center shrink-0"
                            >
                                <span class="text-2xl font-black leading-none">{{ item.pair }}</span>
                                <span class="text-[10px] font-bold opacity-80 mt-1">{{ item.score }}/100</span>
                            </div>

                            <!-- Details -->
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                    <span
                                        :class="getLevelBadge(item.level).bg"
                                        class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border"
                                    >
                                        {{ getLevelBadge(item.level).label }}
                                    </span>

                                    <span
                                        v-if="item.is_custom"
                                        class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/40"
                                    >
                                        ✨ ตำราปรับแต่งเอง (DB)
                                    </span>
                                    <span
                                        v-else
                                        class="px-2.5 py-0.5 rounded-full text-[11px] bg-gray-800 text-gray-400 border border-gray-700"
                                    >
                                        📘 ตำรามาตรฐานระบบ
                                    </span>
                                </div>

                                <h3 class="text-base font-bold text-white mb-1">
                                    {{ item.title }}
                                </h3>
                                <p class="text-xs text-gray-300 leading-relaxed">
                                    {{ item.meaning }}
                                </p>
                            </div>
                        </div>

                        <!-- 4 Aspect Scores & Actions -->
                        <div class="pt-3 border-t border-gray-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="grid grid-cols-4 gap-2 text-[11px] text-center">
                                <div class="px-2 py-1 rounded-lg bg-gray-950 border border-gray-800">
                                    <span class="text-gray-400 block">💰 การเงิน</span>
                                    <span class="font-bold text-amber-300">{{ item.wealth_score }}</span>
                                </div>
                                <div class="px-2 py-1 rounded-lg bg-gray-950 border border-gray-800">
                                    <span class="text-gray-400 block">💼 การงาน</span>
                                    <span class="font-bold text-sky-300">{{ item.career_score }}</span>
                                </div>
                                <div class="px-2 py-1 rounded-lg bg-gray-950 border border-gray-800">
                                    <span class="text-gray-400 block">💖 เสน่ห์</span>
                                    <span class="font-bold text-pink-300">{{ item.charm_score }}</span>
                                </div>
                                <div class="px-2 py-1 rounded-lg bg-gray-950 border border-gray-800">
                                    <span class="text-gray-400 block">🛡️ สุขภาพ</span>
                                    <span class="font-bold text-emerald-300">{{ item.health_score }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0 justify-end">
                                <button
                                    v-if="item.is_custom"
                                    type="button"
                                    @click="resetToDefault(item)"
                                    class="px-3 py-1.5 rounded-xl bg-gray-800 hover:bg-rose-500/20 text-gray-300 hover:text-rose-300 border border-gray-700 hover:border-rose-500/40 text-xs font-medium transition"
                                >
                                    🔄 รีเซ็ตค่าเริ่มต้น
                                </button>
                                <button
                                    type="button"
                                    @click="openEditModal(item)"
                                    class="px-3.5 py-1.5 rounded-xl bg-yellow-500/20 hover:bg-yellow-500/30 text-yellow-300 border border-yellow-500/40 text-xs font-bold transition"
                                >
                                    ✏️ แก้ไขคำทำนาย
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div
            v-if="isEditModalOpen && editingPair"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm overflow-y-auto"
        >
            <div class="w-full max-w-2xl rounded-3xl bg-gray-900 border border-gray-700 shadow-2xl overflow-hidden my-8">
                <div class="px-6 py-4 bg-gray-950 border-b border-gray-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-yellow-500/20 border border-yellow-500/40 text-yellow-300 font-black text-lg flex items-center justify-center">
                            {{ editingPair.pair }}
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-white">
                                แก้ไขตำราคู่เลข {{ editingPair.pair }}
                            </h3>
                            <p class="text-xs text-gray-400">
                                เมื่อบันทึกแล้ว ระบบจะใช้ข้อมูลชุดนี้แทนตำรามาตรฐานทันที
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="closeEditModal"
                        class="text-gray-400 hover:text-white text-xl font-bold px-2"
                    >
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="p-6 space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-300 mb-1.5">
                                ระดับความมงคล
                            </label>
                            <select
                                v-model="form.level"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-gray-950 border border-gray-700 text-sm text-white focus:border-yellow-500"
                            >
                                <option value="excellent">⭐ มงคลสูงสุด (Excellent)</option>
                                <option value="good">✅ คู่เลขมงคล (Good)</option>
                                <option value="neutral">⚖️ ปานกลาง / เฉพาะทาง (Neutral)</option>
                                <option value="bad">⚠️ ควรระวัง / แก้เคล็ด (Bad)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-300 mb-1.5">
                                คะแนนรวมของคู่นี้ (1 - 100)
                            </label>
                            <input
                                v-model.number="form.score"
                                type="number"
                                min="1"
                                max="100"
                                required
                                class="w-full px-3.5 py-2.5 rounded-xl bg-gray-950 border border-gray-700 text-sm text-white focus:border-yellow-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1.5">
                            ฉายา / หัวข้อคู่เลข (Title)
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            placeholder="เช่น เลขเมตตามหานิยม การเงินคล่องตัว"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-gray-950 border border-gray-700 text-sm text-white focus:border-yellow-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1.5">
                            คำทำนายละเอียด (Meaning)
                        </label>
                        <textarea
                            v-model="form.meaning"
                            rows="3"
                            required
                            placeholder="อธิบายความหมาย จุดเด่น อาชีพที่เหมาะสม หรือข้อควรระวัง..."
                            class="w-full px-3.5 py-2.5 rounded-xl bg-gray-950 border border-gray-700 text-sm text-white focus:border-yellow-500"
                        ></textarea>
                    </div>

                    <!-- 4 Aspect Scores -->
                    <div class="p-4 rounded-2xl bg-gray-950 border border-gray-800 space-y-3">
                        <div class="text-xs font-bold text-yellow-400">
                            📊 คะแนนแยก 4 ด้าน (1 - 100) สำหรับคำนวณกราฟพลังเบอร์
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-[11px] text-gray-400 mb-1">💰 การเงิน/โชคลาภ</label>
                                <input
                                    v-model.number="form.wealth_score"
                                    type="number"
                                    min="1"
                                    max="100"
                                    required
                                    class="w-full px-3 py-2 rounded-xl bg-gray-900 border border-gray-700 text-sm text-amber-300 font-bold"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] text-gray-400 mb-1">💼 การงาน/อำนาจ</label>
                                <input
                                    v-model.number="form.career_score"
                                    type="number"
                                    min="1"
                                    max="100"
                                    required
                                    class="w-full px-3 py-2 rounded-xl bg-gray-900 border border-gray-700 text-sm text-sky-300 font-bold"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] text-gray-400 mb-1">💖 เสน่ห์/ความรัก</label>
                                <input
                                    v-model.number="form.charm_score"
                                    type="number"
                                    min="1"
                                    max="100"
                                    required
                                    class="w-full px-3 py-2 rounded-xl bg-gray-900 border border-gray-700 text-sm text-pink-300 font-bold"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] text-gray-400 mb-1">🛡️ สุขภาพ/สติปัญญา</label>
                                <input
                                    v-model.number="form.health_score"
                                    type="number"
                                    min="1"
                                    max="100"
                                    required
                                    class="w-full px-3 py-2 rounded-xl bg-gray-900 border border-gray-700 text-sm text-emerald-300 font-bold"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                        <button
                            type="button"
                            @click="fillDefaultValues"
                            class="px-3.5 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-xs text-gray-300 transition"
                        >
                            📘 ดึงข้อความต้นฉบับจากตำรามาตรฐานมาใส่ฟอร์ม
                        </button>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="closeEditModal"
                                class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-xs text-gray-300 font-medium"
                            >
                                ยกเลิก
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-5 py-2 rounded-xl bg-gradient-to-r from-yellow-400 to-amber-500 text-gray-950 font-black text-xs shadow-lg hover:brightness-110 transition disabled:opacity-50"
                            >
                                💾 บันทึกคำทำนายคู่เลข {{ editingPair.pair }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
