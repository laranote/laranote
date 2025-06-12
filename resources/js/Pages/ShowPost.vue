<template>
    <Head title="Note"/>
    <default-layout :posts="posts">
        <div class="container mx-auto max-w-5xl py-8 px-5 dark:text-gray-300 dark:bg-neutral-800">
            <div class="flex justify-between items-center mb-5">
                <div class="flex items-center gap-2">
                    <SharePostButtons
                        :postId="post.id"
                        :shared="!!post.public"
                        @update:shared="updatePostPublicStatus"
                    />
                </div>
            </div>
            <div class="max-w-[calc(100%-250px)]">
                <textarea
                    v-if="!page.props.is_viewer"
                    v-model="post.title"
                    @editor-mounted="setEditor"
                    class="rounded font-bold text-4xl mb-5 border-0 focus:ring-0 dark:bg-neutral-800 dark:text-gray-300 title-input w-full resize-none break-words whitespace-pre-wrap min-h-[3.5rem] leading-[1.2] overflow-hidden"
                    rows="1"
                    @input="autoResize"
                    ref="titleTextarea"
                ></textarea>
                <p v-else v-html="post.title"
                   class="font-bold text-xl mb-5 w-full dark:bg-neutral-800 dark:text-gray-300 break-words whitespace-pre-wrap">
                </p>
                <ContentEditor
                    :postId="post.id"
                    :collaborationPostName="collaborationPostName"
                    :hocuspocusUrl="hocuspocusUrl"
                    :hocuspocusToken="hocuspocusToken"
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
                        <TableOfContents :editor="editor" :items="items" />
                    </template>
                </div>
            </div>
        </div>
    </default-layout>
</template>

<script setup>
import DefaultLayout from "@/Layouts/DefaultLayout.vue";
import SharePostButtons from "@/Components/SharePostButtons.vue";
import ContentEditor from "@/Components/ContentEditor.vue";
import {Head, router, usePage} from "@inertiajs/vue3";
import _ from "lodash";
import {watch, ref, nextTick, onMounted} from "vue";
import TableOfContents from "@/Components/TableOfContents/TableOfContents.vue";

const page = usePage()

const props = defineProps({
    posts: {
        type: Object,
        required: true
    },
    post: {
        type: Object,
        required: true
    },
    collaborationPostName:{
        type: String,
        required: true
    },
    hocuspocusToken: {
        type: String,
        required: true
    },
    hocuspocusUrl: {
        type: String,
        required: true
    }
})

const saveTitle = () => {
    router.put(route('posts.update', props.post), {
        title: props.post.title,
    }, {
        only: ['posts'],
        onError: (errors) => {
            console.error('Save error:', errors)
        }
    })
}

const debouncedSave = _.debounce(saveTitle, 1000)

watch(
    [() => props.post.title],
    () => {
        debouncedSave()
    }, {deep: true}
)

const editor = ref(null)
const items = ref([])
const titleTextarea = ref(null)

// Auto-resize textarea to fit content
const autoResize = () => {
    if (titleTextarea.value) {
        titleTextarea.value.style.height = 'auto'
        titleTextarea.value.style.height = titleTextarea.value.scrollHeight + 'px'
    }
}

// Ensure textarea resizes on mount
onMounted(() => {
    nextTick(() => {
        autoResize()
    })
})

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

// Update post public status when SharePostButtons emits update:shared event
const updatePostPublicStatus = (isPublic) => {
    props.post.public = isPublic;
};

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
