<script setup>
import ProductLayout from "@/Layouts/ProductLayout.vue";
import Skeleton from "primevue/skeleton";
import {ref} from "vue";
import Tag from "primevue/tag";
import Card from "primevue/card";
import AddAddress from "@/Pages/Setting/DeliveryAddress/AddAddress.vue";
import {useLangObserver} from "@/Composables/localeObserver.js";
import AddPaymentAccount from "@/Pages/Setting/PaymentAccount/AddPaymentAccount.vue";
import EmptyData from "@/Components/EmptyData.vue";

defineProps({
    accountsCount: Number,
})

const accounts = ref([]);
const isLoading = ref(false);
const { locale } = useLangObserver();

const getResults = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/setting/getPaymentAccounts');
        accounts.value = response.data.accounts;
    } catch (error) {
        console.error('Error fetching accounts:', error);
    } finally {
        isLoading.value = false;
    }
};


getResults();

const tooltipText = ref('copy')
const copiedText = ref('')

function copyToClipboard(text) {
    const textToCopy = text;
    copiedText.value = text;

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
</script>

<template>
    <ProductLayout :title="$t('public.payment_account')">
        <div class="flex flex-col items-center gap-3 md:gap-5 self-stretch">
            <div class="flex justify-end w-full">
                <div class="w-auto">
                    <AddPaymentAccount />
                </div>
            </div>

            <div v-if="accountsCount === 0">
                <EmptyData
                    :title="$t('public.no_payment_account')"
                    :message="$t('public.no_payment_account_desc')"
                />
            </div>
            <div
                v-else
                class="w-full"
            >
                <div
                    v-if="isLoading"
                    class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-3 md:gap-5 w-full"
                >
                    <div
                        v-for="(paymentAccount, index) in accountsCount"
                        class="flex flex-col gap-3 border border-gray-300 rounded-md p-3 md:p-5 w-full"
                    >
                        <div class="flex gap-3 items-start self-stretch">
                            <div class="min-w-12 h-12 rounded-md bg-primary-300 flex items-center justify-center text-white font-bold">
                                PA
                            </div>
                            <div class="flex flex-col w-full">
                                <Skeleton width="5rem" height="1rem" borderRadius="2rem"></Skeleton>
                                <Skeleton width="9rem" height="1.5rem" borderRadius="2rem" class="mt-1"></Skeleton>
                            </div>
                        </div>
                        <div class="border-t border-gray-300 pt-2">
                            <Skeleton width="15rem" height="0.8rem" borderRadius="2rem" class="my-1.5"></Skeleton>
                        </div>
                    </div>
                </div>

                <div v-else class="w-full">
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-3 md:gap-5 w-full"
                    >
                        <Card
                            v-for="paymentAccount in accounts"
                        >
                            <template #content>
                                <div class="flex flex-col gap-3">
                                    <div class="flex gap-3 items-start self-stretch">
                                        <div class="min-w-12 h-12 rounded-md bg-primary-300 flex items-center justify-center text-white font-bold">
                                            <div v-if="paymentAccount.payment_platform === 'crypto'">
                                                {{ paymentAccount.currency }}
                                            </div>
                                            <div v-else>
                                                {{ paymentAccount.bank_swift_code.slice(0, 2).toUpperCase() }}
                                            </div>
                                        </div>
                                        <div class="flex flex-col w-full">
                                            <span class="text-xs text-gray-600 uppercase">{{ $t(`public.${paymentAccount.payment_platform}`) }}</span>
                                            <span class="font-semibold text-gray-950">{{ paymentAccount.payment_account_name }}</span>
                                        </div>
                                        <!--                                <PaymentAccountAction-->
                                        <!--                                    :paymentAccount="paymentAccount"-->
                                        <!--                                />-->
                                    </div>
                                    <div class="flex items-start justify-between border-t border-gray-300 pt-2">
                                        <div class="relative">
                                            <Tag
                                                v-if="tooltipText === 'copied' && copiedText === paymentAccount.account_no"
                                                class="absolute w-fit -top-6 left-1 !bg-gray-950 !text-white"
                                                :value="$t(`public.${tooltipText}`)"
                                            />
                                            <span @click="copyToClipboard(paymentAccount.account_no)" class="text-sm text-gray-600 break-words select-none cursor-pointer">{{ paymentAccount.account_no }}</span>
                                        </div>
                                        <Tag
                                            v-if="paymentAccount.default_account"
                                            severity="info"
                                            :value="$t('public.default')"
                                        />
                                    </div>
                                </div>
                            </template>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </ProductLayout>
</template>
