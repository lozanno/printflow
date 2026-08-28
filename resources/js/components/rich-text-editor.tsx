import {
    Details,
    DetailsContent as BaseDetailsContent,
    DetailsSummary,
} from '@tiptap/extension-details';
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import {
    Bold,
    ChevronsDownUp,
    Heading2,
    Image as ImageIcon,
    Italic,
    Link as LinkIcon,
    List,
    ListOrdered,
    MapPin,
} from 'lucide-react';
import type { ReactNode } from 'react';
import { useState } from 'react';
import { Iframe } from '@/components/tiptap-iframe';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

// The server-side sanitizer (HtmlSanitizer) strips the data-type attribute
// from the details-content wrapper div (HTMLPurifier doesn't preserve
// custom data-* attributes), so parsing back a saved value must also accept
// a bare <div> rather than relying on `div[data-type="detailsContent"]`.
const DetailsContent = BaseDetailsContent.extend({
    parseHTML() {
        return [{ tag: 'div' }];
    },
});

// The extension's default toggle button has no visible icon (only an
// aria-label) - a plain chevron makes the collapsible section recognizable
// while editing, matching the ▸/▾ look applied to the final <details> via
// app.css for read-only rendering.
function renderToggleButton({
    element,
    isOpen,
}: {
    element: HTMLButtonElement;
    isOpen: boolean;
}) {
    element.textContent = isOpen ? '▾' : '▸';
    element.setAttribute(
        'aria-label',
        isOpen ? 'Contraer seccion' : 'Expandir seccion',
    );
    element.className = 'mr-1 w-4 text-zinc-500';
}

function ToolbarButton({
    onClick,
    active,
    label,
    children,
}: {
    onClick: () => void;
    active?: boolean;
    label: string;
    children: ReactNode;
}) {
    return (
        <Button
            type="button"
            variant="ghost"
            size="icon"
            aria-label={label}
            onClick={onClick}
            className={cn('size-8', active && 'bg-accent')}
        >
            {children}
        </Button>
    );
}

export function RichTextEditor({
    name,
    defaultValue,
}: {
    name: string;
    defaultValue?: string | null;
}) {
    const [html, setHtml] = useState(defaultValue ?? '');
    const [linkDialogOpen, setLinkDialogOpen] = useState(false);
    const [imageDialogOpen, setImageDialogOpen] = useState(false);
    const [mapDialogOpen, setMapDialogOpen] = useState(false);
    const [urlValue, setUrlValue] = useState('');

    const editor = useEditor({
        extensions: [
            StarterKit,
            Image,
            Link.configure({ openOnClick: false }),
            Details.configure({ persist: true, renderToggleButton }),
            DetailsSummary,
            DetailsContent,
            Iframe,
        ],
        content: defaultValue ?? '',
        onUpdate: ({ editor }) => setHtml(editor.getHTML()),
        editorProps: {
            attributes: {
                class: 'prose prose-sm min-h-48 max-w-none rounded-b-md border border-t-0 px-3 py-2 focus:outline-none',
            },
        },
    });

    if (!editor) {
        return null;
    }

    return (
        <div>
            <div className="flex flex-wrap items-center gap-1 rounded-t-md border border-b-0 bg-muted/40 p-1">
                <ToolbarButton
                    label="Negrita"
                    active={editor.isActive('bold')}
                    onClick={() => editor.chain().focus().toggleBold().run()}
                >
                    <Bold className="size-4" />
                </ToolbarButton>
                <ToolbarButton
                    label="Cursiva"
                    active={editor.isActive('italic')}
                    onClick={() => editor.chain().focus().toggleItalic().run()}
                >
                    <Italic className="size-4" />
                </ToolbarButton>
                <ToolbarButton
                    label="Subtitulo"
                    active={editor.isActive('heading', { level: 2 })}
                    onClick={() =>
                        editor.chain().focus().toggleHeading({ level: 2 }).run()
                    }
                >
                    <Heading2 className="size-4" />
                </ToolbarButton>
                <ToolbarButton
                    label="Lista"
                    active={editor.isActive('bulletList')}
                    onClick={() =>
                        editor.chain().focus().toggleBulletList().run()
                    }
                >
                    <List className="size-4" />
                </ToolbarButton>
                <ToolbarButton
                    label="Lista numerada"
                    active={editor.isActive('orderedList')}
                    onClick={() =>
                        editor.chain().focus().toggleOrderedList().run()
                    }
                >
                    <ListOrdered className="size-4" />
                </ToolbarButton>
                <ToolbarButton
                    label="Enlace"
                    active={editor.isActive('link')}
                    onClick={() => {
                        setUrlValue(editor.getAttributes('link').href ?? '');
                        setLinkDialogOpen(true);
                    }}
                >
                    <LinkIcon className="size-4" />
                </ToolbarButton>
                <ToolbarButton
                    label="Imagen"
                    onClick={() => {
                        setUrlValue('');
                        setImageDialogOpen(true);
                    }}
                >
                    <ImageIcon className="size-4" />
                </ToolbarButton>
                <ToolbarButton
                    label="Seccion plegable"
                    active={editor.isActive('details')}
                    onClick={() => editor.chain().focus().setDetails().run()}
                >
                    <ChevronsDownUp className="size-4" />
                </ToolbarButton>
                <ToolbarButton
                    label="Mapa de Google"
                    onClick={() => {
                        setUrlValue('');
                        setMapDialogOpen(true);
                    }}
                >
                    <MapPin className="size-4" />
                </ToolbarButton>
            </div>

            <EditorContent editor={editor} />

            <input type="hidden" name={name} value={html} readOnly />

            <Dialog open={linkDialogOpen} onOpenChange={setLinkDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Enlace</DialogTitle>
                    </DialogHeader>
                    <div className="grid gap-2">
                        <Label htmlFor="link-url">URL</Label>
                        <Input
                            id="link-url"
                            value={urlValue}
                            placeholder="https://..."
                            onChange={(event) =>
                                setUrlValue(event.target.value)
                            }
                        />
                    </div>
                    <DialogFooter>
                        {editor.isActive('link') && (
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => {
                                    editor.chain().focus().unsetLink().run();
                                    setLinkDialogOpen(false);
                                }}
                            >
                                Quitar enlace
                            </Button>
                        )}
                        <Button
                            type="button"
                            onClick={() => {
                                if (urlValue) {
                                    editor
                                        .chain()
                                        .focus()
                                        .setLink({ href: urlValue })
                                        .run();
                                }

                                setLinkDialogOpen(false);
                            }}
                        >
                            Guardar
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Dialog open={imageDialogOpen} onOpenChange={setImageDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Imagen</DialogTitle>
                    </DialogHeader>
                    <div className="grid gap-2">
                        <Label htmlFor="image-url">URL de la imagen</Label>
                        <Input
                            id="image-url"
                            value={urlValue}
                            placeholder="https://..."
                            onChange={(event) =>
                                setUrlValue(event.target.value)
                            }
                        />
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            onClick={() => {
                                if (urlValue) {
                                    editor
                                        .chain()
                                        .focus()
                                        .setImage({ src: urlValue })
                                        .run();
                                }

                                setImageDialogOpen(false);
                            }}
                        >
                            Insertar
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Dialog open={mapDialogOpen} onOpenChange={setMapDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Mapa de Google</DialogTitle>
                    </DialogHeader>
                    <div className="grid gap-2">
                        <Label htmlFor="map-url">
                            URL para insertar (embed)
                        </Label>
                        <Input
                            id="map-url"
                            value={urlValue}
                            placeholder="https://www.google.com/maps?q=...&output=embed"
                            onChange={(event) =>
                                setUrlValue(event.target.value)
                            }
                        />
                        <p className="text-xs text-muted-foreground">
                            En Google Maps: Compartir → Insertar un mapa → copia
                            la URL del atributo src del iframe.
                        </p>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            disabled={
                                !urlValue.startsWith(
                                    'https://www.google.com/maps',
                                )
                            }
                            onClick={() => {
                                editor
                                    .chain()
                                    .focus()
                                    .setIframe({ src: urlValue })
                                    .run();

                                setMapDialogOpen(false);
                            }}
                        >
                            Insertar
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}
