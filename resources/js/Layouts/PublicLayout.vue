<script setup>
import Navigation from '@/Components/Navigation.vue';
import Footer from '@/Components/Footer.vue';
import FloatingContactButton from '@/Components/FloatingContactButton.vue';

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-red-50 to-white flex flex-col">
        <!-- Navigation -->
        <Navigation :can-login="canLogin" :can-register="canRegister" />

        <!-- Main Content -->
        <main class="pt-20 flex-grow">
            <slot />
        </main>

        <!-- Footer -->
        <Footer />

        <!-- ปุ่มลอยติดต่อแอดมิน / ลงโฆษณาทาง LINE -->
        <FloatingContactButton />
    </div>
</template>

