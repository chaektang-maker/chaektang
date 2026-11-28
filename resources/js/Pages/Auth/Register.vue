<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="สมัครสมาชิก - แจกตัง" />

        <div class="w-full max-w-md">
            <!-- Header -->
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">สมัครสมาชิกฟรี</h1>
                <p class="text-sm text-gray-600">
                    ใช้เพียงเบอร์โทรศัพท์มือถือ (หรืออีเมล) เพื่อรับสิทธิ์โหวตสำนักหวย และสมัคร VIP ดูเลขเด็ดเจาะลึก
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <InputLabel for="name" value="ชื่อที่ใช้แสดง / ชื่อ-นามสกุล *" class="text-gray-700 font-medium" />

                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="เช่น สมชาย สายเฮง"
                    />

                    <InputError class="mt-1.5" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="email" value="เบอร์โทรศัพท์มือถือ 10 หลัก (หรือ อีเมล) *" class="text-gray-700 font-medium" />

                    <TextInput
                        id="email"
                        type="text"
                        class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        placeholder="กรอกเบอร์มือถือ เช่น 0891234567 (หรืออีเมลของคุณ)"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        * หากไม่มีอีเมล สามารถใช้เบอร์โทรศัพท์มือถือ 10 หลักของคุณแทนได้เลยครับ
                    </p>

                    <InputError class="mt-1.5" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="password" value="รหัสผ่าน (อย่างน้อย 8 ตัวอักษร)" class="text-gray-700 font-medium" />

                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                        placeholder="ตั้งรหัสผ่าน"
                    />

                    <InputError class="mt-1.5" :message="form.errors.password" />
                </div>

                <div>
                    <InputLabel
                        for="password_confirmation"
                        value="ยืนยันรหัสผ่านอีกครั้ง"
                        class="text-gray-700 font-medium"
                    />

                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="กรอกรหัสผ่านอีกครั้ง"
                    />

                    <InputError
                        class="mt-1.5"
                        :message="form.errors.password_confirmation"
                    />
                </div>

                <div class="rounded-lg bg-gray-50 border border-gray-200 p-3 text-xs text-gray-600 leading-relaxed">
                    🔒 <strong>การคุ้มครองข้อมูลส่วนบุคคล (PDPA):</strong> ข้อมูลชื่อ อีเมล และเบอร์โทรศัพท์ของท่านใช้สำหรับการยืนยันตัวตนและให้บริการสมาชิกบนเว็บไซต์แจกตังเท่านั้น ทางเว็บไซต์จัดเก็บข้อมูลของท่านอย่างปลอดภัยและไม่เปิดเผยต่อบุคคลภายนอก
                </div>

                <div class="pt-2">
                    <PrimaryButton
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 justify-center"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="!form.processing">สมัครสมาชิก</span>
                        <span v-else>กำลังสร้างบัญชี...</span>
                    </PrimaryButton>
                </div>

                <div class="pt-4 border-t border-gray-200 text-center text-sm text-gray-600">
                    มีบัญชีสมาชิกอยู่แล้ว?
                    <Link
                        :href="route('login')"
                        class="font-bold text-red-600 hover:text-red-700 hover:underline ml-1"
                    >
                        เข้าสู่ระบบที่นี่
                    </Link>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
