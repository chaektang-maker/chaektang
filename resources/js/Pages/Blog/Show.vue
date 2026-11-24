<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ShareButtons from '@/Components/ShareButtons.vue';

defineProps({
    post: {
        type: Object,
        required: true,
    },
    relatedPosts: {
        type: Array,
        default: () => [],
    },
    affiliateProducts: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head>
        <title>{{ post.meta_title }} - แจกตัง</title>
        <meta name="description" :content="post.meta_description" />
        <meta property="og:title" :content="post.meta_title + ' - แจกตัง'" />
        <meta property="og:description" :content="post.meta_description" />
        <meta property="og:type" content="article" />
        <meta v-if="post.cover_image_url" property="og:image" :content="post.cover_image_url" />
    </Head>

    <PublicLayout>
        <div class="py-10 bg-gradient-to-b from-gray-50 to-white min-h-screen">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-8">
                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-xs sm:text-sm text-gray-500 overflow-x-auto whitespace-nowrap">
                    <Link :href="route('home')" class="hover:text-red-600">หน้าแรก</Link>
                    <span>/</span>
                    <Link :href="route('blog.index')" class="hover:text-red-600">บทความ</Link>
                    <span>/</span>
                    <span class="text-gray-800 font-medium truncate max-w-[240px] sm:max-w-md">{{ post.title }}</span>
                </nav>

                <!-- กล่องเนื้อหาบทความหลัก -->
                <article class="bg-white rounded-3xl shadow-md border border-gray-100 overflow-hidden">
                    <!-- รูปปก -->
                    <div v-if="post.cover_image_url" class="w-full max-h-[460px] overflow-hidden bg-gray-100">
                        <img
                            :src="post.cover_image_url"
                            :alt="post.title"
                            class="w-full h-full object-cover"
                        />
                    </div>

                    <div class="p-6 sm:p-10">
                        <!-- Header บทความ -->
                        <header class="pb-6 mb-6 border-b border-gray-100 space-y-4">
                            <div class="flex flex-wrap items-center gap-3 text-xs sm:text-sm text-gray-500">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-red-50 text-red-700 font-bold">
                                    📰 บทความแจกตัง
                                </span>
                                <span>📅 {{ post.published_at }}</span>
                                <span v-if="post.author_name">✍️ {{ post.author_name }}</span>
                                <span>⏱️ อ่าน {{ post.reading_time || 1 }} นาที</span>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 font-semibold">
                                    👁️ อ่านแล้ว {{ (post.view_count || 0).toLocaleString() }} ครั้ง
                                </span>
                            </div>

                            <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight">
                                {{ post.title }}
                            </h1>

                            <!-- แถบแชร์ด้านบนบทความ -->
                            <div class="pt-2 flex flex-wrap items-center justify-between gap-3">
                                <ShareButtons
                                    :title="post.title"
                                    :text="`📰 ${post.title} — อ่านต่อที่เว็บแจกตัง`"
                                    label="แชร์บทความนี้:"
                                />
                            </div>
                        </header>

                        <!-- เนื้อหา HTML จาก RichTextEditor -->
                        <div
                            class="article-content prose prose-lg prose-slate max-w-none
                                   prose-headings:font-bold prose-headings:text-gray-900
                                   prose-h1:text-3xl prose-h1:mb-4 prose-h1:mt-8
                                   prose-h2:text-2xl prose-h2:mb-3 prose-h2:mt-6
                                   prose-h3:text-xl prose-h3:mb-2 prose-h3:mt-4
                                   prose-p:text-gray-700 prose-p:leading-relaxed prose-p:mb-4
                                   prose-a:text-red-600 prose-a:font-semibold prose-a:no-underline hover:prose-a:underline
                                   prose-strong:font-bold prose-strong:text-inherit
                                   prose-ul:my-4 prose-ul:pl-6
                                   prose-ol:my-4 prose-ol:pl-6
                                   prose-li:my-1.5
                                   prose-blockquote:border-l-4 prose-blockquote:border-red-500 prose-blockquote:bg-red-50/50 prose-blockquote:py-2 prose-blockquote:px-4 prose-blockquote:rounded-r-lg prose-blockquote:italic prose-blockquote:text-gray-700 prose-blockquote:my-4
                                   prose-img:rounded-2xl prose-img:shadow-md prose-img:my-6"
                            v-html="post.content"
                        />

                        <!-- แถบแชร์ท้ายบทความ -->
                        <div class="mt-10 pt-6 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <span class="text-sm font-bold text-gray-800">ชอบบทความนี้? ส่งต่อให้เพื่อนในกลุ่ม LINE เลย:</span>
                            <ShareButtons
                                :title="post.title"
                                :text="`📰 ${post.title} — อ่านต่อที่เว็บแจกตัง`"
                                label=""
                            />
                        </div>
                    </div>
                </article>

                <!-- กล่องเปลี่ยนคนอ่านบทความให้เป็นลูกค้า VIP / ไปหน้าเลขเด็ด -->
                <div class="bg-gradient-to-r from-red-600 via-red-700 to-amber-600 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <div class="inline-flex items-center gap-1 rounded-full bg-yellow-400 text-gray-900 px-3 py-0.5 text-xs font-extrabold mb-2">
                            👑 พิเศษสำหรับคอหวย
                        </div>
                        <h2 class="text-xl sm:text-2xl font-extrabold">
                            ส่องเลขเด็ดงวดนี้ & ปลดล็อกเลขสำนักเข้า 3 งวดติด!
                        </h2>
                        <p class="text-red-100 text-sm mt-1">
                            ดูตารางรวมเลขเด็ดจากทุกสำนักดัง พร้อมอันดับเลขมาแรงที่ชนกันมากที่สุดในงวดนี้
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3 shrink-0">
                        <Link
                            :href="route('lucky-numbers.index')"
                            class="px-5 py-3 rounded-xl bg-yellow-400 text-gray-900 font-extrabold text-sm shadow-lg hover:bg-yellow-300 transition-all"
                        >
                            🔥 ดูเลขเด็ดงวดนี้ →
                        </Link>
                        <Link
                            :href="route('vip-request.create')"
                            class="px-5 py-3 rounded-xl bg-white/15 border border-white text-white font-bold text-sm hover:bg-white/25 transition-all"
                        >
                            👑 สมัครสมาชิก VIP
                        </Link>
                    </div>
                </div>

                <!-- สินค้าสายมู & ของนำโชคท้ายบทความ (Affiliate Monetization) -->
                <section
                    v-if="affiliateProducts && affiliateProducts.length > 0"
                    class="bg-white rounded-3xl shadow-sm border border-gray-200 p-6 sm:p-8"
                >
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-extrabold text-gray-900">🧿 วัตถุมงคล & ของนำโชคเสริมดวง</h2>
                            <p class="text-xs sm:text-sm text-gray-500">บูชาวัตถุมงคลเสริมโชคลาภจากร้านค้าออนไลน์ชั้นนำ</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <a
                            v-for="product in affiliateProducts"
                            :key="product.id"
                            :href="product.affiliate_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="group block bg-gray-50 rounded-2xl border border-gray-200 overflow-hidden hover:shadow-md hover:border-red-300 transition-all"
                        >
                            <div class="aspect-square bg-gray-100 overflow-hidden">
                                <img
                                    v-if="product.image_url"
                                    :src="product.image_url"
                                    :alt="product.title"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-3xl text-gray-400">
                                    🧿
                                </div>
                            </div>
                            <div class="p-3">
                                <div class="text-xs font-bold text-gray-900 line-clamp-2 group-hover:text-red-600">
                                    {{ product.title }}
                                </div>
                                <span class="inline-block mt-1.5 text-[11px] text-red-600 font-semibold">
                                    ดูที่ {{ product.platform?.name || 'ร้านค้า' }} →
                                </span>
                            </div>
                        </a>
                    </div>
                </section>

                <!-- บทความน่าสนใจอื่นๆ (Related Posts) -->
                <section v-if="relatedPosts && relatedPosts.length > 0" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-extrabold text-gray-900">📚 บทความน่าสนใจอื่นๆ</h2>
                        <Link :href="route('blog.index')" class="text-sm font-bold text-red-600 hover:underline">
                            ดูบทความทั้งหมด →
                        </Link>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <Link
                            v-for="rel in relatedPosts"
                            :key="rel.id"
                            :href="route('blog.show', rel.slug)"
                            class="group bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-lg hover:border-red-200 transition-all"
                        >
                            <div class="aspect-video bg-gray-100 overflow-hidden">
                                <img
                                    v-if="rel.cover_image_url"
                                    :src="rel.cover_image_url"
                                    :alt="rel.title"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-3xl text-gray-400">
                                    📄
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="text-[11px] text-gray-400 mb-1">
                                    📅 {{ rel.published_at }} · 👁️ {{ (rel.view_count || 0).toLocaleString() }}
                                </div>
                                <h3 class="font-bold text-sm text-gray-900 line-clamp-2 group-hover:text-red-600">
                                    {{ rel.title }}
                                </h3>
                            </div>
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </PublicLayout>
</template>

<style>
.article-content img {
    display: block;
    max-width: 72%;
    max-height: 480px;
    width: auto;
    height: auto;
    object-fit: contain;
    margin: 1.5rem auto;
    border-radius: 1rem;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    border: 1px solid #f3f4f6;
}
@media (max-width: 640px) {
    .article-content img {
        max-width: 100%;
    }
}
.article-content img[data-size="small"] {
    max-width: 40%;
    max-height: 320px;
}
.article-content img[data-size="medium"] {
    max-width: 72%;
    max-height: 480px;
}
.article-content img[data-size="full"] {
    max-width: 100%;
    max-height: 680px;
}
.article-content img[data-align="left"] {
    margin-left: 0;
    margin-right: auto;
}
.article-content img[data-align="center"] {
    margin-left: auto;
    margin-right: auto;
}
.article-content img[data-align="right"] {
    margin-left: auto;
    margin-right: 0;
}
</style>
