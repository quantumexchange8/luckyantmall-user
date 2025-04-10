<script setup>
import Card from "primevue/card";
import {
    IconClockDollar,
    IconCurrencyDollar,
    IconDatabaseDollar,
} from "@tabler/icons-vue";
import {generalFormat} from "@/Composables/format.js";

defineProps({
    todayProfit: Number,
    totalProfit: Number,
    totalTradeLot: Number,
})

const {formatAmount} = generalFormat();
</script>

<template>
    <div class="flex flex-col md:flex-row gap-5 items-center w-full self-stretch">
        <Card class="w-full">
            <template #content>
                <div class="flex gap-3 items-center">
                    <div class="flex flex-col w-full">
                        <span class="text-sm w-full text-surface-400 dark:text-surface-500">{{ $t('public.today_profit') }}</span>
                        <div
                            v-if="todayProfit !== null"
                            :class="[
                                'text-xl font-medium',
                                {
                                    'text-success-500': todayProfit > 0,
                                    'text-red-500': todayProfit < 0,
                                    'text-surface-950 dark:text-white': todayProfit === 0,
                                }
                            ]"
                        >
                            ${{ formatAmount(todayProfit) }}
                        </div>
                        <div v-else class="text-xl">
                            {{ $t('public.calculating') }}...
                        </div>
                    </div>
                    <div class="bg-primary-100 dark:bg-primary-900/30 rounded-md text-primary p-2">
                        <IconClockDollar size="28" stroke-width="1.5" />
                    </div>
                </div>
            </template>
        </Card>
        <Card class="w-full">
            <template #content>
                <div class="flex gap-3 items-center">
                    <div class="flex flex-col w-full">
                        <span class="text-sm w-full text-surface-400 dark:text-surface-500">{{ $t('public.total_profit') }}</span>
                        <div
                            v-if="totalProfit !== null"
                            class="text-xl font-medium"
                        >
                            ${{ formatAmount(totalProfit) }}
                        </div>
                        <div v-else class="text-xl">
                            {{ $t('public.calculating') }}...
                        </div>
                    </div>
                    <div class="bg-orange-100 dark:bg-orange-900/30 rounded-md text-orange-500 p-2">
                        <IconCurrencyDollar size="28" stroke-width="1.5" />
                    </div>
                </div>
            </template>
        </Card>
        <Card class="w-full">
            <template #content>
                <div class="flex gap-3 items-center">
                    <div class="flex flex-col w-full">
                        <span class="text-sm w-full text-surface-400 dark:text-surface-500">{{ $t('public.trade_lot') }}</span>
                        <div
                            v-if="totalTradeLot !== null"
                            class="text-xl font-medium"
                        >
                            {{ formatAmount(totalTradeLot) }}
                        </div>
                        <div v-else class="text-xl">
                            {{ $t('public.calculating') }}...
                        </div>
                    </div>
                    <div class="bg-cyan-100 dark:bg-cyan-900/30 rounded-md text-cyan-500 p-2">
                        <IconDatabaseDollar size="28" stroke-width="1.5" />
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>
