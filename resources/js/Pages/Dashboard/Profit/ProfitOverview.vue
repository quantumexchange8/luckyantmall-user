<script setup>
import Card from "primevue/card";
import DatePicker from "primevue/datepicker";
import SelectButton from "primevue/selectbutton";
import {computed, ref, watch} from "vue";
import {generalFormat} from "@/Composables/format.js";
import ProfitChart from "@/Pages/Dashboard/Profit/ProfitChart.vue";

defineProps({
    tradeProfit: Number
})

const date = ref(new Date());
const {formatAmount} = generalFormat();

const daysValue = ref('7');
const daysOptions = ref(["7", "14", String(getDaysInMonth(date.value))]);
const selectedDays = computed(() => Number(daysValue.value));

const selectedMonth = computed(() => {
    return date.value ? date.value.getMonth() + 1 : null;
});

const selectedYear = computed(() => {
    return date.value ? date.value.getFullYear() : "";
});

function getDaysInMonth(selectedDate) {
    if (!selectedDate) return "30";

    const year = selectedDate.getFullYear();
    const month = selectedDate.getMonth() + 1;

    return String(new Date(year, month, 0).getDate());
}

watch(date, (newDate) => {
    if (newDate) {
        daysOptions.value[2] = String(getDaysInMonth(new Date(newDate)));
    }
});
</script>

<template>
    <Card>
        <template #content>
            <div class="flex flex-col gap-5">
                <div class="flex items-start justify-between">
                    <div class="flex flex-col items-start self-stretch">
                        <span class="text-lg font-semibold">${{ formatAmount(tradeProfit) }}</span>
                        <span class="text-sm text-surface-500">{{ $t('public.total_profit') }}</span>
                    </div>
                    <div class="flex flex-col sm:flex-row items-start gap-4">
                        <!-- Days Select -->
                        <SelectButton
                            v-model="daysValue"
                            :options="daysOptions"
                        />

                        <!-- Date Picker -->
                        <DatePicker
                            v-model="date"
                            view="month"
                            dateFormat="mm/yy"
                            inputClass="!w-20"
                            panelClass="!w-44"
                        />
                    </div>
                </div>

                <ProfitChart
                    :selectedYear="selectedYear"
                    :selectedMonth="selectedMonth"
                    :selectedDays="selectedDays"
                />
            </div>
        </template>
    </Card>
</template>
