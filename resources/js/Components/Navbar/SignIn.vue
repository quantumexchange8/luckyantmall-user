<script setup>
import Menu from 'primevue/menu';
import {ref} from "vue";
import {
    IconUserCircle
} from "@tabler/icons-vue";
import {router} from "@inertiajs/vue3";

const menu = ref();
const items = ref([
    {
        label: 'Login',
        command: () => {
            router.get(route('login'))
        }
    },
    {
        label: 'Register',
        command: () => {
            router.get(route('register'))
        }
    }
]);

const toggle = (event) => {
    menu.value.toggle(event);
};
</script>

<template>
    <div
        @click="toggle"
        aria-haspopup="true"
        aria-controls="overlay_menu"
        class="flex gap-1 py-2 items-center select-none cursor-pointer text-surface-600 dark:text-surface-400 hover:text-surface-600 dark:hover:text-white"
    >
        <IconUserCircle size="20" stroke-width="1.5" />
        <span class="text-sm text-nowrap">Sign In</span>
    </div>
    <Menu
        ref="menu"
        id="overlay_menu"
        :model="items"
        :popup="true"
    />
</template>
