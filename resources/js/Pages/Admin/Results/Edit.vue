<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    result: Object,
});

const page = usePage();

const form = useForm({
    draw_date: props.result.draw_date || '',
    first_prize: props.result.first_prize || '',
    last_two_digit: props.result.last_two_digit || '',
    last_three_digit: props.result.last_three_digit || '',
    running_numbers: Array.isArray(props.result.running_numbers) ? props.result.running_numbers.join(',') : (props.result.running_numbers || ''),
    is_calculated: !!props.result.is_calculated,
});

const submit = () => {
    form.put(route('backoffice.results.update', props.result.id));
};
</script>

<template>
    <Head :title="`แก้ไขผลหวย: ${result.draw_date}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    แก้ไขผลหวย: {{ result.draw_date }}
                </h2>
                <Link :href="route('backoffice.results.index')">
                    <SecondaryButton>ย้อนกลับ</SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8 space-y-4">
                <div v-if="page.props.flash?.success" class="rounded-lg bg-green-50 border border-green-200 p-4 text-green-800">
                    {{ page.props.flash.success }}
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <form class="space-y-4" @submit.prevent="submit">
                        <div>
                            <InputLabel value="งวด (วันที่)" />
                            <TextInput v-model="form.draw_date" type="date" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.draw_date" />
                        </div>

                        <div>
                            <InputLabel value="รางวัลที่ 1" />
                            <TextInput v-model="form.first_prize" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.first_prize" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

                        <div class="flex items-center gap-2">
                            <input id="is_calculated" type="checkbox" v-model="form.is_calculated" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                            <label for="is_calculated" class="text-sm text-gray-700">ทำเครื่องหมายว่า “คำนวณคะแนนแล้ว”</label>
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

