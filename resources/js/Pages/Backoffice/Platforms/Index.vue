<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    platforms: Array,
});

const destroy = (id, name) => {
    if (!confirm(`ยืนยันลบแพลตฟอร์ม "${name}"? สินค้าในแพลตฟอร์มนี้จะถูกลบด้วย`)) return;
    router.delete(route('backoffice.platforms.destroy', id));
};
</script>

<template>
    <Head title="จัดการแพลตฟอร์ม" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    จัดการแพลตฟอร์ม
                </h2>
                <Link :href="route('backoffice.platforms.create')">
                    <PrimaryButton>+ เพิ่มแพลตฟอร์ม</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="platforms.length === 0" class="text-center py-8 text-gray-500">
                            ยังไม่มีแพลตฟอร์ม — เพิ่มแพลตฟอร์มก่อน แล้วค่อยเพิ่มสินค้าวัตถุมงคลในแต่ละแพลตฟอร์ม
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ชื่อ
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Slug
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ลำดับ
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            สินค้า
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            แสดง
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            จัดการ
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="p in platforms" :key="p.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <img
                                                    v-if="p.logo_url"
                                                    :src="p.logo_url"
                                                    :alt="p.name"
                                                    class="h-8 w-8 rounded object-contain"
                                                />
                                                <span class="text-sm font-medium text-gray-900">{{ p.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ p.slug || '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ p.sort_order }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Link
                                                :href="route('backoffice.affiliate-products.index', { platform_id: p.id })"
                                                class="text-indigo-600 hover:text-indigo-800 font-medium"
                                            >
                                                {{ p.affiliate_products_count }} รายการ
                                            </Link>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="p.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                                                class="px-2 py-1 text-xs font-semibold rounded-full"
                                            >
                                                {{ p.is_active ? 'เปิด' : 'ปิด' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link
                                                :href="route('backoffice.platforms.edit', p.id)"
                                                class="text-indigo-600 hover:text-indigo-800 mr-4"
                                            >
                                                แก้ไข
                                            </Link>
                                            <button
                                                @click="destroy(p.id, p.name)"
                                                class="text-red-600 hover:text-red-800"
                                            >
                                                ลบ
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
