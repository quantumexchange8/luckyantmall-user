<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import {Head, Link} from '@inertiajs/vue3';
import {isDark, toggleDarkMode} from "@/Composables/index.js";
import {IconMoon, IconSun} from "@tabler/icons-vue";
import Button from "primevue/button";
import ChangeLocale from "@/Components/Navbar/ChangeLocale.vue";
import dayjs from "dayjs";
import ToastList from "@/Components/ToastList.vue";

defineProps({
    title: String
})
</script>

<template>
    <Head :title="title"></Head>

    <div class="flex flex-col min-h-screen bg-surface-0 dark:bg-surface-950 transition-all duration-200">
        <div class="flex mx-auto w-full h-16 max-w-7xl px-3 sm:px-6 lg:px-5 xl:px-0 justify-between items-center">
            <div>
                <Link href="/">
                    <ApplicationLogo class="h-9 w-auto fill-current text-gray-500" />
                </Link>
            </div>

            <div class="flex items-center gap-2 self-stretch">
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

        <div class="flex flex-col justify-center items-center pb-12 md:px-8 xs:gap-y-[60px]">
            <div class="w-full flex md:flex-1 justify-center">
                <div class="w-full max-w-xs md:max-w-none md:w-[360px] flex flex-col justify-center items-center mx-5">
                    <ToastList />
                    <slot />
                </div>
            </div>
            <div class="text-center text-gray-500 text-xs mt-auto">© {{ dayjs().year() }} Luckyant Mall. All rights reserved.</div>
        </div>
    </div>
</template>
