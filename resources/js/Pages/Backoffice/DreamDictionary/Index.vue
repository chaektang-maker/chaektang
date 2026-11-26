<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    items: {
        type: Array,
        required: true,
    },
    letters: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        required: true,
    },
});

const searchQuery = ref('');
const selectedLetter = ref('all');
const showBulkImport = ref(false);

const filteredItems = computed(() => {
    return props.items.filter((item) => {
        if (selectedLetter.value !== 'all' && item.category_letter !== selectedLetter.value) {
            return false;
        }
        if (searchQuery.value.trim()) {
            const q = searchQuery.value.trim().toLowerCase();
            return (
                (item.keyword && item.keyword.toLowerCase().includes(q)) ||
                (item.meaning && item.meaning.toLowerCase().includes(q)) ||
                (item.two_digits && item.two_digits.includes(q)) ||
                (item.three_digits && item.three_digits.includes(q))
            );
        }
        return true;
    });
});

// Bulk Import Form
const bulkForm = useForm({
    raw_text: '',
    txt_file: null,
});

const onFileChange = (e) => {
    const file = e.target.files?.[0];
    bulkForm.txt_file = file || null;
};

const submitBulkImport = () => {
    bulkForm.post(route('backoffice.dream-dictionary.import-bulk'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            bulkForm.reset();
            showBulkImport.value = false;
        },
    });
};

const syncDefaultFile = () => {
    router.post(route('backoffice.dream-dictionary.sync-file'), {}, {
        preserveScroll: true,
    });
};

// Single Add / Edit Modal
const isModalOpen = ref(false);
const itemForm = useForm({
    id: null,
    keyword: '',
    meaning: '',
    two_digits: '',
    three_digits: '',
    no_number_note: '',
});

const openCreateModal = () => {
    itemForm.id = null;
    itemForm.keyword = '';
    itemForm.meaning = '';
    itemForm.two_digits = '';
    itemForm.three_digits = '';
    itemForm.no_number_note = '';
    itemForm.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (item) => {
    itemForm.id = item.id;
    itemForm.keyword = item.keyword;
    itemForm.meaning = item.meaning;
    itemForm.two_digits = item.two_digits || '';
    itemForm.three_digits = item.three_digits || '';
    itemForm.no_number_note = item.no_number_note || '';
    itemForm.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const submitItemForm = () => {
    itemForm.post(route('backoffice.dream-dictionary.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    });
};

const deleteItem = (item) => {
    if (!confirm(`ต้องการลบคำทำนายฝัน "${item.keyword}" ใช่หรือไม่?`)) {
        return;
    }
    router.delete(route('backoffice.dream-dictionary.destroy', item.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="จัดการตำราทำนายฝัน (ก-ฮ) - Backoffice" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-bold text-xl text-yellow-400 leading-tight flex items-center gap-2">
                        <span>จัดการตำราทำนายฝัน & เลขมงคล (ก - ฮ)</span>
                    </h2>
                    <p class="text-xs text-gray-400 mt-1">
                        นำเข้าคำทำนายทีละหลายร้อยคำจากการวางข้อความ / ไฟล์ .txt หรือเพิ่ม-แก้ไขรายคำได้ตลอดเวลา
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        @click="showBulkImport = !showBulkImport"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs sm:text-sm font-bold shadow transition"
                    >
                        <span>{{ showBulkImport ? 'ซ่อนกล่องนำเข้าข้อความ' : '+ นำเข้าจากข้อความ / ไฟล์ .txt' }}</span>
                    </button>
                    <button
                        type="button"
                        @click="openCreateModal"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-yellow-500 hover:bg-yellow-400 text-gray-950 text-xs sm:text-sm font-black shadow transition"
                    >
                        <span>+ เพิ่มคำทำนายรายคำ</span>
                    </button>
                    <a
                        :href="route('dream-interpretation.index')"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gray-800 border border-gray-700 text-gray-200 hover:bg-gray-700 text-xs sm:text-sm font-semibold transition"
                    >
                        <span>เปิดหน้าทำนายฝัน</span>
                    </a>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Flash Messages -->
                <div
                    v-if="$page.props.flash?.success"
                    class="p-4 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-200 text-sm font-medium"
                >
                    {{ $page.props.flash.success }}
                </div>
                <div
                    v-if="$page.props.flash?.error"
                    class="p-4 rounded-xl bg-rose-500/20 border border-rose-500/40 text-rose-200 text-sm font-medium"
                >
                    {{ $page.props.flash.error }}
                </div>

                <!-- Bulk Import Panel (วิธีที่ 1: วางข้อความหรืออัปโหลดไฟล์ .txt) -->
                <div
                    v-if="showBulkImport"
                    class="p-6 rounded-2xl bg-gradient-to-br from-indigo-950 via-gray-900 to-slate-900 border-2 border-indigo-500/50 shadow-2xl space-y-5"
                >
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-indigo-500/30 pb-4">
                        <div>
                            <h3 class="text-base font-extrabold text-white">
                                นำเข้าคำทำนายชุดใหญ่ (Bulk Paste / อัปโหลดไฟล์ .txt)
                            </h3>
                            <p class="text-xs text-indigo-200 mt-0.5">
                                ก๊อปปี้ข้อความจากไฟล์ .txt มาวางต่อกันได้ทีละหลายร้อยคำ ระบบจะแยก "คำหลัก", "คำทำนาย", "เลข 2 ตัว (ดูฟรี)" และ "เลข 3 ตัว (สำหรับ VIP)" ให้อัตโนมัติ
                            </p>
                        </div>
                        <button
                            type="button"
                            @click="syncDefaultFile"
                            class="shrink-0 px-3.5 py-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 border border-amber-400/40 text-amber-300 text-xs font-bold transition"
                        >
                            ซิงค์จากไฟล์ คำทำนาย.txt ในโปรเจกต์
                        </button>
                    </div>

                    <form @submit.prevent="submitBulkImport" class="space-y-4">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                            <div class="lg:col-span-8">
                                <label class="block text-xs font-bold text-gray-300 mb-1.5">
                                    วางข้อความคำทำนายที่นี่ (เรียงต่อกันได้เลยหลายๆ คำ)
                                </label>
                                <textarea
                                    v-model="bulkForm.raw_text"
                                    rows="10"
                                    placeholder="กงเต๊ก&#10;หากฝัน หรือมีลางบอกเหตุ...&#10;เลขมงคล 40, 60, 90, 046, 049, 460&#10;&#10;กระต่ายน้อย&#10;อย่าตกใจกลัวในสิ่งที่ยังมาไม่ถึง...&#10;เลขมงคล 586, 41, 438, 043, 034"
                                    class="w-full px-4 py-3 rounded-xl bg-gray-950 border border-gray-700 text-sm text-white placeholder-gray-600 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400 font-mono leading-relaxed"
                                ></textarea>
                            </div>

                            <div class="lg:col-span-4 space-y-4">
                                <div class="p-4 rounded-xl bg-gray-950/90 border border-gray-800 space-y-2 text-xs text-gray-300">
                                    <div class="font-bold text-indigo-300">รูปแบบข้อความที่ระบบรองรับอัตโนมัติ:</div>
                                    <pre class="text-[11px] text-gray-400 whitespace-pre-wrap font-mono bg-gray-900 p-2.5 rounded-lg border border-gray-800">ชื่อสิ่งที่ฝัน (บรรทัดแรก)
เนื้อหาคำทำนาย (1 หรือหลายบรรทัด)
เลขมงคล 40, 60, 046, 460</pre>
                                    <p class="text-[11px] text-gray-400 leading-relaxed">
                                        • ระบบจะจบ 1 คำทำนายทุกครั้งที่เจอบรรทัดที่ขึ้นต้นด้วย <strong>"เลขมงคล"</strong><br />
                                        • หากมีคำซ้ำกับในระบบ ระบบจะอัปเดตข้อมูลใหม่ทับให้โดยไม่สร้างซ้ำ
                                    </p>
                                </div>

                                <div class="p-4 rounded-xl bg-gray-950/90 border border-gray-800 space-y-2">
                                    <label class="block text-xs font-bold text-gray-300">
                                        หรือเลือกอัปโหลดไฟล์ .txt จากเครื่องของคุณ
                                    </label>
                                    <input
                                        type="file"
                                        accept=".txt,text/plain"
                                        @change="onFileChange"
                                        class="block w-full text-xs text-gray-400 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button
                                type="button"
                                @click="showBulkImport = false"
                                class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-xs text-gray-300 font-medium"
                            >
                                ปิดหน้าต่างนี้
                            </button>
                            <button
                                type="submit"
                                :disabled="bulkForm.processing"
                                class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:brightness-110 text-white font-black text-xs sm:text-sm shadow-lg transition disabled:opacity-50"
                            >
                                {{ bulkForm.processing ? 'กำลังประมวลผลและนำเข้า...' : 'นำเข้าข้อมูลลง Database ทันที' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Summary & Filters -->
                <div class="p-5 rounded-2xl bg-gray-900/90 border border-gray-800 space-y-4">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="px-4 py-2 rounded-xl bg-gray-950 border border-gray-800">
                                <span class="text-xs text-gray-400 mr-2">คำทำนายทั้งหมด:</span>
                                <span class="text-base font-black text-white">{{ stats.total }} คำ</span>
                            </div>
                            <div class="px-4 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/30">
                                <span class="text-xs text-emerald-300 mr-2">มีเลขเสี่ยงโชค:</span>
                                <span class="text-base font-black text-emerald-400">{{ stats.with_numbers }} คำ</span>
                            </div>
                            <div class="px-4 py-2 rounded-xl bg-amber-500/10 border border-amber-500/30">
                                <span class="text-xs text-amber-300 mr-2">งดเสี่ยงโชค / แก้เคล็ด:</span>
                                <span class="text-base font-black text-amber-400">{{ stats.no_numbers }} คำ</span>
                            </div>
                        </div>

                        <div class="w-full md:w-80">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="ค้นหาคำฝัน, เนื้อหา หรือตัวเลข..."
                                class="w-full px-4 py-2 rounded-xl bg-gray-950 border border-gray-700 text-sm text-white placeholder-gray-500 focus:border-yellow-500"
                            />
                        </div>
                    </div>

                    <!-- Alphabet Filter (ก - ฮ) -->
                    <div class="flex flex-wrap items-center gap-1.5 pt-2 border-t border-gray-800">
                        <span class="text-xs text-gray-400 mr-1">หมวดอักษร:</span>
                        <button
                            type="button"
                            @click="selectedLetter = 'all'"
                            :class="selectedLetter === 'all' ? 'bg-yellow-500 text-gray-950 font-black' : 'bg-gray-800 text-gray-300 hover:bg-gray-700'"
                            class="px-3 py-1 rounded-lg text-xs transition"
                        >
                            ทั้งหมด
                        </button>
                        <button
                            v-for="letter in letters"
                            :key="letter"
                            type="button"
                            @click="selectedLetter = letter"
                            :class="selectedLetter === letter ? 'bg-yellow-500 text-gray-950 font-black' : 'bg-gray-800 text-gray-300 hover:bg-gray-700'"
                            class="w-8 h-8 rounded-lg text-xs flex items-center justify-center transition"
                        >
                            {{ letter }}
                        </button>
                    </div>
                </div>

                <!-- Dream Dictionary List -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="item in filteredItems"
                        :key="item.id"
                        class="p-5 rounded-2xl bg-gray-900/90 border border-gray-800 hover:border-gray-700 transition flex flex-col justify-between gap-4"
                    >
                        <div class="space-y-2.5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-9 h-9 rounded-xl bg-yellow-500/20 border border-yellow-500/40 text-yellow-300 font-black text-base flex items-center justify-center shrink-0">
                                        {{ item.category_letter }}
                                    </span>
                                    <div>
                                        <h3 class="text-lg font-black text-white">
                                            {{ item.keyword }}
                                        </h3>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button
                                        type="button"
                                        @click="openEditModal(item)"
                                        class="px-3 py-1.5 rounded-xl bg-yellow-500/20 hover:bg-yellow-500/30 text-yellow-300 border border-yellow-500/40 text-xs font-bold transition"
                                    >
                                        แก้ไข
                                    </button>
                                    <button
                                        type="button"
                                        @click="deleteItem(item)"
                                        class="px-2.5 py-1.5 rounded-xl bg-gray-800 hover:bg-rose-500/20 text-gray-400 hover:text-rose-300 border border-gray-700 hover:border-rose-500/40 text-xs transition"
                                    >
                                        ลบ
                                    </button>
                                </div>
                            </div>

                            <p class="text-xs text-gray-300 whitespace-pre-line leading-relaxed">
                                {{ item.meaning }}
                            </p>
                        </div>

                        <!-- Numbers Breakdown (Free 2-digit vs VIP 3-digit) -->
                        <div class="pt-3 border-t border-gray-800/80 space-y-2">
                            <div v-if="item.two_digits || item.three_digits" class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                <div class="p-2.5 rounded-xl bg-gray-950 border border-gray-800">
                                    <div class="text-[11px] text-emerald-400 font-bold mb-0.5">
                                        เลข 2 ตัว (เปิดดูฟรีทุกคน)
                                    </div>
                                    <div class="font-black text-white tracking-wide">
                                        {{ item.two_digits || '-' }}
                                    </div>
                                </div>
                                <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/30">
                                    <div class="text-[11px] text-amber-300 font-bold mb-0.5">
                                        เลข 3 ตัว (ปลดล็อกเฉพาะ VIP)
                                    </div>
                                    <div class="font-black text-amber-300 tracking-wide">
                                        {{ item.three_digits || '-' }}
                                    </div>
                                </div>
                            </div>
                            <div
                                v-else
                                class="p-2.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-xs text-rose-300"
                            >
                                <strong>งดเสี่ยงโชค:</strong> {{ item.no_number_note || 'ไม่มีเลขเสี่ยงโชค' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add / Edit Modal -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm overflow-y-auto"
        >
            <div class="w-full max-w-2xl rounded-3xl bg-gray-900 border border-gray-700 shadow-2xl overflow-hidden my-8">
                <div class="px-6 py-4 bg-gray-950 border-b border-gray-800 flex items-center justify-between">
                    <h3 class="text-base font-bold text-white">
                        {{ itemForm.id ? `แก้ไขคำทำนายฝัน: ${itemForm.keyword}` : 'เพิ่มคำทำนายฝันใหม่' }}
                    </h3>
                    <button
                        type="button"
                        @click="closeModal"
                        class="text-gray-400 hover:text-white text-xl font-bold px-2"
                    >
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitItemForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1.5">
                            คำหลัก / สิ่งที่ฝัน (Keyword) *
                        </label>
                        <input
                            v-model="itemForm.keyword"
                            type="text"
                            required
                            placeholder="เช่น กงเต๊ก, กระต่ายน้อย, กุญแจ, งูใหญ่"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-gray-950 border border-gray-700 text-sm text-white focus:border-yellow-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-300 mb-1.5">
                            คำทำนายฝัน (Meaning) *
                        </label>
                        <textarea
                            v-model="itemForm.meaning"
                            rows="5"
                            required
                            placeholder="ใส่เนื้อหาคำทำนาย..."
                            class="w-full px-3.5 py-2.5 rounded-xl bg-gray-950 border border-gray-700 text-sm text-white focus:border-yellow-500 leading-relaxed"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-emerald-400 mb-1.5">
                                เลขมงคล 2 ตัว (คั่นด้วยลูกน้ำ , — เปิดดูฟรี)
                            </label>
                            <input
                                v-model="itemForm.two_digits"
                                type="text"
                                placeholder="เช่น 40, 60, 90"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-gray-950 border border-gray-700 text-sm text-white focus:border-emerald-500"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-amber-400 mb-1.5">
                                เลขมงคล 3 ตัว (คั่นด้วยลูกน้ำ , — สำหรับ VIP)
                            </label>
                            <input
                                v-model="itemForm.three_digits"
                                type="text"
                                placeholder="เช่น 046, 049, 460"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-gray-950 border border-gray-700 text-sm text-amber-300 focus:border-amber-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 mb-1.5">
                            ข้อความกรณีไม่มีเลขเสี่ยงโชค (เว้นว่างไว้หากมีเลขมงคลด้านบน)
                        </label>
                        <input
                            v-model="itemForm.no_number_note"
                            type="text"
                            placeholder="เช่น ถือว่าไม่มีเลขเสี่ยงโชค จึงไม่ควรเสี่ยงโชคใดๆ ในช่วงเวลาประมาณ 3 เดือน"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-gray-950 border border-gray-700 text-sm text-white focus:border-rose-500"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-800">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-xs text-gray-300 font-medium"
                        >
                            ยกเลิก
                        </button>
                        <button
                            type="submit"
                            :disabled="itemForm.processing"
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-yellow-400 to-amber-500 text-gray-950 font-black text-xs shadow-lg hover:brightness-110 transition disabled:opacity-50"
                        >
                            บันทึกคำทำนาย
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
