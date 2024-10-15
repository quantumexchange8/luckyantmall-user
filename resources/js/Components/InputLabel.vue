<script setup>
defineProps({
    value: {
        type: String,
    },
    invalid: Boolean
});
</script>

<template>
    <label
        class="block font-medium text-xs"
        :class="{
            'text-surface-700 dark:text-surface-300': !invalid,
            'text-error-500': invalid
        }"
    >
        <span v-if="value">
            {{ value }}<span class="text-error-500 text-xs font-medium"> *</span>
        </span>
        <span v-else>
            <slot />
        </span>
    </label>
</template>
