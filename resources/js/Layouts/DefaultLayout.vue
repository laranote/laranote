<template>
    <div class="relative w-full h-screen dark:bg-neutral-800">
        <aside
            class="fixed top-0 left-0 w-64 h-screen flex flex-col border-l border-gray-200 bg-neutral-100 shadow-lg dark:bg-neutral-900 dark:border-gray-600">
            <Link :href="route('homepage')" class="p-4 border-b border-gray-200">
                <img v-if="page.props.project?.logo_full_url" :src="page.props.project.logo_full_url"
                     :alt="page.props.project.name">
                <div v-else class="text-xl font-bold text-black dark:text-gray-100 text-center">
                    {{ page.props.project?.name }}
                </div>
            </Link>
            <div ref="sidebarElement" class="flex-1 overflow-y-auto px-3 py-3">
                <div class="flex flex-col gap-1">
                    <draggable
                        v-model="rootPosts"
                        item-key="id"
                        :group="{ name: 'posts', pull: 'clone', put: true, revertClone: false }"
                        :sort="true"
                        :scroll="true"
                        :force-fallback="true"
                        :animation="200"
                        :delay="100"
                        :scrollSensitivity="60"
                        :scrollSpeed="10"
                        @change="handleChange"
                        @start="handleDragStart"
                        @end="handleDragEnd"
                        class="flex flex-col gap-1 overflow-x-auto overflow-y-hidden"
                    >

                    <template #item="{element}">
                            <div>
                                <div class="mb-1">
                                    <Link
                                        :href="route('posts.show', element.id)"
                                        class="group block px-3 py-1 hover:bg-gray-200 border-2 border-neutral-400 rounded-2xl !text-gray-700 font-bold transition-colors dark:hover:bg-neutral-800 dark:border-neutral-500"
                                    >
                                        <div class="flex justify-between items-center text-nowrap gap-1">
                                            <div class="min-w-3 dark:text-gray-300">
                                                <DraggableIcon/>
                                            </div>
                                            <div class="flex w-full justify-start items-center gap-3 overflow-x-auto dark:text-gray-300">
                                                <p>{{ element.title ?? element.created_at }}</p>
                                            </div>
                                            <button
                                                v-if="!page.props.is_viewer"
                                                class="opacity-0 group-hover:opacity-100 text-red-500 hover:text-red-700 dark:text-gray-300"
                                                @click.prevent="confirmDelete(element.id)"
                                            >
                                                ×
                                            </button>
                                        </div>
                                    </Link>
                                </div>

                                <!-- Child Draggable List -->
                                <draggable
                                    :model-value="childrenMap[element.id] || []"
                                    @update:model-value="value => childrenMap[element.id] = value"
                                    :group="{ name: 'posts', pull: 'clone', put: true, revertClone: false }"
                                    :data-parent-id="element.id"
                                    :delay="100"
                                    @change="(e) => handleChange(e, element.id)"
                                    @start="handleDragStart"
                                    @end="handleDragEnd"
                                    item-key="id"
                                    :scroll="true"
                                    :scroll-sensitivity="60"
                                    :scroll-speed="10"
                                    :force-fallback="true"
                                    :animation="200"
                                    class="flex flex-col gap-1 ml-6 pl-2 border-l border-neutral-400 overflow-x-auto overflow-y-hidden relative"
                                >

                                <template #item="{element: child}">
                                        <Link
                                            :href="route('posts.show', child.id)"
                                            class="group block px-3 py-1 mb-1 hover:bg-gray-200 border border-neutral-400 rounded-2xl !text-gray-700 font-bold transition-colors dark:hover:bg-neutral-800 dark:text-gray-300 dark:border-neutral-500"
                                        >
                                            <div class="flex justify-between items-center text-nowrap gap-1">
                                                <div class="min-w-3 dark:text-gray-300">
                                                    <DraggableIcon/>
                                                </div>
                                                <div class="flex w-full justify-start items-center gap-3 overflow-x-auto dark:text-gray-300">
                                                    <p>{{ child.title ?? child.created_at }}</p>
                                                </div>
                                                <button
                                                    class="opacity-0 group-hover:opacity-100 text-red-500 hover:text-red-700 dark:text-gray-300"
                                                    @click.prevent="confirmDelete(child.id)"
                                                >
                                                    ×
                                                </button>
                                            </div>
                                        </Link>
                                    </template>

                                    <!-- White space for dropping children -->
                                    <template #footer>
                                        <div
                                            v-if="isDragging"
                                            class="min-h-[40px] transition-all duration-200 hover:bg-green-100 dark:hover:bg-neutral-700 rounded border border-dashed border-neutral-400">
                                        </div>
                                    </template>
                                </draggable>
                            </div>
                        </template>
                    </draggable>

                    <div
                        v-if="!page.props.is_viewer"
                        class="group px-3 py-1 mb-2 hover:bg-gray-200 border border-neutral-400 rounded-2xl text-gray-700 font-bold transition-colors dark:hover:bg-neutral-800 dark:text-gray-300 dark:border-neutral-500 sticky bottom-0 bg-neutral-100 dark:bg-neutral-900 z-10">
                        <div v-if="!isCreatingPost" @click="startCreatePost" class="cursor-pointer">
                            + Add new note
                        </div>
                        <form v-else @submit.prevent="createPost">
                            <input
                                v-model="form.title"
                                type="text"
                                class="flex-1 p-0 px-1 bg-transparent border-0 border-transparent focus:border-transparent focus:ring-0"
                                placeholder="Enter title"
                                ref="createTitleInput"
                                :disabled="form.processing"
                            >
                        </form>
                    </div>
                </div>
            </div>

            <div class="p-4 border-t border-neutral-400 flex flex-col gap-3">
                <Link
                    v-if="page.props.is_admin"
                    :href="route('admin.index')"
                    class="w-full text-center !text-black py-2 px-4 border border-green-600 hover:bg-green-700 hover:!text-white rounded transition-colors dark:!text-gray-100">
                    Admin Page
                </Link>
                <Link
                    :href="route('docs')"
                    class="w-full text-center !text-white py-2 px-4 bg-green-700 hover:bg-green-800 rounded transition-colors"
                    style="color: white !important;">
                    Docs
                </Link>
                <Link
                    :href="route('logout')"
                    class="w-full text-center !text-black py-2 px-4 border border-green-600 hover:bg-green-700 hover:!text-white rounded transition-colors dark:!text-gray-100">
                    Logout
                </Link>
            </div>
        </aside>
        <div class="h-screen ml-64 dark:bg-neutral-800">
            <DarkModeButton/>
            <slot/>
        </div>
    </div>
</template>

<script setup>
import { nextTick, ref, watch, onMounted, onBeforeUnmount } from 'vue'
import draggable from 'vuedraggable'
import { Link, router, useForm, usePage } from "@inertiajs/vue3"
import DraggableIcon from "../../icons/DraggableIcon.vue"
import DarkModeButton from "@/Components/DarkModeButton.vue"

const page = usePage()
const sidebarScrollPosition = ref(0)
const sidebarElement = ref(null)
const props = defineProps({
    posts: {
        type: Array,
        required: true,
    }
})

const isCreatingPost = ref(false)
const createTitleInput = ref(null)
const rootPosts = ref([])
const childrenMap = ref({})
const isDragging = ref(false)

const handleDragStart = () => {
    setTimeout(() => {
        isDragging.value = true
    }, 1)
}

const handleDragEnd = () => {
    setTimeout(() => {
        isDragging.value = false
    }, 1)
    updateOrder()
}

const form = useForm({
    title: ''
})

const initializePosts = (posts) => {
    const children = {}
    rootPosts.value = posts.filter(post => {
        if (post.post_id) {
            if (!children[post.post_id]) children[post.post_id] = []
            children[post.post_id].push(post)
            return false
        }
        return true
    })
    childrenMap.value = children
}

const createPost = () => {
    form.post(route('posts.store'))
    form.reset()
    isCreatingPost.value = false
}

const startCreatePost = () => {
    isCreatingPost.value = true
    nextTick(() => createTitleInput.value?.focus())
}

const handleChange = (e, parentId = null) => {
    if (e.moved) {
        nextTick(() => updateOrder())
        return
    }

    if (!e.added) return

    const item = e.added.element
    const itemId = item.id.toString()
    const newParentId = parentId?.toString() ?? null
    const itemChildren = childrenMap.value[itemId] || []
    const isParent = itemChildren.length > 0

    // Update the item's parent ID
    item.post_id = newParentId

    // Remove item from all parent child arrays
    Object.keys(childrenMap.value).forEach(key => {
        childrenMap.value[key] = childrenMap.value[key].filter(p => p.id !== item.id)
    })

    // If moving to a parent
    if (newParentId) {
        childrenMap.value[newParentId] ||= []
        if (!childrenMap.value[newParentId].some(p => p.id === item.id)) {
            childrenMap.value[newParentId].push(item)
        }

        // Move children to new parent too
        if (isParent) {
            itemChildren.forEach(child => {
                child.post_id = newParentId
                if (!childrenMap.value[newParentId].some(p => p.id === child.id)) {
                    childrenMap.value[newParentId].push(child)
                }
            })
            childrenMap.value[itemId] = []
        }

        rootPosts.value = rootPosts.value.filter(p => p.id !== item.id)
    } else {
        // Moving to root
        if (!rootPosts.value.some(p => p.id === item.id)) {
            rootPosts.value.push(item)
        }

        // Move children to root
        if (isParent) {
            itemChildren.forEach(child => {
                child.post_id = null
                if (!rootPosts.value.some(p => p.id === child.id)) {
                    rootPosts.value.push(child)
                }
            })
            childrenMap.value[itemId] = []
        }
    }
}

const updateOrder = () => {
    const orderData = []
    let order = 0

    rootPosts.value.forEach(post => {
        orderData.push({ id: post.id, order: order++, post_id: null })
    })

    Object.entries(childrenMap.value).forEach(([parentId, children]) => {
        children.forEach(child => {
            orderData.push({ id: child.id, order: order++, post_id: parseInt(parentId) })
        })
    })

    try {
        router.post(route('posts.reorder'), { orders: orderData }, {
            preserveScroll: true,
            preserveState: true
        })
    } catch (e) {
        console.error('Failed to update post order:', e)
    }
}

const confirmDelete = (postId) => {
    if (confirm('Are you sure you want to delete this post?')) {
        router.delete(route('posts.destroy', postId))
    }
}

watch(() => props.posts, (newPosts) => {
    initializePosts(newPosts)
}, { immediate: true, deep: true })

// Save sidebar scroll position before navigation
onMounted(() => {
    sidebarElement.value = document.querySelector('.overflow-y-auto')
    if (sidebarElement.value) {
        const savedPosition = sessionStorage.getItem('sidebarScrollPosition')
        if (savedPosition) {
            sidebarElement.value.scrollTop = parseInt(savedPosition)
        }

        const noteLinks = document.querySelectorAll('.overflow-y-auto a')
        noteLinks.forEach(link => {
            link.addEventListener('click', saveScrollPosition)
        })
    }
})

onBeforeUnmount(() => {
    const noteLinks = document.querySelectorAll('.overflow-y-auto a')
    noteLinks.forEach(link => {
        link.removeEventListener('click', saveScrollPosition)
    })
})

const saveScrollPosition = () => {
    if (sidebarElement.value) {
        sessionStorage.setItem('sidebarScrollPosition', sidebarElement.value.scrollTop)
    }
}
</script>

<style>
.overflow-x-auto::-webkit-scrollbar {
    height: 0px;
}
.overflow-x-auto {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
