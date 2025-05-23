<script setup>
import {Head} from "@inertiajs/vue3";
import {
    IconArrowLeft,
    IconShoppingCart,
} from "@tabler/icons-vue";
import Button from "primevue/button";
import Navbar from "@/Components/Navbar.vue";
import SignIn from "@/Components/Navbar/SignIn.vue";
import ToastList from "@/Components/ToastList.vue";
import OverlayBadge from 'primevue/overlaybadge';

defineProps({
    title: String,
    backRoute: {
        type: String,
        default: "home"
    },
    routeParam: {
        type: String,
        default: null
    },
})
</script>

<template>
    <Head :title="title"></Head>
    <Navbar hidden />
    <div class="min-h-screen bg-gray-100 dark:bg-surface-950 transition-all duration-200">
        <!-- Page Content -->
        <main class="flex flex-1 justify-center items-start px-3 pt-3 pb-12 md:px-5 md:pt-5">
            <div class="w-full max-w-7xl flex flex-col gap-3 md:gap-5">
                <div class="flex justify-between items-center w-full">
                    <div class="flex gap-3 items-center">
                        <Button
                            severity="contrast"
                            text
                            rounded
                            aria-label="Back"
                            as="a"
                            :href="route(backRoute, routeParam)"
                        >
                            <template #icon>
                                <IconArrowLeft size="16"/>
                            </template>
                        </Button>

                        <div class="text-sm md:text-lg font-semibold text-surface-950 dark:text-white">
                            {{ title }}
                        </div>
                    </div>
                    <SignIn
                        v-if="!$page.props.auth.user"
                    />

                    <div v-else>
                        <OverlayBadge
                            v-if="$page.props.auth.cartItemsCount > 0"
                            size="small"
                            :value="$page.props.auth.cartItemsCount"
                        >
                            <Button
                                severity="secondary"
                                outlined
                                size="small"
                                aria-label="Cart"
                                as="a"
                                :href="route('cart')"
                            >
                                <template #icon>
                                    <IconShoppingCart size="16" />
                                </template>
                            </Button>
                        </OverlayBadge>
                        <Button
                            v-else
                            severity="secondary"
                            outlined
                            size="small"
                            aria-label="Cart"
                            as="a"
                            :href="route('cart')"
                        >
                            <template #icon>
                                <IconShoppingCart size="16" />
                            </template>
                        </Button>
                    </div>
                </div>
                <!-- Toast -->
                <ToastList />

                <slot/>
            </div>
        </main>
    </div>
</template>
