<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canLogin: { type: Boolean, default: false },
    canRegister: { type: Boolean, default: false },
});

const form = useForm({
    slip: null,
    amount: '',
    bank_account_name: '',
    bank_account_number: '',
    note: '',
});

const submit = () => {
    form.post(route('vip-request.store'), {
        forceFormData: true,
        onFinish: () => form.reset('slip'),
    });
};
</script>

<template>
    <Head title="ขอเป็น VIP" />
    <PublicLayout :can-login="canLogin" :can-register="canRegister">
        <section class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">ขอเป็นสมาชิก VIP</h1>
                    <p class="text-gray-600 text-sm mb-6">
                        แนบสลิปการโอนเงิน แล้วกรอกข้อมูลด้านล่าง ทีมงานจะตรวจสอบและอนุมัติให้
                    </p>

                    <div v-if="$page.props.flash?.success || form.recentlySuccessful" class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
                        {{ $page.props.flash?.success || 'ส่งคำขอ VIP เรียบร้อยแล้ว ทีมงานจะตรวจสอบและแจ้งผลให้คุณ' }}
                    </div>
                    <div v-if="form.hasErrors" class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                        กรุณาตรวจสอบข้อมูลอีกครั้ง
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <InputLabel for="slip" value="สลิปการโอนเงิน *" class="font-medium text-gray-700" />
                            <input
                                id="slip"
                                type="file"
                                accept="image/*"
                                class="mt-2 block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-red-50 file:text-red-700 file:font-medium hover:file:bg-red-100"
                                @change="form.slip = $event.target.files[0]"
                            />
                            <p class="mt-1 text-xs text-gray-500">รองรับไฟล์รูป (JPG, PNG ฯลฯ ไม่เกิน 5 MB)</p>
                            <InputError class="mt-1" :message="form.errors.slip" />
                        </div>

                        <div>
                            <InputLabel for="amount" value="ยอดที่โอน (บาท)" class="font-medium text-gray-700" />
                            <TextInput
                                id="amount"
                                v-model="form.amount"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="เช่น 299"
                                class="mt-2 block w-full"
                            />
                            <InputError class="mt-1" :message="form.errors.amount" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="bank_account_name" value="ชื่อบัญชีที่โอน" class="font-medium text-gray-700" />
                                <TextInput
                                    id="bank_account_name"
                                    v-model="form.bank_account_name"
                                    type="text"
                                    placeholder="ชื่อบัญชี"
                                    class="mt-2 block w-full"
                                />
                                <InputError class="mt-1" :message="form.errors.bank_account_name" />
                            </div>
                            <div>
                                <InputLabel for="bank_account_number" value="เลขที่บัญชี" class="font-medium text-gray-700" />
                                <TextInput
                                    id="bank_account_number"
                                    v-model="form.bank_account_number"
                                    type="text"
                                    placeholder="เลขบัญชี"
                                    class="mt-2 block w-full"
                                />
                                <InputError class="mt-1" :message="form.errors.bank_account_number" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="note" value="หมายเหตุ (ถ้ามี)" class="font-medium text-gray-700" />
                            <textarea
                                id="note"
                                v-model="form.note"
                                rows="3"
                                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                                placeholder="เวลาที่โอน หรือข้อมูลเพิ่มเติม"
                            />
                            <InputError class="mt-1" :message="form.errors.note" />
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 pt-2">
                            <PrimaryButton
                                type="submit"
                                class="w-full sm:w-auto bg-red-600 hover:bg-red-700"
                                :disabled="form.processing"
                            >
                                <span v-if="!form.processing">ส่งคำขอ VIP</span>
                                <span v-else>กำลังส่ง...</span>
                            </PrimaryButton>
                            <Link
                                href="/"
                                class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                            >
                                ยกเลิก
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
