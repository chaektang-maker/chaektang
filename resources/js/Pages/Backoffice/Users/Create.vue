<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    allPermissions: Array,
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'staff',
    permission_ids: [],
});

const submit = () => {
    form.post(route('backoffice.users.store'));
};

const togglePermission = (permissionId) => {
    const index = form.permission_ids.indexOf(permissionId);
    if (index > -1) {
        form.permission_ids.splice(index, 1);
    } else {
        form.permission_ids.push(permissionId);
    }
};
</script>

<template>
    <Head title="เพิ่มผู้ใช้" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                เพิ่มผู้ใช้
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- ชื่อ -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                ชื่อ
                            </label>
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <!-- อีเมล -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                อีเมล
                            </label>
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>

                        <!-- รหัสผ่าน -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                รหัสผ่าน
                            </label>
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.password" class="mt-2" />
                        </div>

                        <!-- ยืนยันรหัสผ่าน -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                ยืนยันรหัสผ่าน
                            </label>
                            <TextInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.password_confirmation" class="mt-2" />
                        </div>

                        <!-- บทบาท -->
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700">
                                บทบาท
                            </label>
                            <select
                                id="role"
                                v-model="form.role"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="staff">Staff</option>
                                <option value="admin">Admin</option>
                            </select>
                            <InputError :message="form.errors.role" class="mt-2" />
                        </div>

                        <!-- สิทธิ์ (เฉพาะ Staff) -->
                        <div v-if="form.role === 'staff'">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                สิทธิ์การเข้าถึง
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label
                                    v-for="permission in allPermissions"
                                    :key="permission.id"
                                    class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="form.permission_ids.includes(permission.id)"
                                        @change="togglePermission(permission.id)"
                                        class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
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
                            <InputError :message="form.errors.permission_ids" class="mt-2" />
                        </div>

                        <div v-else class="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                            <p class="text-sm text-purple-800">
                                <strong>Admin</strong> จะมีสิทธิ์เข้าถึงทุกเมนูโดยอัตโนมัติ
                            </p>
                        </div>

                        <!-- ปุ่ม -->
                        <div class="flex items-center justify-end gap-4">
                            <Link :href="route('backoffice.users.index')">
                                <SecondaryButton type="button">ยกเลิก</SecondaryButton>
                            </Link>
                            <PrimaryButton :disabled="form.processing">
                                สร้างผู้ใช้
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
