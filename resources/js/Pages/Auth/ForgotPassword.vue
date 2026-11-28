<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const page = usePage();
const contact = computed(() => page.props.contactInfo || {
    line_id: '@chaektang',
    line_url: 'https://line.me/ti/p/~@chaektang',
    phone: '083-487-1695',
});

const form = useForm({
    email: '',
    name: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="ลืมรหัสผ่าน / ตั้งรหัสผ่านใหม่ - แจกตัง" />

        <div class="w-full max-w-md space-y-6">
            <div class="text-center">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-2">
                    ลืมรหัสผ่านใช่หรือไม่?
                </h1>
                <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                    คุณสามารถยืนยัน <strong>เบอร์โทรศัพท์ (หรืออีเมล)</strong> คู่กับ <strong>ชื่อที่ใช้สมัคร</strong> เพื่อไปหน้าตั้งรหัสผ่านใหม่ด้วยตัวเองได้ทันทีโดยไม่ต้องเปิดอีเมลครับ
                </p>
            </div>

            <div
                v-if="status"
                class="p-4 rounded-xl bg-emerald-50 border border-emerald-300 text-sm font-bold text-emerald-800"
            >
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="email" value="1. เบอร์โทรศัพท์มือถือ หรือ อีเมลที่ใช้สมัคร *" class="font-bold text-gray-800" />
                    <TextInput
                        id="email"
                        type="text"
                        class="mt-1.5 block w-full rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="เช่น 0891234567 หรืออีเมลของคุณ"
                    />
                    <InputError class="mt-1.5" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="name" value="2. ชื่อที่ใช้แสดงตอนสมัครสมาชิก *" class="font-bold text-gray-800" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1.5 block w-full rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500"
                        v-model="form.name"
                        placeholder="เช่น สมชาย สายเฮง"
                    />
                    <p class="mt-1 text-[11px] text-gray-500">
                        * เมื่อชื่อและเบอร์โทร/อีเมลตรงกัน ระบบจะพาไปหน้าตั้งรหัสผ่านใหม่ทันที (หากใช้อีเมลและเว้นว่างช่องชื่อไว้ ระบบจะส่งลิงก์ไปทางอีเมล)
                    </p>
                    <InputError class="mt-1.5" :message="form.errors.name" />
                </div>

                <div class="pt-1">
                    <PrimaryButton
                        class="w-full justify-center py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-extrabold text-sm shadow-lg"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        <span>{{ form.processing ? 'กำลังตรวจสอบ...' : 'ไปหน้าตั้งรหัสผ่านใหม่ด้วยตัวเอง →' }}</span>
                    </PrimaryButton>
                </div>
            </form>

            <!-- ทางเลือกสำหรับผู้สูงอายุ หรือจำชื่อที่สมัครไม่ได้ -->
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 space-y-2.5 text-center">
                <div class="text-xs font-extrabold text-amber-950">
                    จำชื่อหรือเบอร์ที่ใช้สมัครไม่ได้ใช่ไหมครับ?
                </div>
                <p class="text-xs text-amber-800 leading-relaxed">
                    ทักแชท LINE แจ้งแอดมินได้เลยครับ แอดมินสามารถส่งลิงก์ให้คุณกดตั้งรหัสผ่านใหม่ด้วยตัวเองได้ทันที
                </p>
                <a
                    :href="contact.line_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-4 rounded-xl bg-[#06C755] hover:bg-[#05b34c] text-white font-extrabold text-xs shadow transition"
                >
                    <span>ทัก LINE แอดมิน ({{ contact.line_id }}) เพื่อขอลิงก์ตั้งรหัสใหม่</span>
                </a>
            </div>

            <div class="text-center pt-2 border-t border-gray-100">
                <Link :href="route('login')" class="text-xs font-bold text-gray-600 hover:text-red-600">
                    ← กลับไปหน้าเข้าสู่ระบบ
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>
