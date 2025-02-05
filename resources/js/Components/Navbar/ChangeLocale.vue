<script setup>
import Button from "primevue/button";
import {IconLanguage} from "@tabler/icons-vue";
import {ref} from "vue";
import Menu from 'primevue/menu';
import {loadLanguageAsync} from "laravel-vue-i18n";

const menu = ref();
const locales = ref([
    {
        label: 'English',
        command: () => {
            changeLanguage('en');
        }
    },
    {
        label: '简体中文',
        command: () => {
            changeLanguage('cn')
        }
    },
    {
        label: '簡體中文',
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
</script>

<template>
    <Button
        severity="secondary"
        outlined
        aria-label="Language"
        size="small"
        @click="toggle"
    >
        <template #icon>
            <IconLanguage size="16" />
        </template>
    </Button>

    <Menu
        ref="menu"
        id="overlay_menu"
        :model="locales"
        :popup="true"
    />
</template>
