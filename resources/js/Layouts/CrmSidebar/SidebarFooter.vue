<script setup>
import Button from "primevue/button"
// import { MenuFoldLineLeftIcon, MenuFoldLineRightIcon } from '@/Components/Icons/outline'
import { sidebarState } from '@/Composables'
import SidebarLink from "@/Layouts/CrmSidebar/SidebarLink.vue";
import {IconShoppingBag} from "@tabler/icons-vue";
</script>

<template>
    <div class="p-2">
        <SidebarLink
            :title="$t('public.back_to_shop')"
            :href="route('home')"
        >
            <template #icon>
                <IconShoppingBag :size="20" stroke-width="1.5" />
            </template>
        </SidebarLink>
    </div>
</template>
