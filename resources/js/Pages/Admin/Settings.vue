<template>
    <Head title="Admin"/>
    <admin-layout>
        <div class="py-8 px-8 dark:text-gray-100">
            <h2 class="text-2xl font-bold mb-6">Settings</h2>

            <form @submit.prevent="submit" class="max-w-2xl">
                <!-- Project Name -->
                <div class="mb-6">
                    <label for="project_name" class="block text-sm font-medium mb-2 dark:text-gray-200">Project Name</label>
                    <input
                        id="project_name"
                        v-model="form.project_name"
                        type="text"
                        class="w-full px-3 py-2 border rounded-lg dark:bg-neutral-800 dark:border-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Enter project name"
                    >
                </div>

                <!-- Project Logo -->
                <div class="mb-6">
                    <label for="project_logo" class="block text-sm font-medium mb-2 dark:text-gray-200">Project Logo</label>

                    <!-- Current Logo Preview -->
                    <div v-if="project.logo_full_url || imagePreview" class="mb-4 ">
                        <img
                            :src="imagePreview || project.logo_full_url"
                            alt="Project Logo"
                            class="max-w-xs rounded-lg shadow-lg"
                        >
                        <button
                            type="button"
                            @click="removeLogo"
                            class="mt-2 px-3 py-1 text-sm text-red-600 border border-red-600 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20"
                        >
                            Remove Logo
                        </button>
                    </div>

                    <!-- Logo Upload Input -->
                    <input
                        id="project_logo"
                        type="file"
                        @input="handleImageInput"
                        class="w-full px-3 py-2 border rounded-lg dark:bg-neutral-800 dark:border-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500"
                        accept="image/*"
                    >
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Recommended size: 467x91 pixels
                    </p>
                </div>

                <!-- Default User Role -->
                <div class="mb-6">
                    <label for="default_role" class="block text-sm font-medium mb-2 dark:text-gray-200">Default User Role</label>
                    <select
                        id="default_role"
                        v-model="form.default_role"
                        class="w-full px-3 py-2 border rounded-lg dark:bg-neutral-800 dark:border-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                        <option v-for="(value, name) in userRoles" :key="value" :value="value">
                            {{ name }}
                        </option>
                    </select>
                </div>

                <div v-for="key in apiKeyFields" :key="key.id" class="mb-6">
                    <label :for="key.id" class="block text-sm font-medium mb-2 dark:text-gray-200">{{ key.label }}</label>
                    <div class="relative flex items-center">
                        <input
                            :id="key.id"
                            :type="(!project[key.hasKeyProp] && showKeys[key.showKeyProp]) ? 'text' : 'password'"
                            v-model="form[key.model]"
                            :readonly="project[key.hasKeyProp]"
                            :placeholder="project[key.hasKeyProp] ? '••••••••••••••••••••••••••••••••' : `Enter your ${key.label} API key`"
                            :class="project[key.hasKeyProp] ? 'w-full px-3 py-2 border rounded-lg dark:bg-neutral-800 dark:border-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500' : 'w-full pr-10 px-3 py-2 border rounded-lg dark:bg-neutral-800 dark:border-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500'"
                        />
                        <button
                            v-if="!project[key.hasKeyProp]"
                            type="button"
                            @click="toggleVisibility(key.showKeyProp)"
                            class="absolute right-0 inset-y-0 flex items-center px-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer"
                        >
                            <VisibilityIcon v-if="showKeys[key.showKeyProp]" />
                            <VisibilityOffIcon v-else />
                        </button>
                    </div>
                    <div class="flex items-center justify-between mt-1">
                        <p class="text-sm" :class="project[key.hasKeyProp] ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                            {{ project[key.hasKeyProp] ? '✓ API key is configured' : 'No API key configured' }}
                        </p>
                        <button
                            v-if="project[key.hasKeyProp]"
                            type="button"
                            @click="clearApiKey(key.id)"
                            class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                        >
                            Clear Key
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                    :disabled="form.processing"
                >
                    Save Settings
                </button>
            </form>
        </div>
    </admin-layout>
</template>

<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {Head, useForm, usePage, router} from "@inertiajs/vue3";
import {ref, watch} from 'vue';
import VisibilityIcon from "../../../icons/VisibilityIcon.vue";
import VisibilityOffIcon from "../../../icons/VisibilityOffIcon.vue";

const props = defineProps({
    userRoles: {
        type: Object,
        required: true
    },
});

const imagePreview = ref(null);
const project = usePage().props.project;

const showKeys = ref({
    gemini: false,
    fal: false,
    openrouter: false,
});

const apiKeyFields = [
    {
        id: 'gemini',
        label: 'Gemini API Key',
        hasKeyProp: 'has_gemini_key',
        showKeyProp: 'gemini',
        model: 'gemini_api_key',
    },
    {
        id: 'fal',
        label: 'Fal API Key',
        hasKeyProp: 'has_fal_key',
        showKeyProp: 'fal',
        model: 'fal_api_key',
    },
    {
        id: 'openrouter',
        label: 'Openrouter API Key',
        hasKeyProp: 'has_openrouter_key',
        showKeyProp: 'openrouter',
        model: 'openrouter_api_key',
    },
]

const form = useForm({
    project_name: project.name,
    project_logo: null,
    default_role: project.default_user_role,
    gemini_api_key: '',
    fal_api_key: '',
    openrouter_api_key: '',
});

// Watch for project prop changes to update form
watch(() => project, (newProject) => {
    form.project_name = newProject.name;
    form.default_role = newProject.default_user_role;
}, { deep: true });

const handleImageInput = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.project_logo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeLogo = () => {
    const removeForm = useForm({});

    removeForm.delete(route('admin.logo.remove'), {
        preserveScroll: true,
        onSuccess: () => {
            imagePreview.value = null;
            router.visit(window.location.href);
        },
    });
};

function toggleVisibility(keyName) {
    showKeys.value[keyName] = !showKeys.value[keyName];
}

function clearApiKey(keyName) {
    const clearForm = useForm({
        key_type: keyName
    });

    clearForm.delete(route('admin.api-key.clear'), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(window.location.href);
        },
    });
}

const submit = () => {
    form.post(route('admin.store'), {
        preserveScroll: true,
        onSuccess: () => {
            imagePreview.value = null;
            router.visit(window.location.href);
        },
    });
};
</script>
