<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    users: Array,
    allPermissions: Array,
});

const destroyUser = (id, name) => {
    if (!confirm(`ยืนยันลบผู้ใช้ "${name}"?`)) return;
    router.delete(route('backoffice.users.destroy', id));
};

const getRoleBadgeClass = (role) => {
    return role === 'admin' 
        ? 'bg-purple-100 text-purple-800' 
        : 'bg-blue-100 text-blue-800';
};

const getRoleLabel = (role) => {
    return role === 'admin' ? 'Admin' : 'Staff';
};
</script>

<template>
    <Head title="จัดการผู้ใช้" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    จัดการผู้ใช้
                </h2>
                <Link :href="route('backoffice.users.create')">
                    <PrimaryButton>+ เพิ่มผู้ใช้</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="users.length === 0" class="text-center py-8 text-gray-500">
                            ยังไม่มีผู้ใช้ในระบบ
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ชื่อ
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            อีเมล
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            บทบาท
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            สิทธิ์
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            จัดการ
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in users" :key="user.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ user.name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">
                                                {{ user.email }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span 
                                                :class="['px-2 py-1 text-xs font-semibold rounded-full', getRoleBadgeClass(user.role)]"
                                            >
                                                {{ getRoleLabel(user.role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-500">
                                                <div v-if="user.role === 'admin'" class="text-green-600 font-medium">
                                                    เข้าถึงได้ทุกอย่าง
                                                </div>
                                                <div v-else-if="user.permissions.length === 0" class="text-gray-400">
                                                    ไม่มีสิทธิ์
                                                </div>
                                                <div v-else class="flex flex-wrap gap-1">
                                                    <span 
                                                        v-for="permission in user.permissions" 
                                                        :key="permission.id"
                                                        class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded"
                                                    >
                                                        {{ permission.name }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link 
                                                :href="route('backoffice.users.edit', user.id)"
                                                class="text-indigo-600 hover:text-indigo-800 mr-4"
                                            >
                                                แก้ไข
                                            </Link>
                                            <button
                                                @click="destroyUser(user.id, user.name)"
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
