import { Extension } from '@tiptap/core'
import { createApp } from 'vue'
import OpenRouterMenu from '../Components/OpenRouterMenu.vue'

// Store the last mouse position globally (with scroll offset)
let lastMouseX = 0;
let lastMouseY = 0;

// Add event listener to track mouse position with page coordinates
document.addEventListener('mousemove', (event) => {
    lastMouseX = event.pageX;
    lastMouseY = event.pageY;
});

export const OpenRouterAI = Extension.create({
    name: 'openRouterAI',

    addCommands() {
        return {
            insertOpenRouterAI: () => ({ chain, editor }) => {
                console.log('OpenRouter AI command triggered');

                const bubbleMenus = document.querySelectorAll('.bubble-menu');
                bubbleMenus.forEach(menu => {
                    menu.style.display = 'none';
                });

                const { view } = editor;
                const { state } = view;
                const { selection } = state;
                const { ranges } = selection;
                const from = ranges[0].$from;

                const cursorPos = view.coordsAtPos(from.pos);

                let posX = lastMouseX;
                let posY = lastMouseY;

                if (!posX && !posY) {
                    const scrollX = window.pageXOffset;
                    const scrollY = window.pageYOffset;
                    posX = cursorPos.left + scrollX;
                    posY = cursorPos.bottom + scrollY;
                }

                const menuContainer = document.createElement('div');
                document.body.appendChild(menuContainer);

                const app = createApp(OpenRouterMenu, {
                    show: true,
                    editor: editor,
                    position: {
                        x: posX,
                        y: posY + 10,
                    },
                    close: () => {
                        setTimeout(() => {
                            app.unmount();
                            document.body.removeChild(menuContainer);

                            bubbleMenus.forEach(menu => {
                                menu.style.display = '';
                            });
                        }, 100);
                    }
                });

                app.mount(menuContainer);

                return true;
            },
        }
    },
})
