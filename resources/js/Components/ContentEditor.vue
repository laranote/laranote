<template>
    <div class="w-full dark:text-gray-300 dark:bg-neutral-800">
        <BubbleMenu
            v-if="editor && !page.props.is_viewer"
            :should-show="shouldShowBubbleMenu"
            :editor="editor"
            :tippy-options="{
                duration: 100,
                placement: 'bottom-start',
                offset: [0, 10]
            }"
            class="bubble-menu flex items-center gap-1 p-2 rounded-lg shadow-lg bg-white border border-gray-200 min-w-[840px] dark:text-gray-300 dark:bg-neutral-800 "
        >
            <template v-for="(section, sectionIndex) in buttonSections" :key="sectionIndex">
                <template v-for="(button, buttonIndex) in section" :key="buttonIndex">
                    <EditorButton
                        :icon="button.icon"
                        :title="button.title"
                        :is-active="button.isActive(editor)"
                        :onClick="() => button.onClick(editor, setLink)"
                    />
                </template>
                <div v-if="sectionIndex < buttonSections.length - 1" class="w-px h-5 bg-gray-200 mx-1 dark:bg-gray-600"></div>
            </template>

            <div class="relative">
                <EditorButton
                    ref="emojiButtonRef"
                    :icon="EmoticonHappyOutlineIcon"
                    title="Insert Emoji"
                    :is-active="false"
                    :onClick="() => showEmojiPicker = !showEmojiPicker"
                />

                <div v-if="showEmojiPicker"
                     class="absolute z-50 bg-white border border-gray-200 rounded-lg shadow-lg p-2 mt-1 right-0 w-[320px] ">
                    <div class="grid grid-cols-8 gap-1 max-h-[200px] overflow-y-auto p-2 ">
                        <button
                            v-for="emoji in commonEmojis"
                            :key="emoji"
                            @click="insertEmoji(emoji)"
                            class="p-1.5 hover:bg-gray-100 rounded text-xl "
                        >
                            {{ emoji }}
                        </button>
                    </div>
                </div>
            </div>
        </BubbleMenu>
        <TableOptionsMenu v-if="editor" :editor="editor" />
        <EditorContent :editor="editor" class="border-0 rounded-lg"/>
    </div>
</template>

<script setup>
import {useEditor, EditorContent, BubbleMenu} from '@tiptap/vue-3'
import {usePage} from "@inertiajs/vue3";
import {ref, onBeforeUnmount, onMounted} from 'vue'
import {commonEmojis} from '../Data/emojis'
import {buttonSections} from '../Data/editorButtons'
import {createEditor} from '../Config/editor'
import EditorButton from './EditorButton.vue'
import TableOptionsMenu from './TableOptionsMenu.vue'
import EmoticonHappyOutlineIcon from 'vue-material-design-icons/EmoticonHappyOutline.vue'
import {HocuspocusProvider} from '@hocuspocus/provider'

const showEmojiPicker = ref(false)
const emojiButtonRef = ref(null)

const page = usePage()
const emit = defineEmits(['editor-mounted'])

const props = defineProps({
    postId:{
        type: Number,
        required: true
    },
    hocuspocusUrl: {
        type: String,
        required: true
    },
    hocuspocusToken: {
        type: String,
        required: true
    },
    collaborationPostName: {
        type: String,
        required: true
    }
})
const insertEmoji = (emoji) => {
    editor.value.chain().focus().insertContent(emoji).run()
    showEmojiPicker.value = false
}

const provider = new HocuspocusProvider({
    url: props.hocuspocusUrl,
    name: props.collaborationPostName,
    token: props.hocuspocusToken,
    parameters: {
        post_id: props.postId,
    }
})

const editor = useEditor(createEditor({
    provider: provider,
    editable: !page.props.is_viewer,
}))

// Emit editor instance to parent
onMounted(() => {
    if (editor.value) {
        emit('editor-mounted', editor.value)
    }
})

function setLink() {
    const previousUrl = editor.value.getAttributes('link').href
    const url = window.prompt('URL', previousUrl)

    // cancelled
    if (url === null) {
        return
    }

    // empty
    if (url === '') {
        editor.value
            .chain()
            .focus()
            .extendMarkRange('link')
            .unsetLink()
            .run()

        return
    }

    // update link
    editor.value
        .chain()
        .focus()
        .extendMarkRange('link')
        .setLink({href: url})
        .run()
}

const shouldShowBubbleMenu = ({editor}) => {
    if (!editor || page.props.is_viewer) return false;

    const {state} = editor;
    const {selection} = state;
    const {empty, from} = selection;

    // Check if we're at a "/" character
    const lastChar = state.doc.textBetween(Math.max(0, from - 1), from);
    if (lastChar === '/') {
        // Get the text before the "/"
        const textBefore = state.doc.textBetween(Math.max(0, from - 2), from - 1);
        // Show if we're at the start of a line or after a space
        if (!textBefore || textBefore === ' ' || textBefore === '\n') {
            return true;
        }
    }

    // Show for links or selections
    return editor.isActive('link') || !empty;
};

// Handle emoji picker clicks outside
const handleClickOutside = (event) => {
    if (showEmojiPicker.value && !emojiButtonRef.value?.contains(event.target)) {
        showEmojiPicker.value = false;
    }
};

// Handle escape key for emoji picker
const handleKeyDown = (event) => {
    if (event.key === 'Escape' && showEmojiPicker.value) {
        showEmojiPicker.value = false;
        event.preventDefault();
    }
};

if (typeof window !== 'undefined') {
    window.addEventListener('click', handleClickOutside);
    window.addEventListener('keydown', handleKeyDown);

    // Cleanup event listeners
    onBeforeUnmount(() => {
        window.removeEventListener('click', handleClickOutside);
        window.removeEventListener('keydown', handleKeyDown);
        editor.value?.destroy()
        provider.destroy()
    });

}

</script>

<style src="../../css/ContentEditor.css"></style>
<style src="highlight.js/styles/github-dark.css"></style>

<style>


/* Cursor styles */
.collaboration-cursor__caret {
    border-left: 1px solid #0d0d0d;
    border-right: 1px solid #0d0d0d;
    margin-left: -1px;
    margin-right: -1px;
    pointer-events: none;
    position: relative;
    word-break: normal;
}

.collaboration-cursor__label {
    border-radius: 3px 3px 3px 0;
    color: #fff;
    font-size: 12px;
    font-style: normal;
    font-weight: 600;
    left: -1px;
    line-height: normal;
    padding: 0.1rem 0.3rem;
    position: absolute;
    top: -1.4em;
    user-select: none;
    white-space: nowrap;
}
</style>
