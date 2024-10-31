<script setup>
import MasterLayout from "@/Layouts/MasterLayout.vue";
import Card from 'primevue/card';
import {ref, onMounted, onBeforeUnmount} from "vue";
import { PhotoService } from '@/service/PhotoService';
import Galleria from 'primevue/galleria';
import Carousel from 'primevue/carousel';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import {Link} from "@inertiajs/vue3"

onMounted(() => {
    PhotoService.getImages().then((data) => (images.value = data));
});

const images = ref();
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

const categories = ref();


onMounted(() => {
    updateNumVisible();
    window.addEventListener('resize', updateNumVisible);
})

const products = ref();
const numVisible = ref(1);
const isLoading = ref(false);

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
</script>

<template>
    <MasterLayout title="Home">
        <div class="flex flex-col gap-3 md:gap-5 justify-center items-center w-full">
            <Galleria
                :value="images"
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
                    <img :src="slotProps.item.itemImageSrc" class="h-40 md:h-80 object-cover" :alt="slotProps.item.alt" style="width: 100%; display: block" />
                </template>
                <template #thumbnail="slotProps">
                    <img :src="slotProps.item.thumbnailImageSrc" :alt="slotProps.item.alt" style="display: block" />
                </template>
            </Galleria>

            <div class="w-full max-w-7xl flex flex-col justify-center items-start gap-3 md:gap-5 px-3 pb-12 md:px-5">
                <!-- Category -->
                <div class="flex flex-col gap-3 self-stretch">
                    <div class="flex justify-between items-center">
                        <span class="text-surface-950 dark:text-white font-medium">Shop by category</span>
                        <span class="text-sm text-surface-500">See more</span>
                    </div>
                    <div class="flex gap-5 items-center self-stretch overflow-x-auto">
                        <div class="flex flex-col gap-1 items-center self-stretch">
                            <div class="rounded-full w-16 h-16 grow-0 shrink-0 bg-surface-200 dark:bg-surface-800">

                            </div>
                            <div class="text-sm font-semibold text-surface-950 dark:text-white">
                                name
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Popular -->
                <div class="flex flex-col gap-3 self-stretch">
                    <div class="flex justify-between items-center">
                        <span class="text-surface-950 dark:text-white font-medium">Popular</span>
                        <span class="text-sm text-surface-500">See all</span>
                    </div>
                    <Carousel
                        :value="products"
                        :numVisible="numVisible"
                        :numScroll="1"
                    >
                        <template #item="slotProps">
                            <Link :href="route('shop.product_detail', [slotProps.data.slug, slotProps.data.id])">
                                <div class="border border-surface-200 dark:border-surface-700 rounded m-2 p-4">
                                    <div class="mb-4">
                                        <div class="relative mx-auto">
                                            <img :src="slotProps.data.media[0].original_url" :alt="slotProps.data.name" class="w-full md:w-[300px] rounded" />
                                            <Tag :value="slotProps.data.inventoryStatus" :severity="getSeverity(slotProps.data.inventoryStatus)" class="absolute" style="left:5px; top: 5px"/>
                                        </div>
                                    </div>
                                    <div class="mb-4 font-medium">{{ slotProps.data.name }}</div>
                                    <div class="flex justify-between items-center">
                                        <div class="mt-0 font-semibold text-xl">¥{{ slotProps.data.base_price }}</div>
                                        <span>
                    <Button type="button" @click.prevent icon="pi pi-heart" severity="secondary" outlined />
                    <Button icon="pi pi-shopping-cart" class="ml-2"/>
                </span>
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
