<script setup>
import {Head} from '@inertiajs/vue3'
import Navbar from "@/Components/Navbar.vue";

defineProps({
    title: String
})
</script>

<template>
    <Head :title="title"></Head>

    <div class="min-h-screen bg-gray-100 dark:bg-surface-950">

        <Navbar />

        <!-- Page Content -->
        <main class="flex flex-1 justify-center items-start px-3 pt-3 pb-12 md:px-5 md:pt-5">
            <div class="w-full max-w-7xl">
                <slot />
            </div>
        </main>
    </div>
</template>
