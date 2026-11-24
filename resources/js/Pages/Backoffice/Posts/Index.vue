<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    posts: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ q: '', status: 'all' }),
    },
    stats: {
        type: Object,
        default: () => ({ total: 0, published: 0, draft: 0, total_views: 0 }),
    },
});

const page = usePage();
const search = ref(props.filters?.q || '');
const currentStatus = ref(props.filters?.status || 'all');

const applyFilters = () => {
    router.get(
        route('backoffice.posts.index'),
        {
            q: search.value || undefined,
            status: currentStatus.value !== 'all' ? currentStatus.value : undefined,
        },
        { preserveState: true, replace: true }
    );
};

const setStatusTab = (status) => {
    currentStatus.value = status;
    applyFilters();
};

const clearFilters = () => {
    search.value = '';
    currentStatus.value = 'all';
    router.get(route('backoffice.posts.index'), {}, { preserveState: true, replace: true });
};

const togglePublish = (post) => {
    router.post(route('backoffice.posts.toggle-publish', post.id), {}, { preserveScroll: true });
};

const destroy = (id, title) => {
    if (!confirm(`ยืนยันลบบทความ "${title}"?`)) return;
    router.delete(route('backoffice.posts.destroy', id));
};
</script>

<template>
    <Head title="จัดการบทความ SEO" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-gray-800">
                        📰 จัดการบทความ & คอนเทนต์ SEO
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        สร้างบทความวิเคราะห์หวย สถิติ และเรื่องสายมูเพื่อดึงคนเข้าเว็บจาก Google
                    </p>
                </div>
                <Link :href="route('backoffice.posts.create')">
                    <PrimaryButton>+ เขียนบทความใหม่</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div
                    v-if="page.props.flash?.success"
                    class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-sm font-medium"
                >
                    ✓ {{ page.props.flash.success }}
                </div>

                <!-- 4 การ์ดสรุปสถิติบทความ -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-400">บทความทั้งหมด</div>
                        <div class="text-3xl font-extrabold text-gray-900 mt-2">{{ stats.total || 0 }}</div>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <div class="text-xs font-bold uppercase tracking-wider text-emerald-600">เผยแพร่บนเว็บแล้ว</div>
                        <div class="text-3xl font-extrabold text-emerald-600 mt-2">{{ stats.published || 0 }}</div>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <div class="text-xs font-bold uppercase tracking-wider text-amber-600">แบบร่าง (รอเผยแพร่)</div>
                        <div class="text-3xl font-extrabold text-amber-600 mt-2">{{ stats.draft || 0 }}</div>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                        <div class="text-xs font-bold uppercase tracking-wider text-indigo-600">ยอดอ่านรวมทั้งหมด</div>
                        <div class="text-3xl font-extrabold text-indigo-600 mt-2">
                            👁️ {{ (stats.total_views || 0).toLocaleString() }}
                        </div>
                    </div>
                </div>

                <!-- แถบค้นหาและตัวกรองสถานะ -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            @click="setStatusTab('all')"
                            :class="[
                                'px-4 py-2 rounded-xl text-xs font-bold transition-all',
                                currentStatus === 'all'
                                    ? 'bg-gray-900 text-white shadow-sm'
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
                            ]"
                        >
                            ทั้งหมด ({{ stats.total || 0 }})
                        </button>
                        <button
                            type="button"
                            @click="setStatusTab('published')"
                            :class="[
                                'px-4 py-2 rounded-xl text-xs font-bold transition-all',
                                currentStatus === 'published'
                                    ? 'bg-emerald-600 text-white shadow-sm'
                                    : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100',
                            ]"
                        >
                            เผยแพร่แล้ว ({{ stats.published || 0 }})
                        </button>
                        <button
                            type="button"
                            @click="setStatusTab('draft')"
                            :class="[
                                'px-4 py-2 rounded-xl text-xs font-bold transition-all',
                                currentStatus === 'draft'
                                    ? 'bg-amber-500 text-white shadow-sm'
                                    : 'bg-amber-50 text-amber-700 hover:bg-amber-100',
                            ]"
                        >
                            แบบร่าง ({{ stats.draft || 0 }})
                        </button>
                    </div>

                    <form @submit.prevent="applyFilters" class="flex items-center gap-2 w-full md:w-auto">
                        <TextInput
                            v-model="search"
                            type="text"
                            placeholder="ค้นหาชื่อบทความ..."
                            class="w-full md:w-64 text-sm"
                        />
                        <PrimaryButton type="submit">ค้นหา</PrimaryButton>
                        <SecondaryButton v-if="search || currentStatus !== 'all'" type="button" @click="clearFilters">
                            ล้าง
                        </SecondaryButton>
                    </form>
                </div>

                <!-- รายการบทความ -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                    <div class="p-6 text-gray-900">
                        <div v-if="posts.length === 0" class="text-center py-12 text-gray-500">
                            <div class="text-4xl mb-2">📝</div>
                            <div>ไม่พบบทความ — กด "+ เขียนบทความใหม่" เพื่อเริ่มต้น</div>
                        </div>

                        <div v-else class="divide-y divide-gray-100">
                            <div
                                v-for="p in posts"
                                :key="p.id"
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 py-4 first:pt-0 last:pb-0 hover:bg-gray-50/70 px-3 rounded-xl transition-colors"
                            >
                                <div class="flex items-start sm:items-center gap-4 min-w-0">
                                    <img
                                        v-if="p.cover_image"
                                        :src="p.cover_image"
                                        :alt="p.title"
                                        class="w-24 h-16 object-cover rounded-lg border border-gray-200 shrink-0"
                                    />
                                    <div
                                        v-else
                                        class="w-24 h-16 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 text-2xl shrink-0"
                                    >
                                        📄
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span
                                                :class="[
                                                    'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold',
                                                    p.is_published
                                                        ? 'bg-emerald-100 text-emerald-800'
                                                        : 'bg-amber-100 text-amber-800',
                                                ]"
                                            >
                                                {{ p.is_published ? '● เผยแพร่แล้ว' : '○ แบบร่าง' }}
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 text-xs font-semibold">
                                                👁️ {{ (p.view_count || 0).toLocaleString() }} ครั้ง
                                            </span>
                                        </div>
                                        <div class="font-bold text-gray-900 mt-1 truncate">{{ p.title }}</div>
                                        <div v-if="p.excerpt" class="text-xs text-gray-500 truncate mt-0.5">{{ p.excerpt }}</div>
                                        <div class="text-[11px] text-gray-400 mt-1">
                                            สร้างเมื่อ {{ p.created_at }}
                                            <span v-if="p.published_at"> · เผยแพร่ {{ p.published_at }}</span>
                                            <span v-if="p.author_name"> · โดย {{ p.author_name }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 shrink-0">
                                    <button
                                        type="button"
                                        @click="togglePublish(p)"
                                        :class="[
                                            'px-3 py-1.5 rounded-lg text-xs font-bold border transition-colors',
                                            p.is_published
                                                ? 'bg-amber-50 border-amber-200 text-amber-800 hover:bg-amber-100'
                                                : 'bg-emerald-600 border-emerald-600 text-white hover:bg-emerald-700',
                                        ]"
                                    >
                                        {{ p.is_published ? 'เปลี่ยนเป็นแบบร่าง' : 'กดเผยแพร่ทันที' }}
                                    </button>
                                    <a
                                        v-if="p.is_published && p.slug"
                                        :href="route('blog.show', p.slug)"
                                        target="_blank"
                                        class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs font-semibold"
                                    >
                                        ดูหน้าเว็บ ↗
                                    </a>
                                    <Link
                                        :href="route('backoffice.posts.edit', p.id)"
                                        class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-bold"
                                    >
                                        แก้ไข
                                    </Link>
                                    <button
                                        type="button"
                                        @click="destroy(p.id, p.title)"
                                        class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-xs font-bold"
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
