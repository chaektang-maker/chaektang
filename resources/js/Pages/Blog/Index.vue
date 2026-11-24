<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ShareButtons from '@/Components/ShareButtons.vue';

const props = defineProps({
    posts: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    popularPosts: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ q: '', sort: 'latest' }),
    },
});

const search = ref(props.filters?.q || '');
const currentSort = ref(props.filters?.sort || 'latest');

const applyFilters = () => {
    router.get(
        route('blog.index'),
        {
            q: search.value || undefined,
            sort: currentSort.value !== 'latest' ? currentSort.value : undefined,
        },
        { preserveState: true, replace: true }
    );
};

const changeSort = (sort) => {
    currentSort.value = sort;
    applyFilters();
};

const clearSearch = () => {
    search.value = '';
    currentSort.value = 'latest';
    router.get(route('blog.index'), {}, { preserveState: true, replace: true });
};

// บทความเด่นชิ้นแรก (แสดงเฉพาะตอนไม่ได้ค้นหาและอยู่หน้า 1)
const featuredPost = computed(() => {
    if (props.filters?.q) return null;
    if ((props.posts?.current_page || 1) > 1) return null;
    return props.posts?.data?.[0] || null;
});

const gridPosts = computed(() => {
    const list = props.posts?.data || [];
    if (featuredPost.value && list.length > 0) {
        return list.slice(1);
    }
    return list;
});
</script>

<template>
    <Head>
        <title>บทความหวย & คลังความรู้สายมู - แจกตัง | วิเคราะห์สถิติหวย เลขเด็ด ความเชื่อ</title>
        <meta
            name="description"
            content="รวมบทความวิเคราะห์สถิติหวย สลากกินแบ่งรัฐบาล เคล็ดลับเสริมดวงสายมู และแนวทางเลขเด็ดประจำงวด อัปเดตใหม่ทุกสัปดาห์"
        />
        <meta name="keywords" content="บทความหวย, สถิติหวย, เคล็ดลับสายมู, เลขเด็ดงวดนี้, วิเคราะห์หวย, แจกตัง" />
    </Head>

    <PublicLayout>
        <div class="py-10 bg-gradient-to-b from-red-50/40 to-white min-h-screen">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-10">
                <!-- Header + ค้นหา & เรียงลำดับ -->
                <div class="bg-white rounded-3xl shadow-md border border-red-100 p-6 sm:p-8">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <div>
                            <div class="inline-flex items-center gap-1.5 rounded-full bg-red-100 text-red-700 px-3 py-1 text-xs font-bold mb-2">
                                📰 คลังบทความ & สาระสายมู
                            </div>
                            <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900">
                                บทความหวย & เคล็ดลับเสริมโชค
                            </h1>
                            <p class="text-gray-600 text-sm sm:text-base mt-1">
                                เจาะลึกสถิติสลากกินแบ่งรัฐบาล ตำราเลขมงคล และเรื่องน่ารู้สำหรับคอหวย
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <form @submit.prevent="applyFilters" class="flex items-center gap-2">
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="ค้นหาบทความ..."
                                    class="w-full sm:w-64 rounded-xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                                />
                                <button
                                    type="submit"
                                    class="px-4 py-2.5 rounded-xl bg-red-600 text-white font-bold text-sm hover:bg-red-700 transition-colors shrink-0"
                                >
                                    ค้นหา
                                </button>
                                <button
                                    v-if="search"
                                    type="button"
                                    @click="clearSearch"
                                    class="px-3 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-semibold text-sm hover:bg-gray-200 shrink-0"
                                >
                                    ล้าง
                                </button>
                            </form>

                            <div class="inline-flex rounded-xl bg-gray-100 p-1 self-start sm:self-auto">
                                <button
                                    type="button"
                                    @click="changeSort('latest')"
                                    :class="[
                                        'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all',
                                        currentSort === 'latest'
                                            ? 'bg-white text-red-600 shadow-sm'
                                            : 'text-gray-600 hover:text-gray-900',
                                    ]"
                                >
                                    อัปเดตล่าสุด
                                </button>
                                <button
                                    type="button"
                                    @click="changeSort('popular')"
                                    :class="[
                                        'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all',
                                        currentSort === 'popular'
                                            ? 'bg-white text-red-600 shadow-sm'
                                            : 'text-gray-600 hover:text-gray-900',
                                    ]"
                                >
                                    🔥 ยอดอ่านสูงสุด
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- กรณีไม่มีบทความ -->
                <div
                    v-if="!posts.data || posts.data.length === 0"
                    class="bg-white rounded-2xl border border-gray-200 p-16 text-center space-y-3"
                >
                    <div class="text-5xl">📄</div>
                    <div class="text-lg font-bold text-gray-800">
                        {{ search ? `ไม่พบบทความที่ตรงกับ "${search}"` : 'ยังไม่มีบทความในขณะนี้' }}
                    </div>
                    <p class="text-sm text-gray-500">
                        ระหว่างรอบทความใหม่ คุณสามารถเข้าไปดูแนวทางเลขเด็ดงวดนี้ได้ทันที
                    </p>
                    <div class="pt-2">
                        <Link
                            :href="route('lucky-numbers.index')"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-red-600 text-white font-bold text-sm hover:bg-red-700"
                        >
                            🔥 ไปหน้าเลขเด็ดงวดนี้ →
                        </Link>
                    </div>
                </div>

                <template v-else>
                    <!-- Featured Post (บทความไฮไลต์ชิ้นแรก) -->
                    <Link
                        v-if="featuredPost"
                        :href="route('blog.show', featuredPost.slug)"
                        class="group grid grid-cols-1 lg:grid-cols-12 bg-white rounded-3xl border-2 border-red-100 shadow-lg overflow-hidden hover:shadow-xl hover:border-red-300 transition-all"
                    >
                        <div class="lg:col-span-7 aspect-video lg:aspect-auto bg-gray-100 overflow-hidden relative min-h-[260px]">
                            <img
                                v-if="featuredPost.cover_image_url"
                                :src="featuredPost.cover_image_url"
                                :alt="featuredPost.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <div
                                v-else
                                class="w-full h-full bg-gradient-to-br from-red-600 to-amber-500 flex items-center justify-center text-white text-6xl"
                            >
                                📰
                            </div>
                            <span class="absolute top-4 left-4 inline-flex items-center gap-1 rounded-full bg-yellow-400 text-gray-900 px-3 py-1 text-xs font-extrabold shadow">
                                ⭐ บทความเด่นล่าสุด
                            </span>
                        </div>
                        <div class="lg:col-span-5 p-6 sm:p-8 flex flex-col justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 mb-3">
                                    <span>📅 {{ featuredPost.published_at }}</span>
                                    <span>• ⏱️ อ่าน {{ featuredPost.reading_time || 1 }} นาที</span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-50 text-red-700 font-semibold">
                                        👁️ {{ (featuredPost.view_count || 0).toLocaleString() }} ครั้ง
                                    </span>
                                </div>
                                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 group-hover:text-red-600 transition-colors leading-snug mb-3">
                                    {{ featuredPost.title }}
                                </h2>
                                <p v-if="featuredPost.excerpt" class="text-gray-600 text-sm sm:text-base line-clamp-3 leading-relaxed">
                                    {{ featuredPost.excerpt }}
                                </p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                                <span class="text-xs text-gray-500">โดย {{ featuredPost.author_name }}</span>
                                <span class="inline-flex items-center gap-1 text-sm font-bold text-red-600 group-hover:translate-x-1 transition-transform">
                                    อ่านบทความนี้ →
                                </span>
                            </div>
                        </div>
                    </Link>

                    <!-- ตารางการ์ดบทความ -->
                    <div v-if="gridPosts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link
                            v-for="p in gridPosts"
                            :key="p.id"
                            :href="route('blog.show', p.slug)"
                            class="group bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl hover:border-red-200 transition-all duration-300 flex flex-col justify-between"
                        >
                            <div>
                                <div class="aspect-video bg-gray-100 overflow-hidden relative">
                                    <img
                                        v-if="p.cover_image_url"
                                        :src="p.cover_image_url"
                                        :alt="p.title"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    />
                                    <div
                                        v-else
                                        class="w-full h-full bg-gradient-to-br from-red-50 to-amber-50 flex items-center justify-center text-red-400 text-5xl"
                                    >
                                        📄
                                    </div>
                                    <span class="absolute bottom-2.5 right-2.5 inline-flex items-center gap-1 rounded-full bg-black/70 backdrop-blur-sm text-white px-2.5 py-0.5 text-[11px] font-semibold">
                                        👁️ {{ (p.view_count || 0).toLocaleString() }}
                                    </span>
                                </div>
                                <div class="p-5">
                                    <div class="flex items-center gap-2 text-xs text-gray-400 mb-2">
                                        <span>📅 {{ p.published_at }}</span>
                                        <span>•</span>
                                        <span>อ่าน {{ p.reading_time || 1 }} นาที</span>
                                    </div>
                                    <h2 class="font-bold text-lg text-gray-900 line-clamp-2 group-hover:text-red-600 transition-colors leading-snug">
                                        {{ p.title }}
                                    </h2>
                                    <p v-if="p.excerpt" class="text-gray-500 text-sm mt-2 line-clamp-2 leading-relaxed">
                                        {{ p.excerpt }}
                                    </p>
                                </div>
                            </div>
                            <div class="px-5 pb-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                                <span class="text-gray-400">{{ p.author_name }}</span>
                                <span class="font-bold text-red-600 group-hover:underline">อ่านต่อ →</span>
                            </div>
                        </Link>
                    </div>

                    <!-- Pagination (แก้ไขบั๊ก Link component แล้ว) -->
                    <div v-if="posts.links && posts.links.length > 3" class="pt-4 flex flex-wrap justify-center gap-2">
                        <template v-for="(link, i) in posts.links" :key="i">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :class="[
                                    'px-4 py-2 rounded-xl text-sm font-bold transition-all border',
                                    link.active
                                        ? 'bg-red-600 text-white border-red-600 shadow-md'
                                        : 'bg-white text-gray-700 border-gray-200 hover:bg-red-50 hover:border-red-200',
                                ]"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="px-4 py-2 rounded-xl text-sm font-medium bg-gray-50 text-gray-400 border border-gray-200 cursor-not-allowed"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </template>

                <!-- แบนเนอร์เชื่อมไปหน้าเลขเด็ด & สมัคร VIP ท้ายหน้ารวมบทความ -->
                <div class="bg-gradient-to-r from-red-600 via-red-700 to-amber-600 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <div class="inline-flex items-center gap-1 rounded-full bg-yellow-400 text-gray-900 px-3 py-0.5 text-xs font-extrabold mb-2">
                            🔥 อัปเดตครบทุกสำนักดัง
                        </div>
                        <h3 class="text-xl sm:text-2xl font-extrabold">
                            อ่านบทความจบแล้ว อย่าลืมส่องแนวทางเลขเด็ดประจำงวดนี้!
                        </h3>
                        <p class="text-red-100 text-sm mt-1">
                            รวมเลขเด็ด 2 ตัว 3 ตัว เลขวิ่ง พร้อมตารางสถิติสำนักที่เข้าเป้า 3 งวดติด
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3 shrink-0">
                        <Link
                            :href="route('lucky-numbers.index')"
                            class="px-6 py-3 rounded-xl bg-yellow-400 text-gray-900 font-extrabold text-sm shadow-lg hover:bg-yellow-300 transition-all"
                        >
                            ดูเลขเด็ดงวดนี้ →
                        </Link>
                        <Link
                            :href="route('vip-request.create')"
                            class="px-5 py-3 rounded-xl bg-white/15 border border-white text-white font-bold text-sm hover:bg-white/25 transition-all"
                        >
                            👑 สมัคร VIP
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
