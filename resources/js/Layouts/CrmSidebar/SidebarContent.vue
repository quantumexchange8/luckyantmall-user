<script setup>
import SidebarLink from '@/Layouts/CrmSidebar/SidebarLink.vue'
import SidebarCollapsible from '@/Layouts/CrmSidebar/SidebarCollapsible.vue'
import SidebarCollapsibleItem from '@/Layouts/CrmSidebar/SidebarCollapsibleItem.vue'
import { sidebarState } from "@/Composables/index.js";
import {onMounted, ref, watchEffect} from "vue";
import {usePage} from "@inertiajs/vue3";
import {
    IconLayoutDashboard,
    IconUsers,
    IconUsersGroup,
    IconReportAnalytics,
    IconChartInfographic,
    IconCoinMonero,
    IconHistory,
    IconClockDollar,
    IconAdjustmentsDollar,
    IconTag
} from '@tabler/icons-vue';
import SidebarCategoryLabel from "@/Layouts/CrmSidebar/SidebarCategoryLabel.vue";
import ScrollPanel from "primevue/scrollpanel";
//
// const pendingWithdrawals = ref(0);
// const pendingPammAllocate = ref(0);
// const pendingBonusWithdrawal = ref(0);
//
// const getPendingCounts = async () => {
//     try {
//         const response = await axios.get('/getPendingCounts');
//         pendingWithdrawals.value = response.data.pendingWithdrawals
//         pendingPammAllocate.value = response.data.pendingPammAllocate
//         pendingBonusWithdrawal.value = response.data.pendingBonusWithdrawal
//     } catch (error) {
//         console.error('Error pending counts:', error);
//     }
// };
//
// onMounted(() => {
//     getPendingCounts();
// })
//
// watchEffect(() => {
//     if (usePage().props.toast !== null) {
//         getPendingCounts();
//     }
// });
</script>

<template>
    <ScrollPanel style=" width: 100%; height: 100%;">
        <!-- Dashboard -->
        <SidebarLink
            :title="$t('public.dashboard')"
            :href="route('dashboard')"
            :active="route().current('dashboard')"
        >
            <template #icon>
                <IconLayoutDashboard :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <SidebarCategoryLabel
            :title="$t('public.market')"
        />

        <!-- Referrals -->
        <SidebarLink
            :title="$t('public.referral')"
            :href="route('referral')"
            :active="route().current('referral')"
        >
            <template #icon>
                <IconUsers :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <!-- Network -->
        <SidebarLink
            :title="$t('public.network')"
        >
            <template #icon>
                <IconUsersGroup :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <SidebarCategoryLabel
            :title="$t('public.analytic')"
        />

        <!-- Trade History -->
        <SidebarLink
            :title="$t('public.trade_history')"
            :href="route('trade_history')"
            :active="route().current('trade_history')"
        >
            <template #icon>
                <IconReportAnalytics :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>

        <!-- Rebate History -->
        <SidebarLink
            :title="$t('public.rebate_history')"
        >
            <template #icon>
                <IconChartInfographic :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>
    </ScrollPanel>
</template>
