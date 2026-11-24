<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    lottoDataList: Array,
});

const form = useForm({
    lotto_id: '',
    date: '',
    endpoint: '',
});

const submit = () => {
    form.post(route('backoffice.lotto-details-admin.store'));
};
</script>

<template>
    <Head title="เพิ่มรายละเอียดงวดหวยแมนนวล" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('backoffice.lotto-details-admin.index')" class="text-gray-500 hover:text-gray-700 text-sm">
                    รายละเอียดหวย
                </Link>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800 font-semibold text-sm">เพิ่มข้อมูลแมนนวล</span>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-8">
                    <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-4 mb-6">➕ เพิ่มรายละเอียดงวดหวยแมนนวล</h3>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Lotto ID Selector -->
                        <div>
                            <InputLabel for="lotto_id" value="เลือกงวดหวยหลัก (Lotto ID)" />
                            <select
                                id="lotto_id"
                                v-model="form.lotto_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                required
                            >
                                <option value="">-- เลือกงวดหวย --</option>
                                <option v-for="l in lottoDataList" :key="l.lotto_id" :value="l.lotto_id">
                                    {{ l.lotto_id }} - {{ l.draw_date ? new Date(l.draw_date).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' }) : 'ยังไม่มีวันที่' }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.lotto_id" />
                        </div>

                        <!-- Date text -->
                        <div>
                            <InputLabel for="date" value="ข้อความวันที่ดึง (Date Text)" />
                            <TextInput
                                id="date"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.date"
                                placeholder="เช่น 16 สิงหาคม 2566"
                            />
                            <InputError class="mt-2" :message="form.errors.date" />
                        </div>

                        <!-- Endpoint URL -->
                        <div>
                            <InputLabel for="endpoint" value="Endpoint แหล่งอ้างอิง" />
                            <TextInput
                                id="endpoint"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.endpoint"
                                placeholder="เช่น /lotto/16082566"
                            />
                            <InputError class="mt-2" :message="form.errors.endpoint" />
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-6">
                            <Link :href="route('backoffice.lotto-details-admin.index')">
                                <SecondaryButton>ยกเลิก</SecondaryButton>
                            </Link>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                บันทึกข้อมูล
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
