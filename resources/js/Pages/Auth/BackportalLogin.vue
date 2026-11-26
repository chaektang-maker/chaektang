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
    form.post(route('backportal.login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="เข้าสู่ระบบพอร์ทัลเจ้าสำนัก & พาร์ทเนอร์ - แจกตัง" />

        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-amber-100 text-amber-900 border border-amber-300 text-xs font-extrabold mb-3">
                    <span>Partner & Source Owner Portal</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-2">
                    พอร์ทัลเจ้าสำนัก & ร้านค้าสายมู
                </h1>
                <p class="text-sm text-gray-600">
                    เข้าสู่ระบบเพื่อลงเลขเด็ดประจำงวด แก้ไขช่องทางติดต่อ และจัดการสินค้าสายมูของสำนักคุณ
                </p>
            </div>

            <div v-if="status" class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-sm font-medium text-green-700">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <InputLabel for="email" value="อีเมลบัญชีเจ้าสำนัก" class="text-gray-700 font-medium" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 transition-colors"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="กรุณากรอกอีเมลของคุณ"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="password" value="รหัสผ่าน" class="text-gray-700 font-medium" />
                    <TextInput
                        id="password"
                        type="password"
                        class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 transition-colors"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="กรุณากรอกรหัสผ่าน"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" />
                        <span class="ms-2 text-sm text-gray-600">จดจำการเข้าสู่ระบบ</span>
                    </label>
                </div>

                <div>
                    <PrimaryButton
                        class="w-full justify-center bg-gradient-to-r from-red-600 to-amber-600 hover:from-red-700 hover:to-amber-700 text-white font-extrabold py-3 rounded-xl shadow-md hover:shadow-lg transition-all duration-200"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="!form.processing">เข้าสู่พอร์ทัลเจ้าสำนัก</span>
                        <span v-else>กำลังเข้าสู่ระบบ...</span>
                    </PrimaryButton>
                </div>

                <div class="pt-4 border-t border-gray-100 text-center">
                    <Link :href="route('advertise.index')" class="text-xs font-semibold text-gray-500 hover:text-red-600">
                        สนใจเปิดบัญชีเจ้าสำนักหรือโปรโมทสำนัก? ดูรายละเอียดที่นี่ →
                    </Link>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
