<template>
    <Head title="Documentation"/>
    <default-layout
        :posts="posts"
        @reset="resetEditor"
    >
        <div class="dark:bg-neutral-800">
        <div class="container mx-auto max-w-4xl py-8 px-4 dark:bg-neutral-800 dark:text-gray-300">

            <div class="space-y-8">
                <section>
                    <h2 class="text-2xl font-semibold mb-4">Basic Usage</h2>
                    <div class="prose dark:text-gray-300">
                        <ul>
                            <li><strong class="dark:text-gray-300">Create Note:</strong> Click "+ Add new note" to start a fresh note</li>
                            <li><strong class="dark:text-gray-300">Delete Note:</strong> Hover over a note and click the × button</li>
                            <li><strong class="dark:text-gray-300">Organize Notes:</strong>
                                <ul>
                                    <li>Drag and drop notes to reorder them</li>
                                    <li>Drag a note onto another note to create a parent-child relationship</li>
                                    <li>Child notes are indented and show a vertical line indicating their relationship</li>
                                    <li>Drag a child note back to the main list to remove the parent-child relationship</li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold mb-4">Editor Features</h2>
                    <div class="prose dark:text-gray-300">
                        <ul>
                            <li><strong class="dark:text-gray-300">Quick Formatting Menu:</strong> Access the formatting menu in two ways:
                                <ul>
                                    <li>Select text to show all formatting options</li>
                                    <li>Type "/" at the start of a line or after a space to show the menu</li>
                                </ul>
                                The menu includes options for:
                                <ul>
                                    <li>Bold, italic, and underline text</li>
                                    <li>Headings (H1, H2, H3)</li>
                                    <li>Lists (bullet, numbered, and task lists)</li>
                                    <li>Blockquotes</li>
                                    <li>Code blocks</li>
                                    <li><strong class="dark:text-gray-300">Tables:</strong> Insert and edit tables with resizable columns</li>
                                    <li><strong class="dark:text-gray-300">Horizontal Rules:</strong> Add divider lines to separate content</li>
                                    <li><strong class="dark:text-gray-300">Emojis:</strong> Quick access to common emojis via the emoji picker</li>
                                </ul>
                            </li>

                            <li><strong class="dark:text-gray-300">Autosave:</strong> Changes save automatically after 1 second</li>
                            <li><strong class="dark:text-gray-300">Status Indicator:</strong> Shows "Saving", "Saved", or "Error"</li>
                        </ul>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold mb-4">Keyboard Shortcuts</h2>
                    <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg dark:bg-neutral-900 ">
                        <div><code>⌘ + B</code><span class="ml-4">Bold</span></div>
                        <div><code>⌘ + I</code><span class="ml-4">Italic</span></div>
                        <div><code>⌘ + U</code><span class="ml-4">Underline</span></div>
                        <div><code>⌘ + 1</code><span class="ml-4">Heading 1</span></div>
                        <div><code>⌘ + 2</code><span class="ml-4">Heading 2</span></div>
                        <div><code>⌘ + 3</code><span class="ml-4">Heading 3</span></div>
                        <div><code>⌘ + Shift + 7</code><span class="ml-4">Ordered List</span></div>
                        <div><code>⌘ + Shift + 8</code><span class="ml-4">Bullet List</span></div>
                        <div><code>⌘ + Shift + 9</code><span class="ml-4">Task List</span></div>
                        <div><code>⌘ + Shift + B</code><span class="ml-4">Blockquote</span></div>
                        <div><code>⌘ + E</code><span class="ml-4">Code Block</span></div>
                    </div>
                </section>
            </div>
        </div>
        </div>
    </default-layout>
</template>

<script setup>
import {Head, router} from '@inertiajs/vue3'
import DefaultLayout from "../Layouts/DefaultLayout.vue"

defineProps({
    posts: {
        type: Array
    }
})

const resetEditor = () => {
    router.visit(route('homepage'))
}
</script>
