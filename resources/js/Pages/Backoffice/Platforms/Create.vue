<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    slug: '',
    logo_url: '',
    sort_order: 0,
    is_active: true,
});

const submit = () => {
    form.post(route('backoffice.platforms.store'));
};
</script>

<template>
    <Head title="เพิ่มแพลตฟอร์ม" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                เพิ่มแพลตฟอร์ม
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">ชื่อแพลตฟอร์ม</label>
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                placeholder="เช่น Shopee, Lazada"
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700">Slug (ไม่บังคับ)</label>
                            <TextInput
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="shopee, lazada"
                            />
                            <InputError :message="form.errors.slug" class="mt-2" />
                        </div>

                        <div>
                            <label for="logo_url" class="block text-sm font-medium text-gray-700">URL โลโก้</label>
                            <TextInput
                                id="logo_url"
                                v-model="form.logo_url"
                                type="url"
                                class="mt-1 block w-full"
                                placeholder="https://..."
                            />
                            <InputError :message="form.errors.logo_url" class="mt-2" />
                        </div>

                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700">ลำดับแสดง</label>
                            <TextInput
                                id="sort_order"
                                v-model.number="form.sort_order"
                                type="number"
                                min="0"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.sort_order" class="mt-2" />
                        </div>

                        <div class="flex items-center">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            <label for="is_active" class="ml-2 text-sm text-gray-700">แสดงในหน้าแรก</label>
                        </div>
                        <InputError :message="form.errors.is_active" class="mt-2" />

                        <div class="flex items-center justify-end gap-4">
                            <Link :href="route('backoffice.platforms.index')">
                                <SecondaryButton type="button">ยกเลิก</SecondaryButton>
                            </Link>
                            <PrimaryButton :disabled="form.processing">สร้างแพลตฟอร์ม</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
