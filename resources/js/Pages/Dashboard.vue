<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import {ref} from "vue";
import Button from 'primevue/button';
const value = ref('');

import Card from 'primevue/card';

</script>

<template>
    <AuthenticatedLayout :title="$t('public.dashboard')">

    </AuthenticatedLayout>
</template>
