<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    source: Object,
});

const page = usePage();

const form = useForm({
    name: props.source.name || '',
    description: props.source.description || '',
    status: props.source.status || 'active',
    popularity_score: props.source.popularity_score ?? 0,
    is_promoted: !!props.source.is_promoted,
    promoted_until: props.source.promoted_until ? String(props.source.promoted_until).slice(0, 10) : '',
});

const submit = () => {
    form.put(route('admin.sources.update', props.source.id));
};
</script>

<template>
    <Head :title="`แก้ไขสำนัก: ${source.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    แก้ไขสำนัก: {{ source.name }}
                </h2>
                <Link :href="route('admin.sources.index')">
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
                            <InputLabel value="ชื่อสำนัก" />
                            <TextInput v-model="form.name" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel value="คำอธิบาย" />
                            <textarea v-model="form.description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="4" />
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel value="สถานะ" />
                                <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="active">เปิดใช้งาน</option>
                                    <option value="suspended">ระงับ</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.status" />
                            </div>
                            <div>
                                <InputLabel value="คะแนนความนิยม" />
                                <TextInput v-model="form.popularity_score" type="number" min="0" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.popularity_score" />
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input id="is_promoted" type="checkbox" v-model="form.is_promoted" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                            <label for="is_promoted" class="text-sm text-gray-700">ตั้งเป็น “แนะนำ/โปรโมท”</label>
                        </div>

                        <div>
                            <InputLabel value="โปรโมทถึง (ถ้ามี)" />
                            <TextInput v-model="form.promoted_until" type="date" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.promoted_until" />
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

