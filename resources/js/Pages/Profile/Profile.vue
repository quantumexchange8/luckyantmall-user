<script setup>
import MasterLayout from "@/Layouts/MasterLayout.vue";
import Card from "primevue/card";
import Wallet from "@/Pages/Profile/Wallets/Wallet.vue";
import ReferralCode from "@/Pages/Profile/Partials/ReferralCode.vue";

const props = defineProps({
    walletCounts: Number
})
</script>

<template>
    <MasterLayout title="Profile">
        <div class="flex flex-col gap-3 md:gap-5 items-center self-stretch w-full">
            <Card class="w-full">
                <template #content>
                    <div class="flex gap-3 items-center self-stretch">
                        <div class="w-8 h-8 md:w-12 md:h-12 rounded-full bg-primary-50 dark:bg-surface-800"></div>
                        <div class="flex flex-col">
                            <span class="text-sm font-medium">name</span>
                            <span class="text-xs dark:text-surface-400">email@email.com</span>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Wallets -->
            <Wallet
                :walletCounts="walletCounts"
            />

            <!-- Order Status -->
            <div class="text-sm w-full text-left">Order Listing</div>
            <div class="flex justify-evenly items-center self-stretch w-full">
                <div class="flex flex-col">
                    <div class="w-8 h-8 md:w-12 md:h-12 rounded-full bg-primary-50 dark:bg-surface-800"></div>
                    <span class="text-xs">Orders</span>
                </div>
                <div class="flex flex-col">
                    <div class="w-8 h-8 md:w-12 md:h-12 rounded-full bg-primary-50 dark:bg-surface-800"></div>
                    <span class="text-xs">Orders</span>
                </div>
                <div class="flex flex-col">
                    <div class="w-8 h-8 md:w-12 md:h-12 rounded-full bg-primary-50 dark:bg-surface-800"></div>
                    <span class="text-xs">Orders</span>
                </div>
                <div class="flex flex-col">
                    <div class="w-8 h-8 md:w-12 md:h-12 rounded-full bg-primary-50 dark:bg-surface-800"></div>
                    <span class="text-xs">Orders</span>
                </div>
            </div>

            <!-- Referral -->
            <Card class="w-full">
                <template #content>
                    <ReferralCode />
                </template>
            </Card>

            <!-- Settings -->
            <Card class="w-full">
                <template #content>
                    <div class="text-xs w-full text-left">Settings</div>
                </template>
            </Card>
        </div>
    </MasterLayout>
</template>
