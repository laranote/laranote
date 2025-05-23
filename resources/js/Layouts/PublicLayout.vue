<template>
    <div class="relative w-full h-screen dark:bg-neutral-800">
        <aside
            class="fixed top-0 left-0 w-64 h-screen flex flex-col border-l border-gray-200 bg-neutral-100 shadow-lg dark:bg-neutral-900 dark:border-gray-600">
            <Link :href="route('homepage')" class="p-4 border-b border-gray-200">
                <img v-if="page.props.project?.logo_full_url" :src="page.props.project.logo_full_url"
                     :alt="page.props.project?.name">
                <div v-else class="text-xl font-bold text-black dark:text-gray-100 text-center">
                    {{ page.props.project?.name }}
                </div>
            </Link>

            <div class="p-4 border-t border-neutral-400 flex flex-col gap-3">
                <Link
                    :href="route('login')"
                    class="w-full text-center !text-black py-2 px-4 border border-green-600 hover:bg-green-700 hover:!text-white rounded transition-colors dark:!text-gray-100">
                    Login
                </Link>
            </div>
        </aside>
        <div class="h-screen ml-64 dark:bg-neutral-800">
            <DarkModeButton/>
            <slot/>
        </div>
    </div>
</template>

<script setup>
import {Link, usePage} from "@inertiajs/vue3"
import DarkModeButton from "@/Components/DarkModeButton.vue"

const page = usePage()

</script>

<style>
.overflow-x-auto::-webkit-scrollbar {
    height: 0px;
}

.overflow-x-auto {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
