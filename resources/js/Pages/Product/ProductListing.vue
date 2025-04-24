<script setup>
import MasterLayout from "@/Layouts/MasterLayout.vue";
import DataView from 'primevue/dataview';
import Button from "primevue/button";
import SelectButton from "primevue/selectbutton";
import {
    IconMenu2,
    IconLayoutGrid,
} from "@tabler/icons-vue";
import {ref, onMounted, watch} from "vue";
import {generalFormat} from "@/Composables/format.js";
import {useLangObserver} from "@/Composables/localeObserver.js";
import {Link} from "@inertiajs/vue3";
import ProductFilters from "@/Pages/Product/Partials/ProductFilters.vue";
import Drawer from "primevue/drawer";
import Select from "primevue/select";
import Skeleton from "primevue/skeleton";
import EmptyData from "@/Components/EmptyData.vue";

defineProps({
    productsCount: Number,
    categories: Object,
});

const products = ref([]);
const layout = ref('grid');
const options = ref(['list', 'grid']);
const search = ref('');
const selectedCategories = ref();
const isLoading = ref(false);
const currentPage = ref(1);
const rowsPerPage = ref(12);
const totalRecords = ref(0);
const {formatAmount} = generalFormat();
const {locale} = useLangObserver();

const getResults = async (page = 1, rowsPerPage = 12) => {
    isLoading.value = true;

    try {
        let url = `/shop/getProducts?page=${page}&limit=${rowsPerPage}`;

        if (selectedSort.value) {
            const sortObject = JSON.stringify(selectedSort.value);
            url += `&sortType=${encodeURIComponent(sortObject)}`;
        }

        if (search.value) {
            url += `&search=${search.value}`;
        }

        if (selectedCategories.value) {
            url += `&categories=${selectedCategories.value}`;
        }

        const response = await axios.get(url);
        products.value = response.data.products.data;
        totalRecords.value = response.data.total;
        currentPage.value = response.data.current_page;
    } catch (error) {
        console.error('Error getting products:', error);
    } finally {
        isLoading.value = false;
    }
};

const passInCategories = ref('');

onMounted(() => {
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get: (searchParams, prop) => searchParams.get(prop),
    });

    if (params.category) {
        passInCategories.value = params.category;
    } else {
        getResults(currentPage.value, rowsPerPage.value);
    }
});

const sortOptions = ref([
    { name: 'latest', field: 'created_at', direction: 'desc' },
    { name: 'oldest', field: 'created_at', direction: 'asc' },
    { name: 'highest_price', field: 'base_price', direction: 'desc' },
    { name: 'lowest_price', field: 'base_price', direction: 'asc' },
]);
const selectedSort = ref(sortOptions.value[0]);

const visible = ref(false);
const openDrawer = () => {
    visible.value = true;
}

watch(search, () => {
    getResults(currentPage.value, rowsPerPage.value);
})

watch([selectedCategories, selectedSort], () => {
    getResults(currentPage.value, rowsPerPage.value);
})
</script>

<template>
    <MasterLayout :title="$t('public.shop')">
        <div class="flex gap-5 w-full">
            <div class="hidden lg:block flex-col w-full max-w-64 bg-white dark:bg-surface-900 p-4 rounded-[12px]">
                <div class="flex justify-between items-center pb-5">
                    <span class="font-semibold dark:text-white">{{ $t('public.filter') }}</span>
                    <Button
                        type="button"
                        severity="danger"
                        outlined
                        size="small"
                    >
                        <span class="text-xs">{{ $t('public.clear_all') }}</span>
                    </Button>
                </div>

                <!-- Filter conditions -->
                <ProductFilters
                    :categories="categories"
                    :passInCategories="passInCategories"
                    @update:search="search = $event"
                    @update:selectedCategories="selectedCategories = $event"
                />
            </div>
            <div class="w-full">
                <DataView :value="products" :layout="layout">
                    <template #header>
                        <div class="flex lg:justify-between items-center w-full pb-3">
                            <Button
                                type="button"
                                severity="secondary"
                                outlined
                                class="!px-6 !py-1 block lg:hidden"
                                size="small"
                                @click="openDrawer"
                            >
                                <span class="text-xs">{{ $t('public.filter') }}</span>
                            </Button>
                            <div class="flex justify-end w-full">
                                <SelectButton v-model="layout" :options="options" :allowEmpty="false">
                                    <template #option="{ option }">
                                        <component :is="option === 'list' ? IconMenu2 : IconLayoutGrid" size="16" />
                                    </template>
                                </SelectButton>
                            </div>
                        </div>
                        <div class="pt-3 border-t border-surface-300 flex justify-end items-center gap-3">
                            <span class="font-semibold text-sm min-w-14">{{ $t('public.sort_by') }}</span>
                            <Select
                                v-model="selectedSort"
                                :options="sortOptions"
                                optionLabel="name"
                                :placeholder="$t('public.sort_by')"
                                class="w-full md:w-44"
                            >
                                <template #value="slotProps">
                                    <div v-if="slotProps.value" class="flex items-center">
                                        <div>{{ $t(`public.${slotProps.value.name}`) }}</div>
                                    </div>
                                    <span v-else>{{ slotProps.placeholder }}</span>
                                </template>
                                <template #option="slotProps">
                                    <div class="flex items-center">
                                        <div>{{ $t(`public.${slotProps.option.name}`) }}</div>
                                    </div>
                                </template>
                            </Select>
                        </div>
                    </template>

                    <!-- Empty State -->
                    <template #empty>
                        <div
                            v-if="isLoading && layout === 'grid'"
                            class="grid grid-cols-12 gap-5"
                        >
                            <div v-for="i in 6" :key="i" class="col-span-12 sm:col-span-6 xl:col-span-4">
                                <div class="p-4 bg-surface-0 dark:bg-surface-900 rounded-[12px] flex flex-col">
                                    <Skeleton height="20.75rem"></Skeleton>
                                    <div class="pt-5">
                                        <Skeleton width="8rem" height="1rem" class="mb-1" />
                                        <Skeleton width="6rem" height="2rem" class="mt-3 mb-1" />
                                        <div class="flex flex-col gap-6 mt-6">
                                            <Skeleton width="8rem" height="2rem" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex justify-center items-center">
                            <EmptyData
                                :title="$t('public.no_product_found')"
                            />
                        </div>
                    </template>

                    <template #list="slotProps">
                        <div
                            v-if="isLoading"
                            class="flex flex-col"
                        >
                            <div v-for="i in 6" :key="i">
                                <div class="flex flex-col sm:flex-row sm:items-center p-6 gap-4 bg-white dark:bg-surface-900 rounded-[12px]" :class="{ 'mt-5': i !== 0 }">
                                    <div class="md:w-40 h-[300px] md:h-40">
                                        <Skeleton height="10rem"></Skeleton>
                                    </div>
                                    <div class="flex flex-col md:flex-row justify-between md:items-center flex-1 gap-6">
                                        <div class="flex flex-row md:flex-col justify-between items-start gap-2">
                                            <Skeleton width="8rem" height="1rem" class="mb-1" />
                                            <Skeleton width="6rem" height="2rem" class="mt-3 mb-1" />
                                        </div>
                                        <div class="flex flex-col md:items-end gap-8">
                                            <Skeleton width="8rem" height="2rem" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex flex-col">
                            <div v-for="(item, index) in slotProps.items" :key="index">
                                <Link :href="route('shop.product_detail', [item.slug, item.id])">
                                    <div class="flex flex-col sm:flex-row sm:items-center p-6 gap-4 bg-white dark:bg-surface-900 rounded-[12px]" :class="{ 'mt-5': index !== 0 }">
                                        <div class="md:w-40 relative overflow-hidden">
                                            <img
                                                :src="item.media[0].original_url"
                                                :alt="item.name"
                                                class="mx-auto rounded w-[300px] md:w-96 h-[300px] md:h-40 hover:scale-110 transition-transform duration-300 object-cover"
                                            />
                                        </div>
                                        <div class="flex flex-col md:flex-row justify-between md:items-center flex-1 gap-6">
                                            <div class="flex flex-row md:flex-col justify-between items-start gap-2">
                                                <div class="hover:text-primary">
                                                    <span class="font-medium text-surface-500 dark:text-surface-400 text-sm">{{ item.category.name[locale] }}</span>
                                                    <div class="text-lg font-medium mt-2">{{ item.name }}</div>
                                                </div>
                                            </div>
                                            <div class="flex flex-col md:items-end gap-8">
                                                <span class="text-xl font-semibold">¥{{ formatAmount(item.base_price) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </template>

                    <template #grid="slotProps">
                        <div
                            v-if="isLoading"
                            class="grid grid-cols-12 gap-5"
                        >
                            <div v-for="i in 6" :key="i" class="col-span-12 sm:col-span-6 xl:col-span-4">
                                <div class="p-4 bg-surface-0 dark:bg-surface-900 rounded-[12px] flex flex-col">
                                    <Skeleton height="20.75rem"></Skeleton>
                                    <div class="pt-5">
                                        <Skeleton width="8rem" height="1rem" class="mb-1" />
                                        <Skeleton width="6rem" height="2rem" class="mt-3 mb-1" />
                                        <div class="flex flex-col gap-6 mt-6">
                                            <Skeleton width="8rem" height="2rem" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-12 gap-5">
                            <div v-for="(item, index) in slotProps.items" :key="index" class="col-span-12 sm:col-span-6 xl:col-span-4">
                                <Link :href="route('shop.product_detail', [item.slug, item.id])">
                                    <div class="p-4 bg-surface-0 dark:bg-surface-900 rounded-[12px] flex flex-col">
                                        <div class="flex justify-center rounded-md overflow-hidden bg-surface-100 dark:bg-surface-950 p-4">
                                            <img
                                                :src="item.media[0].original_url"
                                                :alt="item.name"
                                                class="w-full h-[300px] rounded hover:scale-110 transition-transform duration-300 object-contain"
                                            />
                                        </div>
                                        <div class="pt-6">
                                            <div class="flex flex-row justify-between items-start gap-2">
                                                <div class="hover:text-primary">
                                                    <span class="font-medium text-surface-500 dark:text-surface-400 text-sm">{{ item.category.name[locale] }}</span>
                                                    <div class="text-lg font-medium mt-1">{{ item.name }}</div>
                                                </div>
                                            </div>
                                            <div class="flex flex-col gap-6 mt-6">
                                                <span class="text-2xl font-semibold">¥{{ formatAmount(item.base_price) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </template>
                </DataView>
            </div>
        </div>

        <Drawer
            v-model:visible="visible"
            :header="$t('public.filter')"
            class="w-full md:max-w-7xl pb-24 !h-4/5"
            position="bottom"
        >
            <ProductFilters
                :categories="categories"
                :passInCategories="passInCategories"
                @update:search="search = $event"
                @update:selectedCategories="selectedCategories = $event"
            />
        </Drawer>
    </MasterLayout>
</template>
