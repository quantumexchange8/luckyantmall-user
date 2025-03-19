<script setup>
import { onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { sidebarState } from '@/Composables'
import SidebarHeader from '@/Layouts/CrmSidebar/SidebarHeader.vue'
import SidebarContent from '@/Layouts/CrmSidebar/SidebarContent.vue'
import SidebarFooter from '@/Layouts/CrmSidebar/SidebarFooter.vue'

onMounted(() => {
    window.addEventListener('resize', sidebarState.handleWindowResize)

    router.on('navigate', () => {
        if (window.innerWidth <= 1024) {
            sidebarState.isOpen = false
        }
    })
})
</script>

<template>
    <transition
        enter-active-class="transition"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-show="sidebarState.isOpen"
            @click="sidebarState.isOpen = false"
            class="fixed inset-0 z-30 bg-black/50 lg:hidden"
        ></div>
    </transition>

    <aside style="
            transition-property: width, transform;
            transition-duration: 150ms;
        " :class="[
            'fixed inset-y-0 z-50 bg-white dark:bg-surface-900 transition-all duration-200 flex flex-col border-r border-transparent dark:border-surface-700 shadow-[0_4px_16px_rgba(204,160,90,0.2)] dark:shadow-none',
            {
                'translate-x-0 w-[252px]':
                    sidebarState.isOpen,
                '-translate-x-full':
                    !sidebarState.isOpen,
            },
        ]"
    >
        <SidebarHeader />

        <div class="flex-1 flex flex-col overflow-hidden px-2">
            <SidebarContent />
        </div>

        <SidebarFooter />
    </aside>
</template>
