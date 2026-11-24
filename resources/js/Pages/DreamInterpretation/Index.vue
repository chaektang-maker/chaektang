<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ShareButtons from '@/Components/ShareButtons.vue';

const props = defineProps({
    oldDream: { type: String, default: '' },
    quota: {
        type: Object,
        default: () => ({
            tier: 'guest',
            is_unlimited: false,
            limit: 1,
            used: 0,
            remaining: 1,
        }),
    },
    affiliateProducts: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    dream: props.oldDream || '',
});

const submit = () => {
    form.post(route('dream-interpretation.interpret'));
};
</script>

<template>
    <Head>
        <title>ทำนายฝัน & ตีเลขมงคล - แจกตัง | เลขเด็ด หวย สถิติ</title>
        <meta name="description" content="ทำนายความฝันและตีเลขมงคลฟรี พิมพ์ความฝันของคุณแล้วรับคำทำนายพร้อมแนวทางเลขเด็ด" />
    </Head>

    <PublicLayout>
        <div class="py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-8">
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">ทำนายฝัน & ตีเลขมงคล</h1>
                    <p class="text-gray-600">
                        พิมพ์ความฝันของคุณ ระบบจะทำนายความหมายและถอดรหัสเลขเด็ดจากสัญลักษณ์ในฝัน
                    </p>
                </div>

                <!-- แถบแสดงโควตาทำนายฝันรายวัน -->
                <div class="bg-white rounded-2xl shadow-sm border border-amber-200 p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 text-sm">
                        <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                            <svg v-if="quota.is_unlimited" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z" />
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </span>
                        <div v-if="quota.is_unlimited" class="font-semibold text-amber-800">
                            สิทธิ์สมาชิก VIP: ทำนายฝันและตีเลขมงคลได้ไม่จำกัดจำนวนครั้ง
                        </div>
                        <div v-else class="text-gray-700">
                            <span>โควตาทำนายฝันวันนี้: </span>
                            <strong :class="quota.remaining > 0 ? 'text-green-600' : 'text-red-600'">
                                เหลือ {{ quota.remaining }} / {{ quota.limit }} ครั้ง
                            </strong>
                            <span v-if="quota.tier === 'guest'" class="text-xs text-gray-500 ml-1">(ผู้เยี่ยมชมทั่วไป)</span>
                            <span v-else class="text-xs text-gray-500 ml-1">(สมาชิกฟรี)</span>
                        </div>
                    </div>

                    <div v-if="!quota.is_unlimited" class="flex items-center gap-2 shrink-0">
                        <Link
                            v-if="quota.tier === 'guest'"
                            :href="route('register')"
                            class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-800 hover:bg-gray-200 text-xs font-semibold transition-colors"
                        >
                            สมัครสมาชิกฟรี (3 ครั้ง/วัน)
                        </Link>
                        <Link
                            :href="route('vip-request.create')"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-gradient-to-r from-amber-500 to-yellow-500 text-gray-900 hover:from-amber-600 hover:to-yellow-600 text-xs font-bold shadow-sm transition-all"
                        >
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z" />
                            </svg>
                            <span>อัปเกรด VIP (ไม่จำกัด)</span>
                        </Link>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 md:p-8">
                    <!-- แสดงผลทำนาย (เมื่อสำเร็จ) -->
                    <div
                        v-if="$page.props.flash?.dream_interpretation"
                        class="mb-6 p-5 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200"
                    >
                        <h2 class="font-semibold text-amber-900 mb-2 flex items-center gap-2">
                            คำทำนายฝัน & เลขมงคล
                        </h2>
                        <div class="text-gray-800 whitespace-pre-line leading-relaxed">
                            {{ $page.props.flash.dream_interpretation }}
                        </div>
                        <div class="mt-4 pt-4 border-t border-amber-200/80 flex flex-wrap items-center justify-between gap-2">
                            <span class="text-xs font-semibold text-amber-900">ส่งต่อคำทำนายและเลขมงคลนี้ให้เพื่อน:</span>
                            <ShareButtons
                                compact
                                title="ทำนายฝันและตีเลขมงคล - เว็บแจกตัง"
                                :text="`คำทำนายฝันและเลขมงคลจากเว็บแจกตัง:\n${$page.props.flash.dream_interpretation}`"
                            />
                        </div>
                    </div>

                    <!-- แสดงข้อความ error -->
                    <div
                        v-if="$page.props.flash?.dream_error"
                        class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm space-y-2"
                    >
                        <div>{{ $page.props.flash.dream_error }}</div>
                        <div v-if="!quota.is_unlimited && quota.remaining <= 0" class="pt-1 flex flex-wrap gap-2">
                            <Link
                                v-if="quota.tier === 'guest'"
                                :href="route('register')"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg bg-white border border-red-300 text-red-700 text-xs font-bold hover:bg-red-100"
                            >
                                สมัครสมาชิกฟรี →
                            </Link>
                            <Link
                                :href="route('vip-request.create')"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg bg-red-600 text-white text-xs font-bold hover:bg-red-700"
                            >
                                👑 สมัคร VIP ใช้งานไม่จำกัด →
                            </Link>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <InputLabel for="dream" value="ความฝันของคุณ" class="font-medium text-gray-700" />
                            <textarea
                                id="dream"
                                v-model="form.dream"
                                rows="5"
                                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-gray-900"
                                placeholder="เช่น ฝันว่าเห็นงูใหญ่ เลื้อยเข้ามาในบ้าน..."
                                maxlength="2000"
                            />
                            <p class="mt-1 text-xs text-gray-500">สูงสุด 2000 ตัวอักษร</p>
                            <InputError class="mt-1" :message="form.errors.dream" />
                        </div>

                        <PrimaryButton
                            type="submit"
                            class="w-full justify-center"
                            :disabled="form.processing || (!quota.is_unlimited && quota.remaining <= 0)"
                        >
                            <span v-if="form.processing">กำลังทำนาย...</span>
                            <span v-else-if="!quota.is_unlimited && quota.remaining <= 0">ใช้โควตาวันนี้ครบแล้ว (อัปเกรด VIP เพื่อใช้ต่อ)</span>
                            <span v-else>ทำนายฝัน</span>
                        </PrimaryButton>
                    </form>
                </div>

                <!-- สินค้าสายมูเสริมโชค (Affiliate Section เพิ่มโอกาสคลิกซื้อหลังทำนายฝัน) -->
                <div v-if="affiliateProducts && affiliateProducts.length > 0" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">🧿 วัตถุมงคล & ของนำโชคเสริมดวง</h2>
                            <p class="text-xs text-gray-500">เสริมสิริมงคลและโชคลาภควบคู่กับเลขมงคลจากความฝัน</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <a
                            v-for="product in affiliateProducts"
                            :key="product.id"
                            :href="product.affiliate_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="group block bg-gray-50 rounded-xl border border-gray-200 overflow-hidden hover:shadow-md hover:border-red-200 transition-all"
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
                                <div class="text-xs font-semibold text-gray-900 line-clamp-2 group-hover:text-red-600">
                                    {{ product.title }}
                                </div>
                                <span class="inline-block mt-1.5 text-[11px] text-red-600 font-medium">
                                    ดูที่ {{ product.platform?.name || 'ร้านค้า' }} →
                                </span>
                            </div>
                        </a>
                    </div>
                </div>

                <p class="text-center text-sm text-gray-500">
                    คำทำนายความฝันและตัวเลขมงคลเป็นความเชื่อส่วนบุคคลเพื่อเป็นแนวทางเท่านั้น
                </p>
            </div>
        </div>
    </PublicLayout>
</template>
