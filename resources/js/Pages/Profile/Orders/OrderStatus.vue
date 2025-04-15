<script setup>
import {
    IconProgressCheck,
    IconTruckDelivery,
    IconCircleCheck,
    IconTruckOff
} from "@tabler/icons-vue";

const orderStatus = [
    {
        key: 'processing',
        icon: IconProgressCheck,
        bgColor: 'bg-primary-100 dark:bg-primary-900/30',
        textColor: 'text-primary',
    },
    {
        key: 'shipping',
        icon: IconTruckDelivery,
        bgColor: 'bg-violet-100 dark:bg-violet-900/30',
        textColor: 'text-violet-500',
    },
    {
        key: 'completed',
        icon: IconCircleCheck,
        bgColor: 'bg-success-100 dark:bg-success-900/30',
        textColor: 'text-success-500',
    },
    {
        key: 'cancelled',
        icon: IconTruckOff,
        bgColor: 'bg-orange-100 dark:bg-orange-900/30',
        textColor: 'text-orange-500',
    },
];
</script>

<template>
    <div class="flex justify-evenly items-center self-stretch w-full">
        <div
            v-for="status in orderStatus"
            :key="status.key"
            class="flex flex-col items-center"
        >
            <div
                class="flex justify-center items-center w-8 h-8 md:w-12 md:h-12 rounded-full p-1.5 md:p-1"
                :class="[status.bgColor, status.textColor]"
            >
                <component :is="status.icon" size="32" stroke-width="1.5" />
            </div>
            <span class="text-xs dark:text-white">{{ $t(`public.${status.key}`) }}</span>
        </div>
    </div>
</template>
