import { Node, mergeAttributes } from '@tiptap/core';

export const Figure = Node.create({
  name: 'figure',
  group: 'block',
  content: 'image figcaption',
  draggable: true,

  parseHTML() {
    return [
      { tag: 'figure' },
    ];
  },

  renderHTML({ HTMLAttributes }) {
    return ['figure', mergeAttributes(HTMLAttributes, { class: 'my-8 mx-auto max-w-full' }), 0];
  },
});

export const Figcaption = Node.create({
  name: 'figcaption',
  content: 'inline*',
  selectable: false,
  draggable: false,

  parseHTML() {
    return [
      { tag: 'figcaption' },
    ];
  },

  renderHTML({ HTMLAttributes }) {
    return ['figcaption', mergeAttributes(HTMLAttributes, { class: 'text-left text-sm text-slate-500 mt-3 font-medium' }), 0];
  },
});
