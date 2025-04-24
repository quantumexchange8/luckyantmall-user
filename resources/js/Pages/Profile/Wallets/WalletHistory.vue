<script setup>
import Card from "primevue/card";
import DataView from 'primevue/dataview';
import Tag from 'primevue/tag';
import ScrollPanel from 'primevue/scrollpanel';
import Skeleton from 'primevue/skeleton';
import {ref, watch, watchEffect} from "vue";
import dayjs from "dayjs";
import Drawer from 'primevue/drawer';
import WalletHistoryFilter from "@/Pages/Profile/Wallets/WalletHistoryFilter.vue";
import {generalFormat} from "@/Composables/format.js";
import EmptyData from "@/Components/EmptyData.vue";
import {usePage} from "@inertiajs/vue3";

const props = defineProps({
    wallet: Object,
    transactionCounts: Number,
    earningCounts: Number,
    types: {
        type: [Array, Object]
    },
})

const histories = ref();
const isLoading = ref(false);
const visible = ref(false);
const drawerData = ref();
const {formatAmount} = generalFormat();

const selectedDate = ref([]);
const selectedTypes = ref([]);
const selectedStatus = ref([]);

const getResults = async () => {
    isLoading.value = true;

    const params = {
        walletId: props.wallet.id,
    };

    if (selectedDate.value.length === 2) {
        params.start_date = selectedDate.value[0];
        params.end_date = selectedDate.value[1];
    }

    if (selectedTypes.value.length) {
        params.types = selectedTypes.value;
    }

    if (selectedStatus.value.length) {
        params.statuses = selectedStatus.value;
    }

    try {
        const response = await axios.get(`/profile/${props.wallet.type}/getWalletHistory`, { params });
        histories.value = response.data.walletHistory;
    } catch (error) {
        console.error('Error fetching histories:', error);
    } finally {
        isLoading.value = false;
    }
};

getResults();

const getSeverity = (historyStatus) => {
    switch (historyStatus) {
        case 'success':
            return 'success';

        case 'fail':
            return 'danger';

        case 'processing':
            return 'info';

        default:
            return null;
    }
};

const openDrawer = (data) => {
    visible.value = true;
    drawerData.value = data;
}

const handleFilters = (filters) => {
    selectedDate.value = filters.selectedDate || [];
    selectedTypes.value = filters.selectedTypes || [];
    selectedStatus.value = filters.selectedStatus || [];
};

watch(
    [selectedDate, selectedTypes, selectedStatus],
    () => {
        if (
            selectedDate.value.length === 2 &&
            selectedDate.value[0] &&
            selectedDate.value[1]
        ) {
            getResults();
        }
    },
    { immediate: true }
);

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getResults();
    }
});
</script>

<template>
    <Card
        class="w-full"
    >
        <template #content>
            <div class="flex flex-col gap-3 md:gap-5 items-center self-stretch">
                <span class="w-full text-left">{{ $t('public.history') }}</span>
                <WalletHistoryFilter
                    :types="types"
                    @update:filterTypes="handleFilters"
                />

                <div v-if="isLoading" class="w-full">
                    <ScrollPanel style="width: 100%; height: calc(84vh - 300px);">
                        <div class="flex flex-col">
                            <div v-for="(i, index) in Number(transactionCounts + earningCounts)" :key="i">
                                <div
                                    class="flex items-center pr-3 py-3 gap-4"
                                    :class="{
                                            'border-t border-surface-200 dark:border-surface-700': index !== 0
                                        }"
                                >
                                    <div class="flex flex-row justify-between items-center flex-1">
                                        <div class="flex flex-col justify-between items-start">
                                            <div class="flex gap-1 items-center">
                                                <Skeleton width="3rem" height="1.25rem" class="mb-0.5" />
                                                <Skeleton width="3rem" height="1.25rem" class="mb-0.5" />
                                            </div>
                                            <Skeleton width="9rem" height="0.75rem" class="mt-1.5 mb-1" />
                                        </div>
                                        <div class="flex flex-col items-end gap-2">
                                            <Skeleton width="4.25rem" height="1.75rem" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </ScrollPanel>
                </div>

                <DataView
                    v-else
                    :value="histories"
                    class="w-full"
                >
                    <!-- Empty State -->
                    <template #empty>
                        <div v-if="!isLoading" class="flex justify-center items-center">
                            <EmptyData
                                :title="$t('public.no_history_found')"
                            />
                        </div>
                    </template>

                    <template #list="slotProps">
                        <ScrollPanel style="width: 100%; max-height: calc(84vh - 300px);">
                            <div class="flex flex-col">
                                <div v-for="(item, index) in slotProps.items" :key="index">
                                    <div
                                        class="flex items-center pr-3 py-3 gap-4"
                                        :class="{
                                            'border-t border-surface-200 dark:border-surface-700': index !== 0
                                        }"
                                        @click="openDrawer(item)"
                                    >
                                        <div class="flex flex-row justify-between items-center flex-1 gap-6">
                                            <div class="flex flex-col justify-between items-start">
                                                <div class="flex gap-1 items-center">
                                                    <span class="font-medium text-sm">{{ $t(`public.${item.type}`) }}</span>
                                                    <Tag
                                                        v-if="item.status !== null"
                                                        :value="$t(`public.${item.status}`)"
                                                        :severity="getSeverity(item.status)"
                                                    />
                                                </div>
                                                <span class="font-medium text-surface-500 dark:text-surface-400 text-xs">{{ dayjs(item.created_at).format('YYYY/MM/DD HH:mm:ss') }}</span>
                                            </div>
                                            <div v-if="item.type !== 'payment' && item.type !== 'withdrawal'" class="flex flex-col items-end gap-1">
                                                <span class="text-lg md:text-xl font-semibold text-primary">¥{{ formatAmount(item.amount) }}</span>
                                            </div>
                                            <div v-else class="flex flex-col items-end gap-1">
                                                <span class="text-lg md:text-xl font-semibold">- ¥{{ formatAmount(item.amount) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </ScrollPanel>
                    </template>
                </DataView>
            </div>
        </template>
    </Card>

    <Drawer
        v-model:visible="visible"
        :header="$t('public.details')"
        class="w-full md:max-w-[360px] pb-24 !h-3/5"
        position="bottom"
    >
        <div class="flex flex-col gap-3 self-stretch">
            <div class="flex justify-between items-center">
                <span class="text-xl font-semibold">¥{{ formatAmount(drawerData.amount) }}</span>
                <Tag
                    v-if="drawerData.status !== null"
                    :value="$t(`public.${drawerData.status}`)"
                    :severity="getSeverity(drawerData.status)"
                />
            </div>
            <div class="flex flex-col gap-3 pt-3 items-start self-stretch border-t dark:border-surface-700">
                <div class="flex justify-between items-start w-full text-sm">
                    <div class="text-surface-700 dark:text-surface-300 w-28">
                        {{ $t('public.type') }}
                    </div>
                    <div class="text-right font-bold w-36">{{ $t(`public.${drawerData.type}`) }}</div>
                </div>
                <div v-if="drawerData.transaction_number" class="flex justify-between items-start w-full text-sm">
                    <div class="text-surface-700 dark:text-surface-300 w-28">
                        {{ $t('public.tnx_no') }}
                    </div>
                    <div class="text-right font-bold w-36">
                        {{ drawerData.transaction_number }}
                    </div>
                </div>
                <div class="flex justify-between items-start w-full text-sm">
                    <div class="text-surface-700 dark:text-surface-300 w-28">
                        {{ $t('public.date') }}
                    </div>
                    <div class="text-right font-bold w-36">
                        {{ dayjs(drawerData.status === 'processing' ? drawerData.created_at : drawerData.approval_at).format('YYYY/MM/DD HH:mm:ss') }}
                    </div>
                </div>
                <div class="flex justify-between items-start w-full text-sm">
                    <div class="text-surface-700 dark:text-surface-300 w-28">
                        {{ $t('public.remarks') }}
                    </div>
                    <div class="text-right font-bold w-36">
                        {{ drawerData.remarks ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
    </Drawer>
</template>
