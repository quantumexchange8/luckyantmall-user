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
import Popover from "primevue/popover";
import DatePicker from "primevue/datepicker"
import debounce from "lodash/debounce.js";
import {
    IconAdjustments,
    IconCircleXFilled,
    IconSearch,
    IconXboxX,
    IconCloudDownload
} from "@tabler/icons-vue";
import Tag from "primevue/tag";
import {generalFormat} from "@/Composables/format.js";
import EmptyData from "@/Components/EmptyData.vue";
import ProgressSpinner from "primevue/progressspinner";
import MultiSelect from "primevue/multiselect";

const props = defineProps({
    selectedType: String,
});

const isLoading = ref(false);
const dt = ref(null);
const histories = ref([]);
const {formatAmount} = generalFormat();
const totalRecords = ref(0);
const first = ref(0);
const todayProfit = ref();
const tradeProfitTrend = ref();
const totalProfit = ref();
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

            const url = route('trade_history.getTradeHistoriesData', params);
            const response = await fetch(url);
            const results = await response.json();

            histories.value = results?.data?.data;
            totalRecords.value = results?.data?.total;
            todayProfit.value = results?.todayProfit;
            tradeProfitTrend.value = results?.tradeProfitTrend;
            totalProfit.value = results?.totalProfit;
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

const op = ref();
const toggle = (event) => {
    op.value.toggle(event);
    getTradeSymbols();
}

const symbols = ref();
const loadingSymbols = ref(false);

const getTradeSymbols = async () => {
    loadingSymbols.value = true;
    try {
        const response = await axios.get('/getTradeSymbols');
        symbols.value = response.data;
    } catch (error) {
        console.error('Error fetching symbols:', error);
    } finally {
        loadingSymbols.value = false;
    }
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

const getSeverity = (status) => {
    switch (status) {
        case 'buy':
            return 'info';

        case 'sell':
            return 'secondary';
    }
}

const emit = defineEmits(['update-totals']);

watch([todayProfit, tradeProfitTrend, totalProfit, totalTradeLot], () => {
    emit('update-totals', {
        todayProfit: todayProfit.value,
        tradeProfitTrend: tradeProfitTrend.value,
        totalProfit: totalProfit.value,
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

    const url = route('trade_history.getTradeHistoriesData', params);

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
                    :globalFilterFields="['meta_login', 'symbol', 'ticket']"
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
                                <Button
                                    type="button"
                                    severity="secondary"
                                    outlined
                                    class="flex gap-3 items-center justify-center w-full md:w-[130px]"
                                    @click="toggle"
                                >
                                    <IconAdjustments size="16" stroke-width="1.5"/>
                                    <div class="text-sm font-medium">
                                        {{ $t('public.filter') }}
                                    </div>
                                </Button>
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
                            field="symbol"
                            class="table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.trade') }}</span>
                            </template>
                            <template #body="slotProps">
                                <span class="font-bold">{{ slotProps.data.symbol }}</span>
                            </template>
                        </Column>
                        <Column
                            field="time_open"
                            sortable
                            class="table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.open') }}</span>
                            </template>
                            <template #body="{data}">
                                {{ dayjs(data.time_open).format('YYYY-MM-DD') }}
                                <div class="text-xs text-surface-500">
                                    {{ dayjs(data.time_open).format('HH:mm:ss') }}
                                </div>
                            </template>
                        </Column>
                        <Column
                            field="time_close"
                            sortable
                            class="table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.close') }}</span>
                            </template>
                            <template #body="{data}">
                                {{ dayjs(data.time_close).format('YYYY-MM-DD') }}
                                <div class="text-xs text-surface-500">
                                    {{ dayjs(data.time_close).format('HH:mm:ss') }}
                                </div>
                            </template>
                        </Column>
                        <Column
                            field="trade_type"
                            class="table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.type') }}</span>
                            </template>
                            <template #body="{data}">
                                <Tag
                                    :severity="getSeverity(data.trade_type)"
                                    :value="$t(`public.${data.trade_type}`)"
                                />
                            </template>
                        </Column>
                        <Column
                            field="ticket"
                            class="table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.ticket') }}</span>
                            </template>
                            <template #body="slotProps">
                                {{ slotProps.data.ticket }}
                            </template>
                        </Column>
                        <Column
                            field="price_open"
                            sortable
                            class="table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.open') }}($)</span>
                            </template>
                            <template #body="slotProps">
                                ${{ slotProps.data.price_open }}
                            </template>
                        </Column>
                        <Column
                            field="price_close"
                            sortable
                            class="table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.close') }}($)</span>
                            </template>
                            <template #body="slotProps">
                                ${{ slotProps.data.price_close }}
                            </template>
                        </Column>
                        <Column
                            field="volume"
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
                            field="trade_profit"
                            sortable
                            class="table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.profit') }}</span>
                            </template>
                            <template #body="{data}">
                                <div
                                    :class="[
                                        'font-medium',
                                        {
                                            'text-success-500': data.trade_profit > 0,
                                            'text-red-500': data.trade_profit < 0,
                                        }
                                    ]"
                                >
                                    ${{ formatAmount(data.trade_profit) }}
                                </div>
                            </template>
                        </Column>
                        <Column
                            field="trade_swap"
                            class="table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.swap') }}</span>
                            </template>
                            <template #body="slotProps">
                                ${{ formatAmount(slotProps.data.trade_swap) }}
                            </template>
                        </Column>
                        <Column
                            field="trade_profit_pct"
                            class="table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.change') }}</span>
                            </template>
                            <template #body="slotProps">
                                {{ formatAmount(slotProps.data.trade_profit_pct) }}%
                            </template>
                        </Column>
                    </template>
                </DataTable>
            </div>
        </template>
    </Card>

    <Popover ref="op">
        <div class="flex flex-col gap-6 w-60">
            <!-- Filter Date -->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-xs text-surface-950 dark:text-white font-semibold">
                    {{ $t('public.filter_by_date') }}
                </div>
                <div class="relative w-full">
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
            </div>

            <!-- Filter Symbols -->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-xs text-surface-950 dark:text-white font-semibold">
                    {{ $t('public.filter_by_symbols') }}
                </div>
                <MultiSelect
                    v-model="filters['symbols'].value"
                    :options="symbols"
                    :placeholder="$t('public.select_symbols')"
                    :maxSelectedLabels="3"
                    class="w-full"
                />
            </div>

            <Button
                type="button"
                severity="info"
                class="w-full"
                size="small"
                outlined
                @click="clearAll"
                :label="$t('public.clear_all')"
            />
        </div>
    </Popover>
</template>
