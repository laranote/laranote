<template>
    <Head title="Welcome"/>
    <default-layout
        :posts="posts"
    >
        <div class="container mx-auto max-w-5xl py-8 dark:text-gray-300">
            <h1 class="text-3xl font-bold mb-5">Welcome to Laranote.</h1>
            <h1 class="text-xl">Make or view a note to get started</h1>
        </div>
    </default-layout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import DefaultLayout from "@/Layouts/DefaultLayout.vue"

defineProps({
    posts: {
        type: Object
    },
})
</script>
