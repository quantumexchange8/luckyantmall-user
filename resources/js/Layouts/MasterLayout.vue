<script setup>
import {Head} from '@inertiajs/vue3'
import Navbar from "@/Components/Navbar.vue";

defineProps({
    title: String
})
</script>

<template>
    <Head :title="title"></Head>

    <div class="min-h-screen bg-surface-100 dark:bg-surface-950">

        <Navbar />

        <!-- Page Content -->
        <main v-if="!route().current('home')" class="flex flex-1 justify-center items-start px-3 pt-3 pb-32 md:px-5 md:pt-5 md:pb-12">
            <div class="w-full max-w-7xl">
                <slot />
            </div>
        </main>

        <main v-else class="flex flex-1 justify-center items-start pb-32 md:pb-12">
            <slot />
        </main>
    </div>
</template>
