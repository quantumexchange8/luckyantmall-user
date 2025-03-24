<script setup>
defineProps({
    title: String,
    message: String,
})
</script>

<template>
    <div class="flex flex-col gap-3 items-center self-stretch py-5">
        <img src="/img/empty/no_data.svg" alt="no data" class="w-80">

        <div class="flex flex-col gap-1 items-center self-stretch text-sm">
            <span class="font-semibold dark:text-white">{{ title }}</span>
            <span class="text-surface-500">{{ message }}</span>
        </div>
    </div>
</template>
