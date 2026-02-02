<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-blue-50 via-white to-red-50 pt-6 sm:pt-0">
        <!-- Background Pattern -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-red-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-blob animation-delay-2000"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-purple-200 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-4000"></div>
        </div>

        <!-- Logo -->
        <div class="relative z-10 mb-8">
            <Link href="/" class="flex items-center gap-3 group">
                <img 
                    src="/logo-29012026.jpg" 
                    alt="แจกตัง" 
                    class="h-16 w-16 md:h-20 md:w-20 object-contain rounded-lg shadow-lg group-hover:shadow-xl transition-shadow"
                />
                <div class="text-2xl font-extrabold">
                    <span class="text-blue-600 group-hover:text-blue-700 transition-colors">แจก</span>
                    <span class="text-red-600 group-hover:text-red-700 transition-colors">ตัง</span>
                </div>
            </Link>
        </div>

        <!-- Login Card -->
        <div class="relative z-10 w-full max-w-md">
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-2xl border border-gray-200/50 px-8 py-10">
                <slot />
            </div>
        </div>

        <!-- Footer -->
        <div class="relative z-10 mt-8 text-center">
            <p class="text-sm text-gray-600">
                © {{ new Date().getFullYear() }} แจกตัง. สงวนลิขสิทธิ์
            </p>
        </div>
    </div>
</template>

<style scoped>
@keyframes blob {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
