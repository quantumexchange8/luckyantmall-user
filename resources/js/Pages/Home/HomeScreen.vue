<script setup>
import MasterLayout from "@/Layouts/MasterLayout.vue";
import Card from 'primevue/card';
import {ref, onMounted, onBeforeUnmount} from "vue";
import Galleria from 'primevue/galleria';
import Carousel from 'primevue/carousel';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import {Link} from "@inertiajs/vue3"
import {generalFormat} from "@/Composables/format.js";
import {useLangObserver} from "@/Composables/localeObserver.js";

defineProps({
    categories: Object,
    settingBanners: Array
})

const responsiveOptions = ref([
    {
        breakpoint: '1300px',
        numVisible: 4
    },
    {
        breakpoint: '575px',
        numVisible: 1
    }
]);

onMounted(() => {
    updateNumVisible();
    window.addEventListener('resize', updateNumVisible);
})

const products = ref();
const numVisible = ref(1);
const isLoading = ref(false);
const {formatAmount} = generalFormat();
const {locale} = useLangObserver();

function updateNumVisible() {
    const width = window.innerWidth;
    if (width >= 1280) {
        numVisible.value = 4;
    } else if (width >= 768) {
        numVisible.value = 3;
    } else if (width >= 640) {
        numVisible.value = 2;
    } else {
        numVisible.value = 1;
    }
}

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateNumVisible);
});

const getResults = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/home/getPopularProducts');
        products.value = response.data.products;
    } catch (error) {
        console.error('Error fetching popular products:', error);
    } finally {
        isLoading.value = false;
    }
};

getResults();

const getSeverity = (status) => {
    switch (status) {
        case 'INSTOCK':
            return 'success';

        case 'LOWSTOCK':
            return 'warn';

        case 'OUTOFSTOCK':
            return 'danger';

        default:
            return null;
    }
};

const redirectToCategory = (category) => {
    window.location.href = route('shop', {category: category});
}

const redirectToPage = (url) => {
    window.location.href = url;
}
</script>

<template>
    <MasterLayout title="Home">
        <div class="flex flex-col gap-5 md:gap-8 justify-center items-center w-full">
            <Galleria
                :value="settingBanners"
                :responsiveOptions="responsiveOptions"
                :numVisible="9"
                containerStyle="width: 100%"
                :showThumbnails="false"
                :showIndicators="true"
                :circular="true"
                :autoPlay="true"
                :transitionInterval="4000"
            >
                <template #item="slotProps">
                    <a
                        v-if="slotProps.item.banner_url"
                        :href="slotProps.item.banner_url"
                        class="w-full text-xs text-surface-500 hover:text-blue-500 truncate"
                    >
                        <img
                            :src="slotProps.item.image_url"
                            class="h-40 md:h-[400px] object-cover"
                            :alt="slotProps.item.alt"
                            style="width: 100%; display: block"
                        />
                    </a>
                    <div
                        v-else
                        class="w-full"
                    >
                        <img
                            :src="slotProps.item.image_url"
                            class="h-40 md:h-80 object-cover"
                            :alt="slotProps.item.alt"
                            style="width: 100%; display: block"
                        />
                    </div>
                </template>
            </Galleria>

            <div class="w-full max-w-7xl flex flex-col justify-center items-start gap-5 md:gap-8 px-3 pb-12 md:px-5">
                <!-- Category -->
                <div class="flex flex-col gap-3 self-stretch">
                    <div class="flex justify-between items-center">
                        <span class="text-surface-950 dark:text-white font-medium">Shop by category</span>
                    </div>
                    <div class="flex gap-3 md:gap-5 items-center self-stretch overflow-x-auto no-scrollbar">
                        <div
                            v-for="category in categories"
                        >
                            <div
                                class="flex flex-col gap-1 items-center self-stretch select-none cursor-pointer group"
                                @click="redirectToCategory(category.slug)"
                            >
                                <div class="rounded-md w-40 h-20 grow-0 shrink-0 bg-surface-200 dark:bg-surface-800">

                                </div>
                                <div class="text-sm font-semibold text-surface-950 dark:text-white group-hover:text-surface-600 dark:group-hover:text-surface-400 transition-colors duration-200">
                                    {{ category.name[locale] }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Popular -->
                <div class="flex flex-col gap-3 self-stretch">
                    <div class="flex justify-between items-center">
                        <span class="text-surface-950 dark:text-white font-medium">Popular</span>
                        <Link :href="route('shop')" class="text-sm text-surface-500 hover:font-medium">See all</Link>
                    </div>
                    <Carousel
                        :value="products"
                        :numVisible="numVisible"
                        :numScroll="1"
                    >
                        <template #item="slotProps">
                            <Link :href="route('shop.product_detail', [slotProps.data.slug, slotProps.data.id])">
                                <div class="border border-surface-200 dark:border-surface-700 rounded m-2 p-4 bg-white dark:bg-surface-900">
                                    <div class="mb-4">
                                        <div class="flex justify-center rounded-md overflow-hidden bg-surface-100 dark:bg-surface-950 p-4">
                                            <img
                                                :src="slotProps.data.media[0].original_url"
                                                :alt="slotProps.data.name"
                                                class="w-full md:w-[300px] h-[240px] object-contain rounded"
                                            />
                                        </div>
                                    </div>
                                    <div class="mb-4 font-medium truncate max-w-52 dark:text-white hover:text-primary">{{ slotProps.data.name }}</div>
                                    <div class="flex justify-between items-center dark:text-surface-300">
                                        <div class="mt-0 font-semibold text-xl">¥{{ formatAmount(slotProps.data.base_price) }}</div>
                                    </div>
                                </div>
                            </Link>
                        </template>
                    </Carousel>
                </div>
            </div>
        </div>
    </MasterLayout>
</template>
