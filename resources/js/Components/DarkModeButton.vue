<script setup>
import { ref, onMounted } from 'vue';

const isDarkMode = ref(false);

onMounted(() => {
    if (localStorage.getItem('theme') === 'dark') {
        isDarkMode.value = true;
        document.documentElement.classList.add('dark');
    }
});

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};
</script>

<template>
    <button
        @click="toggleDarkMode"
        class="fixed top-2 right-2 w-14 h-8 flex items-center bg-neutral-300 dark:bg-neutral-700 rounded-full p-1 transition duration-300 shadow-lg z-[9999]"
    >
        <!-- Sun (Left Side) -->
        <span
            class="absolute left-2  text-gray-700 transition-opacity duration-300"
        >
            ☀️
        </span>

        <!-- Moon (Right Side) -->
        <span
            class="absolute right-2  text-gray-300 transition-opacity duration-300"
        >
            🌙
        </span>

        <!-- Toggle Circle (Moves to Cover Active Icon) -->
        <div
            class="absolute h-6 w-6 bg-white dark:bg-gray-900 rounded-full shadow-md transform transition-transform duration-300"
            :class="{ 'translate-x-6': isDarkMode }"
        ></div>
    </button>
</template>









