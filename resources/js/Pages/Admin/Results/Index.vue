<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

defineProps({
    results: Object,
});

const page = usePage();

const destroyResult = (id) => {
    if (!confirm('ยืนยันลบผลหวยนี้?')) return;
    router.delete(route('backoffice.results.destroy', id));
};
</script>

<template>
    <Head title="บันทึกผลหวย" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">บันทึกผลหวย</h2>
                <div class="flex items-center gap-2">
                    <Link :href="route('backoffice.results.import')">
                        <SecondaryButton>📥 ดึงข้อมูลย้อนหลัง</SecondaryButton>
                    </Link>
                    <Link :href="route('backoffice.results.create')">
                        <PrimaryButton>+ เพิ่มผลหวย</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
                <div v-if="page.props.flash?.success" class="rounded-lg bg-green-50 border border-green-200 p-4 text-green-800">
                    {{ page.props.flash.success }}
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">งวด</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">รางวัลที่ 1</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">2 ตัว</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">3 ตัว</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">คำนวณแล้ว</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="r in results.data" :key="r.id">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ r.draw_date }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ r.first_prize }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ r.last_two_digit }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ r.last_three_digit }}</td>
                                    <td class="px-6 py-4">
                                        <span v-if="r.is_calculated" class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 border border-green-200">ใช่</span>
                                        <span v-else class="inline-flex items-center rounded-full bg-gray-50 px-2 py-0.5 text-xs font-medium text-gray-600 border border-gray-200">ยัง</span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <Link class="text-indigo-600 hover:text-indigo-800 font-medium" :href="route('backoffice.results.edit', r.id)">แก้ไข</Link>
                                        <button class="ml-3 text-red-600 hover:text-red-800 font-medium" type="button" @click="destroyResult(r.id)">ลบ</button>
                                    </td>
                                </tr>
                                <tr v-if="!results.data?.length">
                                    <td class="px-6 py-6 text-center text-sm text-gray-500" colspan="6">ยังไม่มีข้อมูล</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 flex flex-wrap gap-2">
                        <Link
                            v-for="link in results.links"
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

