import { Extension } from '@tiptap/core'
import { Plugin, PluginKey } from '@tiptap/pm/state'
import { createApp } from 'vue'
import DragHandleIcon from "../../icons/DragHandleIcon.vue";

export const DragHandleExtension = Extension.create({
    name: 'dragHandle',

    addProseMirrorPlugins() {
        return [
            new Plugin({
                key: new PluginKey('dragHandlePlugin'),
                view: (editorView) => {
                    return new DragHandlePlugin({
                        editorView,
                        editor: this.editor,
                        options: this.options,
                    })
                }
            })
        ]
    },
})

// Class that handles drag-and-drop functionality for blocks in the editor
class DragHandlePlugin {
    constructor({ editorView, editor, options }) {
        this.editorView = editorView
        this.editor = editor
        this.options = options
        this.currentPos = null
        this.currentNode = null
        this.dragHandleElement = null
        this.mountElement = null
        this.dropIndicator = null
        this.isDragging = false
        this.isMouseOverHandle = false
        this.hoverTimeout = null
        this.scrollInterval = null
        this.scrollSpeed = 0

        // Don't initialize drag handle for viewers
        if (!this.editor.isEditable) {
            return
        }

        // Create DOM elements
        this.createDragHandle()
        this.createDropIndicator()

        // Add event listeners
        this.editorView.dom.addEventListener('mousemove', this.handleMouseMove)
        document.addEventListener('mouseleave', this.handleMouseLeave)
    }

    // Creates the drag handle element and attaches it to the DOM
    createDragHandle() {
        // Create container for the drag handle
        this.mountElement = document.createElement('div')
        this.mountElement.className = 'drag-handle-mount'
        document.body.appendChild(this.mountElement)

        // Create drag handle element container
        this.dragHandleElement = document.createElement('div')
        this.dragHandleElement.className = 'editor-drag-handle'
        document.body.appendChild(this.dragHandleElement)

        // Create container for Vue component
        const iconContainer = document.createElement('div')
        this.dragHandleElement.appendChild(iconContainer)

        // Mount Vue component to the container
        const app = createApp(DragHandleIcon)
        app.mount(iconContainer)

        // Add drag handle event listeners
        this.dragHandleElement.addEventListener('mousedown', this.handleDragStart)
        this.dragHandleElement.addEventListener('mouseenter', this.handleMouseEnter)
        this.dragHandleElement.addEventListener('mouseleave', this.handleMouseLeaveHandle)
    }

    // Handle mouse enter on the drag handle
    handleMouseEnter = () => {
        this.isMouseOverHandle = true
    }

    // Handle mouse leave on the drag handle
    handleMouseLeaveHandle = () => {
        this.isMouseOverHandle = false
        if (!this.isDragging) {
            this.scheduledHide()
        }
    }

    // Creates the drop indicator element and attaches it to the DOM
    createDropIndicator() {
        this.dropIndicator = document.createElement('div')
        this.dropIndicator.className = 'drag-drop-indicator'
        document.body.appendChild(this.dropIndicator)
    }

    // Handles mouse movement over the editor and shows the drag handle when near a valid block node
    handleMouseMove = (event) => {
        // Skip if editor is not editable (shared post)
        if (!this.editor.isEditable || this.isDragging || this.isMouseOverHandle) return

        // Clear pending hide operation
        clearTimeout(this.hoverTimeout)

        // Get position at cursor
        const pos = this.editorView.posAtCoords({
            left: event.clientX,
            top: event.clientY
        })

        if (!pos) {
            this.scheduledHide()
            return
        }

        // Check if we're near the left edge
        const editorRect = this.editorView.dom.getBoundingClientRect()
        const relativeX = event.clientX - editorRect.left

        if (relativeX > this.options.dragHandleWidth) {
            this.scheduledHide()
            return
        }

        const blockNode = this.findBlockNodeAt(pos.pos)
        if (!blockNode.node || blockNode.pos === null) {
            this.scheduledHide()
            return
        }

        // Only update if we're showing a different node
        if (this.currentPos !== blockNode.pos) {
            this.currentNode = blockNode.node
            this.currentPos = blockNode.pos
            this.showDragHandle(event.clientY)
        }
    }

    // Finds a block node at the given position and returns {node, pos} or {null, null} if not found
    findBlockNodeAt(pos) {
        const { doc } = this.editorView.state
        const $pos = doc.resolve(pos)

        // Look for a block node
        for (let i = $pos.depth; i >= 0; i--) {
            try {
                const node = $pos.node(i)
                if (!node) continue

                const nodePos = i > 0 ? $pos.before(i) : 0
                const isAllowedType = this.options.allowedNodeTypes.length === 0 ||
                    this.options.allowedNodeTypes.includes(node.type.name)

                if (node.type.isBlock && isAllowedType) {
                    return { node, pos: nodePos }
                }
            } catch (e) {
            }
        }

        return { node: null, pos: null }
    }

    // Schedules hiding the drag handle after a delay
    scheduledHide() {
        if (!this.isMouseOverHandle && !this.isDragging) {
            clearTimeout(this.hoverTimeout)
            this.hoverTimeout = setTimeout(() => {
                if (!this.isMouseOverHandle && !this.isDragging) {
                    this.hideDragHandle()
                }
            }, 300)
        }
    }

    // Handles mouse leaving the editor
    handleMouseLeave = () => {
        if (!this.isMouseOverHandle && !this.isDragging) {
            this.scheduledHide()
        }
    }

    // Shows and positions the drag handle at the given Y coordinate
    showDragHandle(clientY) {
        const editorRect = this.editorView.dom.getBoundingClientRect()

        // Position the drag handle
        this.dragHandleElement.style.display = 'flex'
        this.dragHandleElement.style.position = 'fixed'
        this.dragHandleElement.style.left = `${editorRect.left - 35}px`
        this.dragHandleElement.style.top = `${clientY - 15}px`
    }

    // Hides the drag handle
    hideDragHandle() {
        if (this.dragHandleElement) {
            this.dragHandleElement.style.display = 'none'
        }
        this.currentNode = null
        this.currentPos = null
    }

    // Starts dragging operation
    handleDragStart = (event) => {
        // Skip if editor is not editable (shared post)
        if (!this.editor.isEditable || !this.currentNode || this.currentPos === null) return

        event.preventDefault()
        event.stopPropagation()

        // Store initial position
        this.isDragging = true
        const initialPos = this.currentPos
        const node = this.currentNode
        const nodeSize = node.nodeSize


        const handleMouseMove = (moveEvent) => {
            if (!this.isDragging) return

            // Update drag handle position
            this.dragHandleElement.style.top = `${moveEvent.clientY - 15}px`

            // Handle auto-scrolling
            this.handleAutoScroll(moveEvent.clientY)

            // Update drop indicator
            this.updateDropIndicator(moveEvent.clientY)
        }

        const handleMouseUp = (upEvent) => {
            if (!this.isDragging) return

            this.isDragging = false
            document.body.style.cursor = ''
            this.dragHandleElement.style.cursor = 'grab'
            this.dragHandleElement.style.transform = 'none'

            // Stop auto-scrolling
            this.stopAutoScroll()

            // Hide drop indicator
            this.hideDropIndicator()

            // Find the position where the node should be moved to
            const targetPos = this.findDropPosition(upEvent.clientY)

            if (targetPos !== null && targetPos !== initialPos) {
                this.moveNode(initialPos, node, targetPos, nodeSize)
            }

            // Schedule hiding the handle
            clearTimeout(this.hoverTimeout)
            this.hoverTimeout = setTimeout(() => {
                if (!this.isMouseOverHandle) {
                    this.hideDragHandle()
                }
            }, 500)

            // Clean up event listeners
            document.removeEventListener('mousemove', handleMouseMove)
            document.removeEventListener('mouseup', handleMouseUp)
        }

        // Show drop indicator initially
        this.updateDropIndicator(event.clientY)

        // Add listeners for the drag operation
        document.addEventListener('mousemove', handleMouseMove)
        document.addEventListener('mouseup', handleMouseUp)
    }

    // Finds the position to drop the node based on Y coordinate
    findDropPosition(clientY) {
        // Find the position in the document based on Y coordinate
        const editorLeft = this.editorView.dom.getBoundingClientRect().left + 10
        const pos = this.editorView.posAtCoords({
            left: editorLeft,
            top: clientY
        })

        if (!pos) {
            // Try positions slightly above and below
            const positionAbove = this.editorView.posAtCoords({
                left: editorLeft,
                top: clientY - 5
            })

            const positionBelow = this.editorView.posAtCoords({
                left: editorLeft,
                top: clientY + 5
            })

            // Check document boundaries
            const editorRect = this.editorView.dom.getBoundingClientRect()

            if (clientY < editorRect.top + 30) {
                // Near the top, return position 0
                return 0
            } else if (clientY > editorRect.bottom - 30) {
                // Near the bottom, return the end
                return this.editorView.state.doc.content.size
            }

            // Use alternative positions if available
            if (positionAbove) return positionAbove.pos
            if (positionBelow) return positionBelow.pos

            return null
        }

        // Calculate drop position based on document structure
        return this.calculatePreciseDropPosition(pos.pos, clientY)
    }

    // Calculates a precise drop position based on document structure
    calculatePreciseDropPosition(pos, clientY) {
        const { state } = this.editorView
        const $pos = state.doc.resolve(pos)

        // Handle top-level nodes
        if ($pos.depth === 0) {
            return this.findTopLevelDropPosition(clientY)
        }

        // Handle nested nodes
        return this.findNestedDropPosition($pos)
    }

    // Finds drop position among top-level nodes
    findTopLevelDropPosition(clientY) {
        const { state } = this.editorView
        const topLevelNodes = []

        // Collect all top-level nodes
        state.doc.forEach((node, offset) => {
            topLevelNodes.push({ node, pos: offset })
        })

        if (topLevelNodes.length === 0) {
            return 0
        }

        // Check if we're before the first node
        const firstNodeCoords = this.editorView.coordsAtPos(topLevelNodes[0].pos)
        if (clientY < firstNodeCoords.top) {
            return 0
        }

        // Check if we're after the last node
        const lastNode = topLevelNodes[topLevelNodes.length - 1]
        const lastNodeCoords = this.editorView.coordsAtPos(lastNode.pos + lastNode.node.nodeSize - 1)
        if (clientY > lastNodeCoords.bottom) {
            return state.doc.content.size
        }

        // Find node nearest to cursor position
        for (let i = 0; i < topLevelNodes.length; i++) {
            const current = topLevelNodes[i]
            const currentCoords = this.editorView.coordsAtPos(current.pos)
            const nodeHeight = currentCoords.bottom - currentCoords.top
            const nodeMiddle = currentCoords.top + (nodeHeight / 2)

            // If cursor is in top half of this node, insert before it
            if (clientY < nodeMiddle) {
                return current.pos
            }

            // If cursor is in bottom half, check next node
            if (i < topLevelNodes.length - 1) {
                const next = topLevelNodes[i + 1]
                const nextCoords = this.editorView.coordsAtPos(next.pos)

                if (clientY < nextCoords.top) {
                    return current.pos + current.node.nodeSize
                }
            } else {
                // At last node, in bottom half
                return current.pos + current.node.nodeSize
            }
        }

        // Fallback
        return state.doc.content.size
    }

    // Finds drop position for nested nodes
    findNestedDropPosition($pos) {
        // Find the nearest parent block node
        for (let depth = $pos.depth; depth >= 0; depth--) {
            const node = $pos.node(depth)
            const isAllowedType = this.options.allowedNodeTypes.length === 0 ||
                this.options.allowedNodeTypes.includes(node.type.name)

            // If this is a valid block node type
            if (node.type.isBlock && isAllowedType) {
                // Get the position of this node
                return depth > 0 ? $pos.before(depth) : 0
            }
        }

        // Fallback to the raw position
        return $pos.pos
    }

    // Moves a node from one position to another
    moveNode(fromPos, node, toPos, nodeSize) {
        try {
            const { state } = this.editorView
            const { tr } = state
            const docSize = state.doc.content.size

            // Validate positions
            if (fromPos < 0 || fromPos + nodeSize > docSize || toPos < 0 || toPos > docSize) {
                return false
            }

            // Get a slice of the document containing the node
            const slice = state.doc.slice(fromPos, fromPos + nodeSize)

            // Move node with appropriate transaction sequence
            if (toPos > fromPos) {
                // Moving forward: delete then insert
                tr.delete(fromPos, fromPos + nodeSize)
                    .insert(toPos - nodeSize, slice.content)
            } else {
                // Moving backward: insert then delete
                tr.insert(toPos, slice.content)
                    .delete(fromPos + slice.content.size, fromPos + nodeSize + slice.content.size)
            }

            // Apply the transaction
            this.editorView.dispatch(tr)
            return true
        } catch (error) {
            return false
        }
    }

    // Handles auto-scrolling when dragging near the viewport edges
    handleAutoScroll(clientY) {
        const viewportHeight = window.innerHeight
        const scrollThreshold = 100
        const maxScrollSpeed = 15

        let newScrollSpeed = 0

        // Calculate scroll speed based on distance from edges
        if (clientY < scrollThreshold) {
            // Near top edge
            const distance = scrollThreshold - clientY
            newScrollSpeed = -Math.min(maxScrollSpeed, (distance / scrollThreshold) * maxScrollSpeed)
        } else if (clientY > viewportHeight - scrollThreshold) {
            // Near bottom edge
            const distance = clientY - (viewportHeight - scrollThreshold)
            newScrollSpeed = Math.min(maxScrollSpeed, (distance / scrollThreshold) * maxScrollSpeed)
        }

        this.scrollSpeed = newScrollSpeed

        // Start or stop scrolling as needed
        if (this.scrollSpeed !== 0) {
            this.startAutoScroll()
        } else {
            this.stopAutoScroll()
        }
    }

    // Starts the auto-scroll interval
    startAutoScroll() {
        if (this.scrollInterval) return

        this.scrollInterval = setInterval(() => {
            if (this.scrollSpeed === 0 || !this.isDragging) {
                this.stopAutoScroll()
                return
            }

            this.performScrolling()
        }, 16) // ~60fps
    }

    // Performs the actual scrolling action
    performScrolling() {
        let scrolled = false

        // Find scrollable container
        let scrollContainer = this.editorView.dom
        while (scrollContainer && scrollContainer !== document.documentElement) {
            const style = window.getComputedStyle(scrollContainer)
            const hasVerticalScroll = scrollContainer.scrollHeight > scrollContainer.clientHeight
            const canScroll = ['auto', 'scroll'].includes(style.overflowY) ||
                ['auto', 'scroll'].includes(style.overflow)

            if (hasVerticalScroll && canScroll) {
                const oldScrollTop = scrollContainer.scrollTop
                scrollContainer.scrollTop += this.scrollSpeed
                if (scrollContainer.scrollTop !== oldScrollTop) {
                    scrolled = true
                    break
                }
            }
            scrollContainer = scrollContainer.parentElement
        }

        // Fallback to window scrolling
        if (!scrolled) {
            window.scrollBy(0, this.scrollSpeed)
        }
    }

    // Stops auto-scrolling
    stopAutoScroll() {
        if (this.scrollInterval) {
            clearInterval(this.scrollInterval)
            this.scrollInterval = null
        }
        this.scrollSpeed = 0
    }

    // Updates the drop indicator position
    updateDropIndicator(clientY) {
        if (!this.isDragging) return

        const dropPos = this.findDropPosition(clientY)
        if (dropPos === null) {
            this.hideDropIndicator()
            return
        }

        try {
            const coords = this.editorView.coordsAtPos(dropPos)
            if (!coords) {
                this.hideDropIndicator()
                return
            }

            // Position the drop indicator
            const editorRect = this.editorView.dom.getBoundingClientRect()
            const indicatorWidth = Math.max(300, editorRect.width)

            this.dropIndicator.style.display = 'block'
            this.dropIndicator.style.left = `${editorRect.left}px`
            this.dropIndicator.style.top = `${coords.top - 1}px` // Slightly above the line
            this.dropIndicator.style.width = `${indicatorWidth}px`
        } catch (e) {
            this.hideDropIndicator()
        }
    }

    // Hides the drop indicator
    hideDropIndicator() {
        if (this.dropIndicator) {
            this.dropIndicator.style.display = 'none'
        }
    }

    // Cleans up resources when the plugin is destroyed
    destroy() {
        if (!this.editor.isEditable) {
            return
        }

        // Clean up event listeners
        this.editorView.dom.removeEventListener('mousemove', this.handleMouseMove)
        document.removeEventListener('mouseleave', this.handleMouseLeave)

        if (this.dragHandleElement) {
            this.dragHandleElement.removeEventListener('mousedown', this.handleDragStart)
            this.dragHandleElement.removeEventListener('mouseenter', this.handleMouseEnter)
            this.dragHandleElement.removeEventListener('mouseleave', this.handleMouseLeaveHandle)

            if (this.dragHandleElement.parentNode) {
                this.dragHandleElement.parentNode.removeChild(this.dragHandleElement)
            }
        }

        if (this.mountElement && this.mountElement.parentNode) {
            this.mountElement.parentNode.removeChild(this.mountElement)
        }

        if (this.dropIndicator && this.dropIndicator.parentNode) {
            this.dropIndicator.parentNode.removeChild(this.dropIndicator)
        }

        clearTimeout(this.hoverTimeout)
        this.stopAutoScroll()
    }
}
