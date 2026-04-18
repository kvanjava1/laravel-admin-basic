<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import Underline from '@tiptap/extension-underline';
import Placeholder from '@tiptap/extension-placeholder';
import TextAlign from '@tiptap/extension-text-align';
import { watch, onBeforeUnmount } from 'vue';

const props = defineProps<{
    modelValue: string;
    placeholder?: string;
}>();

const emit = defineEmits(['update:modelValue', 'open-media-library']);

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit,
        Underline,
        Image.configure({
            HTMLAttributes: {
                class: 'rounded-2xl border border-slate-200 shadow-sm max-w-full h-auto my-6',
            },
        }),
        Link.configure({
            openOnClick: false,
            HTMLAttributes: {
                class: 'text-primary underline font-bold',
            },
        }),
        Placeholder.configure({
            placeholder: props.placeholder || 'Write something amazing...',
        }),
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-slate max-w-none focus:outline-none min-h-[400px] px-5 py-4 font-serif text-lg leading-relaxed text-slate-700',
        },
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
    },
});

// Watch for external content changes
watch(() => props.modelValue, (value) => {
    if (editor.value && editor.value.getHTML() !== value) {
        editor.value.commands.setContent(value, { emitUpdate: false });
    }
});

onBeforeUnmount(() => {
    editor.value?.destroy();
});

// Toolbar Actions
const toggleBold = () => editor.value?.chain().focus().toggleBold().run();
const toggleItalic = () => editor.value?.chain().focus().toggleItalic().run();
const toggleUnderline = () => editor.value?.chain().focus().toggleUnderline().run();
const toggleHeading = (level: any) => editor.value?.chain().focus().toggleHeading({ level }).run();
const toggleBulletList = () => editor.value?.chain().focus().toggleBulletList().run();
const toggleOrderedList = () => editor.value?.chain().focus().toggleOrderedList().run();
const toggleBlockquote = () => editor.value?.chain().focus().toggleBlockquote().run();
const setTextAlign = (align: string) => editor.value?.chain().focus().setTextAlign(align).run();

const openMediaLibrary = () => {
    emit('open-media-library', (imageUrl: string, alt: string) => {
        editor.value?.chain().focus().setImage({ src: imageUrl, alt }).run();
    });
};
</script>

<template>
    <div v-if="editor" class="border border-slate-200 rounded-2xl bg-white overflow-hidden focus-within:ring-4 focus-within:ring-primary/5 focus-within:border-primary transition-all flex flex-col">
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center gap-1 p-2 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-0.5 pr-2 border-r border-slate-200 mr-1">
                <button @click="toggleHeading(2)" :class="{ 'bg-primary text-white': editor.value?.isActive('heading', { level: 2 }) }" 
                    class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Heading 2">format_h2</button>
                <button @click="toggleHeading(3)" :class="{ 'bg-primary text-white': editor.value?.isActive('heading', { level: 3 }) }" 
                    class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Heading 3">format_h3</button>
            </div>

            <div class="flex items-center gap-0.5 pr-2 border-r border-slate-200 mr-1">
                <button @click="toggleBold" :class="{ 'bg-primary text-white': editor.value?.isActive('bold') }" 
                    class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Bold">format_bold</button>
                <button @click="toggleItalic" :class="{ 'bg-primary text-white': editor.value?.isActive('italic') }" 
                    class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Italic">format_italic</button>
                <button @click="toggleUnderline" :class="{ 'bg-primary text-white': editor.value?.isActive('underline') }" 
                    class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Underline">format_underlined</button>
            </div>

            <div class="flex items-center gap-0.5 pr-2 border-r border-slate-200 mr-1">
                <button @click="toggleBulletList" :class="{ 'bg-primary text-white': editor.value?.isActive('bulletList') }" 
                    class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Bullet List">format_list_bulleted</button>
                <button @click="toggleOrderedList" :class="{ 'bg-primary text-white': editor.value?.isActive('orderedList') }" 
                    class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Ordered List">format_list_numbered</button>
                <button @click="toggleBlockquote" :class="{ 'bg-primary text-white': editor.value?.isActive('blockquote') }" 
                    class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Blockquote">format_quote</button>
            </div>

            <div class="flex items-center gap-0.5 pr-2 border-r border-slate-200 mr-1">
                <button @click="setTextAlign('left')" :class="{ 'bg-primary text-white': editor.value?.isActive({ textAlign: 'left' }) }" 
                    class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Align Left">format_align_left</button>
                <button @click="setTextAlign('center')" :class="{ 'bg-primary text-white': editor.value?.isActive({ textAlign: 'center' }) }" 
                    class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Align Center">format_align_center</button>
                <button @click="setTextAlign('right')" :class="{ 'bg-primary text-white': editor.value?.isActive({ textAlign: 'right' }) }" 
                    class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Align Right">format_align_right</button>
            </div>

            <button @click="openMediaLibrary" 
                class="p-1.5 rounded-lg hover:bg-primary/10 hover:text-primary transition-colors material-symbols-outlined text-[20px] ml-auto text-primary" 
                title="Insert Media from Library">image</button>
        </div>

        <!-- Editor Area -->
        <EditorContent :editor="editor as any" />

        <!-- Status Bar -->
        <div class="px-4 py-2 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[10px] font-black uppercase tracking-widest text-slate-400">
            <div class="flex items-center gap-2">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                Ready to Publish
            </div>
            <div v-if="editor.value">
                {{ editor.value.storage.characterCount?.words?.() || 0 }} words
            </div>
        </div>
    </div>
</template>

<style>
/* Tiptap Placeholder Styling */
.prose p.is-editor-empty:first-child::before {
  content: attr(data-placeholder);
  float: left;
  color: #adb5bd;
  pointer-events: none;
  height: 0;
}

/* Image styling in editor */
.prose img {
    transition: all 0.3s ease;
}
.prose img.ProseMirror-selectednode {
    border-color: #3b82f6; /* primary color */
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.3);
}
</style>
