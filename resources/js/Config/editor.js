import StarterKit from '@tiptap/starter-kit'
import Underline from '@tiptap/extension-underline'
import Table from '@tiptap/extension-table'
import TableRow from '@tiptap/extension-table-row'
import TableHeader from '@tiptap/extension-table-header'
import TableCell from '@tiptap/extension-table-cell'
import {getHierarchicalIndexes, TableOfContents} from "@tiptap-pro/extension-table-of-contents";
import TaskItem from '@tiptap/extension-task-item'
import TaskList from '@tiptap/extension-task-list'
import Details from '@tiptap-pro/extension-details'
import DetailsContent from '@tiptap-pro/extension-details-content'
import DetailsSummary from '@tiptap-pro/extension-details-summary'
import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight'
import Code from '@tiptap/extension-code'
import Typography from '@tiptap/extension-typography'
import TextAlign from '@tiptap/extension-text-align'
import Link from '@tiptap/extension-link'
import FileHandler from '@tiptap-pro/extension-file-handler'
import Image from '@tiptap/extension-image'
import IndentHandler from "@/CustomExtensions/indent-handler.js";
import CollaborationCursor from '@tiptap/extension-collaboration-cursor'
import Collaboration from '@tiptap/extension-collaboration'
import {common, createLowlight} from "lowlight"
import {usePage} from "@inertiajs/vue3";
import {GeminiAI} from "@/CustomExtensions/gemini-ai.js";
import {FalAI} from "@/CustomExtensions/fal-ai.js";
import {OpenRouterAI} from "@/CustomExtensions/openrouter-ai.js";

const lowlight = createLowlight(common)
const page = usePage()

export const createEditor = ({provider, editable}) => ({
    editable,
    extensions: [
        StarterKit.configure({
            codeBlock: false,
            code: false,
            history: false
        }),
        Underline,
        Table.configure({
            resizable: true,
        }),
        TableRow,
        TableHeader,
        TableCell,
        TaskList,
        Collaboration.configure({
            document: provider.document,
        }),
        CollaborationCursor.configure({
            provider: provider,
            user: {
                name: page.props.auth?.user?.email,
                color: '#' + Math.floor(Math.random() * 16777215).toString(16),
            },
        }),
        CodeBlockLowlight.configure({
            lowlight,
        }),
        Code,
        Details.configure({
            persist: true,
            HTMLAttributes: {
                class: 'details',
            },
        }),
        DetailsSummary,
        DetailsContent,
        TaskItem.configure({
            nested: true,
        }),
        Typography,
        TextAlign.configure({
            types: ['heading', 'paragraph'],
            alignments: ['left', 'center', 'right'],
        }),
        Link.configure({
            openOnClick: true,
            defaultProtocol: 'https',
        }),
        FileHandler.configure({
            allowedMimeTypes: ['image/png', 'image/jpeg', 'image/gif', 'image/webp'],
            onDrop: (currentEditor, files, pos) => {
                files.forEach(file => {
                    let formData = new FormData()
                    formData.append('file', file)
                    formData.append('post_id', page.props.post.id)

                    axios.post(route('files.store'), formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    })
                        .then(response => {
                            currentEditor.chain().insertContentAt(pos, {
                                type: 'image',
                                attrs: {
                                    src: response.data.url,
                                },
                            }).focus().run()
                        })
                        .catch(error => console.error(error))
                })
            },
            onPaste: (currentEditor, files) => {
                files.forEach(file => {
                    let formData = new FormData()
                    formData.append('file', file)
                    formData.append('post_id', page.props.post.id)

                    axios.post(route('files.store'), formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    })
                        .then(response => {
                            currentEditor.chain().insertContentAt(currentEditor.state.selection.anchor, {
                                type: 'image',
                                attrs: {
                                    src: response.data.url,
                                },
                            }).focus().run()
                        })
                        .catch(error => console.error(error))
                })
            },
        }),
        Image,
        IndentHandler,
        TableOfContents.configure({
            headingLevels: [1, 2, 3],
            anchorTypes: ['heading'],
            getIndex: getHierarchicalIndexes,
            generateID: (node) => {
                // Generate a unique ID based on the heading text
                return node.textContent.toLowerCase()
                    .replace(/\s+/g, '-')
                    .replace(/[^\w-]/g, '')
                    .replace(/^-+|-+$/g, '');
            },
            // Filter out collaboration cursor emails from the heading text for table of contents
            onRenderItem: (props) => {
                const originalText = props.node.textContent;
                let headingText = originalText;

                if (props.node.dom) {
                    const hasCollabCursor = props.node.dom.querySelector('.collaboration-cursor__label');

                    if (hasCollabCursor) {
                        const textNodes = [];
                        props.node.dom.childNodes.forEach(node => {
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
                }

                return {
                    ...props,
                    textContent: headingText
                };
            },
        }),
        GeminiAI,
        FalAI,
        OpenRouterAI
    ],
    editorProps: {
        attributes: {
            class: 'dark:text-gray-300 prose max-w-none min-h-screen p-4 focus:outline-none',
        },
    },
})
