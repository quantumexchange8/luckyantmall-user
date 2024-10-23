<script setup>
import ContentLayout from "@/Layouts/ContentLayout.vue";
import Card from "primevue/card";
import {useForm} from "@inertiajs/vue3";
import InputNumber from "primevue/inputnumber";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import Chip from 'primevue/chip';
import {ref} from "vue";
import Button from "primevue/button";
import {
    IconX
} from "@tabler/icons-vue";
import Image from 'primevue/image';
import Select from 'primevue/select';
import Skeleton from 'primevue/skeleton';
import {useLangObserver} from "@/Composables/localeObserver.js";

const props = defineProps({
    walletType: String,
    wallet: Object
})

const loadingProfiles = ref(false);
const depositProfiles = ref([]);
const selectedDepositProfiles = ref();
const { locale } = useLangObserver();

const getDepositProfiles = async () => {
    loadingProfiles.value = true;
    try {
        const response = await axios.get('/getDepositProfiles');
        depositProfiles.value = response.data;
        selectedDepositProfiles.value = depositProfiles.value[0];
    } catch (error) {
        console.error('Error fetching deposit profiles:', error);
    } finally {
        loadingProfiles.value = false;
    }
};

getDepositProfiles();

const chips = ref([
    { label: '10', value: 10 },
    { label: '20', value: 20 },
    { label: '50', value: 50 },
    { label: '100', value: 100 },
    { label: '200', value: 200 },
    { label: '500', value: 500 },
]);

const handleChipClick = (chipValue) => {
    form.amount = chipValue;
};

const form = useForm({
    wallet_id: props.wallet.id,
    deposit_profile_id: '',
    amount: null,
    payment_slip: null
})

const selectedSlip = ref(null);
const selectedSlipName = ref(null);
const handleUploadSlip = (event) => {
    const paymentSlipInput = event.target;
    const file = paymentSlipInput.files[0];

    if (file) {
        // Display the selected image
        const reader = new FileReader();
        reader.onload = () => {
            selectedSlip.value = reader.result;
        };
        reader.readAsDataURL(file);
        selectedSlipName.value = file.name;
        form.payment_slip = event.target.files[0];
    } else {
        selectedSlip.value = null;
    }
};

const removeSlip = () => {
    selectedSlip.value = null;
    form.payment_slip = '';
};

const submitForm = () => {
    form.deposit_profile_id = selectedDepositProfiles.value.id;
    form.post(route('profile.submitDeposit'), {
        onSuccess: () => {
            form.reset();
        }
    })
}
</script>

<template>
    <ContentLayout
        :title="$t(`public.deposit`)"
        backRoute="profile.wallet_detail"
        :routeParam="walletType"
    >
        <form
            @submit.prevent="submitForm"
            class="flex flex-col gap-3"
        >
            <Card
                class="w-full"
            >
                <template #content>
                    <div class="flex flex-col gap-3 items-center self-stretch">
                        <!-- details -->
                        <div class="flex flex-col gap-1 items-start self-stretch">
                            <InputLabel
                                for="deposit_profile"
                            >
                                {{ $t('public.deposit_profile') }}
                            </InputLabel>

                            <Select
                                v-model="selectedDepositProfiles"
                                :options="depositProfiles"
                                optionLabel="name"
                                placeholder="Select a City"
                                class="w-full"
                                :loading="loadingProfiles"
                            >
                                <template #value="slotProps">
                                    <div v-if="slotProps.value" class="flex items-center">
                                        <div v-if="slotProps.value.type === 'bank'">
                                            {{ slotProps.value.bank_name }}
                                        </div>
                                        <div v-else>
                                            {{ slotProps.value.crypto_tether }}
                                        </div>
                                    </div>
                                    <span v-else>{{ slotProps.placeholder }}</span>
                                </template>
                                <template #option="slotProps">
                                    <div v-if="slotProps.option.type === 'bank'">
                                        {{ slotProps.option.bank_name }}
                                    </div>
                                    <div v-else>
                                        {{ slotProps.option.crypto_tether }}
                                    </div>
                                </template>
                            </Select>
                        </div>

                        <div
                            v-if="loadingProfiles"
                            class="flex flex-col gap-1 items-start self-stretch"
                        >
                            <div
                                v-for="index in 5"
                                class="flex justify-between items-center w-full text-xs"
                            >
                                <Skeleton height="0.75rem" width="5rem" class="mb-2"></Skeleton>
                                <Skeleton height="0.75rem" width="8rem" class="mb-2"></Skeleton>
                            </div>
                        </div>

                        <div v-else class="flex flex-col gap-1 items-start self-stretch">
                            <div class="flex justify-between items-start w-full text-xs">
                                <div class="text-surface-300 w-24">
                                    {{ $t('public.account_number') }}
                                </div>
                                <div class="text-right w-36 break-words">
                                    {{ selectedDepositProfiles.account_number }}
                                </div>
                            </div>

                            <!-- bank -->
                            <div v-if="selectedDepositProfiles.type === 'bank'" class="flex flex-col gap-1 items-start self-stretch">
                                <div class="flex justify-between items-center w-full text-xs">
                                    <div class="text-surface-300 w-24">
                                        {{ $t('public.receiver_name') }}
                                    </div>
                                    <div class="text-right w-36">{{ selectedDepositProfiles.name }}</div>
                                </div>
                                <div class="flex justify-between items-start w-full text-xs">
                                    <div class="text-surface-300 w-24">
                                        {{ $t('public.bank_name') }}
                                    </div>
                                    <div class="text-right w-36">
                                        {{ selectedDepositProfiles.bank_name }}
                                    </div>
                                </div>
                                <div class="flex justify-between items-center w-full text-xs">
                                    <div class="text-surface-300 w-24">
                                        {{ $t('public.bank_branch') }}
                                    </div>
                                    <div class="text-right w-36">
                                        {{ selectedDepositProfiles.bank_branch }}
                                    </div>
                                </div>
                                <div class="flex justify-between items-center w-full text-xs">
                                    <div class="text-surface-300 w-24">
                                        {{ $t('public.country') }}
                                    </div>
                                    <div class="text-right w-36">
                                        {{ locale !== 'en' ? selectedDepositProfiles.country.translations[locale] : selectedDepositProfiles.country.name }} ({{ selectedDepositProfiles.currency }})
                                    </div>
                                </div>
                            </div>

                            <!-- crypto -->
                            <div v-if="selectedDepositProfiles.type === 'crypto'" class="flex flex-col gap-1 items-start self-stretch">
                                <div class="flex justify-between items-start w-full text-xs">
                                    <div class="text-surface-300 w-24">
                                        {{ $t('public.tether') }}
                                    </div>
                                    <div class="text-right w-36 break-words">
                                        {{ selectedDepositProfiles.crypto_tether }}
                                    </div>
                                </div>
                                <div class="flex justify-between items-start w-full text-xs">
                                    <div class="text-surface-300 w-24">
                                        {{ $t('public.network') }}
                                    </div>
                                    <div class="w-44 flex gap-1 flex-wrap justify-end">
                                        <div
                                            v-for="network in selectedDepositProfiles.networks"
                                            class="flex rounded-full border border-primary-400 dark:border-surface-400 text-xs px-2"
                                        >
                                            {{ network }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </Card>

            <Card
                class="w-full"
            >
                <template #content>
                    <div class="flex flex-col gap-3 items-center self-stretch">
                        <!-- amount -->
                        <div class="flex flex-col gap-1 items-start self-stretch">
                            <InputLabel
                                for="amount"
                                :value="$t('public.amount')"
                                :invalid="!!form.errors.amount"
                            />

                            <InputNumber
                                v-model="form.amount"
                                inputId="amount"
                                mode="currency"
                                :currency="wallet.currency"
                                locale="zh-CN"
                                fluid
                            />

                            <InputError :message="form.errors.amount" />
                        </div>

                        <!-- amount chips -->
                        <div class="grid grid-cols-3 gap-2 w-full">
                            <div v-for="(chip, index) in chips" :key="index">
                                <Chip
                                    :label="chip.label"
                                    class="w-full text-primary-500 dark:text-white whitespace-nowrap overflow-hidden"
                                    :class="{
                                        'border border-primary-300 dark:bg-surface-800 bg-primary-50 text-primary-500 hover:bg-primary-50': form.amount === chip.value,
                                        'border border-surface-300 dark:border-surface-700': form.amount !== chip.value,
                                    }"
                                    @click="handleChipClick(chip.value)"
                                />
                            </div>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- upload slip -->
            <div class="flex flex-col gap-1 items-start self-stretch">
                <InputLabel
                    for="name"
                    :value="$t('public.payment_slip')"
                    :invalid="!!form.errors.name"
                />
                <div class="flex flex-col gap-3">
                    <input
                        ref="paymentSlipInput"
                        id="payment_slip"
                        type="file"
                        class="hidden"
                        accept="image/*"
                        @change="handleUploadSlip"
                    />
                    <Button
                        type="button"
                        label="Browse"
                        severity="secondary"
                        size="small"
                        @click="$refs.paymentSlipInput.click()"
                    />
                </div>
                <InputError :message="form.errors.payment_slip" />

                <div
                    v-if="selectedSlip"
                    class="relative w-full py-3 pl-4 flex items-center justify-between rounded-lg bg-primary-50 dark:bg-surface-900"
                >
                    <div class="inline-flex items-center gap-3">
                        <Image
                            :src="selectedSlip"
                            alt="Image"
                            imageClass="max-w-full h-9 object-contain rounded"
                            preview
                        />
                        <div class="text-sm text-surface-900 dark:text-white">
                            {{ selectedSlipName }}
                        </div>
                    </div>
                    <Button
                        type="button"
                        severity="danger"
                        text
                        rounded
                        aria-label="Remove"
                        size="small"
                        @click="removeSlip"
                    >
                        <template #icon>
                            <IconX size="16" />
                        </template>
                    </Button>
                </div>
            </div>

            <div class="pt-8">
                <Button
                    type="submit"
                    size="small"
                    aria-label="Deposit"
                    class="w-full"
                    label="Confirm"
                />
            </div>
        </form>
    </ContentLayout>
</template>
