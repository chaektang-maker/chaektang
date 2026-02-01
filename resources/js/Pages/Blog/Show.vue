<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineProps({
    post: Object,
});
</script>

<template>
    <Head>
        <title>{{ post.meta_title }} - แจกตัง</title>
        <meta name="description" :content="post.meta_description" />
        <meta property="og:title" :content="post.meta_title + ' - แจกตัง'" />
        <meta property="og:description" :content="post.meta_description" />
        <meta property="og:image" :content="post.cover_image_url" />
    </Head>

    <PublicLayout>
        <article class="py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <Link :href="route('blog.index')" class="inline-flex items-center text-gray-600 hover:text-red-600 mb-6">
                    ← กลับไปบทความ
                </Link>

                <header class="mb-8">
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        {{ post.title }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                        <span>{{ post.published_at }}</span>
                        <span v-if="post.author_name">{{ post.author_name }}</span>
                        <span>ดู {{ post.view_count }} ครั้ง</span>
                    </div>
                </header>

                <div v-if="post.cover_image_url" class="mb-8 rounded-xl overflow-hidden shadow-lg">
                    <img
                        :src="post.cover_image_url"
                        :alt="post.title"
                        class="w-full h-auto object-cover"
                    />
                </div>

                <div
                    class="prose prose-lg max-w-none prose-headings:text-gray-900 prose-p:text-gray-700 prose-a:text-red-600 prose-img:rounded-lg"
                    v-html="post.content"
                />

                <div class="mt-12 pt-8 border-t">
                    <Link :href="route('blog.index')" class="text-red-600 hover:text-red-700 font-medium">
                        ← ดูบทความอื่น
                    </Link>
                </div>
            </div>
        </article>
    </PublicLayout>
</template>
