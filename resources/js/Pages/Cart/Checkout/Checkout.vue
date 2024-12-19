<script setup>
import ContentLayout from "@/Layouts/ContentLayout.vue";
import Card from "primevue/card"
import Button from "primevue/button";
import {generalFormat} from "@/Composables/format.js";
import InputError from "@/Components/InputError.vue";
import {useForm} from "@inertiajs/vue3";
import {computed, ref} from "vue";
import Drawer from "primevue/drawer";
import ChangeAddress from "@/Pages/Cart/Checkout/ChangeAddress.vue";
import InputOtp from 'primevue/inputotp';
import InputLabel from "@/Components/InputLabel.vue";
import AddAddress from "@/Pages/Setting/DeliveryAddress/AddAddress.vue";

const props = defineProps({
    cartItems: Object,
    default_address: Object,
    wallet: Object,
    backRoute: String,
})

const requiresDelivery = computed(() =>
    props.cartItems.some(item => item.product.required_delivery)
);

// Conditionally set `delivery_address_id` based on `requiresDelivery`
const delivery_address_id = ref(requiresDelivery.value ? props.default_address?.id : null);

const defaultAddress = props.default_address
const {formatAmount} = generalFormat();

const form = useForm({
    delivery_address_id: '',
    wallet_id: props.wallet.id,
    security_pin: '',
    cart_items: props.cartItems,
    sub_total: '',
    total_price: '',
})

const submitForm = () => {
    form.delivery_address_id = requiresDelivery.value ? delivery_address_id.value : null;
    form.sub_total = subTotalAmount.value;
    form.total_price = subTotalAmount.value;
    form.post(route('cart.confirmPayment'))
}

const visibility = ref(false);

const subTotalAmount = computed(() => {
    return props.cartItems.reduce((acc, item) => acc + item.total_price, 0);
});

// add totalAmount for sum of delivery fee
const afterPaymentBalance = computed(() => {
    return Math.max(0, props.wallet.balance - subTotalAmount.value);
});

const closeDrawer = () => {
    visibility.value = false;
    form.reset();
}
</script>

<template>
    <ContentLayout
        :title="$t('public.checkout')"
        :backRoute="backRoute"
    >
        <div class="flex flex-col gap-3 items-center self-stretch">
            <Card class="w-full">
                <template #content>
                    <div class="flex flex-col gap-3 self-stretch">
                        <span class="text-sm font-semibold">{{ $t('public.checkout_confirmation') }}</span>
                        <!-- Checkout Items -->
                        <div v-for="(item, index) in cartItems" :key="index">
                            <div class="flex flex-col items-center gap-4" :class="{ 'border-t border-surface-200 dark:border-surface-700': index !== 0 }">
                                <img class="block xl:block mx-auto rounded h-[300px] object-cover" :src="item.product.media[0].original_url" :alt="item.name" />
                                <div class="flex justify-between md:items-center gap-6 w-full">
                                    <div class="flex w-full justify-between items-start gap-3">
                                        <div class="flex flex-col">
                                            <div class="text-lg font-medium">
                                                {{ item.product.name }}
                                            </div>
                                            <div class="text-surface-400 dark:text-surface-500 font-semibold">
                                                ¥{{ formatAmount(item.price_per_unit) }}
                                            </div>
                                        </div>
                                        <div class="flex flex-col justify-end text-right">
                                            <div class="text-lg text-primary font-medium">
                                                ¥{{ formatAmount(item.total_price) }}
                                            </div>
                                            <div class="text-sm text-surface-400 dark:text-surface-500">
                                                {{ $t('public.quantity') }}: {{ item.quantity }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Payment Details -->
            <Card class="w-full">
                <template #content>
                    <div class="flex flex-col gap-3 self-stretch">
                        <span class="text-sm font-semibold">{{ $t('public.payment_details') }}</span>
                        <div
                            v-if="defaultAddress"
                        >
                            <ChangeAddress
                                :defaultAddress="defaultAddress"
                                @update:address="delivery_address_id = $event"
                            />
                        </div>
                        <div v-else>
                            <AddAddress />
                        </div>
                        <div class="flex flex-col gap-3 md:gap-5 items-center self-stretch">
                            <div class="flex flex-col gap-1 items-center self-stretch">
                                <div class="flex justify-between gap-1 md:items-center self-stretch">
                                    <span class="text-sm text-left w-full text-surface-600 dark:text-surface-400">{{ $t('public.sub_total') }}</span>
                                    <div class="text-sm font-semibold">¥{{ formatAmount(subTotalAmount) }}</div>
                                </div>
                                <div class="flex justify-between gap-1 md:items-center self-stretch">
                                    <span class="text-sm text-left w-full text-surface-600 dark:text-surface-400">{{ $t('public.shipping_fee') }}</span>
                                    <div class="text-sm font-semibold">¥{{ formatAmount(0) }}</div>
                                </div>
                            </div>

                            <div class="flex justify-between gap-1 md:items-center border-t border-surface-200 dark:border-surface-700 pt-5 self-stretch text-surface-950 dark:text-white">
                                <span class="text-left w-full font-semibold">{{ $t('public.total') }}</span>
                                <div class="font-semibold">¥{{ formatAmount(subTotalAmount) }}</div>
                            </div>

                            <div class="flex flex-col gap-1 text-center items-center w-full">
                                <Button
                                    size="small"
                                    class="w-full"
                                    @click="visibility = true"
                                >
                                    {{ $t('public.pay') }}
                                </Button>
                                <InputError :message="form.errors.cart_items" />
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </ContentLayout>

    <Drawer
        v-model:visible="visibility"
        :header="$t('public.confirm_payment')"
        class="w-full md:max-w-[360px] pb-24 !h-4/5"
        position="bottom"
    >
        <form>
            <div class="flex flex-col gap-3 items-center self-stretch">
                <div class="flex justify-between gap-1 md:items-center self-stretch text-surface-950 dark:text-white">
                    <span class="text-left w-full font-semibold">{{ $t('public.total') }}</span>
                    <div class="font-semibold">¥{{ formatAmount(subTotalAmount) }}</div>
                </div>
                <div class="flex justify-between gap-1 self-stretch text-surface-950 dark:text-white">
                    <span class="text-left w-full font-semibold">{{ $t('public.pay_via') }}</span>
                    <div class="flex flex-col items-end">
                        <div class="text-surface-300 text-sm dark:text-surface-400">{{ $t(`public.${wallet.type}`) }}</div>
                        <div class="font-semibold">¥{{ formatAmount(wallet.balance) }}</div>
                        <InputError :message="form.errors.wallet_id" />
                    </div>
                </div>
                <div class="text-xs text-surface-300 w-full text-right">
                    {{ $t('public.after_payment') }}: ¥{{ formatAmount(afterPaymentBalance) }}
                </div>

                <!-- Security pin -->
                <div class="flex flex-col gap-1 items-start self-stretch border-t border-surface-200 dark:border-surface-700 pt-3">
                    <InputLabel
                        for="security_pin"
                        :value="$t('public.security_pin')"
                        :invalid="!!form.errors.security_pin"
                    />
                    <InputOtp
                        v-model="form.security_pin"
                        input-id="security_pin"
                        mask
                        :length="6"
                    />
                    <InputError :message="form.errors.security_pin" />
                </div>

                <div class="flex gap-3 self-stretch w-full pt-3">
                    <Button
                        type="button"
                        severity="secondary"
                        outlined
                        @click="closeDrawer"
                        :disabled="form.processing"
                        class="w-full"
                        size="small"
                    >
                        {{ $t('public.discard') }}
                    </Button>
                    <Button
                        type="submit"
                        @click="submitForm"
                        :disabled="form.processing"
                        class="w-full"
                        size="small"
                    >
                        {{ $t('public.confirm') }}
                    </Button>
                </div>
            </div>
        </form>
    </Drawer>
</template>
