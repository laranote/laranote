<template>
    <div v-if="showTableMenu"
         class="table-menu fixed z-50 bg-white border border-gray-200 rounded-lg shadow-lg p-2 dark:bg-neutral-800 dark:border-gray-600"
         :style="{ left: menuPosition.x + 'px', top: menuPosition.y + 'px' }">

        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 px-1">
            Table Options
        </div>

        <!-- Row Controls -->
        <div class="flex items-center gap-1 mb-2">
            <span class="text-xs text-gray-600 dark:text-gray-300 w-12">Rows:</span>
            <button
                @click="addRowBefore"
                class="w-10 h-10 p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded text-xs border border-gray-200 dark:border-gray-600"
                title="Add row above"
            >
                ↑
            </button>
            <button
                @click="addRowAfter"
                class="w-10 h-10 p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded text-xs border border-gray-200 dark:border-gray-600"
                title="Add row below"
            >
                ↓
            </button>
            <button
                @click="deleteRow"
                class="w-10 h-10 p-1.5 hover:bg-red-100 dark:hover:bg-red-900 rounded text-xs border border-red-200 dark:border-red-600 text-red-600 dark:text-red-400"
                title="Delete current row"
            >
                ✕
            </button>
        </div>

        <!-- Column Controls -->
        <div class="flex items-center gap-1 mb-2">
            <span class="text-xs text-gray-600 dark:text-gray-300 w-12">Columns:</span>
            <button
                @click="addColumnBefore"
                class="w-10 h-10 p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded text-xs border border-gray-200 dark:border-gray-600"
                title="Add column left"
            >
                ←
            </button>
            <button
                @click="addColumnAfter"
                class="w-10 h-10 p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded text-xs border border-gray-200 dark:border-gray-600"
                title="Add column right"
            >
                →
            </button>
            <button
                @click="deleteColumn"
                class="w-10 h-10 p-1.5 hover:bg-red-100 dark:hover:bg-red-900 rounded text-xs border border-red-200 dark:border-red-600 text-red-600 dark:text-red-400"
                title="Delete current column"
            >
                ✕
            </button>
        </div>

        <!-- Table Actions -->
        <div class="border-t border-gray-200 dark:border-gray-600 pt-2">
            <button
                @click="toggleHeaderRow"
                class="w-full p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded text-xs border border-gray-200 dark:border-gray-600 mb-1"
                title="Toggle header row"
            >
                {{ hasHeaderRow ? 'Remove Header' : 'Add Header' }}
            </button>
            <button
                @click="deleteTable"
                class="w-full p-1.5 hover:bg-red-100 dark:hover:bg-red-900 rounded text-xs border border-red-200 dark:border-red-600 text-red-600 dark:text-red-400"
                title="Delete entire table"
            >
                Delete Table
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    editor: {
        type: Object,
        required: true
    }
})

const showTableMenu = ref(false)
const menuPosition = ref({ x: 0, y: 0 })

const isInTable = computed(() => {
    return props.editor?.isActive('table') || false
})

const hasHeaderRow = computed(() => {
    if (!props.editor?.isActive('table')) return false

    // Get the current table node
    const { state } = props.editor
    const { selection } = state
    let tableNode = null

    state.doc.nodesBetween(selection.from, selection.to, (node, pos) => {
        if (node.type.name === 'table') {
            tableNode = node
            return false
        }
    })

    if (!tableNode) return false

    const firstRow = tableNode.firstChild
    if (!firstRow) return false

    let hasHeader = false
    firstRow.forEach(cell => {
        if (cell.type.name === 'tableHeader') {
            hasHeader = true
        }
    })

    return hasHeader
})

// Table manipulation functions
const addRowBefore = () => {
    props.editor?.chain().focus().addRowBefore().run()
}

const addRowAfter = () => {
    props.editor?.chain().focus().addRowAfter().run()
}

const deleteRow = () => {
    props.editor?.chain().focus().deleteRow().run()
}

const addColumnBefore = () => {
    props.editor?.chain().focus().addColumnBefore().run()
}

const addColumnAfter = () => {
    props.editor?.chain().focus().addColumnAfter().run()
}

const deleteColumn = () => {
    props.editor?.chain().focus().deleteColumn().run()
}

const toggleHeaderRow = () => {
    props.editor?.chain().focus().toggleHeaderRow().run()
}

const deleteTable = () => {
    props.editor?.chain().focus().deleteTable().run()
    showTableMenu.value = false
}


const handleContextMenu = (event) => {
    const editorElement = props.editor?.options.element

    if (!editorElement?.contains(event.target)) {
        showTableMenu.value = false
        return
    }

    if (!isInTable.value) {
        showTableMenu.value = false
        return
    }

    // Prevent the default context menu from showing
    event.preventDefault()

    menuPosition.value = {
        x: event.clientX,
        y: event.clientY
    }

    showTableMenu.value = true
}


const handleClickOutside = (event) => {
    const menu = event.target.closest('.table-menu')
    if (!menu && showTableMenu.value) {
        showTableMenu.value = false
    }
}


onMounted(() => {
    document.addEventListener('contextmenu', handleContextMenu)
    document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
    document.removeEventListener('contextmenu', handleContextMenu)
    document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.table-menu {
    min-width: 180px;
    max-width: 200px;
}
</style>
