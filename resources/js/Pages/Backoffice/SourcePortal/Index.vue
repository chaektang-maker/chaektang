<script setup>
import { ref, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    source: {
        type: Object,
        default: null,
    },
    allSources: {
        type: Array,
        default: () => [],
    },
    availableDrawDates: {
        type: Array,
        default: () => [],
    },
    numbers: {
        type: Array,
        default: () => [],
    },
    products: {
        type: Array,
        default: () => [],
    },
    isAdmin: {
        type: Boolean,
        default: false,
    },
});

const switchSource = (e) => {
    const id = e.target.value;
    router.get(route('backportal.index'), id ? { source_id: id } : {});
};

// 1. ฟอร์มข้อมูลสำนัก & ช่องทางติดต่อ
const profileForm = useForm({
    source_id: props.source?.id || '',
    name: props.source?.name || '',
    description: props.source?.description || '',
});

const submitProfile = () => {
    profileForm.source_id = props.source?.id || '';
    profileForm.post(route('backportal.profile'), {
        preserveScroll: true,
    });
};

// 2. ฟอร์มลงเลขเด็ดประจำงวด
const latestDraw = props.availableDrawDates?.[0] || '';
const existingForLatest = props.numbers?.find((n) => n.draw_date === latestDraw);

const numberForm = useForm({
    source_id: props.source?.id || '',
    draw_date: latestDraw,
    two_digit: existingForLatest?.two_digit || '',
    three_digit: existingForLatest?.three_digit || '',
    running_numbers: existingForLatest?.running_numbers || '',
});

const onDrawDateChange = () => {
    const found = props.numbers?.find((n) => n.draw_date === numberForm.draw_date);
    numberForm.two_digit = found?.two_digit || '';
    numberForm.three_digit = found?.three_digit || '';
    numberForm.running_numbers = found?.running_numbers || '';
};

const loadNumberToEdit = (item) => {
    numberForm.draw_date = item.draw_date;
    numberForm.two_digit = item.two_digit || '';
    numberForm.three_digit = item.three_digit || '';
    numberForm.running_numbers = item.running_numbers || '';
};

const submitNumbers = () => {
    numberForm.source_id = props.source?.id || '';
    numberForm.post(route('backportal.numbers'), {
        preserveScroll: true,
    });
};

// 3. ฟอร์มลงสินค้าวัตถุมงคล / สินค้าสายมูของสำนัก
const isProductModalOpen = ref(false);
const productForm = useForm({
    source_id: props.source?.id || '',
    id: null,
    title: '',
    description: '',
    affiliate_url: '',
    image_url: '',
    image_file: null,
    is_active: true,
});

// เมื่อแอดมินสลับดูสำนักอื่นใน Dropdown ให้ซิงค์ค่าในฟอร์มทั้งหมดตามสำนักที่เลือกใหม่ทันที
watch(
    () => props.source,
    (newSource) => {
        profileForm.source_id = newSource?.id || '';
        profileForm.name = newSource?.name || '';
        profileForm.description = newSource?.description || '';

        const draw = numberForm.draw_date || props.availableDrawDates?.[0] || '';
        const found = props.numbers?.find((n) => n.draw_date === draw);
        numberForm.source_id = newSource?.id || '';
        numberForm.draw_date = draw;
        numberForm.two_digit = found?.two_digit || '';
        numberForm.three_digit = found?.three_digit || '';
        numberForm.running_numbers = found?.running_numbers || '';

        productForm.source_id = newSource?.id || '';
    },
    { deep: true }
);

const openCreateProduct = () => {
    productForm.source_id = props.source?.id || '';
    productForm.id = null;
    productForm.title = '';
    productForm.description = '';
    productForm.affiliate_url = '';
    productForm.image_url = '';
    productForm.image_file = null;
    productForm.is_active = true;
    productForm.clearErrors();
    isProductModalOpen.value = true;
};

const openEditProduct = (p) => {
    productForm.source_id = props.source?.id || '';
    productForm.id = p.id;
    productForm.title = p.title;
    productForm.description = p.description || '';
    productForm.affiliate_url = p.affiliate_url || '';
    productForm.image_url = p.image_url || '';
    productForm.image_file = null;
    productForm.is_active = p.is_active;
    productForm.clearErrors();
    isProductModalOpen.value = true;
};

const onProductImageChange = (e) => {
    productForm.image_file = e.target.files?.[0] || null;
};

const submitProduct = () => {
    productForm.source_id = props.source?.id || '';
    productForm.post(route('backportal.products.save'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            isProductModalOpen.value = false;
        },
    });
};

const deleteProduct = (p) => {
    if (!confirm(`ต้องการลบสินค้า "${p.title}" ออกจากหน้าเว็บใช่หรือไม่?`)) {
        return;
    }
    router.delete(route('backportal.products.delete', { productId: p.id, source_id: props.source?.id }), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="จัดการสำนัก & สินค้าของฉัน - พอร์ทัลเจ้าสำนัก" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-bold text-xl text-gray-900 leading-tight">
                        จัดการสำนัก & สินค้าสายมูของฉัน (ขึ้นหน้าเว็บทันที)
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        ลงเลขเด็ดประจำงวด แก้ไขช่องทางติดต่อสำนัก และเพิ่มสินค้าวัตถุมงคลของสำนักคุณขึ้นแสดงบนหน้าเว็บได้ทันทีโดยไม่ต้องรออนุมัติ
                    </p>
                </div>

                <!-- สำหรับแอดมิน: เลือกสลับดูสำนักไหนก็ได้ -->
                <div v-if="isAdmin && allSources.length > 0" class="flex items-center gap-2">
                    <span class="text-xs font-bold text-gray-600 whitespace-nowrap">มุมมองแอดมิน (เลือกสำนัก):</span>
                    <select
                        :value="source?.id"
                        @change="switchSource"
                        class="rounded-xl border-gray-300 text-sm font-bold text-gray-900 focus:border-red-500 focus:ring-red-500"
                    >
                        <option v-for="s in allSources" :key="s.id" :value="s.id">
                            {{ s.name }} {{ s.is_promoted ? '(โปรโมท)' : '' }}
                        </option>
                    </select>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Flash Messages -->
                <div
                    v-if="$page.props.flash?.success"
                    class="p-4 rounded-2xl bg-emerald-50 border-2 border-emerald-400 text-emerald-900 text-sm font-bold shadow-sm"
                >
                    ✓ {{ $page.props.flash.success }}
                </div>

                <div v-if="!source" class="bg-white rounded-2xl p-8 text-center border border-gray-200">
                    <p class="text-gray-600">ยังไม่มีสำนักหวยในระบบ กรุณาสร้างสำนักหวยก่อน</p>
                </div>

                <template v-else>
                    <!-- แจ้งเตือนกรณีสำนักยังอยู่ในสถานะซ่อนไว้ / รอแอดมินกดเปิดแสดงผล -->
                    <div
                        v-if="source.status !== 'active'"
                        class="p-4 sm:p-5 rounded-2xl bg-amber-50 border-2 border-amber-400 text-amber-950 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                    >
                        <div class="space-y-1">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-200 text-amber-950 text-xs font-extrabold">
                                <span>⏳ สถานะสำนัก: รอแอดมินเปิดแสดงผลบนหน้าเว็บ (ซ่อนอยู่ชั่วคราว)</span>
                            </div>
                            <p class="text-xs sm:text-sm text-amber-900 leading-relaxed">
                                คุณสามารถ <strong>บันทึกข้อมูลสำนัก ลงเลขเด็ดประจำงวด และเพิ่มสินค้าสายมูไว้ล่วงหน้าได้ตามปกติ</strong> ข้อมูลทั้งหมดจะถูกเก็บไว้ในระบบและจะแสดงขึ้นหน้าเว็บทันทีที่แอดมินกดเปิดแสดงสำนักของคุณครับ
                            </p>
                        </div>
                        <Link
                            v-if="isAdmin"
                            :href="route('backoffice.sources.index', { q: source.name })"
                            class="shrink-0 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow transition text-center"
                        >
                            ไปหน้าเปิดแสดงสำนักนี้ →
                        </Link>
                    </div>

                    <!-- แถบสถานะสำนัก & ยอดคนดู -->
                    <div class="bg-gradient-to-r from-red-700 via-red-600 to-amber-600 rounded-3xl p-6 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    v-if="source.is_currently_promoted"
                                    class="inline-flex items-center gap-1 px-3 py-0.5 rounded-full bg-yellow-400 text-gray-950 text-xs font-extrabold"
                                >
                                    สถานะ: สำนักแนะนำกรอบทอง (แสดงหน้าแรก + แถวบนสุด)
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1 px-3 py-0.5 rounded-full bg-white/20 text-white text-xs font-bold"
                                >
                                    สถานะ: สำนักมาตรฐาน
                                </span>
                                <span v-if="source.promoted_until" class="text-xs text-yellow-200">
                                    (โปรโมทถึง {{ source.promoted_until }})
                                </span>
                            </div>
                            <h3 class="text-2xl sm:text-3xl font-extrabold">
                                {{ source.name }}
                            </h3>
                            <p class="text-xs sm:text-sm text-red-100">
                                {{ source.description || 'ยังไม่ได้ระบุคำโปรยหรือช่องทางติดต่อ Line ID / เพจ' }}
                            </p>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <div class="px-5 py-3 rounded-2xl bg-white/15 backdrop-blur-xs border border-white/25 text-center">
                                <div class="text-xs text-red-100">ยอดผู้เข้าชมสำนักของคุณ</div>
                                <div class="text-2xl font-black text-yellow-300">
                                    {{ (source.view_count || 0).toLocaleString() }} ครั้ง
                                </div>
                            </div>
                            <a
                                :href="route('lucky-numbers.index')"
                                target="_blank"
                                class="px-4 py-3 rounded-2xl bg-yellow-400 hover:bg-yellow-300 text-gray-950 font-extrabold text-xs sm:text-sm shadow transition"
                            >
                                ดูหน้าเว็บจริง ↗
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                        <!-- คอลัมน์ซ้าย: 1. ข้อมูลสำนัก & 2. ลงเลขเด็ดประจำงวด -->
                        <div class="lg:col-span-5 space-y-6">
                            <!-- กล่องที่ 1: แก้ไขชื่อสำนัก & ช่องทางติดต่อ (Line ID / ชื่อเพจ) -->
                            <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 space-y-4">
                                <div>
                                    <h3 class="text-base font-extrabold text-gray-900">
                                        1. ข้อมูลสำนัก & ช่องทางติดต่อบนการ์ด
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        ข้อความนี้จะแสดงใต้ชื่อสำนักบนการ์ดเลขเด็ดเพื่อให้คอหวยแอด LINE หรือติดตามเพจของคุณ
                                    </p>
                                </div>

                                <form @submit.prevent="submitProfile" class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1">ชื่อสำนัก *</label>
                                        <input
                                            v-model="profileForm.name"
                                            type="text"
                                            required
                                            class="w-full rounded-xl border-gray-300 text-sm font-bold text-gray-900 focus:border-red-500 focus:ring-red-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1">
                                            คำโปรย / ชื่อเพจ / Line ID ติดต่อสำนัก
                                        </label>
                                        <textarea
                                            v-model="profileForm.description"
                                            rows="2"
                                            placeholder="เช่น เข้ากลุ่มแนวทางฟรี แอด Line: @myline หรือเพจ: อาจารย์ดังพารวย"
                                            class="w-full rounded-xl border-gray-300 text-sm text-gray-900 focus:border-red-500 focus:ring-red-500"
                                        ></textarea>
                                    </div>
                                    <button
                                        type="submit"
                                        :disabled="profileForm.processing"
                                        class="w-full py-2.5 rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-extrabold text-xs sm:text-sm shadow transition disabled:opacity-50"
                                    >
                                        บันทึกข้อมูลสำนักขึ้นหน้าเว็บทันที
                                    </button>
                                </form>
                            </div>

                            <!-- กล่องที่ 2: ลงเลขเด็ดประจำงวด -->
                            <div class="bg-white rounded-2xl shadow-md border-2 border-red-200 p-6 space-y-4">
                                <div>
                                    <span class="inline-block px-2.5 py-0.5 rounded-full bg-red-100 text-red-700 text-[11px] font-extrabold mb-1">
                                        อัปเดตสดทันที
                                    </span>
                                    <h3 class="text-base font-extrabold text-gray-900">
                                        2. ลงเลขเด็ดประจำงวดของสำนัก
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        เลือกงวดแล้วกรอกเลข 2 ตัว, 3 ตัว และเลขวิ่ง กดบันทึกแล้วขึ้นหน้าเว็บทันที
                                    </p>
                                </div>

                                <form @submit.prevent="submitNumbers" class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1">เลือกงวดวันที่ *</label>
                                        <select
                                            v-model="numberForm.draw_date"
                                            @change="onDrawDateChange"
                                            required
                                            class="w-full rounded-xl border-gray-300 text-sm font-bold text-gray-900 focus:border-red-500 focus:ring-red-500"
                                        >
                                            <option v-for="d in availableDrawDates" :key="d" :value="d">
                                                งวดวันที่ {{ d }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="grid grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">เลข 2 ตัว</label>
                                            <input
                                                v-model="numberForm.two_digit"
                                                type="text"
                                                maxlength="10"
                                                placeholder="เช่น 89"
                                                class="w-full rounded-xl border-gray-300 text-center text-lg font-black text-red-600 focus:border-red-500 focus:ring-red-500"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">เลข 3 ตัว</label>
                                            <input
                                                v-model="numberForm.three_digit"
                                                type="text"
                                                maxlength="10"
                                                placeholder="เช่น 589"
                                                class="w-full rounded-xl border-gray-300 text-center text-lg font-black text-red-600 focus:border-red-500 focus:ring-red-500"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">เลขวิ่ง</label>
                                            <input
                                                v-model="numberForm.running_numbers"
                                                type="text"
                                                placeholder="เช่น 8, 9"
                                                class="w-full rounded-xl border-gray-300 text-center text-base font-black text-gray-900 focus:border-red-500 focus:ring-red-500"
                                            />
                                        </div>
                                    </div>

                                    <button
                                        type="submit"
                                        :disabled="numberForm.processing"
                                        class="w-full py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-extrabold text-sm shadow-lg transition disabled:opacity-50"
                                    >
                                        บันทึกเลขเด็ดขึ้นหน้าเว็บทันที
                                    </button>
                                </form>

                                <!-- ประวัติเลขเด็ดที่ลงไว้ -->
                                <div v-if="numbers.length > 0" class="pt-4 border-t border-gray-100 space-y-2">
                                    <div class="text-xs font-bold text-gray-600">ประวัติเลขที่คุณลงไว้ (คลิกเพื่อแก้ไข):</div>
                                    <div class="max-h-52 overflow-y-auto space-y-1.5 pr-1">
                                        <button
                                            v-for="n in numbers"
                                            :key="n.id"
                                            type="button"
                                            @click="loadNumberToEdit(n)"
                                            class="w-full text-left p-2.5 rounded-xl border border-gray-200 hover:border-red-300 hover:bg-red-50/40 flex items-center justify-between text-xs transition"
                                        >
                                            <span class="font-bold text-gray-800">งวด {{ n.draw_date }}</span>
                                            <span class="space-x-2">
                                                <strong class="text-red-600">2 ตัว: {{ n.two_digit || '-' }}</strong>
                                                <strong class="text-amber-700">3 ตัว: {{ n.three_digit || '-' }}</strong>
                                                <span class="text-gray-600">วิ่ง: {{ n.running_numbers || '-' }}</span>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- คอลัมน์ขวา: 3. ลงสินค้าวัตถุมงคล & ของแก้เคล็ดของสำนัก (โชว์ 3 หน้าหลักทันที) -->
                        <div class="lg:col-span-7">
                            <div class="bg-white rounded-2xl shadow-md border-2 border-amber-300 p-6 space-y-5">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100">
                                    <div>
                                        <span class="inline-block px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 text-[11px] font-extrabold mb-1">
                                            แสดงผลอัตโนมัติใน 3 หน้าหลัก (หน้าแรก • เบอร์มงคล • ทำนายฝัน)
                                        </span>
                                        <h3 class="text-lg font-extrabold text-gray-900">
                                            3. สินค้าสายมู & วัตถุมงคลของสำนัก ({{ products.length }} รายการ)
                                        </h3>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            อัปโหลดรูปสินค้าและใส่ลิงก์สั่งซื้อ (เช่น ลิงก์ LINE สำนัก, เพจ, Shopee, TikTok) เมื่อกดบันทึกจะขึ้นโชว์หน้าเว็บทันที
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        @click="openCreateProduct"
                                        class="shrink-0 px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-400 to-yellow-500 hover:from-amber-500 hover:to-yellow-600 text-gray-950 font-extrabold text-xs sm:text-sm shadow transition"
                                    >
                                        + เพิ่มสินค้าของสำนัก
                                    </button>
                                </div>

                                <div v-if="products.length === 0" class="py-12 text-center space-y-3 bg-amber-50/40 rounded-2xl border border-dashed border-amber-300">
                                    <div class="text-sm font-bold text-gray-700">
                                        ยังไม่มีสินค้าสายมูของสำนักคุณบนหน้าเว็บ
                                    </div>
                                    <p class="text-xs text-gray-500 max-w-md mx-auto">
                                        กดปุ่ม "+ เพิ่มสินค้าของสำนัก" เพื่อนำวัตถุมงคล แผ่นทอง หรือเครื่องรางของสำนักคุณขึ้นโปรโมทบนหน้าแรก หน้าวิเคราะห์เบอร์มงคล และหน้าทำนายฝันได้ทันทีครับ
                                    </p>
                                    <button
                                        type="button"
                                        @click="openCreateProduct"
                                        class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs shadow transition"
                                    >
                                        + ลงสินค้าชิ้นแรกเลย
                                    </button>
                                </div>

                                <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div
                                        v-for="p in products"
                                        :key="p.id"
                                        class="rounded-2xl border border-gray-200 overflow-hidden bg-gray-50 flex flex-col justify-between"
                                    >
                                        <div>
                                            <div class="aspect-square bg-gray-100 overflow-hidden relative">
                                                <img
                                                    v-if="p.image_url"
                                                    :src="p.image_url"
                                                    :alt="p.title"
                                                    class="w-full h-full object-cover"
                                                />
                                                <div v-else class="w-full h-full flex items-center justify-center text-xs text-gray-400">
                                                    ไม่มีรูปภาพ
                                                </div>
                                                <span
                                                    :class="p.is_active ? 'bg-emerald-600 text-white' : 'bg-gray-600 text-white'"
                                                    class="absolute top-2.5 right-2.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold shadow"
                                                >
                                                    {{ p.is_active ? 'แสดงอยู่บนหน้าเว็บ' : 'ซ่อนชั่วคราว' }}
                                                </span>
                                            </div>
                                            <div class="p-4 space-y-1">
                                                <h4 class="font-extrabold text-sm text-gray-900 line-clamp-1">
                                                    {{ p.title }}
                                                </h4>
                                                <p v-if="p.description" class="text-xs text-gray-600 line-clamp-2">
                                                    {{ p.description }}
                                                </p>
                                                <a
                                                    :href="p.affiliate_url"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="inline-block text-xs text-red-600 font-bold hover:underline truncate max-w-full"
                                                >
                                                    ลิงก์ปลายทาง ↗
                                                </a>
                                            </div>
                                        </div>

                                        <div class="px-4 py-3 bg-white border-t border-gray-200 flex items-center justify-between gap-2">
                                            <button
                                                type="button"
                                                @click="openEditProduct(p)"
                                                class="px-3 py-1.5 rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-900 font-bold text-xs transition"
                                            >
                                                แก้ไขสินค้า
                                            </button>
                                            <button
                                                type="button"
                                                @click="deleteProduct(p)"
                                                class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-rose-100 text-gray-600 hover:text-rose-700 font-medium text-xs transition"
                                            >
                                                ลบ
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Modal เพิ่ม/แก้ไขสินค้าของสำนัก -->
        <div
            v-if="isProductModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-xs overflow-y-auto"
        >
            <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-gray-200 overflow-hidden my-8">
                <div class="px-6 py-4 bg-gray-900 text-white flex items-center justify-between">
                    <h3 class="text-base font-extrabold">
                        {{ productForm.id ? 'แก้ไขสินค้าสายมูของสำนัก' : 'เพิ่มสินค้าสายมูขึ้นหน้าเว็บทันที' }}
                    </h3>
                    <button
                        type="button"
                        @click="isProductModalOpen = false"
                        class="text-gray-400 hover:text-white text-lg font-bold"
                    >
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitProduct" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">ชื่อสินค้า / วัตถุมงคล *</label>
                        <input
                            v-model="productForm.title"
                            type="text"
                            required
                            placeholder="เช่น แผ่นทองท้าวเวสสุวรรณ เสริมโชคลาภแก้เคล็ด"
                            class="w-full rounded-xl border-gray-300 text-sm text-gray-900 focus:border-red-500 focus:ring-red-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">คำอธิบายสั้นๆ (พุทธคุณ / จุดเด่น)</label>
                        <textarea
                            v-model="productForm.description"
                            rows="2"
                            placeholder="เช่น ผ่านพิธีปลุกเสก พกติดเคสมือถือหรือกระเป๋าสตางค์เรียกทรัพย์"
                            class="w-full rounded-xl border-gray-300 text-sm text-gray-900 focus:border-red-500 focus:ring-red-500"
                        ></textarea>
                    </div>

                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-amber-950 mb-1">
                                อัปโหลดรูปภาพสินค้าจากเครื่อง / มือถือ
                            </label>
                            <input
                                type="file"
                                accept="image/*"
                                @change="onProductImageChange"
                                class="block w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-red-600 file:text-white file:font-bold hover:file:bg-red-700"
                            />
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">
                                หรือวางลิงก์รูปภาพ (Image URL)
                            </label>
                            <input
                                v-model="productForm.image_url"
                                type="text"
                                placeholder="https://..."
                                class="w-full rounded-xl border-gray-300 text-xs text-gray-900"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            ลิงก์สั่งซื้อ / ลิงก์ LINE หรือเพจของสำนัก *
                        </label>
                        <input
                            v-model="productForm.affiliate_url"
                            type="text"
                            required
                            placeholder="เช่น https://line.me/ti/p/~@myline หรือลิงก์ Shopee / TikTok"
                            class="w-full rounded-xl border-gray-300 text-sm text-gray-900 focus:border-red-500 focus:ring-red-500"
                        />
                        <p class="text-[11px] text-gray-500 mt-1">
                            เมื่อลูกค้าคลิกที่การ์ดสินค้าบนหน้าเว็บ ระบบจะพาลูกค้าไปยังลิงก์นี้ทันที
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                        <button
                            type="button"
                            @click="isProductModalOpen = false"
                            class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-xs font-bold text-gray-700"
                        >
                            ยกเลิก
                        </button>
                        <button
                            type="submit"
                            :disabled="productForm.processing"
                            class="px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs sm:text-sm shadow-lg transition disabled:opacity-50"
                        >
                            {{ productForm.processing ? 'กำลังบันทึก...' : 'ลงสินค้าขึ้นหน้าเว็บทันที' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
