<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import { Placeholder } from '@tiptap/extension-placeholder';
import { Underline } from '@tiptap/extension-underline';
import { TextAlign } from '@tiptap/extension-text-align';
import { TextStyle } from '@tiptap/extension-text-style';
import { Color } from '@tiptap/extension-color';
import { Image } from '@tiptap/extension-image';
import { watch, onBeforeUnmount, ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'เขียนเนื้อหาที่นี่...',
    },
});

const emit = defineEmits(['update:modelValue']);

const imageInput = ref(null);
const isUploading = ref(false);
const showColorPicker = ref(false);

const editor = useEditor({
    content: props.modelValue || '',
    extensions: [
        StarterKit.configure({
            heading: { levels: [1, 2, 3] },
        }),
        Placeholder.configure({ placeholder: props.placeholder }),
        Underline,
        TextAlign.configure({ types: ['heading', 'paragraph'] }),
        TextStyle,
        Color,
        Image.configure({ inline: false, allowBase64: false }),
    ],
    editorProps: {
        handlePaste: (view, event) => {
            const items = event.clipboardData?.items;
            if (!items) return false;
            for (const item of items) {
                if (item.type.startsWith('image/')) {
                    event.preventDefault();
                    const file = item.getAsFile();
                    if (file) uploadAndInsert(file);
                    return true;
                }
            }
            return false;
        },
        handleDrop: (view, event) => {
            const files = event.dataTransfer?.files;
            if (!files?.length) return false;
            const file = files[0];
            if (file?.type.startsWith('image/')) {
                event.preventDefault();
                uploadAndInsert(file);
                return true;
            }
            return false;
        },
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
    },
});

watch(() => props.modelValue, (val) => {
    if (editor.value && val !== editor.value.getHTML()) {
        editor.value.commands.setContent(val || '', false);
    }
});

onBeforeUnmount(() => {
    editor.value?.destroy();
});

const setHeading = (level) => {
    if (!editor.value) return;
    if (level === 0) {
        editor.value.chain().focus().setParagraph().run();
    } else {
        editor.value.chain().focus().setHeading({ level }).run();
    }
};

const applyColor = (color) => {
    if (!editor.value) return;
    editor.value.chain().focus().setColor(color).run();
    showColorPicker.value = false;
};

const uploadAndInsert = async (file) => {
    if (isUploading.value) return;
    isUploading.value = true;
    try {
        const formData = new FormData();
        formData.append('image', file);
        const csrf = document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ? decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)[1]) : '';
        const { data } = await axios.post(route('backoffice.posts.upload-image'), formData, {
            headers: { 'X-XSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        editor.value?.chain().focus().setImage({ src: data.url }).run();
    } catch (err) {
        console.error('Upload failed:', err);
        alert('อัปโหลดรูปไม่สำเร็จ');
    } finally {
        isUploading.value = false;
        if (imageInput.value) imageInput.value.value = '';
    }
};

const onImageSelect = (e) => {
    const file = e.target.files?.[0];
    if (file?.type.startsWith('image/')) uploadAndInsert(file);
};

const addImage = () => imageInput.value?.click();

const colors = [
    '#000000', '#374151', '#dc2626', '#ea580c', '#ca8a04', '#16a34a', '#0891b2', '#7c3aed', '#db2777',
    '#ffffff', '#9ca3af', '#fca5a5', '#fdba74', '#fde047', '#86efac', '#67e8f9', '#c4b5fd', '#f9a8d4',
];
</script>

<template>
    <div class="border border-gray-300 rounded-lg overflow-hidden bg-white">
        <!-- Toolbar -->
        <div v-if="editor" class="flex flex-wrap items-center gap-1 p-2 border-b border-gray-200 bg-gray-50">
            <button
                type="button"
                :class="['p-2 rounded hover:bg-gray-200', editor.isActive('bold') && 'bg-gray-300']"
                @click="editor.chain().focus().toggleBold().run()"
                title="ตัวหนา"
            >
                <span class="font-bold text-sm">B</span>
            </button>
            <button
                type="button"
                :class="['p-2 rounded hover:bg-gray-200', editor.isActive('italic') && 'bg-gray-300']"
                @click="editor.chain().focus().toggleItalic().run()"
                title="ตัวเอียง"
            >
                <span class="italic text-sm">I</span>
            </button>
            <button
                type="button"
                :class="['p-2 rounded hover:bg-gray-200', editor.isActive('underline') && 'bg-gray-300']"
                @click="editor.chain().focus().toggleUnderline().run()"
                title="ขีดเส้นใต้"
            >
                <span class="underline text-sm">U</span>
            </button>
            <span class="w-px h-6 bg-gray-300 mx-1" />
            <select
                class="text-sm rounded border-gray-300 py-1.5 px-2"
                @change="(e) => { setHeading(parseInt(e.target.value)); }"
            >
                <option value="0">ปกติ</option>
                <option value="1" :selected="editor.isActive('heading', { level: 1 })">หัวข้อ 1</option>
                <option value="2" :selected="editor.isActive('heading', { level: 2 })">หัวข้อ 2</option>
                <option value="3" :selected="editor.isActive('heading', { level: 3 })">หัวข้อ 3</option>
            </select>
            <span class="w-px h-6 bg-gray-300 mx-1" />
            <div class="relative">
                <button
                    type="button"
                    class="p-2 rounded hover:bg-gray-200"
                    title="สีตัวอักษร"
                    @click="showColorPicker = !showColorPicker"
                >
                    <span class="text-sm">A</span>
                    <span
                        class="block w-4 h-0.5 -mt-0.5"
                        :style="{ backgroundColor: editor.getAttributes('textStyle').color || '#000' }"
                    />
                </button>
                <div
                    v-if="showColorPicker"
                    class="absolute top-full left-0 mt-1 p-2 bg-white border rounded-lg shadow-lg z-20 grid grid-cols-6 gap-1"
                >
                    <button
                        v-for="c in colors"
                        :key="c"
                        type="button"
                        class="w-6 h-6 rounded border hover:scale-110 transition-transform"
                        :style="{ backgroundColor: c, borderColor: c === '#ffffff' ? '#ccc' : c }"
                        @click="applyColor(c)"
                    />
                </div>
                <!-- Backdrop to close color picker when clicking outside -->
                <div v-if="showColorPicker" class="fixed inset-0 z-10" @click="showColorPicker = false" />
            </div>
            <span class="w-px h-6 bg-gray-300 mx-1" />
            <select
                class="text-sm rounded border-gray-300 py-1.5 px-2"
                @change="(e) => { const v = e.target.value; if (v) editor.chain().focus().setTextAlign(v).run(); e.target.value = ''; }"
            >
                <option value="">จัดแนว</option>
                <option value="left">ซ้าย</option>
                <option value="center">กลาง</option>
                <option value="right">ขวา</option>
                <option value="justify">เต็มบรรทัด</option>
            </select>
            <span class="w-px h-6 bg-gray-300 mx-1" />
            <input
                ref="imageInput"
                type="file"
                accept="image/*"
                class="hidden"
                @change="onImageSelect"
            />
            <button
                type="button"
                :disabled="isUploading"
                class="p-2 rounded hover:bg-gray-200 disabled:opacity-50"
                title="แทรกรูป"
                @click="addImage"
            >
                {{ isUploading ? '...' : '🖼️' }}
            </button>
        </div>

        <!-- Editor -->
        <EditorContent :editor="editor" class="prose prose-sm max-w-none min-h-[200px] p-4" />
    </div>

    <p v-if="!editor" class="text-gray-500 text-sm">กำลังโหลด...</p>
</template>

<style>
.ProseMirror {
    min-height: 200px;
    outline: none;
}
.ProseMirror p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left;
    color: #9ca3af;
    pointer-events: none;
    height: 0;
}
.ProseMirror h1 {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 0.5rem;
    color: #111827;
}
.ProseMirror h2 {
    font-size: 1.5rem;
    font-weight: 600;
    line-height: 1.3;
    margin-bottom: 0.5rem;
    color: #1f2937;
}
.ProseMirror h3 {
    font-size: 1.25rem;
    font-weight: 600;
    line-height: 1.4;
    margin-bottom: 0.5rem;
    color: #374151;
}
.ProseMirror p {
    margin-bottom: 0.75rem;
}
.ProseMirror img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
}
</style>
