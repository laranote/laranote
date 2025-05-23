import { Extension } from '@tiptap/core'
import Swal from 'sweetalert2'
import { fal } from '@fal-ai/client'

fal.config({
    proxyUrl: '/api/fal-ai/proxy'
});

export const FalAI = Extension.create({
    name: 'falAI',

    addCommands() {
        return {
            insertFalAI: () => ({ chain, editor }) => {
                console.log('FalAI command triggered');

                const bubbleMenus = document.querySelectorAll('.bubble-menu');

                bubbleMenus.forEach(menu => {
                    menu.style.display = 'none';
                });

                if (editor && editor.view) {
                    editor.commands.blur();
                }
                document.activeElement?.blur();

                const tempFocusable = document.createElement('button');
                tempFocusable.style.position = 'absolute';
                tempFocusable.style.left = '-9999px';
                tempFocusable.tabIndex = 0;
                document.body.appendChild(tempFocusable);
                tempFocusable.focus();

                setTimeout(() => {
                    document.body.removeChild(tempFocusable);

                    const isDarkMode = document.documentElement.classList.contains('dark');

                    Swal.fire({
                        backdrop: 'rgba(0,0,0,0.4)',
                        focusConfirm: true,
                        allowEscapeKey: true,
                        willOpen: () => {
                            if (editor && editor.view) {
                                editor.commands.blur();
                            }
                        },
                        didOpen: () => {
                            const appDiv = document.getElementById('app');
                            if (appDiv) {
                                appDiv.removeAttribute('aria-hidden');
                            }
                        },
                        customClass: {
                            container: isDarkMode ? 'swal2-dark' : ''
                        },
                        background: isDarkMode ? '#262626' : '#ffffff',
                        color: isDarkMode ? '#e5e5e5' : '#000000',
                        title: 'Generate Image',
                        input: 'textarea',
                        inputLabel: 'Describe the image you want to generate',
                        inputPlaceholder: 'A beautiful sunset over mountains...',
                        showCancelButton: true,
                        confirmButtonText: 'Generate',
                        confirmButtonColor: 'darkgreen',
                        showLoaderOnConfirm: false, // Don't show loader since we close immediately
                        inputAttributes: {
                            rows: 3
                        },
                        preConfirm: (prompt) => {
                            if (!prompt || !prompt.trim()) {
                                Swal.showValidationMessage('Please enter a description');
                                return false;
                            }

                            return {
                                prompt: prompt.trim()
                            };
                        },
                        allowOutsideClick: true
                    }).then((result) => {
                        setTimeout(() => {
                            bubbleMenus.forEach(menu => {
                                menu.style.display = '';
                            });
                        }, 100);

                        if (result.isConfirmed && result.value && result.value.prompt) {
                            const prompt = result.value.prompt;

                            // Create a temporary placeholder image
                            const placeholderSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="800" height="450" viewBox="0 0 800 450">
                                <rect width="800" height="450" fill="#f0f0f0"/>
                                <text x="400" y="200" font-family="Arial, sans-serif" font-size="24" text-anchor="middle" fill="#666">
                                    Generating image...
                                </text>
                                <g transform="translate(400, 250)">
                                    <circle cx="0" cy="0" r="30" fill="none" stroke="#999" stroke-width="5"/>
                                    <path d="M 0,-30 A 30,30 0 0,1 30,0" fill="none" stroke="#333" stroke-width="5" stroke-linecap="round">
                                        <animateTransform
                                            attributeName="transform"
                                            type="rotate"
                                            from="0"
                                            to="360"
                                            dur="1s"
                                            repeatCount="indefinite"/>
                                    </path>
                                </g>
                                <text x="400" y="320" font-family="Arial, sans-serif" font-size="14" text-anchor="middle" fill="#999">
                                    Your image will appear here shortly
                                </text>
                            </svg>`;

                            const placeholderUrl = `data:image/svg+xml;charset=utf-8,${encodeURIComponent(placeholderSvg)}`;

                            editor.chain().focus()
                                .insertContent({
                                    type: 'image',
                                    attrs: {
                                        src: placeholderUrl,
                                        alt: `Generating: ${prompt}`
                                    }
                                })
                                .run();

                            (async () => {
                                try {
                                    console.log('Generating image with prompt:', prompt);

                                    // Generate the image
                                    const result = await fal.subscribe('fal-ai/recraft-v3', {
                                        input: {
                                            prompt: prompt,
                                            image_size: 'landscape_16_9',
                                            num_images: 1
                                        }
                                    });

                                    console.log('FAL AI response:', result);

                                    let imageUrl = null;

                                    if (result.data && result.data.images && result.data.images.length > 0) {
                                        const image = result.data.images[0];
                                        imageUrl = typeof image === 'string' ? image : image.url;
                                        console.log('Found image URL in data.images:', imageUrl);
                                    } else if (result.images && result.images.length > 0) {
                                        const image = result.images[0];
                                        imageUrl = typeof image === 'string' ? image : image.url;
                                        console.log('Found image URL in images:', imageUrl);
                                    } else if (result.output && result.output.images && result.output.images.length > 0) {
                                        const image = result.output.images[0];
                                        imageUrl = typeof image === 'string' ? image : image.url;
                                        console.log('Found image URL in output.images:', imageUrl);
                                    }

                                    if (imageUrl) {
                                        console.log('Using image URL:', imageUrl);

                                        const placeholderImages = editor.view.dom.querySelectorAll('img');
                                        let found = false;

                                        for (const img of placeholderImages) {
                                            if (img.src.includes('data:image/svg+xml') && img.alt.includes('Generating:')) {
                                                console.log('Found placeholder image, replacing with actual image');

                                                img.src = imageUrl;
                                                img.alt = prompt || 'Generated image';
                                                found = true;

                                                try {
                                                    const pos = editor.view.posAtDOM(img, 0);
                                                    const transaction = editor.view.state.tr.setNodeMarkup(
                                                        pos,
                                                        null,
                                                        {
                                                            src: imageUrl,
                                                            alt: prompt || 'Generated image'
                                                        }
                                                    );
                                                    editor.view.dispatch(transaction);

                                                    editor.commands.focus();
                                                    console.log('Updated editor state with new image');
                                                } catch (e) {
                                                    console.error('Error updating editor state:', e);
                                                }

                                                break;
                                            }
                                        }

                                        if (!found) {
                                            console.log('Placeholder not found, inserting new image');

                                            editor.chain().focus()
                                                .insertContent({
                                                    type: 'image',
                                                    attrs: {
                                                        src: imageUrl,
                                                        alt: prompt || 'Generated image'
                                                    }
                                                })
                                                .run();
                                        }
                                    } else {
                                        console.error('No image URL found in response:', result);
                                    }
                                } catch (error) {
                                    console.error('Error generating image:', error);
                                }
                            })();
                        }
                    });
                }, 50);

                return true;
            }
        }
    },
})
