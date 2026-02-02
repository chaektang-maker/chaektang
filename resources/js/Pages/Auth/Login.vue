<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="เข้าสู่ระบบ" />

        <div class="w-full max-w-md">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">เข้าสู่ระบบ</h1>
                <p class="text-gray-600">ยินดีต้อนรับสู่ระบบจัดการหลังบ้าน</p>
            </div>

            <!-- Status Message -->
            <div v-if="status" class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-sm font-medium text-green-700">
                {{ status }}
            </div>

            <!-- Login Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Email Field -->
                <div>
                    <InputLabel for="email" value="อีเมล" class="text-gray-700 font-medium" />

                    <TextInput
                        id="email"
                        type="email"
                        class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="กรุณากรอกอีเมล"
                    />

                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <!-- Password Field -->
                <div>
                    <InputLabel for="password" value="รหัสผ่าน" class="text-gray-700 font-medium" />

                    <TextInput
                        id="password"
                        type="password"
                        class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="กรุณากรอกรหัสผ่าน"
                    />

                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" />
                        <span class="ms-2 text-sm text-gray-600">จดจำการเข้าสู่ระบบ</span>
                    </label>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm text-blue-600 hover:text-blue-800 hover:underline transition-colors"
                    >
                        ลืมรหัสผ่าน?
                    </Link>
                </div>

                <!-- Submit Button -->
                <div>
                    <PrimaryButton
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg shadow-md hover:shadow-lg transition-all duration-200"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="!form.processing">เข้าสู่ระบบ</span>
                        <span v-else class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            กำลังเข้าสู่ระบบ...
                        </span>
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
