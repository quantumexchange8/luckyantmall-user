<script setup>
import {isDark, sidebarState, toggleDarkMode} from '@/Composables'
import {
    IconWorld,
    IconLogout,
    IconMenu2,
    IconMoon,
    IconSun
} from '@tabler/icons-vue';
// import ProfilePhoto from "@/Components/ProfilePhoto.vue";
import {Link, router, usePage} from "@inertiajs/vue3";
import Menu from 'primevue/menu';
import Button from "primevue/button";
import {ref} from "vue";
import {loadLanguageAsync} from "laravel-vue-i18n";
import ChangeLocale from "@/Components/Navbar/ChangeLocale.vue";

defineProps({
    title: String
})

const menu = ref();
const locales = ref([
    {
        label: 'English',
        command: () => {
            changeLanguage('en');
        }
    },
    {
        label: '中文',
        command: () => {
            changeLanguage('tw')
        }
    }
]);

const toggle = (event) => {
    menu.value.toggle(event);
};

const changeLanguage = async (langVal) => {
    try {
        await loadLanguageAsync(langVal);
        await axios.get(`/locale/${langVal}`);
    } catch (error) {
        console.error('Error changing locale:', error);
    }
};

const handleLogOut = () => {
    router.post(route('logout'))
}
</script>

<template>
    <nav
        aria-label="secondary"
        class="sticky top-0 z-30 py-2.5 px-3 md:px-5 bg-surface-50 dark:bg-surface-950 flex items-center gap-3 transition-all duration-200"
    >
        <Button
            type="button"
            severity="secondary"
            rounded
            text
            class="!p-3 min-w-11"
            @click="sidebarState.isOpen = !sidebarState.isOpen"
        >
            <IconMenu2 size="20" stroke-width="1.5" />
        </Button>
        <div
            class="font-semibold text-gray-700 dark:text-gray-300 w-full"
        >
            {{ title }}
        </div>
        <div class="flex items-center gap-2 self-stretch">
            <Button
                severity="secondary"
                outlined
                aria-label="Mode"
                size="small"
                @click="() => { toggleDarkMode() }"
            >
                <template #icon>
                    <IconSun v-if="!isDark" size="16" />
                    <IconMoon v-if="isDark" size="16" />
                </template>
            </Button>

            <ChangeLocale />

            <Button
                external
                severity="secondary"
                outlined
                aria-label="logout"
                @click="handleLogOut"
            >
                <IconLogout size="16" stroke-width="1.5" />
            </Button>
        </div>
    </nav>

    <Menu
        ref="menu"
        id="overlay_menu"
        :model="locales"
        :popup="true"
        class="w-32"
    />
</template>
