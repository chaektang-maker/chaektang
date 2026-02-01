<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineProps({
    posts: Object,
});
</script>

<template>
    <Head>
        <title>บทความ - แจกตัง | เลขเด็ด หวย สถิติ บทความหวย</title>
        <meta name="description" content="บทความหวย เลขเด็ด สถิติหวย บทความสาระความรู้เกี่ยวกับหวย สลากกินแบ่ง" />
        <meta name="keywords" content="บทความหวย, เลขเด็ด, สถิติหวย, บทความ, แจกตัง" />
    </Head>

    <PublicLayout>
        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">บทความ</h1>
                    <p class="text-gray-600">สาระความรู้ เลขเด็ด สถิติหวย และเรื่องน่าสนใจ</p>
                </div>

                <div v-if="!posts.data || posts.data.length === 0" class="text-center py-16 text-gray-500">
                    ยังไม่มีบทความ
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <Link
                        v-for="p in posts.data"
                        :key="p.id"
                        :href="route('blog.show', p.slug)"
                        class="group block bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg hover:border-red-200 transition-all"
                    >
                        <div class="aspect-video bg-gray-100 overflow-hidden">
                            <img
                                v-if="p.cover_image_url"
                                :src="p.cover_image_url"
                                :alt="p.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-5xl">
                                📄
                            </div>
                        </div>
                        <div class="p-5">
                            <h2 class="font-bold text-lg text-gray-900 line-clamp-2 group-hover:text-red-600 transition-colors">
                                {{ p.title }}
                            </h2>
                            <p v-if="p.excerpt" class="text-gray-500 text-sm mt-2 line-clamp-2">
                                {{ p.excerpt }}
                            </p>
                            <div class="flex items-center gap-3 mt-3 text-xs text-gray-400">
                                <span>{{ p.published_at }}</span>
                                <span v-if="p.author_name">{{ p.author_name }}</span>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="posts.links && posts.links.length > 3" class="mt-12 flex justify-center gap-2">
                    <component
                        :is="link.url ? 'Link' : 'span'"
                        v-for="(link, i) in posts.links"
                        :key="i"
                        :href="link.url"
                        :class="[
                            'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                            link.active
                                ? 'bg-red-600 text-white'
                                : link.url
                                    ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    : 'bg-gray-50 text-gray-400 cursor-not-allowed'
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
