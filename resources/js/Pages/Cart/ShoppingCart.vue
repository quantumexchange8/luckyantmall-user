<script setup>
import MasterLayout from "@/Layouts/MasterLayout.vue";
import Card from "primevue/card"
import DataView from 'primevue/dataview';
import Button from 'primevue/button';
import InputNumber from 'primevue/inputnumber';
import {computed, onMounted, ref, watch} from "vue";
import {ProductService} from "@/service/ProductService.js";
import {generalFormat} from "@/Composables/format.js";
import {IconMinus, IconPlus} from "@tabler/icons-vue";

const props = defineProps({
    cart: Object
})

onMounted(() => {
    ProductService.getProductsSmall().then((data) => (products.value = data.slice(0, 5)));
});

const products = ref();
const cartItems = ref();
const isLoading = ref(false);
const quantity = ref();
const basePrice = ref();
const finalPrice = ref();
const {formatAmount} = generalFormat();

const getSeverity = (product) => {
    switch (product.inventoryStatus) {
        case 'INSTOCK':
            return 'success';

        case 'LOWSTOCK':
            return 'warn';

        case 'OUTOFSTOCK':
            return 'danger';

        default:
            return null;
    }
};

const getResults = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(`/cart/getCartItems?cartId=${props.cart.id}`);
        cartItems.value = response.data.cart_items;
        cartItems.value.forEach((item, index) => {
            watch(
                () => item.quantity,
                (val) => updatePrice(item, index, val),
                { immediate: true }
            );
        });
    } catch (error) {
        console.error('Error fetching popular products:', error);
    } finally {
        isLoading.value = false;
    }
};

getResults();

const updatePrice = (item, index, quantity) => {
    const product = item.product;

    // Initialize base price from the product
    let pricePerUnit = product.base_price;

    if (product.price_bundles) {
        // Sort bundles by min_quantity in descending order
        const sortedBundles = [...product.price_bundles].sort((a, b) => b.min_quantity - a.min_quantity);
        const priceBundle = sortedBundles.find(bundle => quantity >= bundle.min_quantity);

        // Update price per unit if a matching bundle is found
        pricePerUnit = priceBundle ? priceBundle.price_per_unit : product.base_price;
    }

    // Set the calculated basePrice and finalPrice in each cart item
    item.basePrice = pricePerUnit;
    item.finalPrice = pricePerUnit * quantity;
};

const totalCartPrice = computed(() => {
    // Use an empty array as fallback in case cartItems is null or undefined
    return (cartItems.value || []).reduce((sum, item) => sum + (item.finalPrice || 0), 0);
});
</script>

<template>
    <MasterLayout :title="$t('public.shopping_cart')">
        <div class="flex flex-col sm:flex-row gap-3 md:gap-5 items-start self-stretch w-full">
            <!-- Cart items -->
            <Card class="w-full sm:w-2/3">
                <template #content>
                    <div class="flex flex-col gap-3 md:gap-5 items-center self-stretch">
                        <span class="font-semibold text-left w-full text-surface-950 dark:text-white">{{ $t('public.shopping_cart') }}</span>

                        <DataView :value="cartItems" class="w-full">
                            <template #list="slotProps">
                                <div class="flex flex-col">
                                    <div v-for="(item, index) in slotProps.items" :key="index">
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-4" :class="{ 'border-t border-surface-200 dark:border-surface-700': index !== 0 }">
                                            <img class="block xl:block mx-auto rounded w-full sm:w-32 h-40 object-cover" :src="item.product.media[0].original_url" :alt="item.name" />
                                            <div class="flex flex-col md:flex-row justify-between md:items-center flex-1 gap-6">
                                                <div class="flex flex-col justify-between items-start gap-3">
                                                    <div class="flex flex-col">
                                                        <div class="text-lg font-medium">
                                                            {{ item.product.name }}
                                                        </div>
                                                        <div
                                                            class="font-semibold"
                                                            :class="{
                                                                'text-surface-400 dark:text-surface-500 line-through': basePrice !== item.product.base_price,
                                                            }"
                                                        >
                                                            ¥{{ formatAmount(item.product.base_price) }}
                                                        </div>
                                                        <div v-if="basePrice !== item.product.base_price" class="text-surface-400 dark:text-surface-500 font-semibold">
                                                            ¥{{ formatAmount(item.basePrice) }}
                                                        </div>
                                                    </div>
                                                    <InputNumber
                                                        v-model="item.quantity"
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
                                                <div class="flex flex-col md:items-end md:justify-between self-stretch gap-5">
                                                    <div class="text-xl font-semibold">
                                                        ¥{{ formatAmount(item.finalPrice) }}
                                                    </div>
                                                    <div class="flex flex-row-reverse md:flex-row gap-2">
<!--                                                        <Button icon="pi pi-heart" outlined></Button>-->
                                                        <Button icon="pi pi-shopping-cart" label="Buy Now" :disabled="item.inventoryStatus === 'OUTOFSTOCK'" class="flex-auto md:flex-initial whitespace-nowrap"></Button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </DataView>
                    </div>
                </template>
            </Card>

            <!-- Payment -->
            <Card class="w-full sm:w-1/3">
                <template #content>
                    <div class="flex flex-col gap-3 md:gap-5 items-center self-stretch">
                        <span class="font-semibold text-left w-full text-surface-950 dark:text-white">{{ $t('public.details') }}</span>
                        <div class="flex flex-col gap-3 md:gap-5 items-center self-stretch">
                            <div class="flex flex-col gap-1 items-center self-stretch">
                                <div class="flex flex-col md:flex-row justify-between gap-1 md:items-center self-stretch">
                                    <span class="text-sm font-semibold text-left w-full text-surface-600 dark:text-surface-400">{{ $t('public.sub_total') }}</span>
                                    <div class="text-sm font-semibold">¥{{ formatAmount(totalCartPrice) }}</div>
                                </div>
                                <div class="flex flex-col md:flex-row justify-between gap-1 md:items-center self-stretch">
                                    <span class="text-xs text-left w-full text-surface-600 dark:text-surface-400">{{ $t('public.shipping_fee') }}</span>
                                    <div class="text-xs">¥{{ formatAmount(0) }}</div>
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row justify-between gap-1 md:items-center border-t border-surface-200 dark:border-surface-700 pt-5 self-stretch text-surface-950 dark:text-white">
                                <span class="text-left w-full font-semibold">{{ $t('public.total') }}</span>
                                <div class="font-semibold">¥{{ formatAmount(totalCartPrice) }}</div>
                            </div>

                            <div class="flex flex-col gap-3 w-full">
                                <Button
                                    size="small"
                                    class="w-full"
                                >
                                    {{ $t('public.proceed_to_checkout') }}
                                </Button>
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </MasterLayout>
</template>
