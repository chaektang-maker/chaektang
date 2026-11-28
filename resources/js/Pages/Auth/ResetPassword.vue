<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="ตั้งรหัสผ่านใหม่ด้วยตัวเอง - แจกตัง" />

        <div class="w-full max-w-md space-y-6">
            <div class="text-center">
                <span class="inline-block px-3 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-extrabold mb-2">
                    ยืนยันตัวตนสำเร็จ
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900">
                    ตั้งรหัสผ่านใหม่ของคุณ
                </h1>
                <p class="text-xs sm:text-sm text-gray-600 mt-1">
                    กำหนดรหัสผ่านใหม่ (อย่างน้อย 8 ตัวอักษร) เพื่อใช้เข้าสู่ระบบครั้งต่อไป
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="email" value="เบอร์โทรศัพท์มือถือ หรือ อีเมลบัญชีของคุณ" class="font-bold text-gray-700" />
                    <TextInput
                        id="email"
                        type="text"
                        class="mt-1.5 block w-full bg-gray-50"
                        v-model="form.email"
                        required
                        autocomplete="username"
                    />
                    <InputError class="mt-1.5" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="password" value="รหัสผ่านใหม่ (อย่างน้อย 8 ตัวอักษร) *" class="font-bold text-gray-700" />
                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1.5 block w-full"
                        v-model="form.password"
                        required
                        autofocus
                        autocomplete="new-password"
                        placeholder="กรอกรหัสผ่านใหม่ที่คุณต้องการ"
                    />
                    <InputError class="mt-1.5" :message="form.errors.password" />
                </div>

                <div>
                    <InputLabel
                        for="password_confirmation"
                        value="ยืนยันรหัสผ่านใหม่อีกครั้ง *"
                        class="font-bold text-gray-700"
                    />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="mt-1.5 block w-full"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="กรอกรหัสผ่านใหม่อีกครั้งให้ตรงกัน"
                    />
                    <InputError
                        class="mt-1.5"
                        :message="form.errors.password_confirmation"
                    />
                </div>

                <div class="pt-2">
                    <PrimaryButton
                        class="w-full justify-center py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-extrabold text-sm shadow-lg"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        <span>{{ form.processing ? 'กำลังบันทึกรหัสผ่านใหม่...' : 'บันทึกรหัสผ่านใหม่' }}</span>
                    </PrimaryButton>
                </div>

                <div class="text-center pt-2">
                    <Link :href="route('login')" class="text-xs font-bold text-gray-500 hover:text-red-600">
                        ← ยกเลิกและกลับหน้าเข้าสู่ระบบ
                    </Link>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
