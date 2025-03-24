<script setup>
import Card from "primevue/card";
import dayjs from "dayjs";
import DataTable from "primevue/datatable";
import InputText from "primevue/inputtext";
import Column from "primevue/column";
import {
    IconSearch,
    IconXboxX,
    IconAdjustments
} from "@tabler/icons-vue";
import ProgressSpinner from "primevue/progressspinner";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import {onMounted, ref, watch, watchEffect} from "vue";
import debounce from "lodash/debounce.js";
import { FilterMatchMode } from '@primevue/core/api';
import {generalFormat} from "@/Composables/format.js";
import {usePage} from "@inertiajs/vue3";
import Popover from "primevue/popover";
import DatePicker from "primevue/datepicker";
import MultiSelect from "primevue/multiselect";
import EmptyData from "@/Components/EmptyData.vue";

const props = defineProps({
    levels: Array
})

const isLoading = ref(false);
const dt = ref(null);
const connections = ref([]);
const {formatAmount} = generalFormat();
const totalRecords = ref(0);
const first = ref(0);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    start_join_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    end_join_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    levels: { value: null, matchMode: FilterMatchMode.CONTAINS },
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

            const url = route('referral.getReferralListingData', params);
            const response = await fetch(url);
            const results = await response.json();

            connections.value = results?.data?.data;
            totalRecords.value = results?.data?.total;
            isLoading.value = false;

        }, 100);
    }  catch (e) {
        connections.value = [];
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
}

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

const clearFilterGlobal = () => {
    filters.value['global'].value = null;
}

const selectedDate = ref([]);

const clearJoinDate = () => {
    selectedDate.value = [];
}

watch(selectedDate, (newDateRange) => {
    if (Array.isArray(newDateRange)) {
        const [startDate, endDate] = newDateRange;
        filters.value['start_join_date'].value = startDate;
        filters.value['end_join_date'].value = endDate;

        if (startDate !== null && endDate !== null) {
            loadLazyData();
        }
    } else {
        console.warn('Invalid date range format:', newDateRange);
    }
})

watch([filters.value['levels']], () => {
    loadLazyData();
});

const clearFilter = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        start_join_date: { value: null, matchMode: FilterMatchMode.EQUALS },
        end_join_date: { value: null, matchMode: FilterMatchMode.EQUALS },
        levels: { value: null, matchMode: FilterMatchMode.CONTAINS },
    };

    selectedDate.value = [];
    lazyParams.value.filters = filters.value ;
};

watchEffect(() => {
    if (usePage().props.toast !== null) {
        loadLazyData();
    }
});
</script>

<template>
    <Card class="w-full">
        <template #content>
            <div class="w-full">
                <DataTable
                    :value="connections"
                    lazy
                    paginator
                    removableSort
                    :rows="10"
                    :rowsPerPageOptions="[10, 20, 50, 100]"
                    :first="first"
                    paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                    :currentPageReportTemplate="$t('public.paginator_caption')"
                    v-model:filters="filters"
                    ref="dt"
                    dataKey="id"
                    :loading="isLoading"
                    :totalRecords="totalRecords"
                    @page="onPage($event)"
                    @sort="onSort($event)"
                    @filter="onFilter($event)"
                    :globalFilterFields="['user.name', 'user.email', 'subject_user.name', 'subject_user.email', 'broker.name']"
                >
                    <template #header>
                        <div class="flex flex-col md:flex-row self-stretch gap-3 w-full md:w-auto">
                            <!-- Search bar -->
                            <IconField class="w-full md:w-auto">
                                <InputIcon>
                                    <IconSearch :size="16" stroke-width="1.5" />
                                </InputIcon>
                                <InputText
                                    v-model="filters['global'].value"
                                    :placeholder="$t('public.search_keyword')"
                                    type="text"
                                    class="block w-full pl-10 pr-10"
                                />
                                <!-- Clear filter button -->
                                <div
                                    v-if="filters['global'].value"
                                    class="absolute top-1/2 -translate-y-1/2 right-4 text-surface-300 hover:text-surface-400 select-none cursor-pointer"
                                    @click="clearFilterGlobal"
                                >
                                    <IconXboxX aria-hidden="true" :size="15" />
                                </div>
                            </IconField>

                            <!-- filter button -->
                            <Button
                                type="button"
                                class="w-full md:w-28 flex items-center gap-2"
                                outlined
                                severity="secondary"
                                @click="toggle"
                                size="small"
                            >
                                <IconAdjustments :size="16" stroke-width="1.5" />
                                {{ $t('public.filter') }}
                            </Button>
                        </div>
                    </template>

                    <template #empty>
                        <div class="flex justify-center items-center">
                            <EmptyData
                                :title="$t('public.no_user_found')"
                                :message="$t('public.no_user_found_caption')"
                            />
                        </div>
                    </template>

                    <template #loading>
                        <div class="flex flex-col gap-2 items-center justify-center">
                            <ProgressSpinner
                                strokeWidth="4"
                            />
                        </div>
                    </template>

                    <template v-if="connections?.length > 0">
                        <Column
                            field="created_at"
                            :header="$t('public.date')"
                            class="hidden md:table-cell min-w-32"
                            sortable
                        >
                            <template #body="{ data }">
                                {{ dayjs(data.created_at).format('YYYY-MM-DD') }}
                            </template>
                        </Column>

                        <Column
                            field="level"
                            :header="$t('public.level')"
                            class="hidden md:table-cell min-w-32"
                        >
                            <template #body="{ data }">
                                <span class="text-surface-950 dark:text-white">{{ data.level ?? 0 }}</span>
                            </template>
                        </Column>

                        <Column
                            field="client"
                            :header="$t('public.client')"
                            class="hidden md:table-cell min-w-32"
                        >
                            <template #body="{ data }">
                                <span class="text-surface-950 dark:text-white">{{ data.username }}</span>
                            </template>
                        </Column>

                        <Column
                            field="upline"
                            :header="$t('public.referrer')"
                            class="hidden md:table-cell min-w-32"
                        >
                            <template #body="{ data }">
                                <span class="text-surface-950 dark:text-white">{{ data.upline.username }}</span>
                            </template>
                        </Column>

                        <Column
                            field="active_trading_subscriptions_sum_subscription_amount"
                            :header="$t('public.fund')"
                            class="hidden md:table-cell min-w-32"
                            sortable
                        >
                            <template #body="{ data }">
                                <span class="text-surface-950 dark:text-white">${{ formatAmount(data.active_trading_subscriptions_sum_subscription_amount ?? 0) }}</span>
                            </template>
                        </Column>

                        <!-- Mobile view -->
                        <Column
                            field="mobile"
                            class="md:hidden"
                        >
                            <template #body="{ data }">
                                <div class="flex items-center justify-between">
                                    <div class="flex flex-col items-start">
                                        <div class="flex gap-2 items-center">
                                            <div class="font-semibold">
                                                {{ data.username }}
                                            </div>
                                        </div>
                                        <div class="flex gap-1 items-center text-surface-500 text-xs">
                                            {{ dayjs(data.created_at).format('YYYY-MM-DD') }}
                                            <span>|</span>
                                            <span>{{ data.upline.username }}</span>
                                            <span>|</span>
                                            <span>{{ $t('public.level') }} {{ data.level ?? 0 }}</span>
                                        </div>
                                    </div>
                                    <div class="text-base font-semibold">
                                        ${{ formatAmount(data.active_trading_subscriptions_sum_subscription_amount ?? 0) }}
                                    </div>
                                </div>
                            </template>
                        </Column>
                    </template>
                </DataTable>
            </div>
        </template>
    </Card>

    <Popover ref="op">
        <div class="flex flex-col gap-6 w-60">
            <!-- Filter Join Date-->
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
                        @click="clearJoinDate"
                    >
                        <IconXboxX size="12" stoke-width="1.5" />
                    </div>
                </div>
            </div>

            <!-- Filter level -->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-xs text-surface-950 dark:text-white font-semibold">
                    {{ $t('public.filter_by_level') }}
                </div>
                <MultiSelect
                    v-model="filters['levels'].value"
                    :options="levels"
                    :placeholder="$t('public.select_level')"
                    :maxSelectedLabels="3"
                    class="w-full"
                />
            </div>
            <Button
                type="button"
                severity="info"
                class="w-full"
                outlined
                @click="clearFilter"
            >
                {{ $t('public.clear_all') }}
            </Button>
        </div>
    </Popover>
</template>
