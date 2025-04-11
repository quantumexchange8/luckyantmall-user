<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DashboardOverview from "@/Pages/Dashboard/DashboardOverview.vue";
import {generalFormat} from "@/Composables/format.js";
import {computed} from "vue";
import ProfitOverview from "@/Pages/Dashboard/Profit/ProfitOverview.vue";
import RebateOverview from "@/Pages/Dashboard/Rebate/RebateOverview.vue";

const props = defineProps({
    totalDeposit: Number,
    totalWithdrawal: Number,
    capitalFund: Number,
    tradeProfit: Number,
    totalRebate: Number,
});

const {formatAmount} = generalFormat();

const formattedBalance = computed(() => {
    const amount = formatAmount(props.capitalFund);
    const parts = amount.split(".");

    return parts.length > 1
        ? `$${parts[0]}.<span class='text-lg text-surface-500 dark:text-surface-400'>${parts[1]}</span>`
        : amount;
});
</script>

<template>
    <AuthenticatedLayout :title="$t('public.dashboard')">
        <div class="flex flex-col gap-5 items-center self-stretch">
            <div class="flex flex-col gap-3 items-start w-full">
                <div class="text-2xl font-semibold w-full dark:text-white">
                    {{ $t('public.welcome_back') }}, <span class="text-surface-700 dark:text-surface-400">{{ $page.props.auth.user.username }}</span>
                </div>
                <DashboardOverview
                    :totalDeposit="totalDeposit"
                    :totalWithdrawal="totalWithdrawal"
                />
            </div>
            <div class="flex flex-col gap-3 items-start w-full">
                <div class="flex flex-col">
                    <span class="font-medium dark:text-white">{{ $t('public.capital_fund') }}</span>
                    <span class="text-4xl font-semibold dark:text-white" v-html="formattedBalance"></span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-center self-stretch">
                    <ProfitOverview
                        :tradeProfit="tradeProfit"
                    />
                    <RebateOverview
                        :totalRebate="totalRebate"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
