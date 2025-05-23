<template>
    <Head title="Note"/>
    <public-layout>
        <div class="container mx-auto max-w-5xl py-8 px-5 dark:text-gray-300 dark:bg-neutral-800">
            <div class="">
                <p v-html="post.title"
                   class="font-bold text-xl mb-5 w-full dark:bg-neutral-800 dark:text-gray-300">
                </p>
                <ShowContent
                    :collaborationPostName="collaborationPostName"
                    :postId="post.id"
                    :hocuspocusUrl="hocuspocusUrl"
                    @editor-mounted="setEditor"
                    :key="post.id"
                />
            </div>
        </div>
        <div class="sidebar">
            <div class="sidebar-options">
                <div class="label-large"></div>
                <div class="table-of-contents">
                    <template v-if="editor">
                        <TableOfContents :editor="editor" :items="items"/>
                    </template>
                </div>
            </div>
        </div>
    </public-layout>
</template>

<script setup>
import {Head} from "@inertiajs/vue3";
import {ref} from "vue";
import TableOfContents from "@/Components/TableOfContents/TableOfContents.vue";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import ShowContent from "@/Components/ShowContent.vue";

const props = defineProps({
    post: {
        type: Object,
        required: true
    },
    collaborationPostName: {
        type: String,
        required: true
    },
    hocuspocusUrl: {
        type: String,
        required: true
    }
})

const editor = ref(null)
const items = ref([])

// Get a reference to the editor instance when ContentEditor mounts
const setEditor = (editorInstance) => {
    editor.value = editorInstance;

    // Initialize items when the editor is available
    if (editor.value) {
        // Get the table of contents items from the extension
        const tocExtension = editor.value.extensionManager.extensions.find(
            extension => extension.name === 'tableOfContents'
        );

        if (tocExtension) {
            // Initial update of TOC items
            setTimeout(() => {
                updateTocItems();
            }, 300);

            // Update items when the editor content changes
            editor.value.on('update', () => {
                updateTocItems();
            });
        }
    }
}

// Function to update TOC items from the editor
const updateTocItems = () => {
    if (!editor.value) return;

    // Find all heading elements in the editor
    const headings = editor.value.view.dom.querySelectorAll('h1, h2, h3');

    if (headings.length === 0) {
        items.value = [];
        return;
    }

    // Convert NodeList to Array and map to TOC items
    items.value = Array.from(headings).map((heading, index) => {
        // Get heading level (h1 = 1, h2 = 2, etc.)
        const level = parseInt(heading.tagName.substring(1));

        // Create a unique ID if not present
        const id = heading.id || `heading-${index}`;
        if (!heading.id) {
            heading.id = id;
            // Add data-toc-id attribute for click navigation
            heading.setAttribute('data-toc-id', id);
        }

        const originalText = heading.textContent;

        const hasCollabCursor = heading.querySelector('.collaboration-cursor__label');

        let headingText = originalText;

        if (hasCollabCursor) {
            const textNodes = [];
            heading.childNodes.forEach(node => {
                if (node.nodeType === Node.TEXT_NODE) {
                    textNodes.push(node.textContent);
                }
                else if (node.nodeType === Node.ELEMENT_NODE &&
                         !node.classList.contains('collaboration-cursor__label') &&
                         !node.classList.contains('collaboration-cursor__caret')) {
                    textNodes.push(node.textContent);
                }
            });

            headingText = textNodes.join('').trim();

            if (!headingText) {
                headingText = originalText;
            }
        }

        return {
            id: id,
            level: level,
            text: headingText,
            textContent: headingText,
            isActive: false,
            isScrolledOver: false
        };
    });
}
</script>

<style scoped>
</style>
