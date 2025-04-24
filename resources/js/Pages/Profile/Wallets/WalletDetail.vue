<script setup>
import ContentLayout from "@/Layouts/ContentLayout.vue";
import Card from "primevue/card";
import {
    IconEye,
    IconEyeOff
} from "@tabler/icons-vue";
import Button from "primevue/button";
import WalletAction from "@/Pages/Profile/Wallets/WalletAction.vue";
import {ref} from "vue";
import WalletHistory from "@/Pages/Profile/Wallets/WalletHistory.vue";
import {generalFormat} from "@/Composables/format.js";

const props = defineProps({
    walletType: String,
    wallet: Object,
    transactionCounts: Number,
    earningCounts: Number,
    allowedActions: Array,
    types: {
        type: [Array, Object]
    },
})

const balanceVisibility = ref(props.wallet.balance_visibility);
const {formatAmount} = generalFormat();

const toggleVisibility = async () => {
    balanceVisibility.value = !balanceVisibility.value;

    try {
        await axios.patch(route('profile.updateBalanceVisibility'), {
            id: props.wallet.id,
        });
    } catch (error) {
        console.error('Failed to update balance visibility:', error);
    }
}
</script>

<template>
    <ContentLayout
        :title="$t(`public.${walletType}`)"
        backRoute="profile"
    >
        <div class="flex gap-5 items-start self-stretch">
            <Card
                class="w-full"
            >
                <template #content>
                    <div class="flex flex-col gap-3 md:gap-5 items-center self-stretch">
                        <div class="text-sm text-left w-full dark:text-surface-400">
                            {{ $t('public.balance') }} (<span class="font-semibold dark:text-surface-300">{{ wallet.address }}</span>)
                        </div>
                        <div class="flex items-center justify-center gap-1 mb-1">
                            <div class="text-[32px] font-semibold">
                                <div v-if="balanceVisibility">
                                    <span v-if="wallet.currency_symbol !== 'point'">{{ wallet.currency_symbol }}{{ formatAmount(wallet.balance) }}</span>
                                    <span v-else>{{ formatAmount(wallet.balance) }} {{ $t(`public.${wallet.currency_symbol}`) }}</span>
                                </div>
                               <div v-else class="h-12 pt-1">
                                   ****
                               </div>
                            </div>
                            <Button
                                type="button"
                                severity="contrast"
                                text
                                rounded
                                aria-label="Back"
                                size="small"
                                @click="toggleVisibility"
                            >
                                <template #icon>
                                    <IconEye v-if="balanceVisibility" size="16" />
                                    <IconEyeOff v-else size="16" />
                                </template>
                            </Button>
                        </div>
                        <WalletAction
                            :wallet="wallet"
                            :allowedActions="allowedActions"
                        />
                    </div>
                </template>
            </Card>
        </div>

        <WalletHistory
            :wallet="wallet"
            :transactionCounts="transactionCounts"
            :earningCounts="earningCounts"
            :types="types"
        />
    </ContentLayout>
</template>
