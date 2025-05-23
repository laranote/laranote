<template>
    <div>
        <template v-if="!items || items.length === 0">
            <div class="empty-state">
                <p>Table of Contents</p>
            </div>
        </template>
        <template v-else>
            <p class="empty-state">Table of Contents</p>
            <TableOfContentsItem
                v-for="(item, i) in items"
                :key="item.id || i"
                :item="item"
                :index="i + 1"
                @item-click="onItemClick"
            />
        </template>
    </div>
</template>

<script setup>
import { TextSelection } from '@tiptap/pm/state'
// Removed import for TableOfContentsEmptyState
import TableOfContentsItem from "@/Components/TableOfContents/TableOfContentsItem.vue";

const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },
    editor: {
        type: Object,
        required: true,
    },
});

const onItemClick = (e, id) => {
    if (props.editor) {
        const element = props.editor.view.dom.querySelector(`[data-toc-id="${id}"]`)
        if (!element) return

        const pos = props.editor.view.posAtDOM(element, 0)
        const tr = props.editor.view.state.tr

        tr.setSelection(new TextSelection(tr.doc.resolve(pos)))
        props.editor.view.dispatch(tr)
        props.editor.view.focus()

        if (history.pushState) {
            history.pushState(null, null, `#${id}`)
        }

        window.scrollTo({
            top: element.getBoundingClientRect().top + window.scrollY,
            behavior: 'smooth',
        })
    }
}
</script>
