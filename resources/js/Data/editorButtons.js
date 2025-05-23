import BoldIcon from 'vue-material-design-icons/FormatBold.vue'
import TableIcon from 'vue-material-design-icons/Table.vue'
import ItalicIcon from 'vue-material-design-icons/FormatItalic.vue'
import UnderlineIcon from 'vue-material-design-icons/FormatUnderline.vue'
import H1Icon from 'vue-material-design-icons/FormatHeader1.vue'
import H2Icon from 'vue-material-design-icons/FormatHeader2.vue'
import H3Icon from 'vue-material-design-icons/FormatHeader3.vue'
import ListIcon from 'vue-material-design-icons/FormatListBulleted.vue'
import OrderedListIcon from 'vue-material-design-icons/FormatListNumbered.vue'
import BlockquoteIcon from 'vue-material-design-icons/FormatQuoteClose.vue'
import CodeIcon from 'vue-material-design-icons/CodeTags.vue'
import MinusIcon from 'vue-material-design-icons/Minus.vue'
import CheckboxMarkedOutlineIcon from 'vue-material-design-icons/CheckboxMarkedOutline.vue'
import ChevronDownIcon from 'vue-material-design-icons/ChevronDown.vue'
import FormatAlignLeftIcon from 'vue-material-design-icons/FormatAlignLeft.vue'
import FormatAlignCenterIcon from 'vue-material-design-icons/FormatAlignCenter.vue'
import FormatAlignRightIcon from 'vue-material-design-icons/FormatAlignRight.vue'
import LinkIcon from 'vue-material-design-icons/Link.vue'
import StarFourPointsIcon from 'vue-material-design-icons/StarFourPoints.vue'
import ImagePlus from 'vue-material-design-icons/ImagePlus.vue'


// Group buttons by sections for better organization
export const buttonSections = [
    // Ask AI
    [
        {
            icon: StarFourPointsIcon,
            title: "Ask AI",
            isActive: () => false,
            onClick: (editor, setLink, event) => {
                window.geminiClickX = event ? event.clientX : null;
                window.geminiClickY = event ? event.clientY : null;

                editor.chain().focus().insertGeminiAI().run();
            }
        },
        {
            icon: ImagePlus,
            title: "Generate Image",
            isActive: () => false,
            onClick: (editor, setLink, event) => {
                // Store click position for potential use in the extension
                window.falAIClickX = event ? event.clientX : null;
                window.falAIClickY = event ? event.clientY : null;
                
                // Hide the bubble menu
                const bubbleMenus = document.querySelectorAll('.bubble-menu');
                bubbleMenus.forEach(menu => {
                    menu.style.display = 'none';
                });
                
                // Trigger the FalAI extension
                editor.chain().focus().insertFalAI().run();
            }
        }
    ],
    // Text formatting
    [
        {
            icon: BoldIcon,
            title: "Bold",
            isActive: (editor) => editor.isActive('bold'),
            onClick: (editor) => editor.chain().focus().toggleBold().run()
        },
        {
            icon: ItalicIcon,
            title: "Italic",
            isActive: (editor) => editor.isActive('italic'),
            onClick: (editor) => editor.chain().focus().toggleItalic().run()
        },
        {
            icon: UnderlineIcon,
            title: "Underline",
            isActive: (editor) => editor.isActive('underline'),
            onClick: (editor) => editor.chain().focus().toggleUnderline().run()
        }
    ],
    // Text alignment
    [
        {
            icon: FormatAlignLeftIcon,
            title: "Text to left",
            isActive: (editor) => editor.isActive({ textAlign: 'left' }),
            onClick: (editor) => editor.chain().focus().setTextAlign('left').run()
        },
        {
            icon: FormatAlignCenterIcon,
            title: "Text center",
            isActive: (editor) => editor.isActive({ textAlign: 'center' }),
            onClick: (editor) => editor.chain().focus().setTextAlign('center').run()
        },
        {
            icon: FormatAlignRightIcon,
            title: "Text to right",
            isActive: (editor) => editor.isActive({ textAlign: 'right' }),
            onClick: (editor) => editor.chain().focus().setTextAlign('right').run()
        }
    ],
    // Headings
    [
        {
            icon: H1Icon,
            title: "H1",
            isActive: (editor) => editor.isActive('heading', { level: 1 }),
            onClick: (editor) => editor.chain().focus().toggleHeading({ level: 1 }).run()
        },
        {
            icon: H2Icon,
            title: "H2",
            isActive: (editor) => editor.isActive('heading', { level: 2 }),
            onClick: (editor) => editor.chain().focus().toggleHeading({ level: 2 }).run()
        },
        {
            icon: H3Icon,
            title: "H3",
            isActive: (editor) => editor.isActive('heading', { level: 3 }),
            onClick: (editor) => editor.chain().focus().toggleHeading({ level: 3 }).run()
        }
    ],
    // Lists and quotes
    [
        {
            icon: ListIcon,
            title: "Bullet List",
            isActive: (editor) => editor.isActive('bulletList'),
            onClick: (editor) => editor.chain().focus().toggleBulletList().run()
        },
        {
            icon: OrderedListIcon,
            title: "Ordered List",
            isActive: (editor) => editor.isActive('orderedList'),
            onClick: (editor) => editor.chain().focus().toggleOrderedList().run()
        },
        {
            icon: CheckboxMarkedOutlineIcon,
            title: "Todo List",
            isActive: (editor) => editor.isActive('taskList'),
            onClick: (editor) => editor.chain().focus().toggleTaskList().run()
        },
        {
            icon: BlockquoteIcon,
            title: "Blockquote",
            isActive: (editor) => editor.isActive('blockquote'),
            onClick: (editor) => editor.chain().focus().toggleBlockquote().run()
        },
        {
            icon: LinkIcon,
            title: "Link",
            isActive: (editor) => editor.isActive('link'),
            onClick: (editor, setLink) => !editor.isActive('link') ? setLink() : editor.chain().focus().unsetLink().run()
        }
    ],
    // Code and horizontal rule
    [
        {
            icon: CodeIcon,
            title: "Code",
            isActive: (editor) => editor.isActive('code'),
            onClick: (editor) => editor.chain().focus().toggleCodeBlock().run()
        },
        {
            icon: MinusIcon,
            title: "Horizontal Line",
            isActive: () => false,
            onClick: (editor) => editor.chain().focus().setHorizontalRule().run()
        }
    ],
    // Table and details
    [
        {
            icon: TableIcon,
            title: "Insert Table",
            isActive: (editor) => editor.isActive('table'),
            onClick: (editor) => editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()
        },
        {
            icon: ChevronDownIcon,
            title: "Details Dropdown",
            isActive: () => false,
            onClick: (editor) => editor.can().setDetails() ? editor.chain().focus().setDetails().run() : editor.chain().focus().unsetDetails().run()
        }
    ]
]
