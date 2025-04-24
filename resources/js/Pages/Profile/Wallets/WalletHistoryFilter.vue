<script setup>
import DatePicker from "primevue/datepicker";
import Button from "primevue/button";
import Drawer from "primevue/drawer";
import Checkbox from "primevue/checkbox";
import {IconXboxX, IconAdjustments} from "@tabler/icons-vue";
import {ref, watch} from "vue";
import dayjs from "dayjs";

const props = defineProps({
    types: {
        type: [Array, Object]
    },
})

const selectedDate = ref([
    dayjs().startOf('month').toDate(),
    dayjs().endOf('day').toDate(),
]);

const clearDate = () => {
    selectedDate.value = [];
}

const visible = ref(false);

const openDrawer = () => {
    visible.value = true;
}

const selectedTypes = ref([]);
const selectedStatus = ref([]);

const statusTypes = [
    'success',
    'fail',
    'processing'
]

const emit = defineEmits(['update:filterTypes'])

watch([selectedDate, selectedTypes, selectedStatus], ([newDate, newTypes, newStatus]) => {
    emit('update:filterTypes', {
        selectedDate: newDate,
        selectedTypes: newTypes,
        selectedStatus: newStatus,
    });
}, { immediate: true });
</script>

<template>
    <div class="flex gap-3 items-center self-stretch">
        <div class="relative w-full">
            <DatePicker
                v-model="selectedDate"
                dateFormat="yy-mm-dd"
                class="w-full"
                selectionMode="range"
                placeholder="YYYY-MM-DD - YYYY-MM-DD"
            />
            <div
                v-if="selectedDate && selectedDate.length > 0"
                class="absolute top-2/4 -mt-1.5 right-2 text-surface-400 select-none cursor-pointer bg-transparent"
                @click="clearDate"
            >
                <IconXboxX size="12" stoke-width="1.5" />
            </div>
        </div>
        <Button
            type="button"
            severity="info"
            size="small"
            class="flex gap-2 min-w-20 !py-[7px]"
            outlined
            @click="openDrawer"
        >
            <IconAdjustments size="14" />
            {{ $t('public.filter') }}
        </Button>
    </div>

    <Drawer
        v-model:visible="visible"
        :header="$t('public.filter')"
        class="w-full md:max-w-[360px] pb-24 !h-3/5"
        position="bottom"
    >
        <div class="flex flex-col gap-3 items-center self-stretch">
            <!-- Filter Type -->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-xs text-surface-950 dark:text-white font-semibold">
                    {{ $t('public.filter_by_type') }}
                </div>
                <div
                    v-for="type of types"
                    class="flex items-center gap-2 w-full"
                >
                    <Checkbox
                        v-model="selectedTypes"
                        :inputId="type"
                        name="type"
                        :value="type"
                    />
                    <label :for="type" class="text-xs">{{ $t(`public.${type}`) }}</label>
                </div>
            </div>

            <!-- Filter Status -->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-xs text-surface-950 dark:text-white font-semibold">
                    {{ $t('public.filter_by_status') }}
                </div>
                <div
                    v-for="status of statusTypes"
                    class="flex items-center gap-2 w-full"
                >
                    <Checkbox
                        v-model="selectedStatus"
                        :inputId="status"
                        name="status"
                        :value="status"
                    />
                    <label :for="status" class="text-xs">{{ $t(`public.${status}`) }}</label>
                </div>
            </div>
        </div>
    </Drawer>
</template>
