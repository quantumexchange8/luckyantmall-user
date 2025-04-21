<script setup>
import ProductLayout from "@/Layouts/ProductLayout.vue";
import Skeleton from "primevue/skeleton";
import {ref} from "vue";
import Tag from "primevue/tag";
import AddAddress from "@/Pages/Setting/DeliveryAddress/AddAddress.vue";
import {useLangObserver} from "@/Composables/localeObserver.js";

defineProps({
    addressCounts: Number,
    backRoute: String,
})

const addresses = ref([]);
const isLoading = ref(false);
const { locale } = useLangObserver();

const getResults = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/setting/getDeliveryAddress');
        addresses.value = response.data.addresses;
    } catch (error) {
        console.error('Error fetching addresses:', error);
    } finally {
        isLoading.value = false;
    }
};


getResults();
</script>

<template>
    <ProductLayout
        :title="$t('public.delivery_address')"
        :backRoute="backRoute"
    >
        <div class="flex flex-col items-center gap-3 md:gap-5 self-stretch">
            <div class="flex justify-end w-full">
                <div class="w-auto">
                    <AddAddress />
                </div>
            </div>

            <div
                v-if="addressCounts > 0"
                class="w-full"
            >
                <div
                    v-if="isLoading"
                    class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-5 self-stretch w-full"
                >
                    <div
                        v-for="index in addressCounts"
                        class="flex flex-col self-stretch w-full border border-surface-200 dark:border-surface-800 rounded-lg bg-surface-100 dark:bg-surface-900"
                    >
                        <div class="p-3 flex justify-between items-center text-surface-950 dark:text-white text-sm min-h-[50px] font-semibold">
                            <div class="flex gap-2 divide-x divide-surface-500">
                                <Skeleton width="4rem"></Skeleton>
                                <Skeleton width="8rem"></Skeleton>
                            </div>
                        </div>
                        <div class="flex flex-col gap-3 border-t w-full border-surface-200 dark:border-surface-800 p-3">
                            <div class="text-sm text-surface-950 dark:text-white">
                                <Skeleton width="12rem" class="my-0.5"></Skeleton>
                            </div>
                            <div class="flex gap-2 divide-x divide-surface-500 text-sm text-surface-500">
                                <Skeleton width="4rem" class="my-0.5"></Skeleton>
                                <Skeleton width="4rem" class="my-0.5"></Skeleton>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-5 self-stretch w-full"
                >
                    <div
                        v-for="address in addresses"
                        class="flex flex-col self-stretch w-full border border-surface-200 dark:border-surface-800 rounded-lg bg-surface-100 dark:bg-surface-900"
                    >
                        <div class="p-3 flex justify-between items-center text-surface-950 dark:text-white text-sm min-h-[50px] font-semibold">
                            <div class="flex gap-2 divide-x divide-surface-500">
                                <span>{{ address.receiver_name}}</span>
                                <span class="pl-2 text-surface-500">{{ address.receiver_phone}}</span>
                            </div>
                            <Tag v-if="address.default_address" :value="$t('public.default')" severity="info" />
                        </div>
                        <div class="flex flex-col gap-3 border-t w-full border-surface-200 dark:border-surface-800 p-3">
                            <div class="text-sm text-surface-950 dark:text-white">
                                {{ address.address }}
                            </div>
                            <div class="flex gap-2 divide-x divide-surface-500 text-sm text-surface-500">
                                <span>{{ address.postal_code}}</span>
                                <span class="pl-2">{{ locale !== 'en' ? address.country.translations[locale] : address.country.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ProductLayout>
</template>
