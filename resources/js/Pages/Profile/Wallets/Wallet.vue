<script setup>
import Card from "primevue/card";
import {ref} from "vue";
import {router} from "@inertiajs/vue3";

const props = defineProps({
    walletCounts: Number
})

const wallets = ref([]);
const isLoading = ref(false);

const getResults = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/profile/getWalletData');
        wallets.value = response.data.wallets;
    } catch (error) {
        console.error('Error fetching wallets:', error);
    } finally {
        isLoading.value = false;
    }
};

getResults();

const redirectToWallet = (walletType) => {
    router.get(route('profile.wallet_detail', walletType))
}
</script>

<template>
    <div
        v-if="isLoading"
        class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-5 items-center self-stretch w-full"
    >
        <Card
            v-for="index in walletCounts"
            :key="index"
            class="w-full"
        >
            <template #content>
                <div class="flex gap-3 items-center self-stretch">
                    <div class="flex flex-col">
                        <span class="text-sm font-medium">Wallet</span>
                        <span class="text-xs dark:text-surface-400">¥ 999,999.99</span>
                    </div>
                </div>
            </template>
        </Card>
    </div>

    <div
        v-else
        class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-5 items-center self-stretch w-full"
    >
        <Card
            v-for="wallet in wallets"
            :key="wallet.id"
            class="w-full hover:bg-primary-50 dark:hover:bg-surface-800 select-none cursor-pointer"
            @click="redirectToWallet(wallet.type)"
        >
            <template #content>
                <div class="flex gap-3 items-center justify-between self-stretch w-full">
                    <div class="flex flex-col">
                        <span class="text-xs md:text-sm dark:text-surface-400">{{ $t(`public.${wallet.type}`) }}</span>
                        <div class="md:text-lg text-primary-500 font-semibold">
                            <span v-if="wallet.currency_symbol !== 'point'">{{ wallet.currency_symbol }}{{ wallet.balance }}</span>
                            <span v-else>{{ wallet.balance }} {{ $t(`public.${wallet.currency_symbol}`) }}</span>
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>
