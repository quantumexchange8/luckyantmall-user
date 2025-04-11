<script setup>
import Card from "primevue/card";
import DatePicker from "primevue/datepicker";
import {computed, ref, watch} from "vue";
import {generalFormat} from "@/Composables/format.js";
import RebateChart from "@/Pages/Dashboard/Rebate/RebateChart.vue";

defineProps({
    totalRebate: Number
});

const date = ref(new Date());
const {formatAmount} = generalFormat();

const selectedYear = computed(() => {
    return date.value ? date.value.getFullYear() : "";
});
</script>

<template>
    <Card>
        <template #content>
            <div class="flex flex-col gap-5">
                <div class="flex items-start justify-between">
                    <div class="flex flex-col items-start self-stretch">
                        <span class="text-lg font-semibold">${{ formatAmount(totalRebate) }}</span>
                        <span class="text-sm text-surface-500">{{ $t('public.total_rebate') }}</span>
                    </div>
                    <div class="flex flex-col sm:flex-row items-start gap-4">
                        <!-- Date Picker -->
                        <DatePicker
                            v-model="date"
                            view="year"
                            dateFormat="yy"
                            inputClass="!w-20"
                            panelClass="!w-52"
                        />
                    </div>
                </div>

                <RebateChart
                    :selectedYear="selectedYear"
                />
            </div>
        </template>
    </Card>
</template>
