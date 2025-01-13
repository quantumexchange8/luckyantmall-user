<script setup>
import InputText from "primevue/inputtext";
import Checkbox from "primevue/checkbox";
import {ref, watch} from "vue";
import {useLangObserver} from "@/Composables/localeObserver.js";
import debounce from "lodash/debounce.js";
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import {
    IconSearch
} from "@tabler/icons-vue";

const props = defineProps({
    categories: Object,
    passInCategories: String,
})

const emit = defineEmits(['update:search', 'update:selectedCategories']);
const search = ref('');
const selectedCategories = ref();
const {locale} = useLangObserver();

watch(search, debounce((val) => {
    emit('update:search', val);
}, 300));

watch(() => props.passInCategories, (val) => {
    selectedCategories.value = [val];
})

watch(selectedCategories, (val) => {
    emit('update:selectedCategories', val);
})
</script>

<template>
    <div class="flex flex-col gap-5">
        <div class="flex flex-col items-start gap-1 self-stretch">
            <span class="font-semibold text-sm text-surface-700 dark:text-surface-300">{{ $t('public.keyword') }}</span>
            <IconField class="w-full">
                <InputIcon>
                    <IconSearch size="16" stroke-width="1.5" />
                </InputIcon>
                <InputText
                    id="name"
                    type="text"
                    class="block w-full"
                    v-model="search"
                    :placeholder="$t('public.search_keyword')"
                />
            </IconField>
        </div>

        <div class="flex flex-col items-start gap-1 self-stretch">
            <span class="font-semibold text-sm text-surface-700 dark:text-surface-300">{{ $t('public.category') }}</span>
            <div
                v-for="category in categories"
                :key="category.id"
                class="flex items-center my-0.5"
            >
                <Checkbox
                    v-model="selectedCategories"
                    :inputId="category.slug"
                    :value="category.slug"
                />
                <label :for="category.slug" class="ml-2 text-sm text-surface-600 dark:text-surface-400">{{ category.name[locale] }}</label>
            </div>
        </div>
    </div>
</template>
