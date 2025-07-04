<template>
    <Head title="Authentication"/>
    <admin-layout>
        <div class="py-8 px-8 dark:text-gray-100">
            <h2 class="text-2xl font-bold mb-6">Authentication</h2>

            <form @submit.prevent="submit" class="max-w-2xl">
                <div class="mb-6">
                    <label for="auth_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Authentication
                        Type</label>
                    <select
                        id="auth_type"
                        v-model="form.auth_type"
                        class="mt-1 block w-full rounded-md border-gray-400 shadow-sm focus:border-green-700 focus:ring-green-700 dark:text-gray-100 dark:border-gray-400 dark:bg-neutral-700"
                        required
                    >
                        <option v-for="(type, name) in authTypes" :key="name" :value="type"
                                class="dark:text-gray-100 dark:bg-neutral-700">
                            {{ name }}
                        </option>
                    </select>
                    <div v-if="form.errors.auth_type" class="text-red-500 text-sm mt-1">
                        {{ form.errors.auth_type }}
                    </div>
                </div>

                <template v-if="form.auth_type === magicMkAuthType">
                    <div class="mb-6">
                        <label for="magicmk_slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Magic
                            MK Slug</label>
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
                        <label for="magicmk_api_key" class="block text-sm font-medium mb-2 dark:text-gray-200">Magic MK
                            API Key</label>
                        <div class="relative flex items-center">
                            <input
                                id="magicmk_api_key"
                                :type="(!project.has_magicmk_key && showKey) ? 'text' : 'password'"
                                v-model="form.magicmk_api_key"
                                :readonly="project.has_magicmk_key"
                                :placeholder="project.has_magicmk_key ? '••••••••••••••••••••••••••••••••' : 'Enter your MagicMK API key'"
                                :class="project.has_magicmk_key ? 'w-full px-3 py-2 border rounded-lg dark:bg-neutral-800 dark:border-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500' : 'w-full pr-10 px-3 py-2 border rounded-lg dark:bg-neutral-800 dark:border-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500'"
                            />
                            <button
                                v-if="!project.has_magicmk_key"
                                type="button"
                                @click="toggleVisibility()"
                                class="absolute right-0 inset-y-0 flex items-center px-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer"
                            >
                                <VisibilityIcon v-if="showKey"/>
                                <VisibilityOffIcon v-else/>
                            </button>
                        </div>
                        <div class="flex items-center justify-between mt-1">
                            <p class="text-sm"
                               :class="project.has_openrouter_key ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                                {{ project.has_magicmk_key ? '✓ API key is configured' : 'No API key configured' }}
                            </p>
                            <button
                                v-if="project.has_magicmk_key"
                                type="button"
                                @click="clearApiKey()"
                                class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                            >
                                Clear Key
                            </button>
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
import {Head, useForm, router, usePage} from "@inertiajs/vue3";
import VisibilityIcon from "../../../icons/VisibilityIcon.vue";
import VisibilityOffIcon from "../../../icons/VisibilityOffIcon.vue";
import {ref} from "vue";

const props = defineProps({
    authTypes: {
        type: Object,
        required: true
    },
    magicMkAuthType: {
        type: Number,
        required: true
    }
});

const project = usePage().props.project;

const showKey = ref(false);

const form = useForm({
    auth_type: project.auth_type,
    magicmk_slug: project.magicmk_slug,
    magicmk_api_key: ""
});

function toggleVisibility() {
    showKey.value = !showKey.value;
}

function clearApiKey() {
    const clearForm = useForm({
        key_type: 'magicmk'
    });

    clearForm.delete(route('admin.api-key.clear'), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(window.location.href);
        },
    });
}

const submit = () => {
    form.post(route('authentication.update'), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(window.location.href);
        },
    });
};
</script>
