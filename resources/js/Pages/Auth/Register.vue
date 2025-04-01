<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import {isDark, toggleDarkMode} from "@/Composables/index.js";
import {
    IconMoon,
    IconSun,
    IconCircleCheck,
} from "@tabler/icons-vue";
import Button from "primevue/button";
import ChangeLocale from "@/Components/Navbar/ChangeLocale.vue";
import {computed, ref} from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import InputText from "primevue/inputtext";
import Select from 'primevue/select';
import Password from 'primevue/password';
import Checkbox from 'primevue/checkbox';
import {VueScrollPicker} from "vue-scroll-picker";
import DatePicker from "@/Components/DatePicker.vue";
import ToastList from "@/Components/ToastList.vue";

const props = defineProps({
    referral_code: String
})

const formSteps = ref([
    {
        step: 1,
        title: 'basic_information',
        caption_1: 'register_step_1_desc',
        caption_2: 'register_step_1_desc_2',
        state: 'active',
        selected: false
    },
    {
        step: 2,
        title: 'credentials',
        caption_1: 'register_step_2_desc',
        caption_2: 'register_step_2_desc_2',
        state: 'inactive',
        selected: false
    },
    {
        step: 3,
        title: 'referral_program',
        caption_1: 'register_step_3_desc',
        caption_2: 'register_step_3_desc_3',
        state: 'inactive',
        selected: false
    },
]);

const selectedStep = computed(() => formSteps.value.find(step => step.selected));

// Function to set the selected step and update state
const selectStep = (stepNumber) => {
    formSteps.value.forEach(step => {
        step.selected = step.step === stepNumber;
        step.state = step.step <= stepNumber ? 'active' : 'inactive';
    });
};

// Initially select the first step
selectStep(1);

const form = useForm({
    step: '',
    name: '',
    username: '',
    email: '',
    dob: '',
    country: '',
    dial_code: '',
    phone: '',
    phone_number: '',
    password: '',
    password_confirmation: '',
    referral_code: props.referral_code ? props.referral_code : '',
    terms: '',
})

const loadingCountries = ref(false);
const countries = ref([]);
const selectedCountry = ref();
const selectedPhoneCode = ref();

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

getCountries();

const handleContinue = () => {
    form.country = selectedCountry.value;
    form.dial_code = selectedCountry.value;
    if (selectedPhoneCode.value) {
        form.phone_number = selectedCountry.value.phone_code + form.phone;
    }
    form.step = selectedStep.value.step;
    form.post(route('register'), {
        onSuccess: () => {
            selectStep(selectedStep.value.step + 1);
        }
    });
};

const handleBack = () => {
    if (selectedStep.value.step > 1) {
        selectStep(selectedStep.value.step - 1);
    }
};
</script>

<template>
    <Head :title="$t('public.register')" />

    <div
        class="flex justify-center min-h-screen bg-white dark:bg-surface-950"
    >
        <ToastList />
        <div class="w-full flex">
            <!-- Col -->
            <div class="hidden md:flex flex-col items-start gap-20 px-5 bg-white border-r-2 border-surface-200 dark:bg-surface-950 dark:border-surface-700 min-w-80">
                <Link href="/" class="w-full flex items-center h-16">
                    <div class="flex items-center self-stretch gap-3">
                        <div class="flex shrink-0 items-center">
                            <ApplicationLogo aria-hidden="true" class="w-auto h-9 fill-logo" />
                        </div>
                        <div
                            class="text-lg font-bold text-logo dark:text-white w-full"
                        >
                            {{ $t('public.luckyant_mall') }}
                        </div>
                    </div>
                </Link>

                <!-- stepper -->
                <div class="flex flex-col gap-8 items-center">
                    <div
                        v-for="(step, index) in formSteps"
                        :key="index"
                        class="flex items-start gap-4 self-stretch"
                    >
                        <div
                            :class="step.step === selectedStep.step && step.state === 'active' ? 'text-primary' : 'text-surface-400 dark:text-surface-600'"
                        >
                            <IconCircleCheck size="28" stroke-width="1.5" />
                        </div>
                        <div class="flex flex-col items-start">
                            <div
                                class="font-semibold"
                                :class="step.state === 'active' ? 'text-surface-950 dark:text-white' : 'text-surface-500'"
                            >
                                {{ $t(`public.${step.title}`) }}
                            </div>
                            <div
                                class="text-sm"
                                :class="step.state === 'active' ? 'text-surface-700 dark:text-surface-400' : 'text-surface-400 dark:text-surface-600'"
                            >
                                {{ $t(`public.${step.caption_1}`) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Col -->
            <div class="w-full flex flex-col items-center">
                <div class="flex h-16 justify-between items-center gap-2 w-full mx-auto px-3 sm:px-6 lg:px-5 xl:px-0">
                    <Link :href="route('home')">
                        <ApplicationLogo
                            class="block h-9 w-auto md:hidden"
                        />
                    </Link>

                    <div class="flex gap-2 items-center justify-end">
                        <ChangeLocale />

                        <Button
                            severity="secondary"
                            outlined
                            aria-label="Mode"
                            size="small"
                            @click="() => { toggleDarkMode() }"
                        >
                            <template #icon>
                                <IconSun v-if="!isDark" size="16" />
                                <IconMoon v-if="isDark" size="16" />
                            </template>
                        </Button>
                    </div>
                </div>

                <!-- Register form -->
                <div class="py-5 px-3 flex flex-col items-center gap-8 w-full md:max-w-md">
                    <div
                        class="flex flex-col justify-center items-center gap-8"
                    >
                        <!-- caption -->
                        <div
                            class="flex flex-col gap-1 text-center items-center self-stretch"
                        >
                            <div class="font-bold text-surface-950 dark:text-white w-full text-lg md:text-xl">
                                {{ $t(`public.${selectedStep.title}`) }}
                            </div>
                            <div class="text-surface-500 w-full text-sm md:text-base">
                                {{ $t(`public.${selectedStep.caption_2}`) }}
                            </div>
                        </div>
                    </div>

                    <form class="flex flex-col items-center gap-6 self-stretch">
                        <!-- Basic information -->
                        <template v-if="selectedStep.step === 1">
                            <div class="flex flex-col items-center gap-3 self-stretch pb-6">
                                <!-- name -->
                                <div class="flex flex-col gap-1 items-start self-stretch">
                                    <InputLabel
                                        for="name"
                                        :value="$t('public.full_name')"
                                        :invalid="!!form.errors.name"
                                    />

                                    <InputText
                                        id="name"
                                        type="text"
                                        class="block w-full"
                                        v-model="form.name"
                                        autofocus
                                        :placeholder="$t('public.full_name_placeholder')"
                                        :invalid="!!form.errors.name"
                                        autocomplete="name"
                                    />

                                    <InputError :message="form.errors.name" />
                                </div>

                                <!-- username -->
                                <div class="flex flex-col gap-1 items-start self-stretch">
                                    <InputLabel
                                        for="username"
                                        :value="$t('public.username')"
                                        :invalid="!!form.errors.username"
                                    />

                                    <InputText
                                        id="username"
                                        type="text"
                                        class="block w-full"
                                        v-model="form.username"
                                        :placeholder="$t('public.username_placeholder')"
                                        :invalid="!!form.errors.username"
                                        autocomplete="username"
                                    />

                                    <InputError :message="form.errors.username" />
                                </div>

                                <!-- email -->
                                <div class="flex flex-col gap-1 items-start self-stretch">
                                    <InputLabel
                                        for="email"
                                        :value="$t('public.email')"
                                        :invalid="!!form.errors.email"
                                    />

                                    <InputText
                                        id="email"
                                        type="email"
                                        class="block w-full"
                                        v-model="form.email"
                                        :placeholder="$t('public.email_placeholder')"
                                        :invalid="!!form.errors.email"
                                        autocomplete="email"
                                    />

                                    <InputError :message="form.errors.email" />
                                </div>

                                <!-- dob -->
                                <div class="flex flex-col gap-1 items-start self-stretch">
                                    <InputLabel
                                        for="dob"
                                        :value="$t('public.dob')"
                                        :invalid="!!form.errors.dob"
                                    />
                                    <DatePicker
                                        :invalid="!!form.errors.dob"
                                        @update:date="form.dob = $event"
                                    />
                                    <InputError :message="form.errors.dob" />
                                </div>

                                <!-- country -->
                                <div class="flex flex-col gap-1 items-start self-stretch">
                                    <InputLabel
                                        for="country"
                                        :value="$t('public.country')"
                                        :invalid="!!form.errors.country"
                                    />

                                    <Select
                                        v-model="selectedCountry"
                                        :options="countries"
                                        filter
                                        optionLabel="name"
                                        :placeholder="$t('public.select_country')"
                                        class="w-full"
                                        :loading="loadingCountries"
                                        :invalid="!!form.errors.country"
                                    >
                                        <template #value="slotProps">
                                            <div v-if="slotProps.value" class="flex items-center">
                                                <div>{{ slotProps.value.name }}</div>
                                            </div>
                                            <span v-else>{{ slotProps.placeholder }}</span>
                                        </template>
                                        <template #option="slotProps">
                                            <div class="max-w-[200px] truncate">{{ slotProps.option.name }}</div>
                                        </template>
                                    </Select>

                                    <InputError :message="form.errors.country" />
                                </div>

                                <!-- phone_number -->
                                <div class="flex flex-col gap-1 items-start self-stretch">
                                    <InputLabel
                                        for="phone_number"
                                        :value="$t('public.phone_number')"
                                        :invalid="!!form.errors.dial_code || !!form.errors.phone_number"
                                    />
                                    <div class="flex gap-2 items-center self-stretch relative">
                                        <Select
                                            v-model="selectedPhoneCode"
                                            :options="countries"
                                            filter
                                            :filterFields="['name', 'iso2', 'phone_code']"
                                            optionLabel="name"
                                            :placeholder="$t('public.phone_code')"
                                            class="w-[100px]"
                                            :loading="loadingCountries"
                                            :invalid="!!form.errors.dial_code"
                                        >
                                            <template #value="slotProps">
                                                <div v-if="slotProps.value" class="flex items-center">
                                                    <div>{{ slotProps.value.phone_code }}</div>
                                                </div>
                                                <span v-else>{{ slotProps.placeholder }}</span>
                                            </template>
                                            <template #option="slotProps">
                                                <div class="flex items-center gap-1">
                                                    <div class="font-medium">{{ slotProps.option.iso2 }}</div>
                                                    <div class="max-w-[200px] truncate text-surface-500">({{ slotProps.option.phone_code }})</div>
                                                </div>
                                            </template>
                                        </Select>

                                        <InputText
                                            id="phone"
                                            type="text"
                                            class="block w-full"
                                            v-model="form.phone"
                                            :placeholder="$t('public.phone_number')"
                                            :invalid="!!form.errors.phone"
                                        />
                                    </div>
                                    <InputError :message="form.errors.phone_number" />
                                </div>
                            </div>
                        </template>

                        <!-- Credentials -->
                        <template v-if="selectedStep.step === 2">
                            <div class="flex flex-col items-center gap-3 self-stretch pb-6">

                                <!-- password -->
                                <div class="flex flex-col gap-1 items-start self-stretch">
                                    <InputLabel
                                        for="password"
                                        :value="$t('public.password')"
                                    />
                                    <Password
                                        v-model="form.password"
                                        toggleMask
                                        placeholder="••••••••"
                                        :invalid="!!form.errors.password"
                                    />
                                    <InputError :message="form.errors.password" />
                                    <span class="text-xs text-gray-500">{{ $t('public.password_desc') }}</span>
                                </div>

                                <!-- confirm password -->
                                <div class="flex flex-col gap-1 items-start self-stretch">
                                    <InputLabel
                                        for="password_confirmation"
                                        :value="$t('public.confirm_password')"
                                    />
                                    <Password
                                        v-model="form.password_confirmation"
                                        toggleMask
                                        placeholder="••••••••"
                                        :invalid="!!form.errors.password"
                                    />
                                </div>
                            </div>
                        </template>

                        <!-- Referral Program -->
                        <template v-if="selectedStep.step === 3">
                            <div class="flex flex-col items-center gap-3 self-stretch pb-6">
                                <!-- referral code -->
                                <div class="flex flex-col gap-1 items-start self-stretch">
                                    <InputLabel
                                        for="referral_code"
                                        :invalid="!!form.errors.referral_code"
                                    >
                                        {{ $t('public.referral_code') }}
                                    </InputLabel>

                                    <InputText
                                        id="referral_code"
                                        type="text"
                                        class="block w-full"
                                        v-model="form.referral_code"
                                        :placeholder="$t('public.referral_code_placeholder')"
                                        :invalid="!!form.errors.referral_code"
                                    />

                                    <InputError :message="form.errors.referral_code" />
                                </div>

                                <!-- terms -->
                                <div class="flex flex-col gap-1 items-start self-stretch">
                                    <div class="flex gap-2 items-start self-stretch">
                                        <Checkbox
                                            v-model="form.terms"
                                            inputId="terms"
                                            value="terms"
                                        />
                                        <label for="terms" class="text-xs text-surface-500 dark:text-surface-400">{{ $t('public.terms_and_conditions') }}</label>
                                    </div>
                                    <InputError :message="form.errors.terms" />
                                </div>
                            </div>
                        </template>

                        <div class="flex flex-col gap-1 items-center self-stretch">
                            <Button
                                type="submit"
                                :label="selectedStep.step === 3 ? $t('public.create_an_account') : $t('public.continue')"
                                class="w-full"
                                @click="handleContinue"
                                :disabled="form.processing"
                            />

                            <div
                                v-if="selectedStep.step === 1"
                                class="flex items-center gap-3"
                            >
                                <span class="text-sm text-surface-600 dark:text-surface-400">{{ $t('public.already_have_an_account') }}</span>
                                <Link
                                    :href="route('login')"
                                    class="text-sm text-primary-500 font-semibold"
                                >
                                    {{ $t('public.login') }}
                                </Link>
                            </div>

                            <div
                                v-else
                                class="w-full"
                            >
                                <Button
                                    type="button"
                                    class="w-full"
                                    severity="secondary"
                                    @click="handleBack"
                                    :disabled="form.processing"
                                >
                                    {{ $t('public.back_to_previous_page')}}
                                </Button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
