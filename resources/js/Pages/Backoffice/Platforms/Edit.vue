<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    platform: Object,
});

const form = useForm({
    name: props.platform.name,
    slug: props.platform.slug || '',
    logo_url: props.platform.logo_url || '',
    sort_order: props.platform.sort_order ?? 0,
    is_active: props.platform.is_active ?? true,
});

const isUploading = ref(false);
const uploadError = ref('');

const handleFileUpload = async (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    isUploading.value = true;
    uploadError.value = '';
    try {
        const fd = new FormData();
        fd.append('image', file);
        fd.append('folder', 'platforms');
        const res = await axios.post(route('backoffice.posts.upload-image'), fd);
        form.logo_url = res.data.path || res.data.url;
    } catch (err) {
        uploadError.value = err.response?.data?.message || 'อัปโหลดโลโก้ไม่สำเร็จ (รองรับไฟล์ภาพไม่เกิน 4MB)';
    } finally {
        isUploading.value = false;
        e.target.value = '';
    }
};

const submit = () => {
    form.put(route('backoffice.platforms.update', props.platform.id));
};
</script>

<template>
    <Head title="แก้ไขแพลตฟอร์ม" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                แก้ไขแพลตฟอร์ม
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
                            />
                            <InputError :message="form.errors.slug" class="mt-2" />
                        </div>

                        <div class="space-y-2">
                            <label for="logo_url" class="block text-sm font-medium text-gray-700">โลโก้แพลตฟอร์ม (วาง URL หรืออัปโหลดไฟล์จากเครื่อง)</label>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <TextInput
                                    id="logo_url"
                                    v-model="form.logo_url"
                                    type="text"
                                    class="block w-full flex-1"
                                    placeholder="https://... หรือกดปุ่มอัปโหลดด้านขวา"
                                />
                                <label class="inline-flex items-center justify-center px-4 py-2 bg-indigo-50 border border-indigo-200 rounded-md font-semibold text-xs text-indigo-700 hover:bg-indigo-100 cursor-pointer shrink-0 transition">
                                    <span>{{ isUploading ? 'กำลังอัปโหลด...' : '📁 อัปโหลดโลโก้' }}</span>
                                    <input type="file" accept="image/*" class="hidden" :disabled="isUploading" @change="handleFileUpload" />
                                </label>
                            </div>
                            <p v-if="uploadError" class="text-xs text-red-600">{{ uploadError }}</p>
                            <InputError :message="form.errors.logo_url" class="mt-1" />
                            <div v-if="form.logo_url" class="mt-2 flex items-center gap-3 p-2 bg-gray-50 rounded-lg border border-gray-200 w-fit">
                                <img :src="form.logo_url" alt="Logo Preview" class="h-14 w-14 object-contain rounded border border-gray-200 bg-white p-1" />
                                <span class="text-xs text-gray-500">ตัวอย่างโลโก้ที่จะแสดง</span>
                            </div>
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
                            <PrimaryButton :disabled="form.processing">บันทึก</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
