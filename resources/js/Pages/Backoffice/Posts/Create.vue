<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    title: '',
    slug: '',
    excerpt: '',
    content: '',
    cover_image: null,
    published_at: '',
    is_published: false,
    meta_title: '',
    meta_description: '',
});

const coverPreview = ref(null);

const onCoverChange = (e) => {
    const file = e.target.files?.[0];
    form.cover_image = file || null;
    coverPreview.value = file ? URL.createObjectURL(file) : null;
};

const submit = () => {
    form.post(route('backoffice.posts.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="เพิ่มบทความ" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                เพิ่มบทความ
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">รูปปก</label>
                            <div class="flex items-start gap-4">
                                <div class="w-40 h-28 border-2 border-dashed border-gray-300 rounded-lg overflow-hidden bg-gray-50 flex items-center justify-center">
                                    <img v-if="coverPreview" :src="coverPreview" alt="Preview" class="w-full h-full object-cover" />
                                    <span v-else class="text-gray-400 text-sm">ยังไม่มีรูป</span>
                                </div>
                                <div>
                                    <input
                                        type="file"
                                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                        class="text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                        @change="onCoverChange"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF, WebP สูงสุด 4MB</p>
                                </div>
                            </div>
                            <InputError :message="form.errors.cover_image" class="mt-2" />
                        </div>

                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">ชื่อบทความ</label>
                            <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.title" class="mt-2" />
                        </div>

                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700">Slug (เว้นว่างจะใช้จากชื่อ)</label>
                            <TextInput id="slug" v-model="form.slug" type="text" class="mt-1 block w-full" placeholder="url-seo-friendly" />
                            <InputError :message="form.errors.slug" class="mt-2" />
                        </div>

                        <div>
                            <label for="excerpt" class="block text-sm font-medium text-gray-700">คำอธิบายสั้น</label>
                            <textarea id="excerpt" v-model="form.excerpt" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="สรุปสั้นๆ สำหรับแสดงใน card" />
                            <InputError :message="form.errors.excerpt" class="mt-2" />
                        </div>

                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-1">เนื้อหา</label>
                            <RichTextEditor v-model="form.content" placeholder="เขียนเนื้อหาที่นี่ — ใช้แถบเครื่องมือปรับสี ตัวหนา ขนาด แทรกรูปได้" />
                            <InputError :message="form.errors.content" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="published_at" class="block text-sm font-medium text-gray-700">วันเผยแพร่</label>
                                <TextInput id="published_at" v-model="form.published_at" type="datetime-local" class="mt-1 block w-full" />
                                <InputError :message="form.errors.published_at" class="mt-2" />
                            </div>
                            <div class="flex items-end">
                                <label class="flex items-center gap-2">
                                    <input v-model="form.is_published" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                    <span class="text-sm text-gray-700">เผยแพร่ทันที</span>
                                </label>
                            </div>
                        </div>

                        <div class="border-t pt-6 space-y-4">
                            <h3 class="font-medium text-gray-900">SEO (ไม่บังคับ)</h3>
                            <div>
                                <label for="meta_title" class="block text-sm font-medium text-gray-700">Meta Title</label>
                                <TextInput id="meta_title" v-model="form.meta_title" type="text" class="mt-1 block w-full" />
                            </div>
                            <div>
                                <label for="meta_description" class="block text-sm font-medium text-gray-700">Meta Description</label>
                                <textarea id="meta_description" v-model="form.meta_description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <Link :href="route('backoffice.posts.index')">
                                <SecondaryButton type="button">ยกเลิก</SecondaryButton>
                            </Link>
                            <PrimaryButton :disabled="form.processing">สร้างบทความ</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
