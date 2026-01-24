<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    draw_date: '',
    first_prize: '',
    last_two_digit: '',
    last_three_digit: '',
    running_numbers: '',
});

const submit = () => {
    form.post(route('admin.results.store'));
};
</script>

<template>
    <Head title="เพิ่มผลหวย" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">เพิ่มผลหวย</h2>
                <Link :href="route('admin.results.index')">
                    <SecondaryButton>ย้อนกลับ</SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <form class="space-y-4" @submit.prevent="submit">
                        <div>
                            <InputLabel value="งวด (วันที่)" />
                            <TextInput v-model="form.draw_date" type="date" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.draw_date" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-3">
                                <InputLabel value="รางวัลที่ 1" />
                                <TextInput v-model="form.first_prize" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.first_prize" />
                            </div>
                            <div>
                                <InputLabel value="เลขท้าย 2 ตัว" />
                                <TextInput v-model="form.last_two_digit" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.last_two_digit" />
                            </div>
                            <div>
                                <InputLabel value="เลขท้าย 3 ตัว" />
                                <TextInput v-model="form.last_three_digit" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.last_three_digit" />
                            </div>
                            <div>
                                <InputLabel value="เลขวิ่ง (คั่นด้วย , )" />
                                <TextInput v-model="form.running_numbers" class="mt-1 block w-full" placeholder="เช่น 1,2,3" />
                                <InputError class="mt-2" :message="form.errors.running_numbers" />
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-2">
                            <PrimaryButton type="submit" :disabled="form.processing">บันทึก</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

