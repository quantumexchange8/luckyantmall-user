<script setup>
import Button from "primevue/button";
import {
    IconCircleChevronRight,
} from "@tabler/icons-vue";
import {ref} from "vue";
import {Link} from "@inertiajs/vue3";

const settingOptions = ref([
    {
        label: 'profile',
        route: 'profile',
    },
    {
        label: 'delivery_address',
        route: 'delivery_address',
    }
])
</script>

<template>
    <div class="flex flex-col gap-3 w-full">
        <div class="font-semibold">
            {{ $t('public.setting') }}
        </div>

        <Link
            v-for="setting in settingOptions"
            class="flex justify-between items-center rounded-md border border-surface-300 dark:border-surface-700 px-3 py-2 hover:text-primary"
            :href="route(setting.route)"
        >
            <div class="text-sm font-medium">
                {{ $t(`public.${setting.label}`) }}
            </div>
            <Button
                severity="secondary"
                size="small"
                rounded
                text
                as="a"
                :href="route(setting.route)"
            >
                <IconCircleChevronRight size="20" stroke-width="1.5" />
            </Button>
        </Link>
    </div>
</template>
