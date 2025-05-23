<template>
    <Head title="Initialize Project" />
    <div class="min-h-screen bg-neutral-200 py-6 flex flex-col justify-center sm:py-12 dark:bg-neutral-800">
        <div class="relative py-3 sm:max-w-xl sm:mx-auto">
            <div class="relative px-4 py-10 bg-neutral-100 shadow-lg sm:rounded-3xl sm:p-20 dark:bg-neutral-900 shadow-neutral-500 dark:shadow-neutral-950">
                <div class="max-w-md mx-auto">
                    <div class="divide-y divide-gray-200">
                        <div class="py-8 text-base leading-6 space-y-4 text-gray-700 sm:text-lg sm:leading-7 dark:text-gray-100">
                            <h1 class="text-3xl font-bold mb-8 text-center">Initialize Laranote Project</h1>

                            <form @submit.prevent="submit" class="space-y-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project
                                        Name</label>
                                    <input
                                        type="text"
                                        id="name"
                                        v-model="form.name"
                                        class="mt-1 block w-full rounded-md border-gray-400 shadow-sm focus:border-green-700 focus:ring-green-700 dark:text-gray-100 dark:border-gray-700 dark:bg-neutral-800"
                                        required
                                    >
                                    <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{
                                            form.errors.name
                                        }}
                                    </div>
                                </div>

                                <div>
                                    <label for="logo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Logo</label>
                                    <input
                                        type="file"
                                        id="logo"
                                        @input="form.logo = $event.target.files[0]"
                                        accept="image/*"
                                        class="mt-1 block w-full text-sm text-gray-500
                                        dark:text-gray-400
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-md file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-green-700 file:text-white
                                        hover:file:bg-green-800"
                                    >
                                    <div v-if="form.errors.logo" class="text-red-500 text-sm mt-1">{{
                                            form.errors.logo
                                        }}
                                    </div>
                                </div>

                                <div>
                                    <label for="default_user_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default
                                        User Role</label>
                                    <select
                                        id="default_user_role"
                                        v-model="form.default_user_role"
                                        class="mt-1 block w-full rounded-md border-gray-400 shadow-sm focus:border-green-700 focus:ring-green-700 dark:text-gray-100 dark:border-gray-700 dark:bg-neutral-800"
                                        required
                                    >
                                        <option v-for="(type, name) in userRoles" :key="name" :value="type" class="dark:text-gray-100 dark:bg-neutral-800">
                                            {{ name }}
                                        </option>
                                    </select>

                                    <div v-if="form.errors.default_user_role" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.default_user_role }}
                                    </div>
                                </div>

                                <div>
                                    <label for="auth_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Authentication
                                        Type</label>
                                    <select
                                        id="auth_type"
                                        v-model="form.auth_type"
                                        class="mt-1 block w-full rounded-md border-gray-400 shadow-sm focus:border-green-700 focus:ring-green-700 dark:text-gray-100 dark:border-gray-700 dark:bg-neutral-800"
                                        required
                                    >
                                        <option v-for="(type, name) in authTypes" :key="name" :value="type" class="dark:text-gray-100 dark:bg-neutral-800">
                                            {{ name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.auth_type" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.auth_type }}
                                    </div>
                                </div>

                                <template v-if="form.auth_type === magicMkAuthType">
                                    <div>
                                        <label for="magicmk_slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Magic
                                            MK Slug</label>
                                        <input
                                            type="text"
                                            id="magicmk_slug"
                                            v-model="form.magicmk_slug"
                                            class="mt-1 block w-full rounded-md border-gray-400 shadow-sm focus:border-green-700 focus:ring-green-700 dark:text-gray-100 dark:border-gray-700 dark:bg-neutral-800"
                                            required
                                        >
                                        <div v-if="form.errors.magicmk_slug" class="text-red-500 text-sm mt-1">
                                            {{ form.errors.magicmk_slug }}
                                        </div>
                                    </div>

                                    <div>
                                        <label for="magicmk_api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Magic
                                            MK API Key</label>
                                        <input
                                            type="text"
                                            id="magicmk_api_key"
                                            v-model="form.magicmk_api_key"
                                            class="mt-1 block w-full rounded-md border-gray-400 shadow-sm focus:border-green-700 focus:ring-green-700 dark:text-gray-100 dark:border-gray-700 dark:bg-neutral-800"
                                            required
                                        >
                                        <div v-if="form.errors.magicmk_api_key" class="text-red-500 text-sm mt-1">
                                            {{ form.errors.magicmk_api_key }}
                                        </div>
                                    </div>
                                </template>

                                <div class="flex justify-end">
                                    <button
                                        type="submit"
                                        class="inline-flex justify-center rounded-md border border-transparent bg-green-700 hover:bg-green-800 py-2 px-4 text-sm font-medium text-white shadow-sm  focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                        :disabled="form.processing"
                                    >
                                        Initialize Project
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <DarkModeButton/>
</template>

<script setup>
import {useForm, Head} from '@inertiajs/vue3';
import {ref} from 'vue';
import DarkModeButton from "@/Components/DarkModeButton.vue";

const props = defineProps({
    authTypes: {
        type: Array,
        required: true
    },
    userRoles: {
        type: Array,
        required: true
    },
    magicMkAuthType: {
        type: Number,
        required: true
    },
    userViewerRole: {
        type: Number,
        required: true
    }
});

const emailWhitelistText = ref('');

const form = useForm({
    name: '',
    logo: null,
    default_user_role: props.userViewerRole,
    auth_type: props.magicMkAuthType,
    magicmk_slug: '',
    magicmk_api_key: '',
});

const submit = () => {
    form.post(route('initialize.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>
