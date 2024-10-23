<script setup>
import Drawer from 'primevue/drawer';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import {onBeforeUnmount, onMounted, ref} from "vue";
import {
    IconPlus
} from "@tabler/icons-vue";
import {router} from "@inertiajs/vue3";

const props = defineProps({
    wallet: Object
})

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
            size="small"
            aria-label="Deposit"
            class="w-full flex gap-2"
            @click="redirectToWalletDeposit(wallet.type)"
        >
            <IconPlus size="16" />
            <span>Deposit</span>
        </Button>
        <Button
            severity="secondary"
            size="small"
            aria-label="Withdraw"
            class="w-full"
        >
            Withdraw
        </Button>
    </div>
</template>
