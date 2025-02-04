<script setup>
import Card from "primevue/card";
import {computed, ref} from "vue";
import {router} from "@inertiajs/vue3";
import Select from "primevue/select";
import {generalFormat} from "@/Composables/format.js";
import {IconEye, IconEyeOff} from "@tabler/icons-vue";
import Button from "primevue/button";

const props = defineProps({
    wallets: Object
})

const isLoading = ref(false);
const {formatAmount} = generalFormat();
const selectedWallet = ref(props.wallets[0]);

const formattedBalance = computed(() => {
    const amount = formatAmount(selectedWallet.value.balance);
    const parts = amount.split(".");

    return parts.length > 1
        ? `${parts[0]}.<span class='text-lg text-surface-500 dark:text-surface-400'>${parts[1]}</span>`
        : amount;
});

const redirectToWallet = (walletType) => {
    router.get(route('profile.wallet_detail', walletType))
}

const balanceVisibility = ref(selectedWallet.value.balance_visibility);

const toggleVisibility = async () => {
    balanceVisibility.value = !balanceVisibility.value;

    try {
        await axios.patch(route('profile.updateBalanceVisibility'), {
            id: selectedWallet.value.id,
        });
    } catch (error) {
        console.error('Failed to update balance visibility:', error);
    }
}
</script>

<template>
    <Card
        class="w-full cursor-pointer select-none"
        @click="redirectToWallet(selectedWallet.type)"
    >
        <template #content>
            <div class="flex flex-col gap-5 items-center self-stretch w-full">
                <div class="flex justify-between w-full">
                    <Select
                        v-model="selectedWallet"
                        optionLabel="type"
                        class="w-full"
                        :options="wallets"
                        @click.stop
                    >
                        <template #value="slotProps">
                            <div v-if="slotProps.value" class="flex items-center">
                                <div>{{ $t(`public.${slotProps.value.type}`) }}</div>
                            </div>
                            <span v-else>{{ slotProps.placeholder }}</span>
                        </template>
                        <template #option="slotProps">
                            <div class="max-w-[200px] truncate">{{ $t(`public.${slotProps.option.type}`) }}</div>
                        </template>
                    </Select>
                    <div class="w-full flex justify-end items-center">
                        <span class="font-semibold">{{ selectedWallet.address }}</span>
                    </div>
                </div>

                <!-- Balance -->
                <div class="flex flex-col gap-1 items-start self-stretch w-full">
                    <span class="text-sm font-medium text-surface-500 dark:text-surface-400">{{ $t('public.balance') }}</span>
                    <div class="flex items-center justify-center gap-1">
                        <div class="font-medium text-3xl">
                            <div v-if="balanceVisibility">
                                <div v-if="selectedWallet.currency_symbol !== 'point'">
                                    {{ selectedWallet.currency_symbol }}
                                    <span v-html="formattedBalance"></span>
                                </div>
                                <div v-else>
                                    {{ selectedWallet.balance }} {{ $t(`public.${selectedWallet.currency_symbol}`) }}
                                </div>
                            </div>
                            <div v-else class="h-9 pt-1">
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
                            @click.stop="toggleVisibility"
                        >
                            <template #icon>
                                <IconEye v-if="balanceVisibility" size="16" />
                                <IconEyeOff v-else size="16" />
                            </template>
                        </Button>
                    </div>
                </div>
            </div>
        </template>
    </Card>
</template>
