<script setup>
import ProductLayout from "@/Layouts/ProductLayout.vue";
import Galleria from "primevue/galleria";
import Card from "primevue/card";
import Button from "primevue/button";
import {
    IconShoppingCart,
    IconPlus,
    IconMinus
} from "@tabler/icons-vue";
import Drawer from "primevue/drawer";
import Image from "primevue/image";
import {ref, watch} from "vue";
import {generalFormat} from "@/Composables/format.js";
import InputNumber from 'primevue/inputnumber';
import {useForm, usePage} from "@inertiajs/vue3";
import Chip from 'primevue/chip';
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    product: Object
})

const visible = ref(false);
const drawerAction = ref('');
const basePrice = ref(props.product.base_price)
const finalPrice = ref(props.product.base_price)
const quantity = ref(1)
const {formatAmount} = generalFormat();

const openDrawer = (action) => {
    if (!usePage().props.auth.user) {
        window.location.href = route("login");
    } else {
        visible.value = true;
        drawerAction.value = action;
    }
}

const form = useForm({
    product_id: props.product.id,
    quantity: 1,
    price_per_unit: '',
    total_price: '',
    action: '',
    master_id: ''
})

watch(quantity, (val) => {
    if (props.product.price_bundles) {
        // Sort bundles in descending order of min_quantity
        const sortedBundles = [...props.product.price_bundles].sort((a, b) => b.min_quantity - a.min_quantity);

        const priceBundle = sortedBundles.find(bundle => val >= bundle.min_quantity);

        if (priceBundle) {
            basePrice.value = priceBundle.price_per_unit;
        } else {
            basePrice.value = props.product.base_price;
        }
    }

    // Calculate finalPrice as the sum total
    finalPrice.value = basePrice.value * val;
});

const handleChipClick = (master_id) => {
    form.master_id = master_id
}

const submitForm = () => {
    form.quantity = quantity.value;
    form.price_per_unit = basePrice.value;
    form.total_price = finalPrice.value;

    if (drawerAction.value === 'add_to_cart') {
        form.action = drawerAction.value;
    } else {
        form.action = drawerAction.value;
    }
    form.post(route('shop.addToCart'), {
        onSuccess: () => {
            form.reset();
        }
    })
}
</script>

<template>
    <ProductLayout :title="product.name">
        <div class="flex flex-col gap-5 items-center">
            <div class="flex flex-col lg:flex-row gap-3 md:gap-5 self-stretch">
                <Galleria
                    :value="product.media"
                    :numVisible="3"
                    :circular="true"
                    containerClass="w-full xl:max-w-[540px]"
                    :showItemNavigators="true"
                    :showItemNavigatorsOnHover="true"
                    :autoPlay="true"
                    :transitionInterval="2000"
                >
                    <template #item="slotProps">
                        <img :src="slotProps.item.original_url" :alt="slotProps.item.alt" class="rounded-t-[12px] h-[240px] object-cover" style="width: 100%; display: block" />
                    </template>
                    <template #thumbnail="slotProps">
                        <img :src="slotProps.item.original_url" :alt="slotProps.item.alt" class="w-16 sm:w-40 md:w-44 lg:w-[100px] xl:w-[130px] h-16 object-cover" style="display: block" />
                    </template>
                </Galleria>

                <Card class="w-full">
                    <template #content>
                        <div class="flex flex-col gap-5 self-stretch w-full">
                            <div class="flex flex-col gap-1 self-stretch">
                                <div class="text-xs">
                                    rating
                                </div>
                                <div class="text-lg font-bold">
                                    {{ product.name }}
                                </div>
                                <div class="font-medium text-surface-400">
                                    ¥{{ formatAmount(product.base_price) }}
                                </div>
                            </div>

                            <div v-html="product.descriptions"></div>

                            <div class="flex gap-3 items-center self-stretch">
                                <Button
                                    type="button"
                                    severity="secondary"
                                    size="small"
                                    aria-label="add_to_cart"
                                    class="w-full md:w-40 flex gap-2"
                                    outlined
                                    @click="openDrawer('add_to_cart')"
                                >
                                    <IconShoppingCart size="16" />
                                    {{ $t('public.add_to_cart') }}
                                </Button>
                                <Button
                                    type="button"
                                    size="small"
                                    aria-label="buy_now"
                                    class="w-full md:w-40"
                                    @click="openDrawer('buy_now')"
                                >
                                    {{ $t('public.buy_now') }}
                                </Button>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>

        <Drawer
            v-model:visible="visible"
            :header="$t('public.details')"
            class="w-full md:max-w-7xl pb-24 !h-4/5"
            position="bottom"
        >
            <div class="flex flex-col gap-3 self-stretch">
                <div class="flex gap-3 self-stretch">
                    <Image :src="product.media[0].original_url" alt="Image" imageClass="w-20 h-20 sm:w-32 sm:h-32 object-cover rounded-md" preview />
                    <div class="flex flex-col gap-2 justify-end self-stretch">
                        <div class="">
                            {{ product.name }}
                        </div>
                        <div class="flex flex-col md:flex-row md:items-end md:gap-1 self-stretch">
                            <div
                                class="md:text-lg font-semibold"
                                :class="{
                                    'text-surface-500 line-through': basePrice !== product.base_price,
                                }"
                            >
                                ¥{{ formatAmount(product.base_price) }}
                            </div>
                            <div v-if="basePrice !== product.base_price" class="md:text-xl text-primary font-semibold">
                                ¥{{ formatAmount(basePrice) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Options -->
                <div class="flex flex-col gap-3 self-stretch border-t border-surface-200 dark:border-surface-700 pt-3">
                    <div class="flex justify-between items-start w-full">
                        <span class="text-sm text-surface-400 dark:text-surface-500 w-full">{{ $t('public.quantity') }}</span>
                        <InputNumber
                            v-model="quantity"
                            inputId="product_quantity"
                            showButtons
                            buttonLayout="horizontal"
                            :min="1"
                            input-class="w-[60px]"
                        >
                            <template #incrementbuttonicon>
                                <IconPlus size="16" stroke-width="1.5" />
                            </template>
                            <template #decrementbuttonicon>
                                <IconMinus size="16" stroke-width="1.5" />
                            </template>
                        </InputNumber>
                    </div>
                    <div
                        v-if="product.masters"
                        class="flex flex-col md:flex-row gap-1 md:justify-between items-start w-full"
                    >
                        <span class="text-sm text-surface-400 dark:text-surface-500 w-full">{{ $t('public.investment_plan') }}</span>
                        <div class="flex flex-col gap-1 w-full">
                            <div class="flex gap-1 w-full md:justify-end">
                                <div v-for="(chip, index) in product.masters" :key="index">
                                    <Chip
                                        :label="chip.master_name"
                                        class="border dark:hover:border-surface-800"
                                        :class="{
                                    'border-primary-300 dark:border-surface-800 bg-primary-50 dark:bg-surface-800 hover:bg-primary-25 text-primary-500': form.master_id === chip.id,
                                    'dark:border-surface-800 text-surface-950 dark:text-white': form.master_id !== chip.id,
                                }"
                                        @click="handleChipClick(chip.id)"
                                    />
                                </div>
                            </div>
                            <InputError class="md:text-right" :message="form.errors.master_id" />
                        </div>
                    </div>
                    <div class="flex justify-between items-center w-full">
                        <span class="text-sm text-surface-400 dark:text-surface-500 w-full">{{ $t('public.total') }}</span>
                        <div class="md:text-lg font-semibold">
                            ¥{{ formatAmount(finalPrice) }}
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-5">
                    <Button
                        size="small"
                        class="w-full md:w-40"
                        @click="submitForm"
                    >
                        {{ $t(`public.${drawerAction}`) }}
                    </Button>
                </div>
            </div>
        </Drawer>
    </ProductLayout>
</template>
