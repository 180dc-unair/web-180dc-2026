import { useEffect, useRef, useState } from 'react';
import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';
import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import {
    Bold,
    Code2,
    Heading1,
    Heading2,
    ImagePlus,
    Italic,
    List,
    ListOrdered,
    Loader2,
    Quote,
    Redo2,
    Undo2,
} from 'lucide-react';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { ApiError } from '@/lib/admin-api';
import { mediaService } from '@/services/admin/media.service';
import { cn } from '@/lib/utils';

type NotionEditorProps = {
    value: string;
    onChange: (html: string) => void;
    invalid?: boolean;
};

export function NotionEditor({ value, onChange, invalid = false }: NotionEditorProps) {
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [uploading, setUploading] = useState(false);
    const [uploadError, setUploadError] = useState('');
    const editor = useEditor({
        extensions: [
            StarterKit.configure({ heading: { levels: [1, 2, 3] } }),
            Placeholder.configure({ placeholder: 'Mulai menulis isi artikel...' }),
            Image.configure({
                allowBase64: false,
                HTMLAttributes: {
                    class: 'my-6 max-h-[34rem] w-full rounded-xl border object-cover',
                },
            }),
        ],
        content: value,
        editorProps: {
            attributes: {
                class: 'tiptap min-h-80 px-6 py-5 text-[15px] leading-7 text-black outline-none',
            },
        },
        onUpdate: ({ editor: currentEditor }) => onChange(currentEditor.getHTML()),
    });

    useEffect(() => {
        if (editor && editor.getHTML() !== value) {
            editor.commands.setContent(value || '', { emitUpdate: false });
        }
    }, [editor, value]);

    const addImage = async (file?: File) => {
        if (!file || !editor) return;

        setUploading(true);
        setUploadError('');

        try {
            const media = await mediaService.upload(file, 'article-content');
            editor.chain().focus().setImage({ src: media.url, alt: file.name, title: file.name }).run();
        } catch (error) {
            setUploadError(error instanceof ApiError ? error.message : 'Gambar artikel gagal diunggah.');
        } finally {
            setUploading(false);
            if (fileInputRef.current) fileInputRef.current.value = '';
        }
    };

    if (!editor) return <div className="h-80 animate-pulse rounded-xl border bg-muted/40" />;

    const tools = [
        { label: 'Judul 1', icon: Heading1, active: editor.isActive('heading', { level: 1 }), run: () => editor.chain().focus().toggleHeading({ level: 1 }).run() },
        { label: 'Judul 2', icon: Heading2, active: editor.isActive('heading', { level: 2 }), run: () => editor.chain().focus().toggleHeading({ level: 2 }).run() },
        { label: 'Tebal', icon: Bold, active: editor.isActive('bold'), run: () => editor.chain().focus().toggleBold().run() },
        { label: 'Miring', icon: Italic, active: editor.isActive('italic'), run: () => editor.chain().focus().toggleItalic().run() },
        { label: 'Daftar poin', icon: List, active: editor.isActive('bulletList'), run: () => editor.chain().focus().toggleBulletList().run() },
        { label: 'Daftar angka', icon: ListOrdered, active: editor.isActive('orderedList'), run: () => editor.chain().focus().toggleOrderedList().run() },
        { label: 'Kutipan', icon: Quote, active: editor.isActive('blockquote'), run: () => editor.chain().focus().toggleBlockquote().run() },
        { label: 'Kode', icon: Code2, active: editor.isActive('codeBlock'), run: () => editor.chain().focus().toggleCodeBlock().run() },
    ];

    return (
        <div className={cn('overflow-hidden rounded-xl border bg-white', invalid && 'border-destructive ring-2 ring-destructive/15')}>
            <div className="flex flex-wrap items-center gap-1 border-b bg-muted/25 p-2">
                {tools.map((tool) => {
                    const Icon = tool.icon;
                    return (
                        <Button
                            key={tool.label}
                            type="button"
                            variant={tool.active ? 'secondary' : 'ghost'}
                            size="icon-sm"
                            title={tool.label}
                            aria-label={tool.label}
                            aria-pressed={tool.active}
                            onClick={tool.run}
                        >
                            <Icon />
                        </Button>
                    );
                })}
                <span className="mx-1 h-5 w-px bg-border" aria-hidden="true" />
                <Button type="button" variant="ghost" size="icon-sm" onClick={() => editor.chain().focus().undo().run()} aria-label="Urungkan">
                    <Undo2 />
                </Button>
                <Button type="button" variant="ghost" size="icon-sm" onClick={() => editor.chain().focus().redo().run()} aria-label="Ulangi">
                    <Redo2 />
                </Button>
                <span className="mx-1 h-5 w-px bg-border" aria-hidden="true" />
                <input
                    ref={fileInputRef}
                    type="file"
                    accept="image/jpeg,image/png,image/webp,image/gif"
                    className="sr-only"
                    onChange={(event) => void addImage(event.target.files?.[0])}
                />
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    disabled={uploading}
                    onClick={() => fileInputRef.current?.click()}
                >
                    {uploading ? <Loader2 className="animate-spin" /> : <ImagePlus />}
                    {uploading ? 'Mengunggah...' : 'Gambar'}
                </Button>
            </div>

            <EditorContent
                editor={editor}
                className="[&_.tiptap_blockquote]:my-4 [&_.tiptap_blockquote]:border-l-4 [&_.tiptap_blockquote]:pl-4 [&_.tiptap_blockquote]:text-black/65 [&_.tiptap_h1]:mb-4 [&_.tiptap_h1]:mt-7 [&_.tiptap_h1]:text-3xl [&_.tiptap_h1]:font-bold [&_.tiptap_h2]:mb-3 [&_.tiptap_h2]:mt-6 [&_.tiptap_h2]:text-2xl [&_.tiptap_h2]:font-bold [&_.tiptap_h3]:mb-2 [&_.tiptap_h3]:mt-5 [&_.tiptap_h3]:text-xl [&_.tiptap_h3]:font-semibold [&_.tiptap_ol]:my-4 [&_.tiptap_ol]:list-decimal [&_.tiptap_ol]:pl-7 [&_.tiptap_p]:my-2 [&_.tiptap_p.is-editor-empty:first-child::before]:pointer-events-none [&_.tiptap_p.is-editor-empty:first-child::before]:float-left [&_.tiptap_p.is-editor-empty:first-child::before]:h-0 [&_.tiptap_p.is-editor-empty:first-child::before]:text-black/35 [&_.tiptap_p.is-editor-empty:first-child::before]:content-[attr(data-placeholder)] [&_.tiptap_pre]:my-4 [&_.tiptap_pre]:overflow-x-auto [&_.tiptap_pre]:rounded-lg [&_.tiptap_pre]:bg-black [&_.tiptap_pre]:p-4 [&_.tiptap_pre]:font-mono [&_.tiptap_pre]:text-sm [&_.tiptap_pre]:text-white [&_.tiptap_ul]:my-4 [&_.tiptap_ul]:list-disc [&_.tiptap_ul]:pl-7"
            />

            {uploadError && (
                <Alert variant="destructive" className="m-3 w-auto">
                    <AlertDescription>{uploadError}</AlertDescription>
                </Alert>
            )}
        </div>
    );
}
