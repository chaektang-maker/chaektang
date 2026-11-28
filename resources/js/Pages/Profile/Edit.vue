<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="ข้อมูลส่วนตัว & รหัสผ่าน" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-bold leading-tight text-gray-800"
            >
                ข้อมูลส่วนตัว & เปลี่ยนรหัสผ่าน
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div
                    class="bg-white p-4 shadow sm:rounded-2xl sm:p-8 border border-gray-100"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div
                    class="bg-white p-4 shadow sm:rounded-2xl sm:p-8 border border-gray-100"
                >
                    <UpdatePasswordForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
