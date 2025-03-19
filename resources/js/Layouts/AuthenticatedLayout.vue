<script setup>
import {Head, usePage} from '@inertiajs/vue3'
import CrmSidebar from '@/Layouts/CrmSidebar/Sidebar.vue'
import CrmNavbar from '@/Layouts/CrmNavbar/CrmNavbar.vue'
import { sidebarState } from '@/Composables'
import ToastList from "@/Components/ToastList.vue";
import ConfirmationDialog from "@/Components/ConfirmationDialog.vue";

defineProps({
    title: String
})
</script>

<template>
    <Head :title="title"></Head>

    <div
        class="min-h-screen bg-surface-50 dark:bg-surface-950 transition-all duration-200"
    >
        <!-- Sidebar -->
        <CrmSidebar />

        <div
            style="transition-property: margin; transition-duration: 150ms"
            :class="[
                'min-h-screen flex flex-col',
                {
                    'lg:ml-[252px]': sidebarState.isOpen,
                },
            ]"
        >
            <!-- Navbar -->
            <CrmNavbar :title="title" />

            <!-- Page Content -->
            <main class="flex flex-1 justify-center items-start px-3 pt-3 pb-12 md:px-5 md:pt-5">
                <div class="w-full max-w-[1440px]">
                    <!-- Toast -->
                    <ToastList />
                    <!-- Confirmation Dialog -->
                    <ConfirmationDialog />

                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
