<script setup>
import Card from "primevue/card"
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import {onMounted, ref, watch, watchEffect} from "vue";
import {FilterMatchMode} from "@primevue/core/api";
import {usePage} from "@inertiajs/vue3";
import Button from "primevue/button";
import dayjs from "dayjs";
import InputText from "primevue/inputtext";
import DatePicker from "primevue/datepicker"
import debounce from "lodash/debounce.js";
import {
    IconCircleXFilled,
    IconSearch,
    IconXboxX,
    IconCloudDownload
} from "@tabler/icons-vue";
import {generalFormat} from "@/Composables/format.js";
import EmptyData from "@/Components/EmptyData.vue";
import ProgressSpinner from "primevue/progressspinner";

const props = defineProps({
    selectedType: String,
});

const isLoading = ref(false);
const dt = ref(null);
const histories = ref([]);
const {formatAmount} = generalFormat();
const totalRecords = ref(0);
const first = ref(0);
const todayRebate = ref();
const tradeRebateTrend = ref();
const totalRebate = ref();
const totalTradeLot = ref();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    type: { value: props.selectedType, matchMode: FilterMatchMode.EQUALS },
    start_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    end_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    symbols: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const lazyParams = ref({});

const loadLazyData = (event) => {
    isLoading.value = true;

    lazyParams.value = { ...lazyParams.value, first: event?.first || first.value };
    lazyParams.value.filters = filters.value;
    try {
        setTimeout(async () => {
            const params = {
                page: JSON.stringify(event?.page + 1),
                sortField: event?.sortField,
                sortOrder: event?.sortOrder,
                include: [],
                lazyEvent: JSON.stringify(lazyParams.value)
            };

            const url = route('rebate_history.getRebateHistoriesData', params);
            const response = await fetch(url);
            const results = await response.json();

            histories.value = results?.data?.data;
            totalRecords.value = results?.data?.total;
            todayRebate.value = results?.todayRebate;
            tradeRebateTrend.value = results?.tradeRebateTrend;
            totalRebate.value = results?.totalRebate;
            totalTradeLot.value = results?.totalTradeLot;
            isLoading.value = false;
        }, 100);
    }  catch (e) {
        histories.value = [];
        totalRecords.value = 0;
        isLoading.value = false;
    }
};
const onPage = (event) => {
    lazyParams.value = event;
    loadLazyData(event);
};
const onSort = (event) => {
    lazyParams.value = event;
    loadLazyData(event);
};
const onFilter = (event) => {
    lazyParams.value.filters = filters.value ;
    loadLazyData(event);
};

const selectedDate = ref([]);

const clearDate = () => {
    selectedDate.value = [];
}

watch(selectedDate, (newDateRange) => {
    if (Array.isArray(newDateRange)) {
        const [startDate, endDate] = newDateRange;
        filters.value['start_date'].value = startDate;
        filters.value['end_date'].value = endDate;

        if (startDate !== null && endDate !== null) {
            loadLazyData();
        }
    } else {
        console.warn('Invalid date range format:', newDateRange);
    }
})

onMounted(() => {
    lazyParams.value = {
        first: dt.value.first,
        rows: dt.value.rows,
        sortField: null,
        sortOrder: null,
        filters: filters.value
    };

    loadLazyData();
});

watch(
    filters.value['global'],
    debounce(() => {
        loadLazyData();
    }, 300)
);

watch([filters.value['type'], filters.value['symbols']], () => {
    loadLazyData()
});

watch(() => props.selectedType, (newType) => {
    filters.value['type'].value = newType;
    loadLazyData();
})

const clearAll = () => {
    filters.value['global'].value = null;
    filters.value['symbols'].value = null;
    filters.value['start_date'].value = null;
    filters.value['end_date'].value = null;

    selectedDate.value = [];
};

const clearFilterGlobal = () => {
    filters.value['global'].value = null;
}

watchEffect(() => {
    if (usePage().props.toast !== null) {
        loadLazyData();
    }
});

const emit = defineEmits(['update-totals']);

watch([todayRebate, tradeRebateTrend, totalRebate, totalTradeLot], () => {
    emit('update-totals', {
        todayRebate: todayRebate.value,
        tradeRebateTrend: tradeRebateTrend.value,
        totalRebate: totalRebate.value,
        totalTradeLot: totalTradeLot.value,
    });
});

const exportStatus = ref(false);

const exportReport = () => {
    exportStatus.value = true;
    isLoading.value = true;

    lazyParams.value = { ...lazyParams.value, first: event?.first || first.value };

    const params = {
        page: JSON.stringify(event?.page + 1),
        sortField: event?.sortField,
        sortOrder: event?.sortOrder,
        include: [],
        lazyEvent: JSON.stringify(lazyParams.value),
        exportStatus: true,
    };

    const url = route('rebate_history.getRebateHistoriesData', params);

    try {
        window.location.href = url;
    } catch (e) {
        console.error('Error occurred during export:', e);
    } finally {
        isLoading.value = false;
        exportStatus.value = false;
    }
};
</script>

<template>
    <Card class="w-full">
        <template #content>
            <div
                class="w-full"
            >
                <DataTable
                    :value="histories"
                    :rowsPerPageOptions="[10, 20, 50, 100]"
                    lazy
                    paginator
                    removableSort
                    paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                    :currentPageReportTemplate="$t('public.paginator_caption')"
                    :first="first"
                    :rows="10"
                    v-model:filters="filters"
                    ref="dt"
                    dataKey="id"
                    :totalRecords="totalRecords"
                    :loading="isLoading"
                    @page="onPage($event)"
                    @sort="onSort($event)"
                    @filter="onFilter($event)"
                    :globalFilterFields="['meta_login', 'trader.username']"
                >
                    <template #header>
                        <div class="flex flex-col md:flex-row gap-3 items-center self-stretch md:pb-5">
                            <div class="relative w-full md:w-60">
                                <div class="absolute top-2/4 -mt-[9px] left-4 text-surface-400">
                                    <IconSearch size="20" stroke-width="1.5"/>
                                </div>
                                <InputText
                                    v-model="filters['global'].value"
                                    :placeholder="$t('public.search_keyword')"
                                    class="font-normal pl-12 w-full md:w-60"
                                />
                                <div
                                    v-if="filters['global'].value !== null"
                                    class="absolute top-2/4 -mt-2 right-4 text-surface-300 hover:text-surface-400 dark:text-surface-500 dark:hover:text-surface-400 select-none cursor-pointer"
                                    @click="clearFilterGlobal"
                                >
                                    <IconCircleXFilled size="16"/>
                                </div>
                            </div>
                            <div class="flex justify-between items-center w-full gap-3">
                                <div class="relative w-60">
                                    <DatePicker
                                        v-model="selectedDate"
                                        dateFormat="yy-mm-dd"
                                        class="w-full"
                                        selectionMode="range"
                                        placeholder="YYYY-MM-DD - YYYY-MM-DD"
                                    />
                                    <div
                                        v-if="selectedDate && selectedDate.length > 0"
                                        class="absolute top-2/4 -mt-1.5 right-2 text-surface-400 select-none cursor-pointer bg-transparent"
                                        @click="clearDate"
                                    >
                                        <IconXboxX size="12" stoke-width="1.5" />
                                    </div>
                                </div>
                                <Button
                                    type="button"
                                    severity="secondary"
                                    class="flex gap-3 items-center justify-center w-full md:w-[130px]"
                                    @click="exportReport"
                                    :disabled="exportStatus"
                                >
                                    <IconCloudDownload size="16" stroke-width="1.5"/>
                                    <div class="text-sm font-medium">
                                        {{ $t('public.export') }}
                                    </div>
                                </Button>
                            </div>
                        </div>
                    </template>
                    <template #empty>
                        <EmptyData
                            v-if="!isLoading"
                            :title="$t('public.history_not_found')"
                        />
                    </template>
                    <template #loading>
                        <div class="flex flex-col gap-2 items-center justify-center">
                            <ProgressSpinner
                                strokeWidth="4"
                            />
                            <span class="text-sm text-surface-700 dark:text-surface-300">{{ $t('public.loading_trade_history') }}</span>
                        </div>
                    </template>
                    <template v-if="histories?.length > 0">
                        <Column
                            field="created_at"
                            sortable
                            class="table-cell min-w-32"
                            :header="$t('public.date')"
                        >
                            <template #body="{data}">
                                {{ dayjs(data.created_at).format('YYYY-MM-DD') }}
                                <div class="text-xs text-surface-500">
                                    {{ dayjs(data.created_at).format('HH:mm:ss') }}
                                </div>
                            </template>
                        </Column>
                        <Column
                            v-if="selectedType === 'referral' && !isLoading"
                            field="trader.username"
                            class="table-cell"
                            :header="$t('public.username')"
                        >
                            <template #body="slotProps">
                                {{ slotProps.data.trader.username }}
                            </template>
                        </Column>
                        <Column
                            field="meta_login"
                            class="table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.account') }}</span>
                            </template>
                            <template #body="slotProps">
                                {{ slotProps.data.meta_login }}
                            </template>
                        </Column>
                        <Column
                            field="volume"
                            sortable
                            class="table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.lot') }}</span>
                            </template>
                            <template #body="slotProps">
                                {{ formatAmount(slotProps.data.volume) }}
                            </template>
                        </Column>
                        <Column
                            field="rebate"
                            sortable
                            class="table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.amount') }}</span>
                            </template>
                            <template #body="slotProps">
                                <div class="font-medium">
                                    ${{ formatAmount(slotProps.data.rebate) }} (¥{{ formatAmount(slotProps.data.converted_amount) }})
                                </div>
                            </template>
                        </Column>
                    </template>
                </DataTable>
            </div>
        </template>
    </Card>
</template>
