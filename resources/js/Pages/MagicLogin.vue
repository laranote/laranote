<template>
    <Head title="Magic Login"/>

    <div class="w-full min-h-screen flex justify-center items-center dark:bg-neutral-800 dark:text-gray-100">
        <form id="magic-form" class="flex flex-col justify-center items-center ">
            <img v-if="page.props.project?.logo_full_url" :src="page.props.project.logo_full_url" :alt="page.props.project.name" width="300" height="300" class="mb-5">
            <div v-else class="text-3xl font-bold mb-5">{{ page.props.project?.name }}</div>
            <input id="magic-input" class="rounded-md dark:border-neutral-400 dark:text-black" required>
            <button id="magic-submit"
                    class="border-2 rounded-md font-bold py-2 px-7 border-green-700 mt-2 hover:bg-green-700 hover:text-white transition-all "></button>
            <div id="RecaptchaField"></div>
            <p id="validation-message"></p>
        </form>
    </div>

</template>

<script setup>
import magic_script from "../magiclogin-logic.js";
import {onMounted} from "vue";
import {Head, usePage} from "@inertiajs/vue3";

const page = usePage();
const props = defineProps({
    projectSlug: {
        type: String,
    },
    redirectUrl: {
        type: String,
    }
})

onMounted(function () {
    window.magicmk = {
        project_slug: props.projectSlug,
        language: '',
        redirect_url: props.redirectUrl,
        params: {
            extra: "parameters",
        }
    }

    magic_script();
})

</script>

<style scoped>

</style>
