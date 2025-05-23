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
                        class="w-full px-3 py-2 border rounded-lg dark:bg-neutral-700 dark:border-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500"
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
                        class="w-full px-3 py-2 border rounded-lg dark:bg-neutral-700 dark:border-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                        <option v-for="(value, name) in roles" :key="value" :value="value">
                            {{ name }}
                        </option>
                    </select>
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

const form = useForm({
    project_name: props.project.name,
    project_logo: null,
    default_role: props.project.default_user_role,
    remove_logo: false
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

const submit = () => {
    form.post(route('admin.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.project_logo = null;
            if (!form.remove_logo) {
                imagePreview.value = null;
            }
        },
    });
};
</script>
