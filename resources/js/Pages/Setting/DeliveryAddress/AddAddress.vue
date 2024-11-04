<script setup>
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import Select from "primevue/select";
import ToggleSwitch from 'primevue/toggleswitch';
import {ref} from "vue";
import {useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import ScrollPanel from 'primevue/scrollpanel';

const visible = ref(false);

const openDialog = () => {
    visible.value = true;
    getCountries();
}

const loadingCountries = ref(false);
const countries = ref([]);
const selectedCountry = ref();

const getCountries = async () => {
    loadingCountries.value = true;
    try {
        const response = await axios.get('/getCountries');
        countries.value = response.data;
    } catch (error) {
        console.error('Error fetching countries:', error);
    } finally {
        loadingCountries.value = false;
    }
};

const form = useForm({
    receiver_name: '',
    receiver_phone: '',
    address: '',
    postal_code: '',
    country_id: '',
    default_address: '',
});

const submitForm = () => {
    if (selectedCountry.value) {
        form.country_id = selectedCountry.value.id;
    }
    form.post(route('setting.addDeliveryAddress'), {
        onSuccess: () => {
            closeDialog();
            form.reset();
        }
    })
}

const closeDialog = () => {
    visible.value = false;
}

const handleSuggestion = (value) => {
    form.receiver_phone = value;
}
</script>

<template>
    <Button
        type="button"
        size="small"
        class="w-full"
        outlined
        @click="openDialog"
    >
        {{ $t('public.add_address')}}
    </Button>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t('public.new_address')"
        class="dialog-xs md:dialog-md"
    >
        <form class="flex flex-col gap-5 items-start self-stretch">
            <div class="flex flex-col gap-3 items-center self-stretch">
                <span class="font-bold text-sm text-gray-950 dark:text-white w-full text-left">{{ $t('public.contact_information') }}</span>
                <div class="grid md:grid-cols-2 gap-3 w-full">
                    <!-- Receiver Name -->
                    <div class="flex flex-col items-start gap-1 self-stretch">
                        <InputLabel
                            for="receiver_name"
                            :value="$t('public.receiver_name')"
                            :invalid="!!form.errors.receiver_name"
                        />
                        <InputText
                            id="receiver_name"
                            type="text"
                            class="block w-full"
                            v-model="form.receiver_name"
                            :placeholder="$t('public.receiver_name_placeholder')"
                            :invalid="!!form.errors.receiver_name"
                        />
                        <InputError :message="form.errors.receiver_name" />
                    </div>

                    <!-- Receiver Phone -->
                    <div class="flex flex-col items-start gap-1 self-stretch">
                        <InputLabel
                            for="phone_number"
                            :value="$t('public.phone_number')"
                            :invalid="!!form.errors.receiver_phone"
                        />
                        <InputText
                            id="phone_number"
                            type="text"
                            class="block w-full"
                            v-model="form.receiver_phone"
                            :placeholder="$t('public.phone_number_placeholder')"
                            :invalid="!!form.errors.receiver_phone"
                        />
                        <InputError :message="form.errors.receiver_phone" />
                        <div class="flex items-center gap-2 text-surface-500">
                            <small>{{ $t('public.suggest') }}: {{ $page.props.auth.user.phone_number }}</small>
                            <div
                                class="text-xxs rounded px-2 border border-primary hover:bg-primary-50 dark:hover:bg-primary-900 text-primary dark:text-primary-400 dark:hover:text-primary-200 select-none cursor-pointer"
                                @click="handleSuggestion($page.props.auth.user.phone_number)"
                            >
                                {{ $t('public.select') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 items-center self-stretch">
                <span class="font-bold text-sm text-gray-950 dark:text-white w-full text-left">{{ $t('public.address_details') }}</span>
                <div class="grid md:grid-cols-2 gap-3 w-full">
                    <!-- Full address -->
                    <div class="flex flex-col md:col-span-2 items-start gap-1 self-stretch">
                        <InputLabel
                            for="full_address"
                            :value="$t('public.full_address')"
                            :invalid="!!form.errors.address"
                        />
                        <Textarea
                            id="full_address"
                            v-model="form.address"
                            autoResize
                            rows="3"
                            class="block w-full"
                            :placeholder="$t('public.enter_address_placeholder')"
                            :invalid="!!form.errors.address"
                        />
                        <InputError :message="form.errors.address" />
                    </div>

                    <!-- Postal Code -->
                    <div class="flex flex-col items-start gap-1 self-stretch">
                        <InputLabel
                            for="postal_code"
                            :value="$t('public.postal_code')"
                            :invalid="!!form.errors.postal_code"
                        />
                        <InputText
                            id="postal_code"
                            type="text"
                            class="block w-full"
                            v-model="form.postal_code"
                            :placeholder="$t('public.postal_code_placeholder')"
                            :invalid="!!form.errors.postal_code"
                        />
                        <InputError :message="form.errors.postal_code" />
                    </div>

                    <!-- Country -->
                    <div class="flex flex-col items-start gap-1 self-stretch">
                        <InputLabel
                            for="country"
                            :value="$t('public.country')"
                            :invalid="!!form.errors.country_id"
                        />
                        <Select
                            v-model="selectedCountry"
                            :options="countries"
                            filter
                            optionLabel="name"
                            :placeholder="$t('public.select_country')"
                            class="w-full"
                            :loading="loadingCountries"
                            :invalid="!!form.errors.country_id"
                        >
                            <template #value="slotProps">
                                <div v-if="slotProps.value" class="flex items-center">
                                    <div>{{ slotProps.value.name }}</div>
                                </div>
                                <span v-else>{{ slotProps.placeholder }}</span>
                            </template>
                            <template #option="slotProps">
                                <div class="flex items-center gap-1">
                                    <div>{{ slotProps.option.emoji }}</div>
                                    <div class="max-w-[200px] truncate">{{ slotProps.option.name }}</div>
                                </div>
                            </template>
                        </Select>
                        <InputError :message="form.errors.country_id" />
                    </div>

                    <!-- Default -->
                    <div class="flex flex-col items-start gap-1 self-stretch">
                        <InputLabel
                            for="default_address"
                        >
                            {{ $t('public.default_address') }}
                        </InputLabel>
                        <ToggleSwitch
                            v-model="form.default_address"
                        />
                    </div>
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
                    {{ $t('public.create') }}
                </Button>
            </div>
        </form>
    </Dialog>
</template>
