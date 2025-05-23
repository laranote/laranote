<template>
    <Head title="Login"/>

    <div class="w-full min-h-screen flex justify-center items-center dark:bg-neutral-800 dark:text-gray-100">
        <form @submit.prevent="submit" class="flex flex-col justify-center items-center">
            <img v-if="page.props.project?.logo_full_url" :src="page.props.project.logo_full_url" :alt="page.props.project.name" width="300" height="300" class="mb-5">
            <div v-else class="text-3xl font-bold mb-5">{{ page.props.project?.name }}</div>

            <div class="mb-4 w-64">
                <input
                    v-model="form.email"
                    type="email"
                    placeholder="Email"
                    class="w-full rounded-md dark:border-neutral-400 dark:text-black p-2"
                    required
                >
            </div>

            <div class="mb-4 w-64">
                <input
                    v-model="form.password"
                    type="password"
                    placeholder="Password"
                    class="w-full rounded-md dark:border-neutral-400 dark:text-black p-2"
                    required
                >
            </div>

            <button
                type="submit"
                class="border-2 rounded-md font-bold py-2 px-7 border-green-700 mt-2 hover:bg-green-700 hover:text-white transition-all"
                :disabled="processing"
            >
                {{ processing ? 'Logging in...' : 'Login' }}
            </button>

            <div v-if="errors" class="mt-4 text-red-500">
                <div v-for="(error, key) in errors" :key="key">{{ error }}</div>
            </div>
        </form>
    </div>
</template>

<script setup>
import {Head, useForm, usePage} from '@inertiajs/vue3';
import {ref} from 'vue';

const page = usePage();
const processing = ref(false);
const form = useForm({
    email: '',
    password: ''
});

const errors = ref(null);

const submit = async () => {
    processing.value = true;
    errors.value = null;

    form.post(route('standard-login'), {
        onSuccess: () => {
            processing.value = false;
        },
        onError: (err) => {
            errors.value = err;
            processing.value = false;
        }
    });
};
</script>

<style scoped>
</style>
