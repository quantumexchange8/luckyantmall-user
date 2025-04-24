<script setup>
import ContentLayout from "@/Layouts/ContentLayout.vue";
import Card from "primevue/card";
import {generalFormat} from "@/Composables/format.js";
import {computed, ref, watch} from "vue";
import Drawer from "primevue/drawer";
import Button from "primevue/button";
import InputLabel from "@/Components/InputLabel.vue";
import InputNumber from "primevue/inputnumber";
import {useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputOtp from "primevue/inputotp";
import {IconCircleCheckFilled} from "@tabler/icons-vue";

const props = defineProps({
    walletType: String,
    wallets: Object,
    withdrawalFee: Number,
    withdrawalFeePercentage: Number,
    paymentAccounts: Object,
});

const visibility = ref(false);
const {formatAmount} = generalFormat();
const withdrawAmounts = ref({});
const transactionFee = ref(props.withdrawalFee);
const calculatedBalance = ref(0);

watch(
    () => props.wallets,
    (newWallets) => {
        if (newWallets && Array.isArray(newWallets)) {
            withdrawAmounts.value = {};
            newWallets.forEach(wallet => {
                withdrawAmounts.value[wallet.id] = null;
            });
        }
    },
    { immediate: true }
);

// Compute the total withdrawal amount
const totalWithdrawalBalance = computed(() =>
    Object.values(withdrawAmounts.value).reduce((sum, amount) => sum + (amount || 0), 0)
);

// Watch the total withdrawal balance and update transaction fees and final balance
watch(
    totalWithdrawalBalance,
    (newTotal) => {
        const calculatedMinimumFee = newTotal * (props.withdrawalFeePercentage / 100);

        if (calculatedMinimumFee <= props.withdrawalFee) {
            transactionFee.value = props.withdrawalFee;
            const calculated = newTotal - props.withdrawalFee;
            calculatedBalance.value = calculated <= 0 ? 0 : calculated;
        } else {
            transactionFee.value = calculatedMinimumFee;
            calculatedBalance.value = newTotal * ((100 - props.withdrawalFeePercentage) / 100);
        }
    }
);

const closeDrawer = () => {
    visibility.value = false;
}

const form = useForm({
    withdraw_wallets: null,
    transaction_charges: '',
    transaction_charges_percentage: '',
    payment_account_id: '',
    security_pin: '',
});

const selectedPaymentAccount = ref(props.paymentAccounts[0]);

const selectPaymentAccount = (account) => {
    selectedPaymentAccount.value = account
}

const submitForm = () => {
    form.payment_account_id = selectedPaymentAccount.value.id
    form.withdraw_wallets = withdrawAmounts.value;
    form.transaction_charges = transactionFee.value;
    form.transaction_charges_percentage = props.withdrawalFeePercentage;
    form.post(route('profile.submitWithdrawal'))
}
</script>

<template>
    <ContentLayout
        :title="$t('public.withdrawal')"
        backRoute="profile.wallet_detail"
        :routeParam="walletType"
    >
        <div class="flex flex-col gap-5 items-center self-stretch">
            <div class="flex flex-col gap-3 items-center self-stretch">
                <Card
                    v-for="wallet in wallets"
                    class="w-full"
                >
                    <template #content>
                        <div class="flex items-center gap-3">
                            <div class="text-sm w-full">
                                {{ $t(`public.${wallet.type}`) }}
                            </div>
                            <div class="text-lg w-full text-right font-semibold">
                                {{ wallet.currency_symbol }} {{ formatAmount(wallet.balance) }}
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <div class="flex flex-col gap-1 items-center self-stretch">
                <span class="text-xs dark:text-surface-400">{{ $t('public.withdrawal_amount') }}</span>
                <div class="text-4xl font-medium dark:text-white">
                    ¥ {{ formatAmount(totalWithdrawalBalance) }}
                </div>
            </div>

            <div class="flex flex-col gap-1 items-center self-stretch">
                <Button
                    size="small"
                    class="w-full"
                    severity="info"
                    @click="visibility = true"
                >
                    {{ $t('public.adjust_amount') }}
                </Button>
                <InputError :message="form.errors.withdraw_wallets" />
            </div>

            <Card class="w-full">
                <template #content>
                    <form class="flex flex-col gap-3 items-center self-stretch">
                        <!-- Payment Accounts -->
                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <InputLabel
                                for="payment_account"
                                :value="$t('public.payment_account')"
                                :invalid="!!form.errors.payment_account_id"
                            />
                            <div class="flex items-start overflow-x-auto gap-3 self-stretch">
                                <div
                                    v-for="account in paymentAccounts"
                                    @click="selectPaymentAccount(account)"
                                    class="group flex flex-col items-start py-2 px-3 gap-1 self-stretch rounded-lg border shadow-input transition-colors duration-300 select-none cursor-pointer min-w-28"
                                    :class="{
                                        'bg-primary-50 dark:bg-surface-800 border-primary-500': selectedPaymentAccount.id === account.id,
                                        'bg-white dark:bg-surface-950 border-surface-300 dark:border-surface-700 hover:bg-primary-50 hover:border-primary-500': selectedPaymentAccount.id !== account.id,
                                    }"
                                >
                                    <div class="flex items-center gap-3 self-stretch">
                                        <div
                                            class="flex-grow text-sm font-semibold transition-colors duration-300 group-hover:text-primary-700 dark:group-hover:text-primary-500"
                                            :class="{
                                                'text-primary-700 dark:text-primary-300': selectedPaymentAccount.id === account.id,
                                                'text-surface-950 dark:text-white': selectedPaymentAccount.id !== account.id
                                            }"
                                        >
                                            <div class="flex flex-col w-full">
                                                <span class="text-xs text-surface-600 dark:text-surface-400 uppercase">{{ $t(`public.${account.payment_platform}`) }}</span>
                                                <span class="font-semibold dark:text-white">{{ account.payment_account_name }}</span>
                                            </div>
                                        </div>
                                        <IconCircleCheckFilled v-if="selectedPaymentAccount.id === account.id" size="20" stroke-width="1.5" color="#2970FF" />
                                    </div>
                                </div>
                            </div>
                            <InputError :message="form.errors.payment_account_id" />
                        </div>

                        <div class="flex flex-col gap-1 items-center self-stretch">
                            <div class="flex justify-between gap-1 items-center self-stretch">
                                <span class="text-xs text-left w-full text-surface-600 dark:text-surface-400">{{ $t('public.withdrawal_fee') }}</span>
                                <div class="text-sm font-semibold">¥{{ formatAmount(transactionFee) }}</div>
                            </div>
                            <div class="flex justify-between gap-1 items-center self-stretch">
                                <span class="text-xs text-left w-full text-surface-600 dark:text-surface-400">{{ $t('public.receive_amount') }}</span>
                                <div class="text-sm font-semibold">¥{{ formatAmount(calculatedBalance) }}</div>
                            </div>
                        </div>
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

                        <div class="pt-2 w-full">
                            <Button
                                type="submit"
                                size="small"
                                :label="$t('public.withdrawal')"
                                class="w-full"
                                @click.prevent="submitForm"
                            />
                        </div>
                    </form>
                </template>
            </Card>
        </div>

        <Drawer
            v-model:visible="visibility"
            :header="$t('public.adjust_amount')"
            class="w-full md:max-w-[360px] pb-24 !h-3/5"
            position="bottom"
        >
            <div class="flex flex-col gap-3 items-center self-stretch">
                <div
                    v-for="wallet in wallets"
                    :key="wallet.id"
                    class="w-full"
                >
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel
                            :for="wallet.type"
                            :value="$t(`public.${wallet.type}`)"
                            :invalid="!!form.errors[wallet.type + '_amount']"
                        />
                        <InputNumber
                            v-model="withdrawAmounts[wallet.id]"
                            :inputId="wallet.type"
                            mode="currency"
                            :currency="wallet.currency"
                            locale="zh-CN"
                            placeholder="0.00"
                            fluid
                            :max="Number(wallet.balance)"
                        />
                        <div class="self-stretch text-surface-500 text-xs">
                            {{ $t('public.max') }}:
                            <span class="font-semibold text-sm dark:text-white">¥{{ formatAmount(wallet.balance ?? 0) }}</span>
                        </div>
                        <InputError :message="form.errors[`withdraw_wallets.${wallet.id}`]"/>
                    </div>
                </div>
            </div>
        </Drawer>
    </ContentLayout>
</template>
