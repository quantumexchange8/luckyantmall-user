<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import {Link} from "@inertiajs/vue3";
import {ref} from "vue";
import {
    IconHome,
    IconCategory2,
    IconBellRinging,
    IconUser,
    IconMoon,
    IconSun,
    IconShoppingCart,
    IconLanguage
} from "@tabler/icons-vue"
import SignIn from "@/Components/Navbar/SignIn.vue";
import Button from 'primevue/button';
import {isDark, toggleDarkMode} from '@/Composables'
import ChangeLocale from "@/Components/Navbar/ChangeLocale.vue";

defineProps({
    hidden: {
        type: Boolean,
        default: false
    }
})

const showingNavigationDropdown = ref(false);

const activeTab = ref('home');

const tabs = ref([
    { name: 'home', label: 'Home', icon: IconHome, strokeWidth: 2, route: 'home' },
    { name: 'shop', label: 'Shop', icon: IconCategory2, strokeWidth: 1, route: 'shop' },
    { name: 'notifications', label: 'Notification', icon: IconBellRinging, strokeWidth: 1, route: 'notifications' },
    { name: 'profile', label: 'Profile', icon: IconUser, strokeWidth: 1, route: 'profile', isLink: true },
]);

const setActiveTab = (routeName) => {
    window.location.href = route(routeName);
}
</script>

<template>
    <nav
        class="border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-surface-950"
        :class="{'hidden': hidden}"
    >
        <!-- Primary Navigation Menu -->
        <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-5 xl:px-0">
            <div class="flex h-16 justify-between">
                <div class="flex gap-8 w-full">
                    <!-- Logo -->
                    <div class="flex shrink-0 items-center">
                        <Link :href="route('home')">
                            <ApplicationLogo
                                class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"
                            />
                        </Link>
                    </div>

                    <!-- Items Link -->
                    <div
                        class="hidden sm:flex items-center w-full gap-5"
                    >
                        <NavLink
                            :href="route('shop')"
                            :active="route().current('shop')"
                        >
                            {{ $t('public.shop') }}
                        </NavLink>

                        <NavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            {{ $t('public.report') }}
                        </NavLink>

                        <NavLink
                            :href="route('profile')"
                            :active="route().current('profile')"
                        >
                            {{ $t('public.profile') }}
                        </NavLink>
                    </div>
                </div>

                <!-- System operation -->
                <div
                    v-if="$page.props.canLogin"
                    class="hidden sm:flex sm:items-center gap-2 pl-6"
                >
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

                    <SignIn
                        v-if="!$page.props.auth.user"
                    />

                    <div v-else>
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
                    </div>
                </div>

                <!-- Hamburger -->
                <div class="flex items-center sm:hidden">
                    <SignIn
                        v-if="!$page.props.auth.user"
                    />

                    <div v-else>
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div
            :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
            class="sm:hidden"
        >
            <div class="space-y-1 pb-3 pt-2">
                <ResponsiveNavLink
                    :href="route('dashboard')"
                    :active="route().current('dashboard')"
                >
                    Dashboard
                </ResponsiveNavLink>
            </div>

            <!-- Responsive Settings Options -->
            <div
                v-if="!$page.props.canLogin"
                class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-600"
            >
                <div class="px-4">
                    <div
                        class="text-base font-medium text-gray-800 dark:text-gray-200"
                    >
                        {{ $page.props.auth.user.name }}
                    </div>
                    <div class="text-sm font-medium text-gray-500">
                        {{ $page.props.auth.user.email }}
                    </div>
                </div>

                <div class="mt-3 space-y-1">
                    <ResponsiveNavLink :href="route('profile.edit')">
                        Profile
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        :href="route('logout')"
                        method="post"
                        as="button"
                    >
                        Log Out
                    </ResponsiveNavLink>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile bottom bar -->
    <div
        class="fixed flex items-start justify-between gap-8 md:hidden inset-x-0 z-50 bottom-0 px-6 py-4 transition-transform duration-500 bg-white dark:bg-surface-900"
        :class="{'hidden': hidden}"
    >
        <div
            v-for="tab in tabs"
            :key="tab.name"
            :class="{
                'text-primary-500 font-bold': route().current(`${tab.route}*`),
                'text-surface-400 dark:text-surface-500': !route().current(`${tab.route}*`),
            }"
            class="flex flex-col gap-1 items-center self-stretch w-16 cursor-pointer transition-colors duration-200"
            @click="setActiveTab(tab.route)"
        >
            <component :is="tab.icon" :size="28" :stroke-width="route().current(`${tab.route}*`) ? 2 : 1" />
            <span class="text-xs">{{ tab.label }}</span>
        </div>
    </div>
</template>
