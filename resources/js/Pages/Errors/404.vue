<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineProps({
    canLogin: {
        type: Boolean,
        default: false,
    },
    canRegister: {
        type: Boolean,
        default: false,
    },
});

const goBack = () => {
    // เช็คว่ามี referrer (หน้าก่อนหน้า) หรือไม่
    if (document.referrer && document.referrer !== window.location.href) {
        // ถ้ามี referrer ให้กลับไปหน้าก่อนหน้า
        window.history.back();
    } else {
        // ถ้าไม่มี referrer หรือ referrer เป็นหน้าเดียวกัน ให้ไปหน้าหลักแทน
        router.visit(route('home'));
    }
};
</script>

<template>
    <Head title="ไม่พบหน้านี้" />
    
    <PublicLayout :can-login="canLogin" :can-register="canRegister">
        <div class="min-h-[60vh] flex items-center justify-center py-20">
            <div class="text-center px-4">
                <div class="mb-8">
                    <h1 class="text-9xl font-bold text-red-600 mb-4">404</h1>
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                        ไม่พบหน้านี้
                    </h2>
                    <p class="text-xl text-gray-600 mb-8 max-w-md mx-auto">
                        ขออภัย หน้าที่คุณกำลังมองหาไม่มีอยู่ในระบบ
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link
                        :href="route('home')"
                        class="px-8 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-lg shadow-lg"
                    >
                        กลับหน้าหลัก
                    </Link>
                </div>
                
                <div class="mt-12 pt-8 border-t border-gray-200">
                    <p class="text-gray-500 mb-4">หรือลองไปที่:</p>
                    <div class="flex flex-wrap gap-4 justify-center">
                        <Link :href="route('lucky-numbers.index')" class="text-red-600 hover:text-red-700 hover:underline">
                            รวมเลขเด็ด
                        </Link>
                        <Link :href="route('statistics.index')" class="text-red-600 hover:text-red-700 hover:underline">
                            สถิติหวย
                        </Link>
                        <Link :href="route('accuracy.index')" class="text-red-600 hover:text-red-700 hover:underline">
                            ตารางคะแนน
                        </Link>
                        <Link :href="route('results.index')" class="text-red-600 hover:text-red-700 hover:underline">
                            ตรวจหวย
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
