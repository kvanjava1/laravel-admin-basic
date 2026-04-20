<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import Underline from '@tiptap/extension-underline';
import Placeholder from '@tiptap/extension-placeholder';
import TextAlign from '@tiptap/extension-text-align';
import { watch, onBeforeUnmount, ref } from 'vue';

import { Figure, Figcaption } from './Figure';
import BaseLabel from './BaseLabel.vue';

const props = defineProps<{
    modelValue: string;
    placeholder?: string;
    hideImage?: boolean;
    minimal?: boolean;
    error?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'open-media-library', callback: any): void;
}>();

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [1, 2, 3, 4],
            },
        }),
        Underline,
        Figure,
        Figcaption,
        Image.configure({
            HTMLAttributes: {
                class: 'rounded-2xl border border-slate-200 shadow-sm max-w-full h-auto',
            },
        }),
        Link.configure({
            openOnClick: false,
            HTMLAttributes: {
                class: 'text-primary underline font-bold',
            },
        }),
        Placeholder.configure({
            placeholder: ({ node }) => {
                if (node.type.name === 'figcaption') {
                    return 'Write a caption for this image...';
                }
                return props.placeholder || 'Write something amazing...';
            },
        }),
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
    ],
    editorProps: {
        attributes: {
            class: `prose prose-slate max-w-none focus:outline-none ${props.minimal ? 'min-h-[150px]' : 'min-h-[400px]'} px-5 py-4 text-lg leading-relaxed text-slate-700`,
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
const isActive = (nameOrAttributes: string | Record<string, any>, attributes?: Record<string, any>) => {
    if (typeof nameOrAttributes === 'string') {
        return editor.value?.isActive(nameOrAttributes, attributes);
    }
    return editor.value?.isActive(nameOrAttributes);
};

const setLink = () => {
    const previousUrl = editor.value?.getAttributes('link').href;
    const url = window.prompt('URL', previousUrl);

    if (url === null) return;
    if (url === '') {
        editor.value?.chain().focus().extendMarkRange('link').unsetLink().run();
        return;
    }

    editor.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};

const isSourceMode = ref(false);

const toggleSourceMode = () => {
    isSourceMode.value = !isSourceMode.value;
};

const handleImageClick = () => {
    emit('open-media-library', (imageUrl: string, item: any) => {
        editor.value?.chain().focus().insertContent([
            {
                type: 'figure',
                content: [
                    {
                        type: 'image',
                        attrs: { 
                            src: imageUrl, 
                            alt: item.alt_text || '',
                            title: item.title || ''
                        },
                    },
                    {
                        type: 'figcaption',
                        content: item.caption ? [{ type: 'text', text: item.caption }] : []
                    },
                ],
            },
            {
                type: 'paragraph',
            }
        ]).run();
    });
};
</script>

<template>
    <div class="flex flex-col gap-1">
        <div v-if="editor" 
            :class="[
                error ? 'border-rose-400 ring-2 ring-rose-50' : 'border-slate-200 focus-within:ring-4 focus-within:ring-primary/5 focus-within:border-primary',
            ]"
            class="border rounded-2xl bg-white overflow-hidden transition-all flex flex-col"
        >
            <!-- Toolbar -->
            <div class="flex flex-wrap items-center gap-1 p-2 border-b border-slate-100 bg-slate-50/50" :class="{ 'bg-rose-50/30 border-rose-100': error }">
            <template v-if="!isSourceMode">
                <!-- Heading (Hide in minimal) -->
                <div v-if="!minimal" class="flex items-center gap-0.5 pr-2 border-r border-slate-200 mr-1">
                    <button @click="toggleHeading(2)" :class="{ 'bg-primary text-white': isActive('heading', { level: 2 }) }" 
                        class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Heading 2">format_h2</button>
                    <button @click="toggleHeading(3)" :class="{ 'bg-primary text-white': isActive('heading', { level: 3 }) }" 
                        class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Heading 3">format_h3</button>
                </div>

                <!-- Basic Styles (Always show) -->
                <div class="flex items-center gap-0.5 pr-2 border-r border-slate-200 mr-1">
                    <button @click="toggleBold" :class="{ 'bg-primary text-white': isActive('bold') }" 
                        class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Bold">format_bold</button>
                    <button @click="toggleItalic" :class="{ 'bg-primary text-white': isActive('italic') }" 
                        class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Italic">format_italic</button>
                    <button @click="toggleUnderline" :class="{ 'bg-primary text-white': isActive('underline') }" 
                        class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Underline">format_underlined</button>
                </div>

                <!-- Lists (Always show) -->
                <div class="flex items-center gap-0.5 pr-2 border-r border-slate-200 mr-1">
                    <button @click="toggleBulletList" :class="{ 'bg-primary text-white': isActive('bulletList') }" 
                        class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Bullet List">format_list_bulleted</button>
                    <button @click="toggleOrderedList" :class="{ 'bg-primary text-white': isActive('orderedList') }" 
                        class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Ordered List">format_list_numbered</button>
                    <button v-if="!minimal" @click="toggleBlockquote" :class="{ 'bg-primary text-white': isActive('blockquote') }" 
                        class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Blockquote">format_quote</button>
                </div>

                <!-- Alignment & Links (Hide in minimal) -->
                <template v-if="!minimal">
                    <div class="flex items-center gap-0.5 pr-2 border-r border-slate-200 mr-1">
                        <button @click="setTextAlign('left')" :class="{ 'bg-primary text-white': isActive({ textAlign: 'left' }) }" 
                            class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Align Left">format_align_left</button>
                        <button @click="setTextAlign('center')" :class="{ 'bg-primary text-white': isActive({ textAlign: 'center' }) }" 
                            class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Align Center">format_align_center</button>
                        <button @click="setTextAlign('right')" :class="{ 'bg-primary text-white': isActive({ textAlign: 'right' }) }" 
                            class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Align Right">format_align_right</button>
                    </div>

                    <div class="flex items-center gap-0.5">
                        <button @click="setLink" :class="{ 'bg-primary text-white': isActive('link') }" 
                            class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Insert Link">link</button>
                        <button v-if="!hideImage" @click="handleImageClick" class="p-1.5 rounded-lg hover:bg-slate-200 transition-colors material-symbols-outlined text-[20px]" title="Insert Image">add_photo_alternate</button>
                    </div>
                </template>
            </template>

            <div v-else class="flex-1 flex items-center px-3">
                <span class="text-[10px] font-black uppercase tracking-widest text-primary animate-pulse flex items-center gap-2">
                    <span class="material-symbols-outlined text-[14px]">html</span>
                    Source Code Mode
                </span>
            </div>

            <div class="flex items-center gap-1 ml-auto" v-if="!minimal">
                <div class="w-[1px] h-6 bg-slate-200 mx-1" v-if="!isSourceMode"></div>

                <button @click="toggleSourceMode" :class="{ 'bg-slate-800 text-white': isSourceMode, 'text-slate-500 hover:bg-slate-200': !isSourceMode }"
                    class="p-1.5 rounded-lg transition-all material-symbols-outlined text-[20px]" 
                    title="Toggle Source Code">code</button>
            </div>
        </div>

        <!-- Editor Area -->
        <div class="relative flex-1 flex flex-col min-h-[400px]">
            <EditorContent v-show="!isSourceMode" :editor="editor as any" class="flex-1" />
            <textarea 
                v-if="isSourceMode"
                :value="modelValue"
                @input="e => emit('update:modelValue', (e.target as HTMLTextAreaElement).value)"
                class="flex-1 w-full p-6 font-mono text-sm text-slate-700 bg-slate-50 focus:outline-none resize-none leading-relaxed"
                spellcheck="false"
            ></textarea>
        </div>

        <!-- Status Bar -->
        <div class="px-4 py-2 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[10px] font-black uppercase tracking-widest text-slate-400">
            <div class="flex items-center gap-2">
                <span class="h-1.5 w-1.5 rounded-full" :class="isSourceMode ? 'bg-amber-500' : 'bg-emerald-500'"></span>
                {{ isSourceMode ? 'Direct HTML Editing' : 'Ready to Publish' }}
            </div>
            <div v-if="editor.value">
                {{ editor.value.storage.characterCount?.words?.() || 0 }} words
            </div>
        </div>
    </div>
    <div v-if="error" class="flex items-center gap-1 mt-1 ml-1 text-rose-600 animate-in fade-in slide-in-from-top-1">
        <span class="material-symbols-outlined text-[14px]">error</span>
        <p class="text-sm font-bold leading-none tracking-tight">
            {{ error }}
        </p>
    </div>
</div>
</template>

<style scoped>
/* Ensure headings are visible in the editor */
:deep(.ProseMirror) {
    outline: none !important;
}

:deep(.ProseMirror h1) {
    font-size: 2.25rem;
    font-weight: 900;
    line-height: 1.2;
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    color: #0f172a;
}

:deep(.ProseMirror h2) {
    font-size: 1.875rem;
    font-weight: 800;
    line-height: 1.3;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
    color: #1e293b;
}

:deep(.ProseMirror h3) {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.4;
    margin-top: 1.25rem;
    margin-bottom: 0.5rem;
    color: #334155;
}

:deep(.ProseMirror h4) {
    font-size: 1.25rem;
    font-weight: 700;
    line-height: 1.5;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

:deep(.ProseMirror p) {
    margin-bottom: 1rem;
}

:deep(.ProseMirror blockquote) {
    border-left: 4px solid #e2e8f0;
    padding-left: 1rem;
    font-style: italic;
    color: #475569;
    margin: 1.5rem 0;
}

/* Placeholder styling */
:deep(.ProseMirror p.is-editor-empty:first-child::before) {
    content: attr(data-placeholder);
    float: left;
    color: #cbd5e1;
    pointer-events: none;
    height: 0;
}

:deep(.ProseMirror figcaption.is-empty::before) {
    content: attr(data-placeholder);
    float: left;
    color: #94a3b8;
    pointer-events: none;
    height: 0;
}
</style>
