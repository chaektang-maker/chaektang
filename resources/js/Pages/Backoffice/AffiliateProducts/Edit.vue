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
    product: Object,
    platforms: Array,
});

const form = useForm({
    platform_id: props.product.platform_id,
    title: props.product.title,
    description: props.product.description ?? '',
    image_url: props.product.image_url ?? '',
    affiliate_url: props.product.affiliate_url,
    sort_order: props.product.sort_order ?? 0,
    is_active: props.product.is_active ?? true,
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
        fd.append('folder', 'affiliate-products');
        const res = await axios.post(route('backoffice.posts.upload-image'), fd);
        form.image_url = res.data.path || res.data.url;
    } catch (err) {
        uploadError.value = err.response?.data?.message || 'อัปโหลดรูปภาพไม่สำเร็จ (รองรับไฟล์ภาพไม่เกิน 4MB)';
    } finally {
        isUploading.value = false;
        e.target.value = '';
    }
};

const submit = () => {
    form.put(route('backoffice.affiliate-products.update', props.product.id));
};
</script>

<template>
    <Head title="แก้ไขสินค้าวัตถุมงคล" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                แก้ไขสินค้าวัตถุมงคล
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <label for="platform_id" class="block text-sm font-medium text-gray-700">แพลตฟอร์ม</label>
                            <select
                                id="platform_id"
                                v-model="form.platform_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="">-- เลือกแพลตฟอร์ม --</option>
                                <option v-for="p in platforms" :key="p.id" :value="p.id">
                                    {{ p.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.platform_id" class="mt-2" />
                        </div>

                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">ชื่อสินค้า</label>
                            <TextInput
                                id="title"
                                v-model="form.title"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.title" class="mt-2" />
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">คำอธิบาย (ไม่บังคับ)</label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>

                        <div class="space-y-2">
                            <label for="image_url" class="block text-sm font-medium text-gray-700">รูปภาพสินค้า (วาง URL หรืออัปโหลดไฟล์จากเครื่อง)</label>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <TextInput
                                    id="image_url"
                                    v-model="form.image_url"
                                    type="text"
                                    class="block w-full flex-1"
                                    placeholder="https://... หรือกดปุ่มอัปโหลดด้านขวา"
                                />
                                <label class="inline-flex items-center justify-center px-4 py-2 bg-indigo-50 border border-indigo-200 rounded-md font-semibold text-xs text-indigo-700 hover:bg-indigo-100 cursor-pointer shrink-0 transition">
                                    <span>{{ isUploading ? 'กำลังอัปโหลด...' : '📁 อัปโหลดรูปภาพ' }}</span>
                                    <input type="file" accept="image/*" class="hidden" :disabled="isUploading" @change="handleFileUpload" />
                                </label>
                            </div>
                            <p v-if="uploadError" class="text-xs text-red-600">{{ uploadError }}</p>
                            <InputError :message="form.errors.image_url" class="mt-1" />
                            <div v-if="form.image_url" class="mt-2 flex items-center gap-3 p-2 bg-gray-50 rounded-lg border border-gray-200 w-fit">
                                <img :src="form.image_url" alt="Preview" class="h-20 w-20 object-cover rounded border border-gray-200 bg-white" />
                                <span class="text-xs text-gray-500">ตัวอย่างรูปภาพที่จะแสดง</span>
                            </div>
                        </div>

                        <div>
                            <label for="affiliate_url" class="block text-sm font-medium text-gray-700">ลิงก์ Affiliate</label>
                            <TextInput
                                id="affiliate_url"
                                v-model="form.affiliate_url"
                                type="url"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.affiliate_url" class="mt-2" />
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
                            <Link :href="route('backoffice.affiliate-products.index')">
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
