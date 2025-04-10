<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TradeOverview from "@/Pages/Trade/TradeOverview.vue";
import {ref, watch} from "vue";
import TradeHistoryTable from "@/Pages/Trade/TradeHistoryTable.vue";
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';

const todayProfit = ref(null);
const totalProfit = ref(null);
const totalTradeLot = ref(null);

const tabs = ref([
    {
        type: 'all_trades',
        value: '0',
    },
    {
        type: 'buy',
        value: '1',
    },
    {
        type: 'sell',
        value: '2',
    },
]);

const selectedType = ref('all_trades');
const activeIndex = ref('0');

// Watch for changes in selectedType and update the activeIndex accordingly
watch(activeIndex, (newIndex) => {
    const activeTab = tabs.value.find(tab => tab.value === newIndex);
    if (activeTab) {
        selectedType.value = activeTab.type;
    }
});

const handleUpdateTotals = (data) => {
    todayProfit.value = data.todayProfit;
    totalProfit.value = data.totalProfit;
    totalTradeLot.value = data.totalTradeLot;
};
</script>

<template>
    <AuthenticatedLayout :title="$t('public.trade_history')">
        <div class="flex flex-col gap-5 items-center self-stretch">
            <TradeOverview
                :todayProfit="todayProfit"
                :totalProfit="totalProfit"
                :totalTradeLot="totalTradeLot"
            />

            <Tabs v-model:value="activeIndex" class="w-full">
                <TabList>
                    <Tab
                        v-for="tab in tabs"
                        :key="tab.type"
                        :value="tab.value"
                    >
                        {{ $t(`public.${tab.type}`) }}
                    </Tab>
                </TabList>
            </Tabs>

            <TradeHistoryTable
                :selectedType="selectedType"
                @update-totals="handleUpdateTotals"
            />
        </div>
    </AuthenticatedLayout>
</template>
