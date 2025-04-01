<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputText from 'primevue/inputtext';
import {Link, useForm} from '@inertiajs/vue3';
import Button from 'primevue/button';

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('proceedGroupSignIn'), {
        onError: (errors) => {
            console.error(errors);
        }
    });
};
</script>

<template>
    <GuestLayout :title="$t('public.sign_up_with_group')">
        <div class="w-full flex flex-col justify-center items-center gap-6 md:gap-8">
            <div class="w-full flex flex-col items-start gap-1 self-stretch pt-10">
                <div class="self-stretch text-center text-gray-950 dark:text-white text-xl font-semibold">{{ $t('public.sign_up_with_group') }}</div>
                <div class="text-sm md:text-base self-stretch text-center text-surface-500">{{ $t('public.sign_up_with_group_caption') }}</div>
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
                </div>
                <div class="flex flex-col gap-1 items-center self-stretch">
                    <Button
                        type="submit"
                        :label="$t('public.proceed')"
                        class="w-full"
                        size="small"
                        :disabled="form.processing"
                    />
                    <div class="text-sm text-surface-600 dark:text-surface-400">
                        <Link
                            :href="route('login')"
                            class="text-right text-sm hover:text-primary-600"
                        >
                            {{ $t('public.back_to_login') }}
                        </Link>
                    </div>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
