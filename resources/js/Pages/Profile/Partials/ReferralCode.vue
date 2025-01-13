<script setup>
import Button from "primevue/button";
import {computed, ref} from "vue";
import Dialog from "primevue/dialog";
import QrcodeVue from 'qrcode.vue'
import {usePage} from "@inertiajs/vue3";
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
import InputText from "primevue/inputtext";
import {IconCopy} from "@tabler/icons-vue";
import toast from "@/Composables/toast.js";

const visible = ref(false);
const registerLink = ref(`${window.location.origin}/register/${usePage().props.auth.user.referral_code}`);

const openDialog = () => {

}

const copyToClipboard = () => {

}

const qrcodeContainer = ref(null);
const canWebShare = ref(!!navigator.share);

const buttonLabel = computed(() =>
    canWebShare.value
        ? 'share_referral_code'
        : 'reveal_qr_code'
);

const handleClick = () => {
    if (canWebShare.value) {
        shareRegisterLink();
    } else {
        revealQrCode();
    }
};

const shareRegisterLink = async () => {
    try {
        await navigator.share({
            title: 'Invite Friends',
            text: 'Click the link to register:',
            url: registerLink,
        });

        toast.add({
            title: 'Success',
            message: 'Register Link was shared successfully',
            type: 'success'
        })
    } catch (error) {
        console.error('Error sharing the register link:', error);
    }
};

const revealQrCode = () => {
    console.log('Reveal the QR code here');
    // Add logic to display the QR code
};
</script>

<template>
    <div class="flex gap-3 items-center">
        <div class="block md:hidden rounded bg-primary grow-0 shrink-0 w-28 md:w-40 h-32 md:h-40">

        </div>
        <div class="flex flex-col gap-3 w-full">
            <div class="flex flex-col gap-1">
                <div>Invite your friends</div>
                <div class="text-xs">Invite your friends to join and enjoy exclusive rewards together. Share the benefits and grow your community!</div>
            </div>

            <!-- Qr code-->
            <div class="hidden md:flex flex-col items-center gap-3 self-stretch">
                <div
                    ref="qrcodeContainer">
                    <qrcode-vue
                        ref="qrcode"
                        :value="registerLink"
                        :margin="2"
                        :size="200"
                    />
                </div>
                <Button
                    type="button"
                    @click="handleClick"
                >
                    {{ $t('public.download_qr_to_invite_friends') }}
                </Button>
            </div>

            <div class="flex gap-3 items-center self-stretch">
                <div class="h-[1px] bg-surface-200 dark:bg-surface-700 rounded-[5px] w-full"></div>
                <div class="text-xs md:text-sm text-surface-600 dark:text-surface-400 text-center min-w-[145px] md:w-full">
                    {{ $t('public.copy_referral_link') }}
                </div>
                <div class="h-[1px] bg-surface-200 dark:bg-surface-700 rounded-[5px] w-full"></div>
            </div>

            <InputGroup>
                <InputText v-model="registerLink" readonly />
                <Button
                    type="button"
                    severity="info"
                    @click="copyToClipboard(registerLink)"
                >
                    <IconCopy size="20" stroke-width="1.5" />
                </Button>
            </InputGroup>

            <Button
                type="button"
                rounded
                size="small"
                class="block md:hidden"
                @click="handleClick"
            >
                {{ $t(`public.${buttonLabel}`) }}
            </Button>
        </div>
    </div>
</template>
