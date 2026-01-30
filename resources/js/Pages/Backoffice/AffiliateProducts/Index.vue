<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    products: Array,
    platforms: Array,
    filterPlatformId: [Number, null],
});

const destroy = (id, title) => {
    if (!confirm(`ยืนยันลบสินค้า "${title}"?`)) return;
    router.delete(route('backoffice.affiliate-products.destroy', id));
};

const filterUrl = (platformId) => {
    if (!platformId) return route('backoffice.affiliate-products.index');
    return route('backoffice.affiliate-products.index', { platform_id: platformId });
};
</script>

<template>
    <Head title="จัดการสินค้าวัตถุมงคล" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    จัดการสินค้าวัตถุมงคล (Affiliate)
                </h2>
                <div class="flex items-center gap-3">
                    <select
                        v-if="platforms.length"
                        :value="filterPlatformId ?? ''"
                        class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        @change="(e) => router.get(filterUrl(e.target.value ? Number(e.target.value) : null))"
                    >
                        <option value="">ทุกแพลตฟอร์ม</option>
                        <option v-for="p in platforms" :key="p.id" :value="p.id">
                            {{ p.name }}
                        </option>
                    </select>
                    <Link :href="route('backoffice.affiliate-products.create', filterPlatformId ? { platform_id: filterPlatformId } : {})">
                        <PrimaryButton>+ เพิ่มสินค้า</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="products.length === 0" class="text-center py-8 text-gray-500">
                            ยังไม่มีสินค้า — เพิ่มแพลตฟอร์มจากเมนู "แพลตฟอร์ม" ก่อน แล้วค่อยเพิ่มสินค้า
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            รูป
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ชื่อสินค้า
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            แพลตฟอร์ม
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ลำดับ
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
                                    <tr v-for="p in products" :key="p.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <img
                                                v-if="p.image_url"
                                                :src="p.image_url"
                                                :alt="p.title"
                                                class="h-12 w-12 rounded object-cover"
                                            />
                                            <span v-else class="text-gray-400 text-sm">—</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ p.title }}</div>
                                            <div v-if="p.description" class="text-xs text-gray-500 line-clamp-2 max-w-xs">
                                                {{ p.description }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ p.platform_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ p.sort_order }}
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
                                                :href="route('backoffice.affiliate-products.edit', p.id)"
                                                class="text-indigo-600 hover:text-indigo-800 mr-4"
                                            >
                                                แก้ไข
                                            </Link>
                                            <button
                                                @click="destroy(p.id, p.title)"
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
