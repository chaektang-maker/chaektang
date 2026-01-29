<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    numbers: Object,
    sources: Array,
    filters: Object,
    availableDrawDates: Array,
});

const page = usePage();

const localFilters = reactive({
    source_id: props.filters?.source_id ?? '',
    draw_date: props.filters?.draw_date ?? '',
});

const applyFilters = (e) => {
    e?.preventDefault?.();
    router.get(route('backoffice.numbers.index'), {
        source_id: localFilters.source_id || '',
        draw_date: localFilters.draw_date || '',
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    localFilters.source_id = '';
    localFilters.draw_date = '';
    router.get(route('backoffice.numbers.index'), {}, { preserveState: true, replace: true });
};

const destroyNumber = (id) => {
    if (!confirm('ยืนยันลบรายการเลขนี้?')) return;
    router.delete(route('backoffice.numbers.destroy', id));
};

const fmtRunning = (arr) => {
    if (!arr) return '-';
    if (Array.isArray(arr)) return arr.join(', ');
    return String(arr);
};
</script>

<template>
    <Head title="จัดการเลขสำนัก" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">จัดการเลขสำนัก</h2>
                <Link :href="route('backoffice.numbers.create')">
                    <PrimaryButton>+ เพิ่มเลข</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
                <div v-if="page.props.flash?.success" class="rounded-lg bg-green-50 border border-green-200 p-4 text-green-800">
                    {{ page.props.flash.success }}
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <form class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end" @submit="applyFilters">
                        <div>
                            <InputLabel value="สำนัก" />
                            <select v-model="localFilters.source_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">ทั้งหมด</option>
                                <option v-for="s in sources" :key="s.id" :value="s.id">
                                    {{ s.name }} ({{ s.status }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="งวด (จาก lotto_data)" />
                            <select v-model="localFilters.draw_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">ทั้งหมด</option>
                                <option v-for="d in availableDrawDates" :key="d" :value="d">{{ d }}</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <PrimaryButton type="submit">กรอง</PrimaryButton>
                            <SecondaryButton type="button" @click="clearFilters">ล้าง</SecondaryButton>
                        </div>
                    </form>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">งวด</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สำนัก</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">2 ตัว</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">3 ตัว</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วิ่ง</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="n in numbers.data" :key="n.id">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ n.draw_date }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ n.source?.name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ n.two_digit || '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ n.three_digit || '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ fmtRunning(n.running_numbers) }}</td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <Link class="text-indigo-600 hover:text-indigo-800 font-medium" :href="route('backoffice.numbers.edit', n.id)">แก้ไข</Link>
                                        <button class="ml-3 text-red-600 hover:text-red-800 font-medium" type="button" @click="destroyNumber(n.id)">ลบ</button>
                                    </td>
                                </tr>
                                <tr v-if="!numbers.data?.length">
                                    <td class="px-6 py-6 text-center text-sm text-gray-500" colspan="6">ยังไม่มีข้อมูล</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 flex flex-wrap gap-2">
                        <Link
                            v-for="link in numbers.links"
                            :key="link.label"
                            :href="link.url || ''"
                            class="px-3 py-1 rounded border text-sm"
                            :class="link.active ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-200'"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

