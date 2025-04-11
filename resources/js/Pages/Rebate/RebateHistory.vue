<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {ref, watch} from "vue";
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import RebateHistoryTable from "@/Pages/Rebate/RebateHistoryTable.vue";
import RebateOverview from "@/Pages/Rebate/RebateOverview.vue";

const todayRebate = ref(null);
const tradeRebateTrend = ref(null);
const totalRebate = ref(null);
const totalTradeLot = ref(null);

const tabs = ref([
    {
        type: 'personal',
        value: '0',
    },
    {
        type: 'referral',
        value: '1',
    },
]);

const selectedType = ref('personal');
const activeIndex = ref('0');

// Watch for changes in selectedType and update the activeIndex accordingly
watch(activeIndex, (newIndex) => {
    const activeTab = tabs.value.find(tab => tab.value === newIndex);
    if (activeTab) {
        selectedType.value = activeTab.type;
    }
});

const handleUpdateTotals = (data) => {
    todayRebate.value = data.todayRebate;
    tradeRebateTrend.value = data.tradeRebateTrend;
    totalRebate.value = data.totalRebate;
    totalTradeLot.value = data.totalTradeLot;
};
</script>

<template>
    <AuthenticatedLayout :title="$t('public.rebate_history')">
        <div class="flex flex-col gap-5 items-center self-stretch">
            <RebateOverview
                :todayRebate="todayRebate"
                :tradeRebateTrend="tradeRebateTrend"
                :totalRebate="totalRebate"
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

            <RebateHistoryTable
                :selectedType="selectedType"
                @update-totals="handleUpdateTotals"
            />
        </div>
    </AuthenticatedLayout>
</template>
