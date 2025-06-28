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
                        <option v-for="(value, name) in roles" :key="value" :value="value">
                            {{ name }}
                        </option>
                    </select>
                </div>

                <!-- Gemini API Key -->
                <div class="mb-6">
                    <label for="gemini_api_key" class="block text-sm font-medium mb-2 dark:text-gray-200">Gemini API Key</label>
                    <div class="relative flex items-center">
                        <input
                            id="gemini_api_key"
                            :type="(!project.has_gemini_key && showKeys.gemini) ? 'text' : 'password'"
                            v-model="form.gemini_api_key"
                            :readonly="project.has_gemini_key"
                            :placeholder="project.has_gemini_key ? '••••••••••••••••••••••••••••••••' : 'Enter your Gemini API key'"
                            :class="project.has_gemini_key ? 'w-full px-3 py-2 border rounded-lg dark:bg-neutral-800 dark:border-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500' : 'w-full pr-10 px-3 py-2 border rounded-lg dark:bg-neutral-800 dark:border-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500'"
                        />
                        <button
                            v-if="!project.has_gemini_key"
                            type="button"
                            @click="toggleVisibility('gemini')"
                            class="absolute right-0 inset-y-0 flex items-center px-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer"
                        >
                            <VisibilityIcon v-if="showKeys.gemini" />
                            <VisibilityOffIcon v-else />
                        </button>
                    </div>
                    <div class="flex items-center justify-between mt-1">
                        <p class="text-sm" :class="project.has_gemini_key ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                            {{ project.has_gemini_key ? '✓ API key is configured' : 'No API key configured' }}
                        </p>
                        <button
                            v-if="project.has_gemini_key"
                            type="button"
                            @click="clearApiKey('gemini')"
                            class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                        >
                            Clear Key
                        </button>
                    </div>
                </div>

                <!-- Fal API Key -->
                <div class="mb-6">
                    <label for="fal_api_key" class="block text-sm font-medium mb-2 dark:text-gray-200">Fal API Key</label>
                    <div class="relative flex items-center">
                        <input
                            id="fal_api_key"
                            :type="(!project.has_fal_key && showKeys.fal) ? 'text' : 'password'"
                            v-model="form.fal_api_key"
                            :readonly="project.has_fal_key"
                            :placeholder="project.has_fal_key ? '••••••••••••••••••••••••••••••••' : 'Enter your Fal API key'"
                            :class="project.has_fal_key ? 'w-full px-3 py-2 border rounded-lg dark:bg-neutral-800 dark:border-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500' : 'w-full pr-10 px-3 py-2 border rounded-lg dark:bg-neutral-800 dark:border-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500'"
                        />
                        <button
                            v-if="!project.has_fal_key"
                            type="button"
                            @click="toggleVisibility('fal')"
                            class="absolute right-0 inset-y-0 flex items-center px-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer"
                        >
                            <VisibilityIcon v-if="showKeys.fal" />
                            <VisibilityOffIcon v-else />
                        </button>
                    </div>
                    <div class="flex items-center justify-between mt-1">
                        <p class="text-sm" :class="project.has_fal_key ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                            {{ project.has_fal_key ? '✓ API key is configured' : 'No API key configured' }}
                        </p>
                        <button
                            v-if="project.has_fal_key"
                            type="button"
                            @click="clearApiKey('fal')"
                            class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                        >
                            Clear Key
                        </button>
                    </div>
                </div>

                <!-- Openrouter API Key -->
                <div class="mb-6">
                    <label for="openrouter_api_key" class="block text-sm font-medium mb-2 dark:text-gray-200">Openrouter API Key</label>
                    <div class="relative flex items-center">
                        <input
                            id="openrouter_api_key"
                            :type="(!project.has_openrouter_key && showKeys.openrouter) ? 'text' : 'password'"
                            v-model="form.openrouter_api_key"
                            :readonly="project.has_openrouter_key"
                            :placeholder="project.has_openrouter_key ? '••••••••••••••••••••••••••••••••' : 'Enter your OpenRouter API key'"
                            :class="project.has_openrouter_key ? 'w-full px-3 py-2 border rounded-lg dark:bg-neutral-800 dark:border-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500' : 'w-full pr-10 px-3 py-2 border rounded-lg dark:bg-neutral-800 dark:border-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500'"
                        />
                        <button
                            v-if="!project.has_openrouter_key"
                            type="button"
                            @click="toggleVisibility('openrouter')"
                            class="absolute right-0 inset-y-0 flex items-center px-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer"
                        >
                            <VisibilityIcon v-if="showKeys.openrouter" />
                            <VisibilityOffIcon v-else />
                        </button>
                    </div>
                    <div class="flex items-center justify-between mt-1">
                        <p class="text-sm" :class="project.has_openrouter_key ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                            {{ project.has_openrouter_key ? '✓ API key is configured' : 'No API key configured' }}
                        </p>
                        <button
                            v-if="project.has_openrouter_key"
                            type="button"
                            @click="clearApiKey('openrouter')"
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
import {Head, useForm} from "@inertiajs/vue3";
import {ref, watch} from 'vue';
import VisibilityIcon from "../../../icons/VisibilityIcon.vue";
import VisibilityOffIcon from "../../../icons/VisibilityOffIcon.vue";

const props = defineProps({
    roles: {
        type: Object,
        required: true
    },
    project: {
        type: Object,
        required: true
    }
});

const imagePreview = ref(null);

const showKeys = ref({
    gemini: false,
    fal: false,
    openrouter: false,
});

const form = useForm({
    project_name: props.project.name,
    project_logo: null,
    default_role: props.project.default_user_role,
    remove_logo: false,
    gemini_api_key: '',
    fal_api_key: '',
    openrouter_api_key: '',
});

// Watch for project prop changes to update form
watch(() => props.project, (newProject) => {
    form.project_name = newProject.name;
    form.default_role = newProject.default_user_role;
}, { deep: true });

const handleImageInput = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.project_logo = file;
        form.remove_logo = false;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeLogo = () => {
    const removeForm = useForm({
        project_name: form.project_name,
        default_role: form.default_role,
        remove_logo: true
    });

    removeForm.post(route('admin.store'), {
        preserveScroll: true,
        onSuccess: () => {
            imagePreview.value = null;
        },
    });
};

function toggleVisibility(keyName) {
    showKeys.value[keyName] = !showKeys.value[keyName];
}

function clearApiKey(keyName) {
    const clearForm = useForm({
        project_name: form.project_name,
        default_role: form.default_role,
        gemini_api_key: keyName === 'gemini' ? 'CLEAR_KEY' : '',
        fal_api_key: keyName === 'fal' ? 'CLEAR_KEY' : '',
        openrouter_api_key: keyName === 'openrouter' ? 'CLEAR_KEY' : '',
    });

    clearForm.post(route('admin.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // The page will refresh with updated project data
        },
    });
}

const submit = () => {
    form.post(route('admin.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.project_logo = null;
            // Clear API key fields after successful save
            form.gemini_api_key = '';
            form.fal_api_key = '';
            form.openrouter_api_key = '';
            if (!form.remove_logo) {
                imagePreview.value = null;
            }
        },
    });
};
</script>
