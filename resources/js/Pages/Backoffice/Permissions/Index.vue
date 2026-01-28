<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    staffUsers: Array,
    allPermissions: Array,
});

const updateUserPermissions = (userId, permissionIds) => {
    router.put(route('backoffice.permissions.update', userId), {
        permission_ids: permissionIds,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by Inertia
        },
    });
};

const togglePermission = (user, permissionId) => {
    const currentPermissionIds = user.permissions.map(p => p.id);
    const newPermissionIds = currentPermissionIds.includes(permissionId)
        ? currentPermissionIds.filter(id => id !== permissionId)
        : [...currentPermissionIds, permissionId];
    
    updateUserPermissions(user.id, newPermissionIds);
};
</script>

<template>
    <Head title="จัดการสิทธิ์ Staff" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                จัดการสิทธิ์ Staff
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-6">
                            <p class="text-gray-600">
                                จัดการสิทธิ์การเข้าถึงเมนูต่างๆ สำหรับ Staff โดยเลือก checkbox ที่ต้องการให้ Staff คนนั้นๆ เข้าถึงได้
                            </p>
                        </div>

                        <div v-if="staffUsers.length === 0" class="text-center py-8 text-gray-500">
                            ยังไม่มี Staff ในระบบ
                        </div>

                        <div v-else class="space-y-6">
                            <div
                                v-for="user in staffUsers"
                                :key="user.id"
                                class="border border-gray-200 rounded-lg p-6"
                            >
                                <div class="mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ user.name }}</h3>
                                    <p class="text-sm text-gray-500">{{ user.email }}</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <label
                                        v-for="permission in allPermissions"
                                        :key="permission.id"
                                        class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="user.permissions.some(p => p.id === permission.id)"
                                            @change="togglePermission(user, permission.id)"
                                            class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500"
                                        />
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ permission.name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ permission.description }}
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
