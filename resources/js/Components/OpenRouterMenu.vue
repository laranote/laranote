<template>
    <div v-if="show" class="openrouter-menu" :style="{ top: position.y + 'px', left: position.x + 'px' }" @click.stop>
        <!-- Model selection -->
        <div v-if="!showPrompt" class="menu-container">
            <div class="menu-header">
                <span>🤖 Choose AI Model</span>
            </div>
            <div class="model-list">
                <div
                    v-for="model in availableModels"
                    :key="model.id"
                    class="menu-item-openrouter model-item"
                    @click="selectModel(model.id)"
                >
                    <div class="model-info">
                        <span class="model-name">{{ model.name }}</span>
                        <span class="model-description">{{ model.description }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prompt input -->
        <div v-if="showPrompt" class="prompt-container-openrouter">
            <div class="prompt-header">
                <button @click="goBack" class="back-button">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                    </svg>
                </button>
                <span class="selected-model-name">{{ getModelDisplayName(selectedModel) }}</span>
            </div>
            <div class="input-section">
                <textarea
                    ref="promptInput"
                    v-model="prompt"
                    class="prompt-input-openrouter"
                    placeholder="Enter your prompt..."
                    rows="4"
                    @keydown.enter="submitPrompt"
                ></textarea>
                <button
                    class="prompt-button-openrouter"
                    :disabled="isLoading || !prompt.trim()"
                    @click="submitPrompt"
                >
                    <svg v-if="isLoading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span v-else>Send</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { marked } from 'marked';
import Swal from "sweetalert2";

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    editor: {
        type: Object,
        required: true
    },
    position: {
        type: Object,
        default: () => ({ x: 0, y: 0 })
    },
    close: {
        type: Function,
        required: true
    }
});

const prompt = ref('');
const isLoading = ref(false);
const promptInput = ref(null);
const cancelTokenSource = ref(null);
const showPrompt = ref(false);
const selectedModel = ref('');

const availableModels = [
    {
        id: 'anthropic/claude-3.5-sonnet',
        name: 'Claude 3.5 Sonnet',
        description: 'Best overall model for most tasks'
    },
    {
        id: 'openai/gpt-4o',
        name: 'GPT-4o',
        description: 'OpenAI\'s latest multimodal model'
    },
    {
        id: 'openai/gpt-4o-mini',
        name: 'GPT-4o Mini',
        description: 'Faster and cheaper GPT-4o variant'
    },
    {
        id: 'google/gemini-pro-1.5',
        name: 'Gemini Pro 1.5',
        description: 'Google\'s advanced AI model'
    },
    {
        id: 'meta-llama/llama-3.1-70b-instruct',
        name: 'Llama 3.1 70B',
        description: 'Meta\'s open-source model'
    },
    {
        id: 'mistralai/mistral-7b-instruct',
        name: 'Mistral 7B',
        description: 'Fast and efficient model'
    }
];

const handleClickOutside = (event) => {
    if (event.target.closest('.bubble-menu')) {
        return;
    }
    const menuElement = document.querySelector('.openrouter-menu');
    if (menuElement && !menuElement.contains(event.target)) {
        closeMenu();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    checkForSelectedText();
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

watch(() => showPrompt.value, (newVal) => {
    if (newVal) {
        nextTick(() => {
            promptInput.value?.focus();
        });
    }
});

const checkForSelectedText = () => {
    const { state } = props.editor;
    if (state.selection && !state.selection.empty) {
        const { from, to } = state.selection;
        const selectedText = state.doc.textBetween(from, to, ' ');
        prompt.value = selectedText;
        return true;
    }
    return false;
};

const closeMenu = () => {
    if (isLoading.value && cancelTokenSource.value) {
        cancelTokenSource.value.cancel('Operation canceled by the user');
    }
    isLoading.value = false;
    showPrompt.value = false;
    selectedModel.value = '';
    prompt.value = '';
    props.close();
};

const selectModel = (modelId) => {
    selectedModel.value = modelId;
    showPrompt.value = true;
};

const goBack = () => {
    showPrompt.value = false;
    selectedModel.value = '';
    prompt.value = '';
};

const getModelDisplayName = (modelId) => {
    const model = availableModels.find(m => m.id === modelId);
    return model ? model.name : modelId;
};

const submitPrompt = async () => {
    if (!prompt.value.trim() || isLoading.value) return;

    isLoading.value = true;
    cancelTokenSource.value = axios.CancelToken.source();

    try {
        const { state } = props.editor;
        const hasSelection = state.selection && !state.selection.empty;

        const response = await axios.post('/api/openrouter-ai/generate', {
            prompt: prompt.value,
            type: 'new',
            model: selectedModel.value
        }, {
            cancelToken: cancelTokenSource.value.token
        });

        if (isLoading.value) {
            const data = response.data;
            if (data.error) {
                // Handle error
                if (hasSelection) {
                    // Replace selected text with error message
                    props.editor.chain().focus()
                        .deleteSelection()
                        .insertContent(`<p><strong>Error:</strong> ${data.error}</p>`)
                        .run();
                } else {
                    props.editor.chain().focus()
                        .insertContent(`<p><strong>Error:</strong> ${data.error}</p>`)
                        .run();
                }
            } else {
                const content = data.content || '';
                const html = marked.parse(content);
                if (hasSelection) {
                    props.editor.chain().focus()
                        .deleteSelection()
                        .insertContent(html)
                        .run();
                } else {
                    props.editor.chain().focus()
                        .insertContent(html)
                        .run();
                }
            }
            closeMenu();
        }
    } catch (error) {
        if (axios.isCancel(error)) {
            console.log('Request canceled:', error.message);
        } else {
            console.error('Error:', error);
            // Only handle error if we're still loading (not canceled)
            if (isLoading.value) {
                // Extract error information
                let errorMessage = 'Failed to get response from OpenRouter AI';
                let errorDetails = 'Unknown error';

                if (error.response && error.response.data) {
                    // Get the user-friendly error message from our API
                    errorMessage = error.response.data.error || errorMessage;
                    errorDetails = error.response.data.message || error.message || 'Unknown error';
                } else {
                    errorDetails = error.message || 'Unknown error';
                }

                // Show a SweetAlert with the error
                Swal.fire({
                    icon: 'error',
                    title: 'AI Service Error',
                    text: errorMessage,
                    footer: `<small class="text-gray-500">${errorDetails}</small>`,
                    confirmButtonColor: 'green'
                });
            }
        }
    } finally {
        cancelTokenSource.value = null;
        isLoading.value = false;
    }
};
</script>

<style scoped>

</style>
