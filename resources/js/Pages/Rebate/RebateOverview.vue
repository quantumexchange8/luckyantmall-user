<script setup>
import Card from "primevue/card";
import {
    IconClockDollar,
    IconWorldDollar,
    IconDatabaseDollar,
    IconTrendingUp,
    IconTrendingDown,
} from "@tabler/icons-vue";
import {generalFormat} from "@/Composables/format.js";

defineProps({
    todayRebate: Number,
    tradeRebateTrend: Number,
    totalRebate: Number,
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
                        <span class="text-sm w-full text-surface-400 dark:text-surface-500">{{ $t('public.today_rebate') }}</span>
                        <div
                            v-if="todayRebate !== null"
                            class="flex gap-2 items-end"
                        >
                            <span class="text-xl font-medium">${{ formatAmount(todayRebate) }}</span>
                            <div
                                v-if="tradeRebateTrend !== 0"
                                :class="[
                                    'flex gap-1 items-center',
                                    {
                                        'text-success-500': tradeRebateTrend > 0,
                                        'text-red-500': tradeRebateTrend < 0,
                                    }
                                ]">
                                <IconTrendingUp
                                    v-if="tradeRebateTrend > 0"
                                    size="18"
                                    stroke-width="1.5"
                                />
                                <IconTrendingDown
                                    v-else-if="tradeRebateTrend < 0"
                                    size="18"
                                    stroke-width="1.5"
                                />
                                {{ formatAmount(tradeRebateTrend) }}%
                            </div>
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
                        <span class="text-sm w-full text-surface-400 dark:text-surface-500">{{ $t('public.total_rebate') }}</span>
                        <div
                            v-if="totalRebate !== null"
                            class="text-xl font-medium"
                        >
                            ${{ formatAmount(totalRebate) }}
                        </div>
                        <div v-else class="text-xl">
                            {{ $t('public.calculating') }}...
                        </div>
                    </div>
                    <div class="bg-violet-100 dark:bg-violet-900/30 rounded-md text-violet-500 p-2">
                        <IconWorldDollar size="28" stroke-width="1.5" />
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
