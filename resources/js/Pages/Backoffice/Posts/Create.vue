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
    is_published: true,
    meta_title: '',
    meta_description: '',
});

const coverPreview = ref(null);

const onCoverChange = (e) => {
    const file = e.target.files?.[0];
    form.cover_image = file || null;
    coverPreview.value = file ? URL.createObjectURL(file) : null;
};

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
    autoFillSeoAndExcerpt();
    form.post(route('backoffice.posts.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="เขียนบทความใหม่" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-bold leading-tight text-gray-800">
                    ✍️ เขียนบทความใหม่
                </h2>
                <Link :href="route('backoffice.posts.index')">
                    <SecondaryButton type="button">← ย้อนกลับ</SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                    <form @submit.prevent="submit" class="p-6 sm:p-8 space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">รูปปกบทความ (แนะนำขนาดแนวนอน 16:9)</label>
                            <div class="flex flex-col sm:flex-row items-start gap-4">
                                <div class="w-48 h-28 border-2 border-dashed border-gray-300 rounded-xl overflow-hidden bg-gray-50 flex items-center justify-center shrink-0">
                                    <img v-if="coverPreview" :src="coverPreview" alt="Preview" class="w-full h-full object-cover" />
                                    <span v-else class="text-gray-400 text-xs">ยังไม่มีรูปปก</span>
                                </div>
                                <div>
                                    <input
                                        type="file"
                                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                        class="text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 file:font-semibold hover:file:bg-indigo-100"
                                        @change="onCoverChange"
                                    />
                                    <p class="text-xs text-gray-500 mt-1.5">รองรับไฟล์ JPG, PNG, GIF, WebP ขนาดไม่เกิน 4MB</p>
                                </div>
                            </div>
                            <InputError :message="form.errors.cover_image" class="mt-2" />
                        </div>

                        <div>
                            <label for="title" class="block text-sm font-bold text-gray-700">ชื่อบทความ (H1) *</label>
                            <TextInput
                                id="title"
                                v-model="form.title"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="เช่น สถิติหวยออกวันพระย้อนหลัง 10 ปี พร้อมเลขเด็ดที่น่าจับตา"
                                required
                            />
                            <InputError :message="form.errors.title" class="mt-2" />
                        </div>

                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700">
                                ลิงก์บทความ (Slug — เว้นว่างได้ ระบบจะสร้างจากชื่อภาษาไทยให้อัตโนมัติ)
                            </label>
                            <TextInput
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                class="mt-1 block w-full text-sm"
                                placeholder="เว้นว่างไว้เพื่อให้ระบบสร้างลิงก์ภาษาไทยอัตโนมัติ"
                            />
                            <InputError :message="form.errors.slug" class="mt-2" />
                        </div>

                        <div>
                            <label for="content" class="block text-sm font-bold text-gray-700 mb-1">เนื้อหาบทความ *</label>
                            <RichTextEditor
                                v-model="form.content"
                                placeholder="เขียนเนื้อหาที่นี่ — ใช้แถบเครื่องมือปรับหัวข้อ ตัวหนา สีตัวอักษร หรือแทรกรูปภาพประกอบได้"
                            />
                            <InputError :message="form.errors.content" class="mt-2" />
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="excerpt" class="block text-sm font-medium text-gray-700">
                                    คำอธิบายสั้น (Excerpt — หากเว้นว่างระบบจะดึงจากย่อหน้าแรกให้อัตโนมัติ)
                                </label>
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
                                placeholder="สรุปสั้นๆ 1–2 บรรทัดสำหรับแสดงในการ์ดหน้าบทความ"
                            />
                            <InputError :message="form.errors.excerpt" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <div>
                                <label for="published_at" class="block text-sm font-medium text-gray-700">
                                    วันเวลาที่เผยแพร่ (เว้นว่าง = เวลาปัจจุบัน)
                                </label>
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
                                    <span class="text-sm font-bold text-gray-800">เผยแพร่ขึ้นหน้าเว็บทันที</span>
                                </label>
                            </div>
                        </div>

                        <div class="border-t pt-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="font-bold text-gray-900">การตั้งค่า SEO (ไม่บังคับ — ระบบเติมให้อัตโนมัติหากเว้นว่าง)</h3>
                            </div>
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
                                {{ form.processing ? 'กำลังบันทึก...' : 'บันทึกบทความ' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
