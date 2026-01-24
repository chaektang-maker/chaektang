<script setup>
import Navigation from '@/Components/Navigation.vue';

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
    <div class="min-h-screen bg-gradient-to-b from-red-50 to-white">
        <!-- Navigation -->
        <Navigation :can-login="canLogin" :can-register="canRegister" />

        <!-- Main Content -->
        <main class="pt-20">
            <slot />
        </main>
    </div>
</template>

