<template>
    <button
        type="button"
        @click="onClick"
        :class="{ 'bg-gray-100 dark:bg-neutral-600': isActive}"
        class="p-1.5
        rounded hover:bg-gray-100 transition-colors dark:hover:bg-neutral-700"
    >
        <component :is="icon" :title="title" :size="18"/>
    </button>
</template>

<script setup>
defineProps({
    icon: {
        type: Object,
        required: true
    },
    title: {
        type: String,
        required: true
    },
    isActive: {
        type: Boolean,
        default: false
    },
    onClick: {
        type: Function,
        required: true
    }
})
</script>
