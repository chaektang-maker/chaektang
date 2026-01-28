<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    sources: Object,
    filters: Object,
});

const page = usePage();

const applyFilters = (e) => {
    e?.preventDefault?.();
    router.get(route('backoffice.sources.index'), {
        q: props.filters?.q || '',
        status: props.filters?.status || '',
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    router.get(route('backoffice.sources.index'), {}, { preserveState: true, replace: true });
};

const destroySource = (id) => {
    if (!confirm('ยืนยันลบสำนักนี้?')) return;
    router.delete(route('backoffice.sources.destroy', id));
};
</script>

<template>
    <Head title="จัดการสำนักหวย" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    จัดการสำนักหวย
                </h2>
                <Link :href="route('backoffice.sources.create')">
                    <PrimaryButton>+ เพิ่มสำนัก</PrimaryButton>
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
                            <InputLabel value="ค้นหา (ชื่อสำนัก)" />
                            <TextInput v-model="props.filters.q" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="สถานะ" />
                            <select v-model="props.filters.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">ทั้งหมด</option>
                                <option value="active">เปิดใช้งาน</option>
                                <option value="suspended">ระงับ</option>
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สำนัก</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ความนิยม</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">โปรโมท</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="s in sources.data" :key="s.id">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ s.name }}</div>
                                        <div class="text-sm text-gray-500 line-clamp-2">{{ s.description }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span v-if="s.status === 'active'" class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 border border-green-200">เปิดใช้งาน</span>
                                        <span v-else class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-0.5 text-xs font-medium text-yellow-700 border border-yellow-200">ระงับ</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ s.popularity_score }}</td>
                                    <td class="px-6 py-4">
                                        <span v-if="s.is_promoted" class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 border border-indigo-200">แนะนำ</span>
                                        <span v-else class="text-sm text-gray-400">-</span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <Link class="text-indigo-600 hover:text-indigo-800 font-medium" :href="route('backoffice.sources.edit', s.id)">แก้ไข</Link>
                                        <button class="ml-3 text-red-600 hover:text-red-800 font-medium" type="button" @click="destroySource(s.id)">ลบ</button>
                                    </td>
                                </tr>
                                <tr v-if="!sources.data?.length">
                                    <td class="px-6 py-6 text-center text-sm text-gray-500" colspan="5">ยังไม่มีข้อมูล</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 flex flex-wrap gap-2">
                        <Link
                            v-for="link in sources.links"
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

