<script setup>
import MasterLayout from "@/Layouts/MasterLayout.vue";
import Card from "primevue/card";
import Wallet from "@/Pages/Profile/Wallets/Wallet.vue";
import ReferralCode from "@/Pages/Profile/Partials/ReferralCode.vue";
import {usePage} from "@inertiajs/vue3";
import Setting from "@/Pages/Setting/Setting.vue";
import Tag from "primevue/tag";
import Button from "primevue/button";
import {
    IconEdit,
} from "@tabler/icons-vue";
import OrderStatus from "@/Pages/Profile/Orders/OrderStatus.vue";

const props = defineProps({
    wallets: Object
});

const user = usePage().props.auth.user;
</script>

<template>
    <MasterLayout title="Profile">
        <div class="flex flex-col gap-3 md:gap-5 items-center self-stretch w-full">
            <div class="flex flex-col md:flex-row gap-3 md:gap-5 self-stretch w-full">
                <Card class="w-full">
                    <template #content>
                        <div class="flex flex-col gap-5 self-stretch">
                            <div class="flex gap-3 items-center self-stretch">
                                <div class="min-w-12 h-12 rounded-full bg-primary-50 dark:bg-surface-800"></div>
                                <div class="flex flex-col w-full">
                                    <span class="text-sm font-medium">{{ user.username }}</span>
                                    <span class="text-xs dark:text-surface-400">{{ user.email }}</span>
                                </div>
                                <div class="max-w-10">
                                    <Button
                                        severity="secondary"
                                        size="small"
                                        rounded
                                        as="a"
                                        :href="route('profile')"
                                        class="!p-2"
                                    >
                                        <IconEdit size="20" stroke-width="1.5" />
                                    </Button>
                                </div>
                            </div>
                            <div class="flex gap-3 items-center self-stretch w-full">
                                <div class="flex flex-col gap-1 items-start w-full">
                                    <span class="text-xs font-medium">{{ $t('public.status') }}</span>
                                    <Tag :severity="user.status === 'verified' ? 'success' : 'danger'" :value="$t(`public.${user.kyc_status}`)" />
                                </div>
                                <div class="flex flex-col gap-1 items-start w-full">
                                    <span class="text-xs font-medium">{{ $t('public.phone_number') }}</span>
                                    <span>{{ user.dial_code }} {{ user.phone }}</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Wallets -->
                <Wallet
                    :wallets="wallets"
                />
            </div>


            <!-- Order Status -->
            <OrderStatus />

            <div class="flex flex-col md:flex-row gap-3 md:gap-5 self-stretch w-full">
                <!-- Referral -->
                <Card class="w-full">
                    <template #content>
                        <ReferralCode />
                    </template>
                </Card>

                <!-- Settings -->
                <Card class="w-full">
                    <template #content>
                        <Setting />
                    </template>
                </Card>
            </div>
        </div>
    </MasterLayout>
</template>
