<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    lotto_id: '',
    url: '',
    date_text: '',
    draw_date: '',
    is_fetched: false,
});

const submit = () => {
    form.post(route('backoffice.lotto-data.store'));
};
</script>

<template>
    <Head title="เพิ่มข้อมูลหวย (lotto_data)" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">เพิ่มงวดหวย (lotto_data)</h2>
                <Link :href="route('backoffice.lotto-data.index')">
                    <SecondaryButton>ย้อนกลับ</SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    <form class="space-y-4" @submit.prevent="submit">
                        <div>
                            <InputLabel value="Lotto ID (รหัสงวดตัวเลข 8 หลัก)" />
                            <TextInput 
                                v-model="form.lotto_id" 
                                placeholder="เช่น 16082566 (วันเดือนปี พ.ศ.)" 
                                class="mt-1 block w-full"
                                maxlength="8" 
                            />
                            <p class="text-xs text-gray-500 mt-1">ต้องประกอบด้วยตัวเลข 8 หลัก เช่น 16082566 (วันที่ 16 สิงหาคม พ.ศ. 2566)</p>
                            <InputError class="mt-2" :message="form.errors.lotto_id" />
                        </div>

                        <div>
                            <InputLabel value="ข้อความแสดงวันที่ (date_text)" />
                            <TextInput 
                                v-model="form.date_text" 
                                placeholder="เช่น 16 สิงหาคม 2566" 
                                class="mt-1 block w-full" 
                            />
                            <p class="text-xs text-gray-500 mt-1">ข้อความวันที่ฉบับภาษาไทยสำหรับแสดงผลให้ผู้ใช้</p>
                            <InputError class="mt-2" :message="form.errors.date_text" />
                        </div>

                        <div>
                            <InputLabel value="URL แหล่งข้อมูล (อ้างอิงสำหรับดึงรายละเอียด)" />
                            <TextInput 
                                v-model="form.url" 
                                placeholder="เช่น https://www.lottery.co.th/lotto/16082566" 
                                class="mt-1 block w-full" 
                            />
                            <InputError class="mt-2" :message="form.errors.url" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel value="วันจับรางวัล (draw_date)" />
                                <TextInput 
                                    v-model="form.draw_date" 
                                    type="date" 
                                    class="mt-1 block w-full" 
                                />
                                <p class="text-xs text-gray-500 mt-1">หากไม่ระบุ ระบบจะพยายามคำนวณแปลงจาก Lotto ID ให้ตอนเซฟ</p>
                                <InputError class="mt-2" :message="form.errors.draw_date" />
                            </div>
                            <div>
                                <InputLabel value="สถานะการดึงข้อมูลรางวัลรายละเอียด" />
                                <select 
                                    v-model="form.is_fetched" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option :value="false">ยังไม่ได้ดึงรายละเอียด (Pending)</option>
                                    <option :value="true">ดึงรางวัลแล้ว (Fetched)</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.is_fetched" />
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                            <PrimaryButton type="submit" :disabled="form.processing">บันทึกข้อมูล</PrimaryButton>
                            <SecondaryButton type="button" @click="form.reset()">ล้างฟอร์ม</SecondaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
