<script setup>
import Card from "primevue/card";
import DataView from 'primevue/dataview';
import Tag from 'primevue/tag';
import ScrollPanel from 'primevue/scrollpanel';
import Skeleton from 'primevue/skeleton';
import {ref} from "vue";
import dayjs from "dayjs";
import Drawer from 'primevue/drawer';

const props = defineProps({
    wallet: Object,
    transactionCounts: Number,
})

const histories = ref();
const isLoading = ref(false);
const visible = ref(false);
const drawerData = ref();

const getResults = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(`/profile/${props.wallet.type}/getWalletHistory?walletId=${props.wallet.id}`);
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
</script>

<template>
    <Card
        class="w-full"
    >
        <template #content>
            <div class="flex flex-col gap-3 md:gap-5 items-center self-stretch">
                <span class="w-full text-left">{{ $t('public.history') }}</span>

                <div v-if="isLoading" class="w-full">
                    <ScrollPanel style="width: 100%; height: calc(84vh - 300px);">
                        <div class="flex flex-col">
                            <div v-for="(i, index) in transactionCounts" :key="i">
                                <div
                                    class="flex items-center pr-3 py-3 gap-4"
                                    :class="{
                                            'border-t border-surface-200 dark:border-surface-700': index !== 0
                                        }"
                                >
                                    <div class="flex flex-row justify-between items-center flex-1">
                                        <div class="flex flex-col justify-between items-start">
                                            <Skeleton width="8rem" height="1rem" />
                                            <Skeleton width="6rem" height="1rem" class="mt-3" />
                                            <Skeleton width="9rem" height="0.75rem" class="mt-1.5 mb-1" />
                                        </div>
                                        <div class="flex flex-col items-end gap-2">
                                            <Skeleton width="4.25rem" height="1.75rem" />
                                            <Skeleton width="3rem" height="1.25rem" />
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
                                                <span class="font-medium text-sm">{{ $t(`public.${item.transaction_type}`) }}</span>
                                                <span class="font-medium text-surface-500 dark:text-surface-400 text-xs">{{ dayjs(item.approval_at).format('YYYY/MM/DD HH:mm:ss') }}</span>
                                            </div>
                                            <div class="flex flex-col items-end gap-1">
                                                <span v-if="item.to_wallet" class="text-lg md:text-xl font-semibold">{{ item.to_wallet.currency_symbol }}{{ item.amount }}</span>
                                                <span v-else class="text-lg md:text-xl font-semibold">{{ item.from_wallet.currency_symbol }}{{ item.amount }}</span>
                                                <Tag :value="$t(`public.${item.status}`)" :severity="getSeverity(item.status)"></Tag>
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
        class="w-full md:max-w-[360px] pb-24"
        position="bottom"
    >
        <div class="flex flex-col gap-3 self-stretch">
            <div class="flex justify-between items-center">
                <span class="text-xl font-semibold">{{ drawerData.to_wallet.currency_symbol }}{{ drawerData.amount }}</span>
                <Tag :value="$t(`public.${drawerData.status}`)" :severity="getSeverity(drawerData.status)"></Tag>
            </div>
            <div class="flex flex-col gap-3 pt-3 items-start self-stretch border-t dark:border-surface-700">
                <div class="flex justify-between items-start w-full text-sm">
                    <div class="text-surface-700 dark:text-surface-300 w-28">
                        {{ $t('public.transaction_type') }}
                    </div>
                    <div class="text-right font-bold w-36">{{ $t(`public.${drawerData.transaction_type}`) }}</div>
                </div>
                <div class="flex justify-between items-start w-full text-sm">
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
