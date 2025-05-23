<template>
    <Head title="Authentication"/>
    <admin-layout>
        <div class="py-8 px-8 dark:text-gray-100">
            <h2 class="text-2xl font-bold mb-6">Authentication</h2>

            <form @submit.prevent="submit" class="max-w-2xl">
                <div class="mb-6">
                    <label for="auth_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Authentication Type</label>
                    <select
                        id="auth_type"
                        v-model="form.auth_type"
                        class="mt-1 block w-full rounded-md border-gray-400 shadow-sm focus:border-green-700 focus:ring-green-700 dark:text-gray-100 dark:border-gray-400 dark:bg-neutral-700"
                        required
                    >
                        <option v-for="(type, name) in authTypes" :key="name" :value="type" class="dark:text-gray-100 dark:bg-neutral-700">
                            {{ name }}
                        </option>
                    </select>
                    <div v-if="form.errors.auth_type" class="text-red-500 text-sm mt-1">
                        {{ form.errors.auth_type }}
                    </div>
                </div>

                <template v-if="form.auth_type === magicMkAuthType">
                    <div class="mb-6">
                        <label for="magicmk_slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Magic MK Slug</label>
                        <input
                            type="text"
                            id="magicmk_slug"
                            v-model="form.magicmk_slug"
                            class="mt-1 block w-full rounded-md border-gray-400 shadow-sm focus:border-green-700 focus:ring-green-700 dark:text-gray-100 dark:border-gray-400 dark:bg-neutral-700"
                            required
                        >
                        <div v-if="form.errors.magicmk_slug" class="text-red-500 text-sm mt-1">
                            {{ form.errors.magicmk_slug }}
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="magicmk_api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Magic MK API Key</label>
                        <input
                            type="text"
                            id="magicmk_api_key"
                            v-model="form.magicmk_api_key"
                            class="mt-1 block w-full rounded-md border-gray-400 shadow-sm focus:border-green-700 focus:ring-green-700 dark:text-gray-100 dark:border-gray-400 dark:bg-neutral-700"
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
                        class="inline-flex justify-center rounded-md border border-transparent bg-green-700 hover:bg-green-800 py-2 px-4 text-sm font-medium text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </admin-layout>
</template>

<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";

const props = defineProps({
    project: Object,
    authTypes: Object,
    magicMkAuthType: {
        type: Number,
        required: true
    }
});

const form = useForm({
    auth_type: props.project.auth_type,
    magicmk_slug: props.project.magicmk_slug,
    magicmk_api_key: props.project.magicmk_api_key
});

const submit = () => {
    form.post(route('authentication.update'), {
        preserveScroll: true
    });
};
</script>
