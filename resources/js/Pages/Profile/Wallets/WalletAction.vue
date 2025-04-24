<script setup>
import Drawer from 'primevue/drawer';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import {computed, onBeforeUnmount, onMounted, ref} from "vue";
import {
    IconPlus,
    IconArrowDown
} from "@tabler/icons-vue";
import {router} from "@inertiajs/vue3";

const props = defineProps({
    wallet: Object,
    allowedActions: Array
})

const allowedActions = computed(() => props.allowedActions);
const wallet = computed(() => props.wallet);

// const visible = ref(false);
// const isSmallScreen = ref(window.innerWidth < 640);
//
// const toggleVisibility = () => {
//     visible.value = true;
// };
//
// const handleResize = () => {
//     isSmallScreen.value = window.innerWidth < 640;
// };
//
// onMounted(() => {
//     window.addEventListener('resize', handleResize);
// });
//
// onBeforeUnmount(() => {
//     window.removeEventListener('resize', handleResize);
// });

const redirectToWalletDeposit = (walletType) => {
    router.get(route('profile.wallet_deposit', walletType))
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
            as="a"
            :href="route('profile.wallet_withdrawal', wallet.type)"
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
