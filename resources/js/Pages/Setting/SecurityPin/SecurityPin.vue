<script setup>
import ContentLayout from "@/Layouts/ContentLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputOtp from 'primevue/inputotp';
import Button from 'primevue/button';
import {useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import ForgetPin from "@/Pages/Setting/SecurityPin/ForgetPin.vue";

defineProps({
    backRoute: String,
});

const form = useForm({
    current_pin: '',
    pin: '',
    pin_confirmation: '',
});

const submitForm = () => {
    form.post(route('setting.updateSecurityPin'))
}
</script>

<template>
    <ContentLayout
        :title="$t('public.security_pin')"
        :backRoute="backRoute"
    >
        <form class="flex flex-col gap-5 items-center self-stretch">
            <div class="p-3 md:p-5 flex flex-col items-center bg-surface-50 dark:bg-surface-800 w-full">
                <div class="text-sm font-semibold dark:text-white">
                    {{ $t('public.setup_pin') }}
                </div>
                <div class="text-xs text-surface-500 dark:text-surface-400">
                    {{ $t('public.setup_pin_caption') }}
                </div>
            </div>

            <div class="flex flex-col gap-3 items-center self-stretch">
                <div
                    v-if="$page.props.auth.user.security_pin !== null"
                    class="flex flex-col items-start gap-1 self-stretch"
                >
                    <InputLabel
                        for="current_pin"
                        :value="$t('public.current_pin_code')"
                        :invalid="!!form.errors.current_pin"
                    />
                    <InputOtp
                        v-model="form.current_pin"
                        input-id="current_pin"
                        mask
                        integerOnly
                        :length="6"
                    />
                    <InputError :message="form.errors.current_pin" />
                </div>

                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel
                        for="pin"
                        :value="$t('public.pin_code')"
                        :invalid="!!form.errors.pin"
                    />
                    <InputOtp
                        v-model="form.pin"
                        input-id="pin"
                        mask
                        integerOnly
                        :length="6"
                    />
                    <InputError :message="form.errors.pin" />
                </div>

                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel
                        for="pin_confirmation"
                        :value="$t('public.confirm_your_pin')"
                        :invalid="!!form.errors.pin_confirmation"
                    />
                    <InputOtp
                        v-model="form.pin_confirmation"
                        input-id="pin_confirmation"
                        mask
                        integerOnly
                        :length="6"
                    />
                    <InputError :message="form.errors.pin_confirmation" />
                </div>
            </div>

            <div class="flex flex-col gap-1 items-center self-stretch pt-5">
                <Button
                    type="submit"
                    :label="$t('public.save')"
                    size="small"
                    class="w-full"
                    @click.prevent="submitForm"
                />
                <ForgetPin />
            </div>
        </form>
    </ContentLayout>
</template>
