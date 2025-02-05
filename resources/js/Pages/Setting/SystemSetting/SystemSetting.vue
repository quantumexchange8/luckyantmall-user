<script setup>
import ContentLayout from "@/Layouts/ContentLayout.vue";
import Card from "primevue/card";
import ToggleSwitch from 'primevue/toggleswitch';
import {computed, ref, watch} from "vue";
import {isDark, toggleDarkMode} from '@/Composables'
import Select from "primevue/select";
import {useLangObserver} from "@/Composables/localeObserver.js";
import {loadLanguageAsync} from "laravel-vue-i18n";

defineProps({
    backRoute: String
});

const checked = ref(isDark);
const { locale } = useLangObserver();
const locales = ref([
    { name: 'English', code: 'en' },
    { name: '简体中文', code: 'cn' },
    { name: '簡體中文', code: 'tw' },
]);

const selectedLocale = ref(locales.value.find(l => l.code === locale.value) || locales.value[0]);

watch(selectedLocale, (val) => {
    changeLanguage(val.code);
})

const changeLanguage = async (langVal) => {
    try {
        await loadLanguageAsync(langVal); // Load translation
        await axios.get(`/locale/${langVal}`); // Sync with backend
        locale.value = langVal; // Manually update locale if needed
    } catch (error) {
        console.error('Error changing locale:', error);
    }
};
</script>

<template>
    <ContentLayout
        :title="$t('public.system_setting')"
        :backRoute="backRoute"
    >
        <Card class="w-full">
            <template #content>
                <div class="flex flex-col w-full self-stretch">
                    <div class="py-3 flex justify-between items-center">
                        <span class="text-sm font-medium">{{ $t('public.dark_mode') }}</span>
                        <ToggleSwitch
                            v-model="checked"
                            readonly
                            @click="() => { toggleDarkMode() }"
                        />
                    </div>
                    <div class="py-3 border-t border-gray-200 flex justify-between items-center">
                        <span class="text-sm font-medium w-full">{{ $t('public.language') }}</span>
                        <Select
                            v-model="selectedLocale"
                            optionLabel="name"
                            class="w-full"
                            :options="locales"
                        >
                            <template #value="slotProps">
                                <div v-if="slotProps.value" class="flex items-center">
                                    <div>{{ slotProps.value.name }}</div>
                                </div>
                                <span v-else>{{ slotProps.placeholder }}</span>
                            </template>
                            <template #option="slotProps">
                                <div class="max-w-[200px] truncate">{{ slotProps.option.name }}</div>
                            </template>
                        </Select>
                    </div>
                </div>
            </template>
        </Card>
    </ContentLayout>
</template>
