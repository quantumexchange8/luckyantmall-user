<script setup>
import Button from 'primevue/button';
import {computed} from "vue";
import {
    IconPlus,
    IconArrowDown
} from "@tabler/icons-vue";
import {router, usePage} from "@inertiajs/vue3";
import {useConfirm} from "primevue/useconfirm";
import {trans} from "laravel-vue-i18n";

const props = defineProps({
    wallet: Object,
    allowedActions: Array
})

const allowedActions = computed(() => props.allowedActions);
const wallet = computed(() => props.wallet);

const redirectToWalletDeposit = (walletType) => {
    router.get(route('profile.wallet_deposit', walletType))
}

const confirm = useConfirm();

const requireConfirmation = (action_type) => {
    const messages = {
        no_payment_accounts: {
            group: 'headless-primary',
            header: trans('public.no_payment_accounts'),
            text: trans('public.no_payment_accounts_caption'),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.proceed'),
            action: () => {
                router.get(route('setting.payment_account'))
            }
        },
        no_security_pin: {
            group: 'headless-primary',
            header: trans('public.no_security_pin'),
            text: trans('public.no_security_pin_caption'),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.proceed'),
            action: () => {
                router.get(route('setting.security_pin'))
            }
        },
    };

    const { group, header, text, dynamicText, suffix, actionType, cancelButton, acceptButton, action } = messages[action_type];

    confirm.require({
        group,
        header,
        actionType,
        text,
        dynamicText,
        suffix,
        cancelButton,
        acceptButton,
        accept: action
    });
};

const user = usePage().props.auth.user;

const redirectToWalletWithdrawal = (walletType) => {
    if (user.security_pin === null) {
        requireConfirmation('no_security_pin');
    } else if (usePage().props.auth.paymentAccounts.length === 0) {
        requireConfirmation('no_payment_accounts');
    } else {
        router.get(route('profile.wallet_withdrawal', walletType))
    }
}
</script>

<template>
    <div class="flex items-center justify-between gap-3 w-full">
        <Button
            v-if="allowedActions.includes('deposit')"
            type="button"
            size="small"
            aria-label="Deposit"
            class="w-full flex gap-2"
            @click="redirectToWalletDeposit(wallet.type)"
        >
            <IconPlus size="16" />
            <span>{{ $t('public.deposit') }}</span>
        </Button>
        <Button
            v-if="allowedActions.includes('withdraw')"
            type="button"
            :severity="allowedActions.includes('deposit') ? 'secondary' : null"
            size="small"
            class="w-full flex gap-2"
            @click="redirectToWalletWithdrawal(wallet.type)"
        >
            <IconArrowDown size="16" />
            {{ $t('public.withdrawal') }}
        </Button>
        <Button
            v-if="allowedActions.includes('transfer')"
            type="button"
            severity="secondary"
            size="small"
            aria-label="Transfer"
            class="w-full"
        >
            Transfer
        </Button>
    </div>
</template>
