<template>
    <div class="w-full dark:text-gray-300 dark:bg-neutral-800">
        <EditorContent :editor="editor" class="border-0 rounded-lg"/>
    </div>
</template>

<script setup>
import {useEditor, EditorContent} from '@tiptap/vue-3'
import {usePage} from "@inertiajs/vue3";
import {onBeforeUnmount, onMounted} from 'vue'
import {createEditor} from '../Config/editor'
import {HocuspocusProvider} from '@hocuspocus/provider'

const page = usePage()
const emit = defineEmits(['editor-mounted'])

const props = defineProps({
    postId: {
        type: Number,
        required: true
    },
    hocuspocusUrl: {
        type: String,
        required: true
    },
    collaborationPostName: {
        type: String,
        required: true
    }
})

const provider = new HocuspocusProvider({
    url: props.hocuspocusUrl,
    name: props.collaborationPostName,
    token: "public",
    parameters: {
        post_id: props.postId,
    }
})

const editor = useEditor(createEditor({
    provider: provider,
    editable: false,
}))

// Emit editor instance to parent
onMounted(() => {
    if (editor.value) {
        emit('editor-mounted', editor.value)
    }
})

if (typeof window !== 'undefined') {
    onBeforeUnmount(() => {
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
