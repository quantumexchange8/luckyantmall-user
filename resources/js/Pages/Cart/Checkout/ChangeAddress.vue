<script setup>
import Button from "primevue/button";
import {IconCircleChevronRight} from "@tabler/icons-vue";
import AddAddress from "@/Pages/Setting/DeliveryAddress/AddAddress.vue";
import {ref, watch} from "vue";
import Drawer from "primevue/drawer";
import {useLangObserver} from "@/Composables/localeObserver.js";
import Tag from "primevue/tag";
import RadioButton from 'primevue/radiobutton';
import Skeleton from "primevue/skeleton";

const props = defineProps({
    defaultAddress: Object
})

const visibility = ref(false);

const openDrawer = () => {
    visibility.value = !visibility.value;
    getAddresses();
}

const loadingAddresses = ref(false);
const addresses = ref([]);
const selectedAddress = ref(props.defaultAddress.id);
const { locale } = useLangObserver();
const emit = defineEmits(["update:address"]);

const getAddresses = async () => {
    loadingAddresses.value = true;
    try {
        const response = await axios.get('/setting/getDeliveryAddress');
        addresses.value = response.data.addresses;
    } catch (error) {
        console.error('Error fetching countries:', error);
    } finally {
        loadingAddresses.value = false;
    }
};

// Watch for changes to selectedAddress and update displayed address
watch(selectedAddress, (newVal) => {
    if (newVal) {
        const selected = addresses.value.find(address => address.id === newVal);
        if (selected) {
            displayAddress.value = selected;
            emit("update:address", selected.id);
            visibility.value = false;
        }
    }
});

// Displayed address, initially set to defaultAddress
const displayAddress = ref(props.defaultAddress);
</script>

<template>
    <div
        class="flex justify-between gap-3 w-full items-center self-stretch rounded-lg border border-surface-200 dark:border-surface-700 p-3 select-none cursor-pointer"
        @click="openDrawer"
    >
        <div class="flex flex-col gap-1 w-full">
            <span class="text-xs text-left w-full text-surface-600 dark:text-surface-400">{{ $t('public.delivery_address') }}</span>
            <div class="flex justify-between gap-3 w-full">
                <div v-if="displayAddress" class="text-sm w-full">
                    {{ displayAddress.address }}
                </div>
                <div v-else class="w-full">
                    <AddAddress />
                </div>
            </div>
        </div>
        <div class="w-8">
            <Button
                severity="secondary"
                text
                rounded
                aria-label="Change Address"
                size="small"
            >
                <template #icon>
                    <IconCircleChevronRight size="24" />
                </template>
            </Button>
        </div>
    </div>

    <Drawer
        v-model:visible="visibility"
        :header="$t('public.change_address')"
        class="w-full md:max-w-[360px] pb-24 h-4/5"
        position="bottom"
    >
        <div
            v-if="loadingAddresses"
            class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-5 self-stretch w-full"
        >
            <div class="flex flex-col self-stretch w-full border border-surface-200 dark:border-surface-800 rounded-lg bg-surface-100 dark:bg-surface-900">
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
        <div v-else class="flex flex-col gap-3 self-stretch">
            <div
                v-for="address in addresses"
                class="flex gap-3 items-center self-stretch"
            >
                <RadioButton
                    v-model="selectedAddress"
                    :inputId="address.key"
                    name="dynamic"
                    :value="address.id"
                />
                <div
                    class="flex flex-col self-stretch w-full border border-surface-200 dark:border-surface-800 rounded-lg bg-surface-100 dark:bg-surface-900 select-none cursor-pointer hover:border-primary dark:hover:border-primary-400"
                    @click="selectedAddress = address.id"
                >
                    <div class="p-3 flex justify-between items-center text-surface-950 dark:text-white text-sm min-h-[50px] font-semibold">
                        <div class="flex gap-2 divide-x divide-surface-500">
                            <span>{{ address.receiver_name}}</span>
                            <span class="pl-2 text-surface-500">{{ address.receiver_phone}}</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 border-t w-full border-surface-200 dark:border-surface-800 p-3">
                        <div class="text-sm text-surface-950 dark:text-white">
                            {{ address.address }}
                        </div>
                        <div class="flex gap-2 items-center divide-x divide-surface-500 text-sm text-surface-500">
                            <span>{{ address.postal_code}}</span>
                            <span class="pl-2">{{ locale !== 'en' ? address.country.translations[locale] : address.country.name }}</span>
                            <Tag v-if="address.default_address" :value="$t('public.default')" severity="info" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Drawer>
</template>
