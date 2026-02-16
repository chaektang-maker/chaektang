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
                    class="prose prose-lg prose-slate max-w-none 
                           prose-headings:font-bold prose-headings:text-gray-900 
                           prose-h1:text-4xl prose-h1:mb-4 prose-h1:mt-8
                           prose-h2:text-3xl prose-h2:mb-3 prose-h2:mt-6
                           prose-h3:text-2xl prose-h3:mb-2 prose-h3:mt-4
                           prose-p:text-gray-700 prose-p:leading-relaxed prose-p:mb-4
                           prose-a:text-red-600 prose-a:no-underline hover:prose-a:underline
                           prose-strong:text-gray-900 prose-strong:font-semibold
                           prose-ul:my-4 prose-ul:pl-6
                           prose-ol:my-4 prose-ol:pl-6
                           prose-li:my-2
                           prose-blockquote:border-l-4 prose-blockquote:border-gray-300 prose-blockquote:pl-4 prose-blockquote:italic prose-blockquote:text-gray-600 prose-blockquote:my-4
                           prose-code:text-red-600 prose-code:bg-gray-100 prose-code:px-1 prose-code:py-0.5 prose-code:rounded prose-code:text-sm prose-code:font-mono
                           prose-pre:bg-gray-900 prose-pre:text-gray-100 prose-pre:rounded-lg prose-pre:p-4 prose-pre:overflow-x-auto prose-pre:my-4
                           prose-pre code:bg-transparent prose-pre code:text-gray-100 prose-pre code:p-0
                           prose-hr:my-8 prose-hr:border-gray-300
                           prose-img:rounded-lg prose-img:shadow-lg prose-img:my-6
                           prose-table:my-6 prose-table:w-full
                           prose-th:border prose-th:border-gray-300 prose-th:bg-gray-50 prose-th:px-4 prose-th:py-2 prose-th:text-left prose-th:font-semibold
                           prose-td:border prose-td:border-gray-300 prose-td:px-4 prose-td:py-2"
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
