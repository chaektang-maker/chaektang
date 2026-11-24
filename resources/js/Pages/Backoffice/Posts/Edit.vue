<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    post: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});

const form = useForm({
    title: props.post.title || '',
    slug: props.post.slug || '',
    excerpt: props.post.excerpt || '',
    content: props.post.content || '',
    cover_image: null,
    remove_cover_image: false,
    published_at: props.post.published_at || '',
    is_published: !!props.post.is_published,
    meta_title: props.post.meta_title || '',
    meta_description: props.post.meta_description || '',
});

watch(
    () => props.post,
    (newPost) => {
        if (newPost && newPost.id) {
            form.title = newPost.title || '';
            form.slug = newPost.slug || '';
            form.excerpt = newPost.excerpt || '';
            form.content = newPost.content || '';
            form.published_at = newPost.published_at || '';
            form.is_published = !!newPost.is_published;
            form.meta_title = newPost.meta_title || '';
            form.meta_description = newPost.meta_description || '';
        }
    },
    { deep: true }
);

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

const autoFillSeoAndExcerpt = () => {
    const plainText = (form.content || '')
        .replace(/<[^>]*>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

    if (!form.excerpt && plainText) {
        form.excerpt = plainText.slice(0, 160);
    }
    if (!form.meta_title && form.title) {
        form.meta_title = form.title;
    }
    if (!form.meta_description) {
        form.meta_description = (form.excerpt || plainText).slice(0, 160);
    }
};

const submit = () => {
    if (!form.title || !form.title.trim()) {
        alert('กรุณากรอกชื่อบทความ');
        return;
    }

    const contentText = form.content ? form.content.replace(/<[^>]*>/g, '').trim() : '';
    if (!contentText) {
        alert('กรุณากรอกเนื้อหาบทความ');
        return;
    }

    autoFillSeoAndExcerpt();

    // ใช้ POST พร้อม _method: 'PUT' เพื่อให้รองรับการอัปโหลดรูปปกใหม่ใน PHP (multipart/form-data) 100%
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('backoffice.posts.update', props.post.id), {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`แก้ไขบทความ: ${post.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-bold leading-tight text-gray-800">
                    ✏️ แก้ไขบทความ
                </h2>
                <div class="flex items-center gap-2">
                    <a
                        v-if="post.is_published && post.slug"
                        :href="route('blog.show', post.slug)"
                        target="_blank"
                        class="px-3 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs font-bold"
                    >
                        ดูบทความบนหน้าเว็บ ↗
                    </a>
                    <Link :href="route('backoffice.posts.index')">
                        <SecondaryButton type="button">← ย้อนกลับ</SecondaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                    <form @submit.prevent="submit" class="p-6 sm:p-8 space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">รูปปกบทความ</label>
                            <div class="flex flex-col sm:flex-row items-start gap-4">
                                <div class="w-48 h-28 border-2 border-dashed border-gray-300 rounded-xl overflow-hidden bg-gray-50 flex items-center justify-center shrink-0">
                                    <img v-if="currentCoverUrl() && !form.remove_cover_image" :src="currentCoverUrl()" alt="Preview" class="w-full h-full object-cover" />
                                    <span v-else class="text-gray-400 text-xs">ไม่มีรูปปก</span>
                                </div>
                                <div class="space-y-2">
                                    <input
                                        type="file"
                                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                        class="text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 file:font-semibold hover:file:bg-indigo-100"
                                        @change="onCoverChange"
                                    />
                                    <button
                                        v-if="post.cover_image && !form.remove_cover_image"
                                        type="button"
                                        class="block text-xs font-semibold text-red-600 hover:text-red-800"
                                        @click="removeCover"
                                    >
                                        ลบรูปปกออก
                                    </button>
                                    <p class="text-xs text-gray-500">รองรับไฟล์ JPG, PNG, GIF, WebP ขนาดไม่เกิน 4MB</p>
                                </div>
                            </div>
                            <InputError :message="form.errors.cover_image" class="mt-2" />
                        </div>

                        <div>
                            <label for="title" class="block text-sm font-bold text-gray-700">ชื่อบทความ (H1) *</label>
                            <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.title" class="mt-2" />
                        </div>

                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700">
                                ลิงก์บทความ (Slug — เว้นว่างเพื่อสร้างใหม่จากชื่อบทความ)
                            </label>
                            <TextInput id="slug" v-model="form.slug" type="text" class="mt-1 block w-full text-sm" />
                            <InputError :message="form.errors.slug" class="mt-2" />
                        </div>

                        <div>
                            <label for="content" class="block text-sm font-bold text-gray-700 mb-1">เนื้อหาบทความ *</label>
                            <RichTextEditor
                                v-model="form.content"
                                placeholder="เขียนเนื้อหาที่นี่"
                            />
                            <InputError :message="form.errors.content" class="mt-2" />
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="excerpt" class="block text-sm font-medium text-gray-700">คำอธิบายสั้น (Excerpt)</label>
                                <button
                                    type="button"
                                    @click="autoFillSeoAndExcerpt"
                                    class="text-xs font-bold text-indigo-600 hover:text-indigo-800"
                                >
                                    ✨ ดึงข้อความสรุป & SEO อัตโนมัติ
                                </button>
                            </div>
                            <textarea
                                id="excerpt"
                                v-model="form.excerpt"
                                rows="2"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            />
                            <InputError :message="form.errors.excerpt" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <div>
                                <label for="published_at" class="block text-sm font-medium text-gray-700">วันเวลาที่เผยแพร่</label>
                                <TextInput id="published_at" v-model="form.published_at" type="datetime-local" class="mt-1 block w-full" />
                                <InputError :message="form.errors.published_at" class="mt-2" />
                            </div>
                            <div class="flex items-center pt-5">
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input
                                        v-model="form.is_published"
                                        type="checkbox"
                                        class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <span class="text-sm font-bold text-gray-800">เผยแพร่บนหน้าเว็บ</span>
                                </label>
                            </div>
                        </div>

                        <div class="border-t pt-6 space-y-4">
                            <h3 class="font-bold text-gray-900">การตั้งค่า SEO</h3>
                            <div>
                                <label for="meta_title" class="block text-sm font-medium text-gray-700">Meta Title</label>
                                <TextInput id="meta_title" v-model="form.meta_title" type="text" class="mt-1 block w-full text-sm" />
                            </div>
                            <div>
                                <label for="meta_description" class="block text-sm font-medium text-gray-700">Meta Description</label>
                                <textarea
                                    id="meta_description"
                                    v-model="form.meta_description"
                                    rows="2"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t">
                            <Link :href="route('backoffice.posts.index')">
                                <SecondaryButton type="button">ยกเลิก</SecondaryButton>
                            </Link>
                            <PrimaryButton :disabled="form.processing">
                                {{ form.processing ? 'กำลังบันทึก...' : 'บันทึกการแก้ไข' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
