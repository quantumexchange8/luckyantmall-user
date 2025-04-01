<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import {Link, useForm} from '@inertiajs/vue3';
import Button from 'primevue/button';
import Divider from "primevue/divider";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout :title="$t('public.login')">
        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <div class="w-full flex flex-col justify-center items-center gap-6 md:gap-8">
            <div class="w-full flex flex-col items-start gap-1 self-stretch pt-10">
                <div class="self-stretch text-center text-gray-950 dark:text-white text-xl font-semibold">{{ $t('public.login_header') }}</div>
                <div class="text-sm md:text-base self-stretch text-center text-surface-500">{{ $t('public.login_header_caption') }}</div>
            </div>
            <form @submit.prevent="submit" class="flex flex-col items-center gap-3 self-stretch">
                <div class="flex flex-col items-start gap-5 self-stretch">
                    <div class="flex flex-col items-start gap-1 self-stretch">
                        <InputLabel for="username" :value="$t('public.username')" :invalid="!!form.errors.username" />

                        <InputText
                            id="username"
                            type="text"
                            class="block w-full"
                            v-model="form.username"
                            autofocus
                            :placeholder="$t('public.enter_your_username')"
                            :invalid="!!form.errors.username"
                            autocomplete="username"
                        />

                        <InputError :message="form.errors.username" />
                    </div>

                    <div class="flex flex-col items-start gap-1 self-stretch">
                        <InputLabel
                            for="password"
                            :value="$t('public.password')"
                            :invalid="!!form.errors.password"
                        />

                        <Password
                            v-model="form.password"
                            toggleMask
                            placeholder="••••••••"
                            :invalid="!!form.errors.password"
                            :feedback="false"
                        />

                        <InputError :message="form.errors.password" />
                    </div>
                </div>
                <div class="flex justify-end items-center self-stretch">
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-right text-sm text-primary-500 hover:text-primary-600 font-medium"
                    >
                        {{ $t('public.forgot_your_password') }}?
                    </Link>
                </div>

                <div class="flex flex-col gap-1 items-center self-stretch">
                    <Button
                        type="submit"
                        :label="$t('public.sign_in')"
                        class="w-full"
                        size="small"
                        :disabled="form.processing"
                    />
                    <div class="text-sm text-surface-600 dark:text-surface-400">
                        {{ $t('public.dont_have_an_account') }}
                        <Link
                            :href="route('register')"
                            class="text-right text-sm text-primary-500 font-semibold hover:text-primary-600"
                        >
                            {{ $t('public.register') }}
                        </Link>
                    </div>
                </div>
            </form>

            <Divider align="center">
                <span class="text-sm text-surface-400">{{ $t('public.or') }}</span>
            </Divider>
            <Button
                type="button"
                as="a"
                :href="route('sign_up_with_group')"
                severity="secondary"
                outlined
                class="w-full flex gap-2"
                size="small"
                :disabled="form.processing"
            >
                <img src="/img/luckyant-group-logo-mark.svg" alt="logo" width="16" />
                <span class="font-medium text-surface-950
 dark:text-white">{{ $t('public.sign_up_with_group') }}</span>
            </Button>
        </div>
    </GuestLayout>
</template>
