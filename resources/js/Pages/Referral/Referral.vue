<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {ref, h} from "vue";
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import ReferralView from "@/Pages/Referral/Structure/ReferralStructure.vue";
import ReferralListing from "@/Pages/Referral/Listing/ReferralListing.vue";

const props = defineProps({
    levels: Array
})

const tabs = ref([
    {
        title: 'structure',
        component: h(ReferralView),
        value: '0'
    },
    {
        title: 'listing',
        component: h(ReferralListing, {
            levels: props.levels
        }),
        value: '1'
    }
]);

const activeIndex = ref('0');
</script>

<template>
    <AuthenticatedLayout :title="$t('public.referral')">
        <Tabs v-model:value="activeIndex">
            <TabList>
                <Tab
                    v-for="tab in tabs"
                    :key="tab.title"
                    :value="tab.value"
                >
                    {{ $t(`public.${tab.title}`) }}
                </Tab>
            </TabList>
            <TabPanels>
                <TabPanel
                    v-for="tab in tabs"
                    :key="tab.value"
                    :value="tab.value"
                >
                    <component :is="tab.component" />
                </TabPanel>
            </TabPanels>
        </Tabs>
    </AuthenticatedLayout>
</template>
