<script setup>
import Button from "primevue/button";
import {
    IconCircleChevronRight,
} from "@tabler/icons-vue";
import {computed, ref} from "vue";
import {Link} from "@inertiajs/vue3";
import {useWindowSize} from "@vueuse/core";

const settingOptions = ref([
    {
        label: 'delivery_address',
        route: 'delivery_address',
        mobileOnly: false,
    },
    {
        label: 'payment_account',
        route: 'setting.payment_account',
        mobileOnly: false,
    },
    {
        label: 'system_setting',
        route: 'setting.system_setting',
        mobileOnly: true // Custom flag for filtering
    }
])

const { width } = useWindowSize() // Get screen width dynamically

const filteredSettingOptions = computed(() => {
    return settingOptions.value.filter(option => {
        return !option.mobileOnly || width.value < 768
    })
})
</script>

<template>
    <div class="flex flex-col gap-3 w-full">
        <div class="font-semibold">
            {{ $t('public.setting') }}
        </div>

        <Link
            v-for="setting in filteredSettingOptions"
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
