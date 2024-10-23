<script setup>
import ContentLayout from "@/Layouts/ContentLayout.vue";
import Card from "primevue/card";
import Divider from "primevue/divider";
import {
    IconEye,
    IconEyeOff
} from "@tabler/icons-vue";
import Button from "primevue/button";
import WalletAction from "@/Pages/Profile/Wallets/WalletAction.vue";

const props = defineProps({
    walletType: String,
    wallet: Object
})
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
                    <div class="flex flex-col gap-3 items-center self-stretch">
                        <div class="flex flex-col w-full">
                            <span class="text-xs dark:text-surface-400">{{ $t('public.balance') }} ({{ wallet.address }})</span>
                            <div class="flex items-center justify-between">
                                <div class="text-lg font-semibold">
                                    <span v-if="wallet.currency_symbol !== 'point'">{{ wallet.currency_symbol }}{{ wallet.balance }}</span>
                                    <span v-else>{{ wallet.balance }} {{ $t(`public.${wallet.currency_symbol}`) }}</span>
                                </div>
                                <Button
                                    severity="contrast"
                                    text
                                    rounded
                                    aria-label="Back"
                                    size="small"
                                >
                                    <template #icon>
                                        <IconEye v-if="wallet.balance_visibility" size="16" />
                                        <IconEyeOff v-else size="16" />
                                    </template>
                                </Button>
                            </div>
                        </div>
                        <Divider />
                        <WalletAction
                            :wallet="wallet"
                        />
                    </div>
                </template>
            </Card>
<!--            <Card-->
<!--                class="w-full hidden md:block self-stretch"-->
<!--            >-->
<!--                <template #content>-->
<!--                    <div class="flex flex-col gap-3 items-start self-stretch">-->
<!--                        <span class="text-xl font-semibold">$10000</span>-->
<!--                        <span class="text-xs">Last month</span>-->
<!--                    </div>-->
<!--                </template>-->
<!--            </Card>-->
        </div>

        <Card
            class="w-full"
        >
            <template #content>
                <div class="flex flex-col gap-3 items-center self-stretch">
                    Transaction
                </div>
            </template>
        </Card>
    </ContentLayout>
</template>
