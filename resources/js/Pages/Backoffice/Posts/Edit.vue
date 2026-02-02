<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
    post: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});

// สร้าง form โดยใช้ข้อมูลจาก props.post
const form = useForm({
    title: '',
    slug: '',
    excerpt: '',
    content: '',
    cover_image: null,
    remove_cover_image: false,
    published_at: '',
    is_published: false,
    meta_title: '',
    meta_description: '',
});

// ฟังก์ชันสำหรับอัปเดต form จาก props
const updateFormFromProps = () => {
    if (props.post && props.post.id) {
        form.title = props.post.title || '';
        form.slug = props.post.slug || '';
        form.excerpt = props.post.excerpt || '';
        form.content = props.post.content || '';
        form.published_at = props.post.published_at || '';
        form.is_published = props.post.is_published || false;
        form.meta_title = props.post.meta_title || '';
        form.meta_description = props.post.meta_description || '';
    }
};

// อัปเดต form เมื่อ component mount
onMounted(() => {
    updateFormFromProps();
});

// Watch props.post เพื่ออัปเดต form เมื่อ props เปลี่ยน
watch(() => props.post, () => {
    updateFormFromProps();
}, { immediate: true, deep: true });

const coverPreview = ref(null);

const onCoverChange = (e) => {
    const file = e.target.files?.[0];
    form.cover_image = file || null;
    form.remove_cover_image = false;
    coverPreview.value = file ? URL.createObjectURL(file) : (props.post.cover_image || null);
};

const removeCover = () => {
    form.cover_image = null;
    form.remove_cover_image = true;
    coverPreview.value = null;
};

const currentCoverUrl = () => coverPreview.value ?? props.post.cover_image;

const submit = () => {
    // ตรวจสอบว่ามีข้อมูลครบ
    if (!form.title || !form.title.trim()) {
        alert('กรุณากรอกชื่อบทความ');
        return;
    }
    
    // ตรวจสอบ content - ลบ HTML tags เพื่อตรวจสอบว่ามีเนื้อหาจริงหรือไม่
    const contentText = form.content ? form.content.replace(/<[^>]*>/g, '').trim() : '';
    if (!contentText || contentText.length === 0) {
        alert('กรุณากรอกเนื้อหา');
        return;
    }
    
    // Debug: แสดงข้อมูลที่จะส่ง
    console.log('Form data before submit:', {
        title: form.title,
        content: form.content,
        contentLength: form.content?.length || 0,
        hasTitle: !!form.title,
        hasContent: !!form.content,
        formData: form.data(),
    });
    
    // ตรวจสอบว่ามี file upload หรือไม่
    const hasFileUpload = form.cover_image instanceof File;
    
    // ใช้ patch โดยใช้ forceFormData เฉพาะเมื่อมี file upload
    form.patch(route('backoffice.posts.update', props.post.id), {
        ...(hasFileUpload && { forceFormData: true }),
        preserveScroll: true,
        onError: (errors) => {
            console.error('Validation errors:', errors);
            console.error('Form data on error:', {
                title: form.title,
                content: form.content,
                allData: form.data(),
            });
        },
        onSuccess: () => {
            console.log('Update successful');
        },
    });
};
</script>

<template>
    <Head title="แก้ไขบทความ" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                แก้ไขบทความ
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
                                    <img v-if="currentCoverUrl() && !form.remove_cover_image" :src="currentCoverUrl()" alt="Preview" class="w-full h-full object-cover" />
                                    <span v-else class="text-gray-400 text-sm">ไม่มีรูป</span>
                                </div>
                                <div class="space-y-2">
                                    <input
                                        type="file"
                                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                        class="text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                        @change="onCoverChange"
                                    />
                                    <button v-if="post.cover_image && !form.remove_cover_image" type="button" class="block text-sm text-red-600 hover:text-red-800" @click="removeCover">
                                        ลบรูป
                                    </button>
                                    <p class="text-xs text-gray-500">JPG, PNG, GIF, WebP สูงสุด 4MB</p>
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
                            <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                            <TextInput id="slug" v-model="form.slug" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors.slug" class="mt-2" />
                        </div>

                        <div>
                            <label for="excerpt" class="block text-sm font-medium text-gray-700">คำอธิบายสั้น</label>
                            <textarea id="excerpt" v-model="form.excerpt" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <InputError :message="form.errors.excerpt" class="mt-2" />
                        </div>

                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-1">เนื้อหา</label>
                            <RichTextEditor 
                                v-model="form.content"
                                placeholder="เขียนเนื้อหาที่นี่" 
                            />
                            <InputError :message="form.errors.content" class="mt-2" />
                            <!-- Debug: แสดง content length -->
                            <p class="text-xs text-gray-500 mt-1">
                                ความยาวเนื้อหา: {{ form.content ? form.content.length : 0 }} ตัวอักษร
                            </p>
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
                                    <span class="text-sm text-gray-700">เผยแพร่</span>
                                </label>
                            </div>
                        </div>

                        <div class="border-t pt-6 space-y-4">
                            <h3 class="font-medium text-gray-900">SEO</h3>
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
                            <PrimaryButton :disabled="form.processing">บันทึก</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
