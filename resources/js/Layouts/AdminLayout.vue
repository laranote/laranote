<template>
    <div class="relative w-full h-screen dark:bg-neutral-800">
        <!-- Sidebar -->
        <aside class="fixed top-0 left-0 w-64 h-screen flex flex-col border-l border-gray-200 bg-neutral-100 shadow-lg dark:bg-neutral-900 dark:border-gray-600">
            <Link :href="route('homepage')" class="p-4 border-b border-gray-200 ">
                <img v-if="page.props.project?.logo_full_url" :src="page.props.project.logo_full_url" :alt="page.props.project.name">
                <div v-else class="text-xl font-bold text-black dark:text-gray-100 text-center">{{ page.props.project?.name }}</div>
            </Link>
            <div class="flex-1 overflow-y-auto p-1">
                <div class="flex flex-col gap-1">
                    <ul>
                        <li class="mb-2">
                            <Link :href="route('admin.index')">
                            <button class="w-full p-2 text-left rounded hover:bg-gray-300 dark:text-gray-100 dark:hover:bg-neutral-800 text-gray-950">
                                ⚙️ Settings
                            </button>
                            </Link>
                        </li>
                        <li class="mb-2">
                            <Link :href="route('users.index')">
                            <button class="w-full p-2 text-left rounded hover:bg-gray-300 dark:text-gray-100 dark:hover:bg-neutral-800 text-gray-950">
                                👥 Users
                            </button>
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('authentication.index')">
                            <button class="w-full p-2 text-left rounded hover:bg-gray-300 dark:text-gray-100 dark:hover:bg-neutral-800 text-gray-950">
                                🔐 Authentication
                            </button>
                            </Link>
                        </li>
                    </ul>
                </div>

            </div>
            <div class="p-4 border-t border-gray-200 flex flex-col gap-3">
                <Link
                    :href="route('docs')"
                    class="w-full text-center !text-white py-2 px-4 bg-green-700 hover:bg-green-800  rounded transition-colors"
                    style="color: white !important;"
                >
                    Docs
                </Link>

                <Link
                    :href="route('logout')"
                    class="w-full text-center !text-black py-2 px-4 border border-green-600 hover:bg-green-700 hover:!text-white rounded transition-colors  dark:!text-gray-100 dark:hover:bg-neutral-700"
                >
                    Logout
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="h-screen ml-64 dark:bg-neutral-800">
            <DarkModeButton/>
            <slot/>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import DarkModeButton from "@/Components/DarkModeButton.vue";
import {Link, usePage} from "@inertiajs/vue3";

const page = usePage();
</script>
