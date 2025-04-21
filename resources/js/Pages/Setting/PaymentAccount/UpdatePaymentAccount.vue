<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Select from "primevue/select";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import ToggleSwitch from "primevue/toggleswitch";
import {useForm} from "@inertiajs/vue3";
import {ref, watch} from "vue";
import {useLangObserver} from "@/Composables/localeObserver.js";

const props = defineProps({
    paymentAccount: Object
});

const form = useForm({
    payment_account_id: props.paymentAccount.id,
    payment_account_name: props.paymentAccount.payment_account_name,
    payment_platform_name: props.paymentAccount.payment_platform_name,
    account_no: props.paymentAccount.account_no,
    country_id: null,
    currency: '',
    bank_region: props.paymentAccount.bank_region,
    bank_sub_branch: props.paymentAccount.bank_sub_branch,
    bank_swift_code: props.paymentAccount.bank_swift_code,
    default_account: props.paymentAccount.default_account,
});

const loadingCountries = ref(false);
const countries = ref([]);
const selectedCountry = ref();
const selectedCurrency = ref();
const {locale} = useLangObserver();

const getCountries = async () => {
    loadingCountries.value = true;
    try {
        const response = await axios.get('/getCountries');
        countries.value = response.data;

        if (props.paymentAccount.payment_platform === 'bank') {
            selectedCountry.value = countries.value.find(country => country.id === Number(props.paymentAccount.country_id)) || null;
        }
    } catch (error) {
        console.error('Error fetching countries:', error);
    } finally {
        loadingCountries.value = false;
    }
};

getCountries();

watch(selectedCountry, (country) => {
    selectedCurrency.value = country.currency;
});

const submitForm = () => {
    if (props.paymentAccount.payment_platform === 'bank' && selectedCountry.value) {
        form.country_id = selectedCountry.value.id;
        form.currency = selectedCountry.value.currency;
    }

    form.post(route('setting.updatePaymentAccount'), {
        onSuccess: () => {
            closeDialog();
            form.reset();
        }
    })
}

const emit = defineEmits(["update:visible"])
const closeDialog = () => {
    emit('update:visible', false)
}
</script>

<template>
    <form class="flex flex-col gap-5 items-start self-stretch">
        <div class="flex flex-col gap-3 items-center self-stretch">
            <span class="font-bold text-sm text-surface-950 dark:text-white w-full text-left">{{ $t('public.payment_information') }}</span>
            <div
                v-if="paymentAccount.payment_platform === 'bank'"
                class="grid md:grid-cols-2 gap-3 w-full"
            >
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel
                        for="payment_platform_name"
                        :value="$t('public.bank_name')"
                        :invalid="!!form.errors.payment_platform_name"
                    />
                    <InputText
                        id="payment_platform_name"
                        type="text"
                        class="block w-full"
                        v-model="form.payment_platform_name"
                        :placeholder="$t('public.enter_bank_name')"
                        :invalid="!!form.errors.payment_platform_name"
                    />
                    <InputError :message="form.errors.payment_platform_name" />
                </div>

                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel
                        for="bank_region"
                        :value="$t('public.bank_region')"
                        :invalid="!!form.errors.bank_region"
                    />
                    <InputText
                        id="bank_region"
                        type="text"
                        class="block w-full"
                        v-model="form.bank_region"
                        :placeholder="$t('public.enter_state_and_city')"
                        :invalid="!!form.errors.bank_region"
                    />
                    <InputError :message="form.errors.bank_region" />
                </div>

                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel
                        for="payment_account_name"
                        :value="$t('public.account_name')"
                        :invalid="!!form.errors.payment_account_name"
                    />
                    <InputText
                        id="payment_account_name"
                        type="text"
                        class="block w-full"
                        v-model="form.payment_account_name"
                        :placeholder="$t('public.enter_account_name')"
                        :invalid="!!form.errors.payment_account_name"
                    />
                    <InputError :message="form.errors.payment_account_name" />
                </div>

                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel
                        for="account_no"
                        :value="$t('public.account_number')"
                        :invalid="!!form.errors.account_no"
                    />
                    <InputText
                        id="account_no"
                        type="text"
                        class="block w-full"
                        v-model="form.account_no"
                        :placeholder="$t('public.enter_account_number')"
                        :invalid="!!form.errors.account_no"
                    />
                    <InputError :message="form.errors.account_no" />
                </div>

                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel
                        for="bank_sub_branch"
                        :value="$t('public.bank_branch')"
                        :invalid="!!form.errors.bank_sub_branch"
                    />
                    <InputText
                        id="bank_sub_branch"
                        type="text"
                        class="block w-full"
                        v-model="form.bank_sub_branch"
                        :placeholder="$t('public.enter_bank_branch')"
                        :invalid="!!form.errors.bank_sub_branch"
                    />
                    <InputError :message="form.errors.bank_sub_branch" />
                </div>

                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel
                        for="bank_swift_code"
                        :value="$t('public.swift_code')"
                        :invalid="!!form.errors.bank_swift_code"
                    />
                    <InputText
                        id="bank_swift_code"
                        type="text"
                        class="block w-full"
                        v-model="form.bank_swift_code"
                        :placeholder="$t('public.enter_swift_code')"
                        :invalid="!!form.errors.bank_swift_code"
                    />
                    <InputError :message="form.errors.bank_swift_code" />
                </div>

                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel
                        for="country_id"
                        :value="$t('public.country')"
                        :invalid="!!form.errors.country_id"
                    />
                    <Select
                        v-model="selectedCountry"
                        :options="countries"
                        :loading="loadingCountries"
                        optionLabel="name"
                        :placeholder="$t('public.select_country')"
                        class="block w-full"
                        :invalid="!!form.errors.country_id"
                        filter
                        :filter-fields="['name', 'iso2', 'translations']"
                    >
                        <template #value="slotProps">
                            <div v-if="slotProps.value" class="flex items-center">
                                <div>{{ JSON.parse(slotProps.value.translations)[locale] || slotProps.value.name }}</div>
                            </div>
                            <span v-else class="text-surface-400 dark:text-surface-500">{{ slotProps.placeholder }}</span>
                        </template>
                        <template #option="slotProps">
                            <div class="flex items-center gap-1">
                                <img
                                    v-if="slotProps.option.iso2"
                                    :src="`https://flagcdn.com/w40/${slotProps.option.iso2.toLowerCase()}.png`"
                                    :alt="slotProps.option.iso2"
                                    width="18"
                                    height="12"
                                />
                                <div class="max-w-[200px] truncate">{{ JSON.parse(slotProps.option.translations)[locale] || slotProps.option.name }}</div>
                            </div>
                        </template>
                    </Select>
                    <InputError :message="form.errors.country_id" />
                </div>

                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel
                        for="currency"
                        :value="$t('public.currency')"
                        :invalid="!!form.errors.currency"
                    />
                    <InputText
                        id="currency"
                        type="text"
                        class="block w-full"
                        v-model="selectedCurrency"
                        :placeholder="$t('public.currency')"
                        disabled
                        :invalid="!!form.errors.currency"
                    />
                    <InputError :message="form.errors.currency" />
                </div>
            </div>

            <div
                v-else-if="paymentAccount.payment_platform === 'crypto'"
                class="grid md:grid-cols-2 gap-3 w-full"
            >
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel
                        for="payment_platform_name"
                        :value="$t('public.crypto_network')"
                        :invalid="!!form.errors.payment_platform_name"
                    />
                    <InputText
                        id="payment_platform_name"
                        type="text"
                        class="block w-full"
                        disabled
                        v-model="form.payment_platform_name"
                        :invalid="!!form.errors.payment_platform_name"
                    />
                    <InputError :message="form.errors.payment_platform_name" />
                </div>
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel
                        for="payment_account_name"
                        :value="$t('public.wallet_name')"
                        :invalid="!!form.errors.payment_account_name"
                    />
                    <InputText
                        id="payment_account_name"
                        type="text"
                        class="block w-full"
                        v-model="form.payment_account_name"
                        :placeholder="$t('public.enter_wallet_name')"
                        :invalid="!!form.errors.payment_account_name"
                    />
                    <InputError :message="form.errors.payment_account_name" />
                </div>
                <div class="flex flex-col items-start gap-1 self-stretch md:col-span-2">
                    <InputLabel
                        for="account_no"
                        :value="$t('public.token_address')"
                        :invalid="!!form.errors.account_no"
                    />
                    <InputText
                        id="account_no"
                        type="text"
                        class="block w-full"
                        v-model="form.account_no"
                        :placeholder="$t('public.enter_token_address')"
                        :invalid="!!form.errors.account_no"
                    />
                    <InputError :message="form.errors.account_no" />
                </div>
            </div>

            <!-- Default -->
            <div class="flex flex-col items-start gap-1 self-stretch">
                <InputLabel
                    for="default_account"
                >
                    {{ $t('public.default_account') }}
                </InputLabel>
                <ToggleSwitch
                    v-model="form.default_account"
                />
            </div>
        </div>

        <div class="flex gap-3 justify-end self-stretch w-full">
            <Button
                type="button"
                severity="secondary"
                outlined
                @click="closeDialog"
                :disabled="form.processing"
                class="w-full md:w-auto"
                size="small"
            >
                {{ $t('public.cancel') }}
            </Button>
            <Button
                @click="submitForm"
                :disabled="form.processing"
                class="w-full md:w-auto"
                size="small"
            >
                {{ $t('public.update') }}
            </Button>
        </div>
    </form>
</template>
