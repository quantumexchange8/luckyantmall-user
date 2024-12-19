<script setup>
import Button from "primevue/button";
import {ref} from "vue";
import Dialog from "primevue/dialog";

const visible = ref(false);

const openDialog = () => {

}
</script>

<template>
    <div class="flex flex-col gap-3">
        <div class="flex flex-col gap-1">
            <div>Invite your friends</div>
            <div class="text-xs">Invite your friends to join and enjoy exclusive rewards together. Share the benefits and grow your community!</div>
        </div>
        <Button
            type="button"
            rounded
            size="small"
        >
            {{ $t('public.my_referral_code') }}
        </Button>
    </div>
</template>
