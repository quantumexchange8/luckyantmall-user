<script setup>
import {IconCreditCardPay, IconCreditCardRefund} from "@tabler/icons-vue";
import Card from "primevue/card";
import {generalFormat} from "@/Composables/format.js";

defineProps({
    totalDeposit: Number,
    totalWithdrawal: Number,
})

const {formatAmount} = generalFormat();
</script>

<template>
    <div class="flex flex-col md:flex-row gap-5 items-center self-stretch">
        <Card class="w-full">
            <template #content>
                <div class="flex gap-3 items-center">
                    <div class="flex flex-col w-full">
                        <span class="text-sm w-full text-surface-400 dark:text-surface-500">{{ $t('public.total_deposit') }}</span>
                        <div
                            v-if="totalDeposit !== null"
                            class="text-xl font-medium"
                        >
                            ¥{{ formatAmount(totalDeposit) }}
                        </div>
                        <div v-else class="text-xl">
                            {{ $t('public.calculating') }}...
                        </div>
                    </div>
                    <div class="bg-success-100 dark:bg-success-900/30 rounded-md text-success-500 p-2">
                        <IconCreditCardRefund size="28" stroke-width="1.5" />
                    </div>
                </div>
            </template>
        </Card>
        <Card class="w-full">
            <template #content>
                <div class="flex gap-3 items-center">
                    <div class="flex flex-col w-full">
                        <span class="text-sm w-full text-surface-400 dark:text-surface-500">{{ $t('public.total_withdrawal') }}</span>
                        <div
                            v-if="totalWithdrawal !== null"
                            class="text-xl font-medium"
                        >
                            ¥{{ formatAmount(totalWithdrawal) }}
                        </div>
                        <div v-else class="text-xl">
                            {{ $t('public.calculating') }}...
                        </div>
                    </div>
                    <div class="bg-red-100 dark:bg-red-900/30 rounded-md text-red-500 p-2">
                        <IconCreditCardPay size="28" stroke-width="1.5" />
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>
