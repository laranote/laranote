<template>
    <div class="fixed top-2 right-20 flex items-center space-x-2">
        <!-- View Site Button (Only visible when shared) -->
        <transition name="fade">
            <a v-if="isShared" :href="sharedUrl" target="_blank">
                <button class="shared-btn bg-green-700 hover:bg-green-800 text-white">
                    View Note
                </button>
            </a>
        </transition>

        <!-- Copy Link Button (Only visible when shared) -->
        <transition name="fade">
            <div v-if="isShared" class="relative">
                <button
                    @click="copyLink"
                    class="shared-btn bg-neutral-700 hover:bg-neutral-600 flex items-center space-x-2 text-white"
                >
                    <span>{{ copied ? "Copied!" : "Copy Link" }}</span>
                    <svg
                        v-if="!copied"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 11V7a4 4 0 014-4h4a4 4 0 014 4v4m-8 4h4a4 4 0 004-4V7m-8 8H8a4 4 0 01-4-4V7"
                        />
                    </svg>
                </button>

                <!-- Tooltip -->
                <div
                    v-if="copied"
                    class="absolute bottom-[-35px] left-1/2 transform -translate-x-1/2 bg-black text-white text-xs py-1 px-2 rounded-md"
                >
                    Link Copied!
                </div>
            </div>
        </transition>

        <!-- Publish / Unpublish Button -->
        <button
            @click="toggleShare"
            :class="isShared
        ? 'border border-green-700 text-green-700 bg-white hover:bg-green-700 hover:text-white dark:bg-transparent dark:hover:text-gray-100 dark:text-green-700'
        : 'bg-green-700 hover:bg-green-800 text-white'"
            class="shared-btn"
        >
            {{ isShared ? 'Unpublish' : 'Publish' }}
        </button>

    </div>
</template>

<script setup>
import {ref, watch} from "vue";
import axios from "axios";

const props = defineProps({
    postId: Number,
    shared: Boolean,
});

const emit = defineEmits(['update:shared']);

const isShared = ref(props.shared);

// Watch for prop changes to update local state
watch(() => props.shared, (newValue) => {
    isShared.value = newValue;
});
const sharedUrl = route('public.post', props.postId);
const copied = ref(false);

// Toggle Publish/Unpublish
const toggleShare = async () => {
    try {
        const response = await axios.patch(route('posts.update', props.postId), {
            public: !isShared.value
        });

        // Update local state and emit event to parent
        isShared.value = !isShared.value;
        emit('update:shared', isShared.value);
    } catch (error) {
        console.error("Error toggling share status:", error);
    }
};

// Copy Link to Clipboard with animation
const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(sharedUrl);
        copied.value = true;

        // Reset after 2 seconds
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (error) {
        console.error("Failed to copy link:", error);
    }
};
</script>

<style>
/* Shared button styles for consistent font, size, padding */
.shared-btn {
    @apply h-8 px-4 text-sm font-medium rounded-md transition focus:outline-none focus:ring-2 focus:ring-offset-2;
}

/* Transition effects */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
