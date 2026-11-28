<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    source_name: '',
    description: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('backportal.register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="สมัครเปิดบัญชีเจ้าสำนัก & ร้านค้าสายมู - แจกตัง" />

        <div class="w-full max-w-md">
            <div class="text-center mb-6">
                <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-amber-100 text-amber-900 border border-amber-300 text-xs font-extrabold mb-3">
                    <span>ลงทะเบียนเจ้าสำนัก & ร้านค้าพาร์ทเนอร์</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-2">
                    สมัครเปิดสำนักบนเว็บแจกตัง
                </h1>
                <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                    สร้างบัญชีเพื่อลงเลขเด็ดประจำงวด แก้ไขช่องทางติดต่อ (Line ID / ชื่อเพจ) และลงสินค้าสายมูของสำนักคุณ
                </p>
            </div>

            <!-- กล่องแจ้งขั้นตอนการเปิดแสดงผล -->
            <div class="mb-6 p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 leading-relaxed">
                <div class="font-extrabold text-amber-950 mb-0.5">
                    ขั้นตอนหลังสมัครเสร็จ:
                </div>
                หลังสมัคร คุณสามารถเข้าพอร์ทัลไป <strong>ลงเลขเด็ดและเพิ่มสินค้าไว้ล่วงหน้าได้ทันที</strong> โดยสำนักจะถูกซ่อนไว้ชั่วคราวและจะแสดงขึ้นหน้าเว็บทันทีเมื่อแอดมินกดอนุมัติเปิดสำนักครับ
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- 1. ชื่อสำนัก -->
                <div>
                    <InputLabel for="source_name" value="ชื่อสำนักหวย / ชื่อเพจอาจารย์ *" class="text-gray-800 font-bold" />
                    <TextInput
                        id="source_name"
                        type="text"
                        class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                        v-model="form.source_name"
                        required
                        autofocus
                        placeholder="เช่น สำนักอาจารย์ดัง พารวย"
                    />
                    <InputError class="mt-1.5" :message="form.errors.source_name" />
                </div>

                <!-- 2. คำโปรย / ช่องทางติดต่อสำนัก -->
                <div>
                    <InputLabel for="description" value="ช่องทางติดต่อสำนัก (Line ID / ชื่อเพจ Facebook)" class="text-gray-700 font-medium" />
                    <TextInput
                        id="description"
                        type="text"
                        class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                        v-model="form.description"
                        placeholder="เช่น แอดไลน์เข้ากลุ่มฟรี Line: @myline หรือเพจ: อาจารย์ดัง"
                    />
                    <p class="mt-1 text-[11px] text-gray-500">
                        ข้อความนี้จะแสดงบนการ์ดสำนักของคุณ (สามารถแก้ไขภายหลังได้ตลอดเวลา)
                    </p>
                    <InputError class="mt-1.5" :message="form.errors.description" />
                </div>

                <!-- 3. ชื่อผู้ดูแลสำนัก -->
                <div>
                    <InputLabel for="name" value="ชื่อผู้ดูแลสำนัก / ชื่ออาจารย์ *" class="text-gray-800 font-bold" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                        v-model="form.name"
                        required
                        placeholder="เช่น อาจารย์สมชาย"
                    />
                    <InputError class="mt-1.5" :message="form.errors.name" />
                </div>

                <!-- 4. เบอร์โทร หรือ อีเมล สำหรับล็อกอิน -->
                <div>
                    <InputLabel for="email" value="เบอร์โทรศัพท์มือถือ (10 หลัก) หรือ อีเมล (ใช้ล็อกอิน) *" class="text-gray-800 font-bold" />
                    <TextInput
                        id="email"
                        type="text"
                        inputmode="text"
                        class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        placeholder="เช่น 0812345678 หรือ email@example.com"
                    />
                    <InputError class="mt-1.5" :message="form.errors.email" />
                </div>

                <!-- 5. รหัสผ่าน -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <InputLabel for="password" value="ตั้งรหัสผ่าน *" class="text-gray-800 font-bold" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            placeholder="อย่างน้อย 8 ตัวอักษร"
                        />
                        <InputError class="mt-1.5" :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="ยืนยันรหัสผ่าน *" class="text-gray-800 font-bold" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="กรอกรหัสผ่านอีกครั้ง"
                        />
                        <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
                    </div>
                </div>

                <div class="pt-2">
                    <PrimaryButton
                        class="w-full justify-center bg-gradient-to-r from-red-600 to-amber-600 hover:from-red-700 hover:to-amber-700 text-white font-extrabold py-3.5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="!form.processing">ยืนยันสมัครเปิดบัญชีเจ้าสำนัก</span>
                        <span v-else>กำลังสร้างบัญชีสำนัก...</span>
                    </PrimaryButton>
                </div>

                <div class="pt-4 border-t border-gray-100 text-center space-y-2">
                    <div class="text-sm text-gray-600">
                        มีบัญชีเจ้าสำนักอยู่แล้ว?
                        <Link :href="route('backportal.login')" class="font-extrabold text-red-600 hover:text-red-700 hover:underline ms-1">
                            เข้าสู่ระบบพอร์ทัลเจ้าสำนัก
                        </Link>
                    </div>
                    <div>
                        <Link :href="route('advertise.index')" class="text-xs font-semibold text-gray-500 hover:text-red-600">
                            ดูรายละเอียดแพ็กเกจโปรโมทสำนักแนะนำกรอบทอง →
                        </Link>
                    </div>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
