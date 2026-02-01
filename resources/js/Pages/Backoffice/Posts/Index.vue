<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    posts: Array,
});

const destroy = (id, title) => {
    if (!confirm(`ยืนยันลบบทความ "${title}"?`)) return;
    router.delete(route('backoffice.posts.destroy', id));
};
</script>

<template>
    <Head title="จัดการบทความ" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    จัดการบทความ
                </h2>
                <Link :href="route('backoffice.posts.create')">
                    <PrimaryButton>+ เพิ่มบทความ</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="posts.length === 0" class="text-center py-12 text-gray-500">
                            ยังไม่มีบทความ — กด "เพิ่มบทความ" เพื่อเริ่มเขียน
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="p in posts"
                                :key="p.id"
                                class="flex items-center gap-4 p-4 rounded-lg border border-gray-200 hover:bg-gray-50"
                            >
                                <img
                                    v-if="p.cover_image"
                                    :src="p.cover_image"
                                    :alt="p.title"
                                    class="w-20 h-20 object-cover rounded"
                                />
                                <div v-else class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center text-gray-400 text-2xl">
                                    📄
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-gray-900">{{ p.title }}</div>
                                    <div v-if="p.excerpt" class="text-sm text-gray-500 truncate">{{ p.excerpt }}</div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        {{ p.created_at }}
                                        <span v-if="p.author_name"> · {{ p.author_name }}</span>
                                        <span class="ml-2" :class="p.is_published ? 'text-green-600' : 'text-amber-600'">
                                            {{ p.is_published ? 'เผยแพร่' : 'แบบร่าง' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-sm text-gray-500">ดู {{ p.view_count }}</span>
                                    <Link
                                        :href="route('blog.show', p.slug)"
                                        target="_blank"
                                        class="text-sm text-indigo-600 hover:text-indigo-800"
                                    >
                                        ดูหน้า
                                    </Link>
                                    <Link
                                        :href="route('backoffice.posts.edit', p.id)"
                                        class="text-sm text-indigo-600 hover:text-indigo-800"
                                    >
                                        แก้ไข
                                    </Link>
                                    <button
                                        @click="destroy(p.id, p.title)"
                                        class="text-sm text-red-600 hover:text-red-800"
                                    >
                                        ลบ
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
