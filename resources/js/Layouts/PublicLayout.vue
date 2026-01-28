<script setup>
import Navigation from '@/Components/Navigation.vue';
import Footer from '@/Components/Footer.vue';

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
    </div>
</template>

