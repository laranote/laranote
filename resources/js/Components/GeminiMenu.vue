<template>
    <div v-if="show" class="gemini-menu" :style="{ top: position.y + 'px', left: position.x + 'px' }" @click.stop>
        <div v-if="!showPrompt" class="menu-container">
            <!-- Main menu options -->
            <div class="menu-item" @click="handleNewPrompt">
                <span>✏️ New prompt</span>
            </div>

            <div class="menu-item" @click="handleSummarize">
                <span>📖 Summarize text</span>
            </div>

            <div class="menu-item" @click="handleShorten">
                <span>📝 Shorten text</span>
            </div>

            <div class="menu-item" @click="handleSpelling">
                <span>✓ Fix grammar & spelling</span>
            </div>


            <!-- Translate submenu -->
            <div class="menu-item has-submenu" @click="toggleTranslateMenu">
                <div class="flex justify-between w-full">
                    <span>🌐 Translate text</span>
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>

            <!-- Language submenu -->
            <div v-if="showTranslateMenu" class="language-submenu">
                <div class="menu-item" v-for="language in languages" :key="language.code" @click="handleTranslate(language.code)">
                    <span>{{ language.name }}</span>
                </div>
            </div>
        </div>

        <!-- Prompt input -->
        <div v-if="showPrompt" class="prompt-container">
            <input
                ref="promptInput"
                v-model="prompt"
                class="prompt-input"
                :placeholder="promptPlaceholder"
                @keydown.enter="submitPrompt"
            />
            <button
                class="prompt-button"
                :disabled="isLoading || !prompt.trim()"
                @click="submitPrompt"

            >
                <svg v-if="isLoading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span v-else>Go</span>
            </button>
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
const showTranslateMenu = ref(false);
const showPrompt = ref(false);
const promptType = ref('new');
const promptPlaceholder = ref('Enter your prompt...');
const selectedText = ref('');
const languages = [
    {code: 'en', name: 'English'},
    {code: 'mk', name: 'Macedonian'},
    {code: 'es', name: 'Spanish'},
    {code: 'fr', name: 'French'},
    {code: 'de', name: 'German'},
    {code: 'it', name: 'Italian'},
    {code: 'pt', name: 'Portuguese'},
    {code: 'ru', name: 'Russian'},
    {code: 'zh', name: 'Chinese'},
    {code: 'ja', name: 'Japanese'},
    {code: 'ko', name: 'Korean'}
];
const handleClickOutside = (event) => {
    if (event.target.closest('.bubble-menu')) {
        return;
    }
    const menuElement = document.querySelector('.gemini-menu');
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
        selectedText.value = state.doc.textBetween(from, to, ' ');
        return true;
    }
    selectedText.value = '';
    return false;
};
const closeMenu = () => {
    if (isLoading.value && cancelTokenSource.value) {
        cancelTokenSource.value.cancel('Operation canceled by the user');
    }
    isLoading.value = false;
    showPrompt.value = false;
    showTranslateMenu.value = false;
    props.close();
};
const toggleTranslateMenu = () => {
    showTranslateMenu.value = !showTranslateMenu.value;
};
const handleNewPrompt = () => {
    promptType.value = 'new';
    promptPlaceholder.value = 'Enter your prompt...';
    if (checkForSelectedText()) {
        prompt.value = selectedText.value;
    } else {
        prompt.value = '';
    }
    showPrompt.value = true;
};
const handleSummarize = () => {
    if (!checkForSelectedText()) {
        Swal.fire({
            icon: 'warning',
            title: 'Oops...',
            text: 'Please select text to summarize!',
            confirmButtonColor: 'green'
        });
        return;
    }
    promptType.value = 'summarize';
    promptPlaceholder.value = 'Summarize the selected text in the language it is written...';
    prompt.value = 'Summarize the selected text';
    submitPrompt();
};
const handleShorten = () => {
    if (!checkForSelectedText()) {
        Swal.fire({
            icon: 'warning',
            title: 'Oops...',
            text: 'Please select text to shorten!',
            confirmButtonColor: 'green'
        });
        return;
    }
    promptType.value = 'shorten';
    promptPlaceholder.value = 'Shorten the selected text...';
    prompt.value = 'Shorten the selected text while preserving its meaning';
    submitPrompt();
};
const handleSpelling = () => {
    if (!checkForSelectedText()) {
        Swal.fire({
            icon: 'warning',
            title: 'Oops...',
            text: 'Please select text to fix grammar & spelling!',
            confirmButtonColor: 'green'
        });
        return;
    }
    promptType.value = 'spelling';
    promptPlaceholder.value = 'Fix the grammar and the spelling of the selected text...';
    prompt.value = 'Fix the grammar and the spelling of the selected text';
    submitPrompt();
};
const handleTranslate = (languageCode) => {
    if (!checkForSelectedText()) {
        Swal.fire({
            icon: 'warning',
            title: 'Oops...',
            text: 'Please select text to translate!',
            confirmButtonColor: 'green'
        });
        return;
    }
    promptType.value = 'translate';
    const languageName = languages.find(lang => lang.code === languageCode)?.name || languageCode;
    promptPlaceholder.value = `Translate to ${languageName}...`;
    prompt.value = `Translate to ${languageName}`;
    submitPrompt();
    showTranslateMenu.value = false;
};
const submitPrompt = async () => {
    if (!prompt.value.trim() || isLoading.value) return;
    isLoading.value = true;
    cancelTokenSource.value = axios.CancelToken.source();
    try {
        const { state } = props.editor;
        const hasSelection = state.selection && !state.selection.empty;
        let actualText = '';
        if (hasSelection) {
            const { from, to } = state.selection;
            actualText = state.doc.textBetween(from, to, ' ');
        }
        let finalPrompt = prompt.value;
        if (hasSelection && ['summarize', 'shorten', 'translate', 'spelling'].includes(promptType.value)) {
            switch (promptType.value){
                case 'summarize':
                    finalPrompt = `Summarize this text concisely: ${actualText}`;
                    break;
                case 'shorten':
                    finalPrompt = `Shorten this text while preserving its meaning: ${actualText}`;
                    break;
                case 'spelling':
                    finalPrompt = `Fix the grammar and spelling of this text: ${actualText}`;
                    break;
                case 'translate':
                    // Extract the target language from the prompt
                    const targetLang = prompt.value.replace('Translate to ', '');
                    finalPrompt = `Translate this text to ${targetLang}. Only return the translated text without explanations: ${actualText}`;
                    break;
            }
        }
        const response = await axios.post('/api/gemini-ai/generate', {
            prompt: finalPrompt,
            type: promptType.value
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
            prompt.value = '';
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
                let errorMessage = 'Failed to get response from Gemini AI';
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
