<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const page = usePage();

const form = useForm({
    start_page: 23,
    end_page: 1,
});

const isProcessing = ref(false);
const progress = ref(null);
const summary = ref(null);

const submit = () => {
    if (!confirm('ยืนยันการดึงข้อมูลหวยย้อนหลัง? กระบวนการนี้อาจใช้เวลาสักครู่')) {
        return;
    }

    isProcessing.value = true;
    progress.value = null;
    summary.value = null;

    form.post(route('admin.results.import.store'), {
        preserveScroll: true,
        onSuccess: (page) => {
            isProcessing.value = false;
            if (page.props.flash?.import_summary) {
                summary.value = page.props.flash.import_summary;
            }
        },
        onError: () => {
            isProcessing.value = false;
        },
        onFinish: () => {
            isProcessing.value = false;
        },
    });
};
</script>

<template>
    <Head title="ดึงข้อมูลหวยย้อนหลัง" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">ดึงข้อมูลหวยย้อนหลัง</h2>
                <Link :href="route('admin.results.index')">
                    <SecondaryButton>ย้อนกลับ</SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8 space-y-6">
                <!-- Success Message -->
                <div v-if="page.props.flash?.success" class="rounded-lg bg-green-50 border border-green-200 p-4 text-green-800">
                    {{ page.props.flash.success }}
                </div>

                <!-- Error Messages -->
                <div v-if="form.errors.error" class="rounded-lg bg-red-50 border border-red-200 p-4 text-red-800">
                    {{ form.errors.error }}
                </div>
                <div v-if="form.errors.start_page" class="rounded-lg bg-red-50 border border-red-200 p-4 text-red-800">
                    {{ form.errors.start_page }}
                </div>
                <div v-if="form.errors.end_page" class="rounded-lg bg-red-50 border border-red-200 p-4 text-red-800">
                    {{ form.errors.end_page }}
                </div>

                <!-- Import Summary -->
                <div v-if="summary" class="bg-white shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">สรุปผลการดึงข้อมูล</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <div class="text-sm text-blue-600 font-medium">รวมงวด</div>
                            <div class="text-2xl font-bold text-blue-900 mt-1">{{ summary.total_lotteries }}</div>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <div class="text-sm text-green-600 font-medium">สำเร็จ</div>
                            <div class="text-2xl font-bold text-green-900 mt-1">{{ summary.success }}</div>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                            <div class="text-sm text-red-600 font-medium">ล้มเหลว</div>
                            <div class="text-2xl font-bold text-red-900 mt-1">{{ summary.failed }}</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="text-sm text-gray-600 font-medium">ข้าม</div>
                            <div class="text-2xl font-bold text-gray-900 mt-1">{{ summary.skipped }}</div>
                        </div>
                    </div>
                    <div v-if="summary.errors && summary.errors.length > 0" class="mt-4">
                        <details class="mt-2">
                            <summary class="text-sm text-gray-600 cursor-pointer hover:text-gray-800">
                                ดูข้อผิดพลาด ({{ summary.errors.length }} รายการ)
                            </summary>
                            <div class="mt-2 max-h-48 overflow-y-auto bg-gray-50 rounded p-3 text-xs text-gray-700 space-y-1">
                                <div v-for="(error, index) in summary.errors" :key="index" class="font-mono">
                                    {{ error }}
                                </div>
                            </div>
                        </details>
                    </div>
                </div>

                <!-- Import Form -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">ตั้งค่าการดึงข้อมูล</h3>
                        <p class="text-sm text-gray-600">
                            ระบบจะดึงข้อมูลหวยย้อนหลังจาก API โดยเริ่มจากหน้าเริ่มต้นไปจนถึงหน้าสุดท้าย
                            <br>
                            <span class="font-medium">หมายเหตุ:</span> กระบวนการนี้อาจใช้เวลาสักครู่ เนื่องจากต้องดึงข้อมูลหลายงวด
                        </p>
                    </div>

                    <form class="space-y-6" @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel value="หน้าเริ่มต้น" />
                                <TextInput 
                                    v-model.number="form.start_page" 
                                    type="number" 
                                    min="1" 
                                    max="23" 
                                    class="mt-1 block w-full" 
                                    :disabled="isProcessing"
                                />
                                <p class="mt-1 text-xs text-gray-500">ระบุหน้าเริ่มต้น (1-23, ค่าเริ่มต้น: 23)</p>
                                <InputError class="mt-2" :message="form.errors.start_page" />
                            </div>

                            <div>
                                <InputLabel value="หน้าสุดท้าย" />
                                <TextInput 
                                    v-model.number="form.end_page" 
                                    type="number" 
                                    min="1" 
                                    max="23" 
                                    class="mt-1 block w-full" 
                                    :disabled="isProcessing"
                                />
                                <p class="mt-1 text-xs text-gray-500">ระบุหน้าสุดท้าย (1-23, ค่าเริ่มต้น: 1)</p>
                                <InputError class="mt-2" :message="form.errors.end_page" />
                            </div>
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <div class="text-sm text-yellow-800">
                                    <p class="font-medium mb-1">คำเตือน:</p>
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>หน้าเริ่มต้นต้องมากกว่าหรือเท่ากับหน้าสุดท้าย</li>
                                        <li>กระบวนการนี้อาจใช้เวลานาน ขึ้นอยู่กับจำนวนงวดที่ดึง</li>
                                        <li>ข้อมูลที่ซ้ำกันจะถูกอัพเดทอัตโนมัติ</li>
                                        <li>กรุณาอย่าปิดหน้าจอระหว่างการดึงข้อมูล</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pt-4 border-t">
                            <PrimaryButton 
                                type="submit" 
                                :disabled="form.processing || isProcessing"
                                class="min-w-[120px]"
                            >
                                <span v-if="!form.processing && !isProcessing">เริ่มดึงข้อมูล</span>
                                <span v-else class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    กำลังดำเนินการ...
                                </span>
                            </PrimaryButton>
                            <Link :href="route('admin.results.index')">
                                <SecondaryButton :disabled="form.processing || isProcessing">ยกเลิก</SecondaryButton>
                            </Link>
                        </div>
                    </form>
                </div>

                <!-- Info Card -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h4 class="text-sm font-semibold text-blue-900 mb-2">ข้อมูลเพิ่มเติม</h4>
                    <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                        <li>ระบบจะดึงข้อมูลจาก API: <code class="bg-blue-100 px-1 rounded">https://lotto.api.rayriffy.com</code></li>
                        <li>ข้อมูลที่ดึงมา: รางวัลที่ 1, เลขท้าย 2 ตัว, เลขท้าย 3 ตัว, และเลขวิ่ง</li>
                        <li>หลังจากดึงข้อมูลเสร็จแล้ว สามารถดูผลลัพธ์ได้ที่หน้า <Link :href="route('admin.results.index')" class="underline font-medium">บันทึกผลหวย</Link></li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
