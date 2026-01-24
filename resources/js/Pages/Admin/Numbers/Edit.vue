<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    number: Object,
    sources: Array,
});

const page = usePage();

const form = useForm({
    source_id: props.number.source_id,
    draw_date: props.number.draw_date || '',
    two_digit: props.number.two_digit || '',
    three_digit: props.number.three_digit || '',
    running_numbers: Array.isArray(props.number.running_numbers) ? props.number.running_numbers.join(',') : (props.number.running_numbers || ''),
});

const submit = () => {
    form.put(route('admin.numbers.update', props.number.id));
};
</script>

<template>
    <Head title="แก้ไขเลขสำนัก" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">แก้ไขเลขสำนัก</h2>
                <Link :href="route('admin.numbers.index')">
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
                            <InputLabel value="สำนัก" />
                            <select v-model="form.source_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option v-for="s in sources" :key="s.id" :value="s.id">
                                    {{ s.name }} ({{ s.status }})
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.source_id" />
                        </div>

                        <div>
                            <InputLabel value="งวด (วันที่)" />
                            <TextInput v-model="form.draw_date" type="date" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.draw_date" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <InputLabel value="เลข 2 ตัว" />
                                <TextInput v-model="form.two_digit" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.two_digit" />
                            </div>
                            <div>
                                <InputLabel value="เลข 3 ตัว" />
                                <TextInput v-model="form.three_digit" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.three_digit" />
                            </div>
                            <div>
                                <InputLabel value="เลขวิ่ง (คั่นด้วย , )" />
                                <TextInput v-model="form.running_numbers" class="mt-1 block w-full" />
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

