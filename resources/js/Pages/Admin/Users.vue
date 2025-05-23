<template>
    <Head title="Users"/>
    <admin-layout>
        <div class="py-12 ">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg dark:shadow-neutral-900">
                    <div class="p-6 dark:bg-neutral-800 dark:text-gray-300">
                        <h2 class="text-2xl font-bold mb-6 dark:text-gray-100">User Management</h2>

                        <div class="h-5 relative">
                            <small v-if="loading" class="text-gray-500 absolute dark:text-gray-200">Loading...</small>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 dark:bg-neutral-900 ">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                        Email
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                        Role
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                        Sign Up Date
                                    </th>

                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(user, user_n) in users" :key="user.id">
                                    <td class="px-6 py-4 whitespace-nowrap dark:bg-neutral-800 ">
                                        <div class="text-sm text-gray-900 dark:text-gray-300">{{ user.email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:bg-neutral-800">
                                        <div class="flex items-center gap-4">
                                            <template v-if="user.id === $page.props.auth.user.id">
                                                <span class="text-sm text-gray-900 dark:text-gray-300">
                                                    (Current User)
                                                </span>
                                            </template>
                                            <template v-else>
                                                <select
                                                    :disabled="loading"
                                                    v-model="users[user_n].role"
                                                    @change="changeRole(user_n)"
                                                    class="mt-1 block w-full py-2 ps-3 pe-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-neutral-800 dark:text-gray-300"
                                                >
                                                    <option v-for="(index, role) in userRoles"
                                                            :key="index"
                                                            :value="index">
                                                        {{ role }}
                                                    </option>
                                                </select>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap dark:bg-neutral-800 ">
                                        <div class="text-sm text-gray-900 dark:text-gray-300">{{ user.created_at }}</div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </admin-layout>
</template>

<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {Head, router} from "@inertiajs/vue3"
import {ref} from "vue"

const props = defineProps({
    users: {
        type: Array,
        required: true
    },
    userRoles: {
        type: Object,
    }
});

const loading = ref(false)

function changeRole(user_n) {
    loading.value = true;
    router.post(route("user.role.change"),
        {"user_id":  props.users[user_n].id, "role_id": props.users[user_n].role},
        {
            onFinish: () => {
                loading.value = false;
            },
        }
    );
}
</script>

<style scoped>

</style>
