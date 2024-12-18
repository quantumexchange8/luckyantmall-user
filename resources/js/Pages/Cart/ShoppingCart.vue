<script setup>
import MasterLayout from "@/Layouts/MasterLayout.vue";
import Card from "primevue/card"
import DataView from 'primevue/dataview';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import InputNumber from 'primevue/inputnumber';
import {computed, onMounted, ref, watch} from "vue";
import {ProductService} from "@/service/ProductService.js";
import {generalFormat} from "@/Composables/format.js";
import {IconMinus, IconPlus} from "@tabler/icons-vue";
import {useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import Skeleton from "primevue/skeleton";

const props = defineProps({
    cart: Object
})

onMounted(() => {
    ProductService.getProductsSmall().then((data) => (products.value = data.slice(0, 5)));
});

const products = ref();
const cartItems = ref();
const isLoading = ref(false);
const basePrice = ref();
const {formatAmount} = generalFormat();

const checkedItems = ref(new Set()); // Store selected item IDs

// Form to handle checkout
const form = useForm({
    cart_items: []
});

const getResults = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(`/cart/getCartItems?cartId=${props.cart.id}`);
        cartItems.value = response.data.cart_items;

        cartItems.value.forEach((item, index) => {
            item.isSelected = false; // Add `isSelected` property to each item for checkbox binding
            watch(
                () => item.quantity,
                (val) => {
                    updatePrice(item, index, val);
                    updateFormItem(item); // Update form when quantity changes
                },
                { immediate: true }
            );
        });
    } catch (error) {
        console.error('Error fetching cart items:', error);
    } finally {
        isLoading.value = false;
    }
};

getResults();

// Update item price based on quantity and any applicable bundle discounts
const updatePrice = (item, index, quantity) => {
    const product = item.product;
    let pricePerUnit = product.base_price;

    if (product.price_bundles) {
        const sortedBundles = [...product.price_bundles].sort((a, b) => b.min_quantity - a.min_quantity);
        const priceBundle = sortedBundles.find(bundle => quantity >= bundle.min_quantity);
        pricePerUnit = priceBundle ? priceBundle.price_per_unit : product.base_price;
    }

    item.basePrice = pricePerUnit;
    item.finalPrice = pricePerUnit * quantity;
};

// Update form data when the quantity changes for a selected item
const updateFormItem = (item) => {
    if (checkedItems.value.has(item.id)) {
        const index = form.cart_items.findIndex(ci => ci.id === item.id);
        if (index !== -1) {
            form.cart_items[index] = {
                id: item.id,
                quantity: item.quantity,
                price_per_unit: item.basePrice,
                total_price: item.finalPrice
            };
        }
    }
};

// Compute total price of selected items
const totalSelectedPrice = computed(() => {
    return (cartItems.value || []).reduce((sum, item) => {
        if (checkedItems.value.has(item.id)) {
            return sum + (item.finalPrice || 0);
        }
        return sum;
    }, 0);
});

// Handle checkbox change
const toggleItemSelection = (item) => {
    if (item.isSelected) {
        checkedItems.value.add(item.id);
        form.cart_items.push({
            id: item.id,
            quantity: item.quantity,
            price_per_unit: item.basePrice,
            total_price: item.finalPrice
        });
    } else {
        checkedItems.value.delete(item.id);
        form.cart_items = form.cart_items.filter(ci => ci.id !== item.id);
    }
};

const proceedCheckout = () => {
    form.post(route('cart.proceedCheckout'));
}
</script>

<template>
    <MasterLayout :title="$t('public.shopping_cart')">
        <div class="flex flex-col sm:flex-row gap-3 md:gap-5 items-start self-stretch w-full">
            <!-- Cart items -->
            <Card class="w-full sm:w-2/3">
                <template #content>
                    <div class="flex flex-col gap-3 md:gap-5 items-center self-stretch">
                        <span class="font-semibold text-left w-full text-surface-950 dark:text-white">{{ $t('public.shopping_cart') }}</span>

                        <DataView
                            v-if="isLoading"
                            :value="products"
                            layout="list"
                            class="w-full"
                        >
                            <template #list>
                                <div class="flex flex-col">
                                    <div v-for="(i, index) in cart.cart_items_count" :key="index">
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-4" :class="{ 'border-t border-surface-200 dark:border-surface-700': index !== 0 }">
                                            <div class="flex justify-start items-start self-stretch">
                                                <Checkbox
                                                    :binary="true"
                                                    disabled
                                                />
                                            </div>
                                            <Skeleton class="w-full sm:!w-32 !h-40 mx-auto" />
                                            <div class="flex flex-col md:flex-row justify-between md:items-center flex-1 gap-6">
                                                <div class="flex flex-col justify-between items-start gap-3">
                                                    <div class="flex flex-col gap-2">
                                                        <Skeleton width="8rem" height="2rem" />
                                                        <Skeleton width="8rem" height="2rem" />
                                                    </div>
                                                    <Skeleton width="8rem" height="2rem" />
                                                </div>
                                                <div class="flex flex-col md:items-end md:justify-between self-stretch gap-5">
                                                    <Skeleton width="8rem" height="2rem" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </DataView>

                        <DataView
                            v-else
                            :value="cartItems"
                            class="w-full"
                        >
                            <template #list="slotProps">
                                <div class="flex flex-col">
                                    <div v-for="(item, index) in slotProps.items" :key="index">
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-4" :class="{ 'border-t border-surface-200 dark:border-surface-700': index !== 0 }">
                                            <div class="flex justify-start items-start self-stretch">
                                                <Checkbox
                                                    v-model="item.isSelected"
                                                    @change="() => toggleItemSelection(item)"
                                                    :binary="true"
                                                />
                                            </div>
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
                                    <div class="text-sm font-semibold">¥{{ formatAmount(totalSelectedPrice) }}</div>
                                </div>
                                <div class="flex flex-col md:flex-row justify-between gap-1 md:items-center self-stretch">
                                    <span class="text-xs text-left w-full text-surface-600 dark:text-surface-400">{{ $t('public.shipping_fee') }}</span>
                                    <div class="text-xs">¥{{ formatAmount(0) }}</div>
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row justify-between gap-1 md:items-center border-t border-surface-200 dark:border-surface-700 pt-5 self-stretch text-surface-950 dark:text-white">
                                <span class="text-left w-full font-semibold">{{ $t('public.total') }}</span>
                                <div class="font-semibold">¥{{ formatAmount(totalSelectedPrice) }}</div>
                            </div>

                            <div class="flex flex-col gap-1 text-center items-center w-full">
                                <Button
                                    size="small"
                                    class="w-full"
                                    @click="proceedCheckout"
                                    :disabled="cart.cart_items_count === 0"
                                >
                                    {{ $t('public.proceed_to_checkout') }}
                                </Button>
                                <InputError :message="form.errors.cart_items" />
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </MasterLayout>
</template>
