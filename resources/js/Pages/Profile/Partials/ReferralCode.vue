<script setup>
import Button from "primevue/button";
import {computed, ref} from "vue";
import Dialog from "primevue/dialog";
import QrcodeVue from 'qrcode.vue'
import {usePage} from "@inertiajs/vue3";
import Tag from 'primevue/tag';
import InputText from "primevue/inputtext";
import {IconCopy} from "@tabler/icons-vue";
import toast from "@/Composables/toast.js";
import {trans} from "laravel-vue-i18n";

const visible = ref(false);
const registerLink = ref(`${window.location.origin}/register/${usePage().props.auth.user.referral_code}`);
const tooltipText = ref('copy')

const copyToClipboard = (text) => {
    const textToCopy = text;

    const textArea = document.createElement('textarea');
    document.body.appendChild(textArea);

    textArea.value = textToCopy;
    textArea.select();

    try {
        const successful = document.execCommand('copy');

        tooltipText.value = 'copied';
        setTimeout(() => {
            tooltipText.value = 'copy';
        }, 1500);
    } catch (err) {
        console.error('Copy to clipboard failed:', err);
    }

    document.body.removeChild(textArea);
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
            title: trans('public.invite_friends'),
            text: trans('public.click_the_link_to_register'),
            url: registerLink.value,
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
    visible.value = true;
};

const downloadQrCode = () => {
    const canvas = qrcodeContainer.value.querySelector("canvas");
    const link = document.createElement("a");
    link.download = "referral-qr-code.png";
    link.href = canvas.toDataURL("image/png");
    link.click();
}
</script>

<template>
    <div class="flex flex-col gap-3 w-full">
        <div class="flex flex-col gap-1">
            <div class="font-semibold">
                {{ $t('public.invite_your_friends') }}
            </div>
            <div class="text-xs text-surface-600 dark:text-surface-400">
                {{ $t('public.invite_your_friends_caption') }}
            </div>
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
                @click="downloadQrCode"
            >
                <span class="text-sm font-semibold">{{ $t('public.download_qr_to_share') }}</span>
            </Button>
        </div>

        <div class="flex gap-3 items-center self-stretch">
            <div class="h-[1px] bg-surface-200 dark:bg-surface-700 rounded-[5px] w-full"></div>
            <div class="text-xs md:text-sm text-surface-600 dark:text-surface-400 text-center min-w-[145px] md:w-full">
                {{ $t('public.copy_referral_link') }}
            </div>
            <div class="h-[1px] bg-surface-200 dark:bg-surface-700 rounded-[5px] w-full"></div>
        </div>

        <div class="flex gap-1 items-center self-stretch relative">
            <InputText v-model="registerLink" readonly />
            <Tag
                v-if="tooltipText === 'copied'"
                class="absolute -top-7 -right-3"
                severity="contrast"
                :value="$t(`public.${tooltipText}`)"
            ></Tag>
            <Button
                type="button"
                severity="secondary"
                text
                @click="copyToClipboard(registerLink)"
            >
                <IconCopy size="20" stroke-width="1.5" />
            </Button>
        </div>

        <Button
            type="button"
            rounded
            size="small"
            class="block md:hidden"
            @click="handleClick"
        >
            {{ $t(`public.${buttonLabel}`) }}
        </Button>

        <Dialog
            v-model:visible="visible"
            modal
            header="&nbsp"
            class="dialog-xs md:dialog-sm"
        >
            <div class="flex flex-col gap-5 items-center self-stretch">
                <div class="flex flex-col gap-1 items-center self-stretch">
                    <span class="font-semibold">{{ $t('public.referral_qr_code') }}</span>
                    <span class="text-xs">{{ $t('public.scan_to_register') }}</span>
                </div>
                <div class="flex flex-col items-center gap-3 self-stretch pb-6">
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
                        @click="downloadQrCode"
                    >
                        <span class="text-xs font-semibold">{{ $t('public.download_qr_to_share') }}</span>
                    </Button>
                </div>
            </div>
        </Dialog>
    </div>
</template>
