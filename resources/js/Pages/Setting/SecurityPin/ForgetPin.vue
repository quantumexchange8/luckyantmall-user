<script setup>
import Button from "primevue/button";
import Drawer from "primevue/drawer";
import {onUnmounted, ref, watch} from "vue";
import InputError from "@/Components/InputError.vue";
import InputOtp from "primevue/inputotp";
import InputLabel from "@/Components/InputLabel.vue";
import {useForm} from "@inertiajs/vue3";
import Tag from "primevue/tag";

const visible = ref(false);

const form = useForm({
    pin: '',
    pin_confirmation: '',
    otp: ''
});

const otpRequested = ref(false);
const countdown = ref(10);
let countdownIntervalId;

const startCountdown = () => {
    clearInterval(countdownIntervalId);
    countdownIntervalId = setInterval(() => {
        countdown.value -= 1;
        if (countdown.value === 0) {
            clearInterval(countdownIntervalId);
        }
    }, 1000);
};

// Watch to restart countdown if needed
watch(countdown, (newVal) => {
    if (newVal > 0 && !countdownIntervalId) {
        startCountdown();
    }
});

// Cleanup interval on unmount
onUnmounted(() => {
    clearInterval(countdownIntervalId);
});

const requestOTP = () => {
    otpRequested.value = true;
    axios.post(route('setting.requestOtp'))
    startCountdown();
};

const resendOTP = () => {
    countdown.value = 10;
    clearInterval(countdownIntervalId);
    axios.post(route('setting.requestOtp'))
    startCountdown();
};

const submitForm = () => {
    form.post(route('setting.resetPin'))
}
</script>

<template>
    <div
        @click="visible = true"
        class="text-right text-sm text-primary-500 font-semibold hover:text-primary-600 select-none cursor-pointer"
    >
        {{ $t('public.reset_pin') }}
    </div>

    <Drawer
        v-model:visible="visible"
        :header="$t('public.reset_pin')"
        class="w-full md:max-w-[360px] pb-24 !h-4/5"
        position="bottom"
    >
        <div class="flex flex-col gap-3 items-center self-stretch">

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

            <div
                v-if="!otpRequested"
                class="flex flex-col gap-1 w-full pt-3"
            >
                <Button
                    type="button"
                    severity="info"
                    size="small"
                    :label="$t('public.request_otp')"
                    @click="requestOTP"
                />
                <div class="text-xs font-medium">
                    {{ $t('public.otp_will_send_to', {email: $page.props.auth.user.email}) }}
                </div>
                <InputError :message="form.errors.otp" />
            </div>

            <div
                v-else
                class="flex flex-col gap-3"
            >
                <div
                    class="flex flex-col items-start gap-1 self-stretch"
                >
                    <InputLabel
                        for="otp"
                        :value="$t('public.otp_code')"
                        :invalid="!!form.errors.otp"
                    />
                    <InputOtp
                        v-model="form.otp"
                        input-id="otp"
                        mask
                        integerOnly
                        :length="6"
                    />
                    <InputError :message="form.errors.otp" />
                </div>

                <div
                    v-if="otpRequested && countdown > 0"
                    class="flex justify-between items-center gap-3"
                >
                    <Tag
                        severity="success"
                        :value="$t('public.otp_sent')"
                    />
                    <span class="text-xs font-medium text-cyan-500">{{ $t('public.resend_in', {seconds: countdown}) }}</span>
                </div>
                <div
                    v-else-if="otpRequested && countdown <= 0"
                    class="w-full flex justify-end"
                >
                    <Button
                        type="button"
                        severity="info"
                        size="small"
                        :label="$t('public.resend_otp')"
                        @click="resendOTP"
                    />
                </div>
            </div>

            <Button
                type="button"
                size="small"
                class="w-full mt-2"
                :label="$t('public.reset_pin')"
                @click="submitForm"
            />
        </div>
    </Drawer>
</template>
