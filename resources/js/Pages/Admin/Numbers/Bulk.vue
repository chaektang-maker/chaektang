<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    availableDrawDates: {
        type: Array,
        default: () => [],
    },
    selectedDrawDate: {
        type: String,
        default: '',
    },
    rows: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const searchQuery = ref('');

const form = useForm({
    draw_date: props.selectedDrawDate || '',
    items: (props.rows || []).map((r) => ({ ...r })),
});

watch(
    () => props.rows,
    (newRows) => {
        form.draw_date = props.selectedDrawDate || '';
        form.items = (newRows || []).map((r) => ({ ...r }));
    },
    { deep: true }
);

const onDrawDateChange = () => {
    router.get(
        route('backoffice.numbers.bulk'),
        { draw_date: form.draw_date },
        { preserveState: false, replace: true }
    );
};

const filteredItems = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return form.items;
    return form.items.filter((item) => item.source_name?.toLowerCase().includes(q));
});

const filledCount = computed(() => {
    return form.items.filter(
        (i) =>
            String(i.two_digit || '').trim() !== '' ||
            String(i.three_digit || '').trim() !== '' ||
            String(i.running_numbers || '').trim() !== ''
    ).length;
});

const submit = () => {
    form.post(route('backoffice.numbers.bulk.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="กรอกเลขด่วนประจำงวด (Bulk Entry)" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-gray-800">
                        ⚡ กรอกเลขเด็ดด่วนทั้งงวด (Bulk Entry)
                    </h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        คีย์เลขเด็ดของทุกสำนักในตารางเดียว แล้วกดบันทึกพร้อมกันในคลิกเดียว
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('backoffice.numbers.index')">
                        <SecondaryButton>← กลับหน้ารายการเลขสำนัก</SecondaryButton>
                    </Link>
                    <PrimaryButton type="button" :disabled="form.processing" @click="submit">
                        💾 บันทึกทั้งหมด ({{ filledCount }}/{{ form.items.length }} สำนัก)
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-5">
                <div v-if="page.props.flash?.success" class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 font-medium">
                    ✅ {{ page.props.flash.success }}
                </div>
                <div v-if="page.props.flash?.error" class="rounded-xl bg-red-50 border border-red-200 p-4 text-red-800 font-medium">
                    ⚠️ {{ page.props.flash.error }}
                </div>

                <!-- Control Bar: เลือกงวด & ค้นหาสำนัก -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-end gap-4 flex-1">
                        <div class="w-full sm:w-64">
                            <InputLabel value="เลือกงวดหวยที่ต้องการลงเลข" />
                            <select
                                v-model="form.draw_date"
                                @change="onDrawDateChange"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-semibold text-gray-900"
                            >
                                <option v-for="d in availableDrawDates" :key="d" :value="d">
                                    งวดวันที่ {{ d }}
                                </option>
                            </select>
                        </div>

                        <div class="flex-1 max-w-md">
                            <InputLabel value="ค้นหาชื่อสำนักในตาราง" />
                            <TextInput
                                v-model="searchQuery"
                                type="text"
                                placeholder="พิมพ์ชื่อสำนักเพื่อกรอง..."
                                class="mt-1 block w-full text-sm"
                            />
                        </div>
                    </div>

                    <div class="text-sm text-gray-600 bg-gray-50 px-4 py-2.5 rounded-xl border border-gray-200">
                        กรอกแล้ว <strong class="text-indigo-600 text-base">{{ filledCount }}</strong> จากทั้งหมด
                        <strong>{{ form.items.length }}</strong> สำนักที่เปิดใช้งาน
                    </div>
                </div>

                <!-- Bulk Table -->
                <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-12">#</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ชื่อสำนัก</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-40">เลข 2 ตัว</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-44">เลข 3 ตัว</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-56">เลขวิ่ง (คั่นด้วย ,)</th>
                                    <th class="px-5 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-28">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr
                                    v-for="(item, idx) in filteredItems"
                                    :key="item.source_id"
                                    class="hover:bg-indigo-50/30 transition"
                                >
                                    <td class="px-5 py-3 text-xs text-gray-400 font-medium">
                                        {{ idx + 1 }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-sm text-gray-900">{{ item.source_name }}</span>
                                            <span
                                                v-if="item.is_promoted"
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300"
                                            >
                                                ⭐ แนะนำ
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3">
                                        <TextInput
                                            v-model="item.two_digit"
                                            type="text"
                                            maxlength="10"
                                            placeholder="เช่น 89"
                                            class="w-full text-center font-bold tracking-wider"
                                        />
                                    </td>
                                    <td class="px-5 py-3">
                                        <TextInput
                                            v-model="item.three_digit"
                                            type="text"
                                            maxlength="10"
                                            placeholder="เช่น 789"
                                            class="w-full text-center font-bold tracking-wider"
                                        />
                                    </td>
                                    <td class="px-5 py-3">
                                        <TextInput
                                            v-model="item.running_numbers"
                                            type="text"
                                            maxlength="50"
                                            placeholder="เช่น 8,9"
                                            class="w-full text-center font-semibold"
                                        />
                                    </td>
                                    <td class="px-5 py-3 text-center whitespace-nowrap">
                                        <span
                                            v-if="item.two_digit || item.three_digit || item.running_numbers"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800"
                                        >
                                            พร้อมบันทึก
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500"
                                        >
                                            ว่าง
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="!filteredItems.length">
                                    <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                        ไม่พบสำนักที่ตรงกับคำค้นหา
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <p class="text-xs text-gray-500">
                            💡 เคล็ดลับ: สำนักไหนยังไม่ออกเลขสามารถเว้นว่างไว้ได้ ระบบจะบันทึกเฉพาะสำนักที่มีการกรอกตัวเลขเท่านั้น
                        </p>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            💾 บันทึกเลขเด็ดทั้งหมด ({{ filledCount }} สำนัก)
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

