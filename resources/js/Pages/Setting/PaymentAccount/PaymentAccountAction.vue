<script setup>
import Button from "primevue/button"
import {
    IconPencil,
    IconDots,
} from "@tabler/icons-vue";
import {h, ref} from "vue";
import TieredMenu from "primevue/tieredmenu";
import Dialog from "primevue/dialog";
import UpdatePaymentAccount from "@/Pages/Setting/PaymentAccount/UpdatePaymentAccount.vue";

const props = defineProps({
    paymentAccount: Object,
});

const menu = ref();
const visible = ref(false);
const dialogType = ref('');

const items = ref([
    {
        label: 'edit',
        icon: h(IconPencil),
        command: () => {
            visible.value = true;
            dialogType.value = 'edit';
        },
    },
]);

const toggle = (event) => {
    menu.value.toggle(event);
};
</script>

<template>
    <Button
        severity="secondary"
        outlined
        rounded
        size="small"
        class="!p-2 min-w-[34px]"
        @click="toggle"
        aria-haspopup="true"
        aria-controls="overlay_tmenu"
    >
        <IconDots size="16" stroke-width="1.25" color="#737373" />
    </Button>

    <!-- Menu -->
    <TieredMenu ref="menu" id="overlay_tmenu" :model="items" popup>
        <template #item="{ item, props }">
            <div
                class="flex items-center gap-3 self-stretch"
                v-bind="props.action"
                :class="{ 'hidden': item.disabled }"
            >
                <component :is="item.icon" size="20" stroke-width="1.25" :color="item.label === 'delete_account' ? '#F04438' : '#737373'" />
                <span class="font-medium" :class="{'text-error-500': item.label === 'delete_account'}">{{ $t(`public.${item.label}`) }}</span>
            </div>
        </template>
    </TieredMenu>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t(`public.${dialogType}`)"
        class="dialog-xs md:dialog-md"
    >
        <template v-if="dialogType === 'edit'">
            <UpdatePaymentAccount
                :paymentAccount="paymentAccount"
                @update:visible="visible = false"
            />
        </template>
    </Dialog>
</template>
