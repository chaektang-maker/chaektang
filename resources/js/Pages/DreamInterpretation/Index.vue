<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    oldDream: { type: String, default: '' },
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
        <title>ทำนายฝัน - แจกตัง | เลขเด็ด หวย สถิติ</title>
        <meta name="description" content="ทำนายความฝัน ด้วย AI ฟรี พิมพ์ความฝันของคุณแล้วรับคำทำนายและเลขเด็ด" />
    </Head>

    <PublicLayout>
        <div class="py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">ทำนายฝัน</h1>
                    <p class="text-gray-600">
                        พิมพ์ความฝันของคุณ ระบบจะใช้ AI ทำนายความหมายและอาจแนะนำเลขเด็ดจากสัญลักษณ์ในฝัน
                    </p>
                </div>

                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 md:p-8">
                    <!-- แสดงผลทำนาย (เมื่อสำเร็จ) -->
                    <div
                        v-if="$page.props.flash?.dream_interpretation"
                        class="mb-6 p-5 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200"
                    >
                        <h2 class="font-semibold text-amber-900 mb-2 flex items-center gap-2">
                            <span class="text-xl">✨</span> คำทำนายฝัน
                        </h2>
                        <div class="text-gray-800 whitespace-pre-line leading-relaxed">
                            {{ $page.props.flash.dream_interpretation }}
                        </div>
                    </div>

                    <!-- แสดงข้อความ error -->
                    <div
                        v-if="$page.props.flash?.dream_error"
                        class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm"
                    >
                        {{ $page.props.flash.dream_error }}
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
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing">กำลังทำนาย...</span>
                            <span v-else">ทำนายฝัน</span>
                        </PrimaryButton>
                    </form>
                </div>

                <p class="mt-6 text-center text-sm text-gray-500">
                    ใช้ AI จาก Google AI Studio (Gemini) คำทำนายเป็นความบันเทิงเท่านั้น
                </p>
            </div>
        </div>
    </PublicLayout>
</template>
