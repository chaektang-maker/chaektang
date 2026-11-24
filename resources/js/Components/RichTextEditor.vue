<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import { Placeholder } from '@tiptap/extension-placeholder';
import { Underline } from '@tiptap/extension-underline';
import { TextAlign } from '@tiptap/extension-text-align';
import { TextStyle } from '@tiptap/extension-text-style';
import { Color } from '@tiptap/extension-color';
import { Image } from '@tiptap/extension-image';
import { Link } from '@tiptap/extension-link';
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
const showLinkDialog = ref(false);
const linkUrl = ref('');
const linkText = ref('');

const normalizeContentImages = (html) => {
    if (!html) return '';
    return String(html).replace(/https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?\/storage\//gi, '/storage/');
};

// ขยายความสามารถของ Image extension ให้รองรับการปรับขนาด (small / medium / full) และตำแหน่ง (left / center / right)
const CustomImage = Image.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            size: {
                default: 'medium',
                parseHTML: (element) => element.getAttribute('data-size') || 'medium',
                renderHTML: (attributes) => ({
                    'data-size': attributes.size || 'medium',
                }),
            },
            align: {
                default: 'center',
                parseHTML: (element) => element.getAttribute('data-align') || 'center',
                renderHTML: (attributes) => ({
                    'data-align': attributes.align || 'center',
                }),
            },
        };
    },
});

const editor = useEditor({
    content: normalizeContentImages(props.modelValue || ''),
    extensions: [
        StarterKit.configure({
            heading: { levels: [1, 2, 3] },
            bulletList: { keepMarks: true, keepAttributes: false },
            orderedList: { keepMarks: true, keepAttributes: false },
        }),
        Placeholder.configure({ placeholder: props.placeholder }),
        Underline,
        TextAlign.configure({ types: ['heading', 'paragraph'] }),
        TextStyle,
        Color,
        CustomImage.configure({ inline: false, allowBase64: false }),
        Link.configure({
            openOnClick: false,
            HTMLAttributes: {
                class: 'text-blue-600 underline hover:text-blue-800',
            },
        }),
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
        const html = editor.getHTML();
        emit('update:modelValue', html);
    },
    immediatelyRender: false,
});

watch(() => props.modelValue, (val) => {
    if (editor.value) {
        const normalized = normalizeContentImages(val || '');
        const currentContent = editor.value.getHTML();
        // ตรวจสอบว่า content เปลี่ยนจริงๆ หรือไม่ (เพื่อป้องกัน infinite loop)
        if (normalized !== currentContent) {
            editor.value.commands.setContent(normalized, false);
        }
    }
}, { immediate: true });

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

const clearColor = () => {
    if (!editor.value) return;
    editor.value.chain().focus().unsetColor().run();
    showColorPicker.value = false;
};

const applyBoldRed = () => {
    if (!editor.value) return;
    editor.value.chain().focus().setBold().setColor('#dc2626').run();
};

const quickColors = [
    { label: 'ดำ', color: '#111827' },
    { label: 'แดงเลขเด็ด', color: '#dc2626' },
    { label: 'ส้มทอง', color: '#d97706' },
    { label: 'เขียว', color: '#16a34a' },
    { label: 'น้ำเงิน', color: '#2563eb' },
    { label: 'ม่วง', color: '#7c3aed' },
];

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
        editor.value?.chain().focus().setImage({
            src: data.url,
            size: 'medium',
            align: 'center',
        }).run();
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

const setImageSize = (size) => {
    if (!editor.value) return;
    editor.value.chain().focus().updateAttributes('image', { size }).run();
};

const setImageAlign = (align) => {
    if (!editor.value) return;
    editor.value.chain().focus().updateAttributes('image', { align }).run();
};

const deleteSelectedImage = () => {
    if (!editor.value) return;
    editor.value.chain().focus().deleteSelection().run();
};

const openLinkDialog = () => {
    if (!editor.value) return;
    const { from, to } = editor.value.state.selection;
    const selectedText = editor.value.state.doc.textBetween(from, to, ' ');
    const linkAttrs = editor.value.getAttributes('link');
    
    linkText.value = selectedText || '';
    linkUrl.value = linkAttrs.href || '';
    showLinkDialog.value = true;
};

const insertLink = () => {
    if (!editor.value || !linkUrl.value) return;
    const url = linkUrl.value.trim();
    if (!url) return;
    
    if (linkText.value) {
        editor.value.chain().focus().insertContent(`<a href="${url}">${linkText.value}</a>`).run();
    } else {
        editor.value.chain().focus().setLink({ href: url }).run();
    }
    showLinkDialog.value = false;
    linkUrl.value = '';
    linkText.value = '';
};

const removeLink = () => {
    if (!editor.value) return;
    editor.value.chain().focus().unsetLink().run();
    showLinkDialog.value = false;
};

const colors = [
    '#000000', '#374151', '#dc2626', '#ea580c', '#ca8a04', '#16a34a', '#0891b2', '#7c3aed', '#db2777',
    '#ffffff', '#9ca3af', '#fca5a5', '#fdba74', '#fde047', '#86efac', '#67e8f9', '#c4b5fd', '#f9a8d4',
];
</script>

<template>
    <div class="border border-gray-300 rounded-xl bg-white shadow-sm">
        <!-- Toolbar -->
        <div v-if="editor" class="flex flex-wrap items-center gap-1.5 p-2.5 border-b border-gray-200 bg-gray-50 rounded-t-xl sticky top-0 z-20">
            <!-- Undo/Redo -->
            <button
                type="button"
                class="p-2 rounded hover:bg-gray-200 disabled:opacity-50"
                :disabled="!editor.can().undo()"
                @click="editor.chain().focus().undo().run()"
                title="ย้อนกลับ"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                </svg>
            </button>
            <button
                type="button"
                class="p-2 rounded hover:bg-gray-200 disabled:opacity-50"
                :disabled="!editor.can().redo()"
                @click="editor.chain().focus().redo().run()"
                title="ทำซ้ำ"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6" />
                </svg>
            </button>
            <span class="w-px h-6 bg-gray-300 mx-1" />
            
            <!-- Text Formatting -->
            <button
                type="button"
                :class="['p-2 rounded hover:bg-gray-200', editor.isActive('bold') && 'bg-gray-300']"
                @click="editor.chain().focus().toggleBold().run()"
                title="ตัวหนา (Ctrl+B)"
            >
                <span class="font-bold text-sm">B</span>
            </button>
            <button
                type="button"
                :class="['p-2 rounded hover:bg-gray-200', editor.isActive('italic') && 'bg-gray-300']"
                @click="editor.chain().focus().toggleItalic().run()"
                title="ตัวเอียง (Ctrl+I)"
            >
                <span class="italic text-sm">I</span>
            </button>
            <button
                type="button"
                :class="['p-2 rounded hover:bg-gray-200', editor.isActive('underline') && 'bg-gray-300']"
                @click="editor.chain().focus().toggleUnderline().run()"
                title="ขีดเส้นใต้ (Ctrl+U)"
            >
                <span class="underline text-sm">U</span>
            </button>
            <button
                type="button"
                :class="['p-2 rounded hover:bg-gray-200', editor.isActive('strike') && 'bg-gray-300']"
                @click="editor.chain().focus().toggleStrike().run()"
                title="ขีดฆ่า"
            >
                <span class="line-through text-sm">S</span>
            </button>
            <span class="w-px h-6 bg-gray-300 mx-1" />
            
            <!-- Headings -->
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
            
            <!-- Lists -->
            <button
                type="button"
                :class="['p-2 rounded hover:bg-gray-200', editor.isActive('bulletList') && 'bg-gray-300']"
                @click="editor.chain().focus().toggleBulletList().run()"
                title="รายการแบบจุด"
            >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 6a1 1 0 100-2 1 1 0 000 2zm0 4a1 1 0 100-2 1 1 0 000 2zm0 4a1 1 0 100-2 1 1 0 000 2zm4-8h12a1 1 0 110 2H6a1 1 0 010-2zm0 4h12a1 1 0 110 2H6a1 1 0 010-2zm0 4h12a1 1 0 110 2H6a1 1 0 010-2z" />
                </svg>
            </button>
            <button
                type="button"
                :class="['p-2 rounded hover:bg-gray-200', editor.isActive('orderedList') && 'bg-gray-300']"
                @click="editor.chain().focus().toggleOrderedList().run()"
                title="รายการแบบเลข"
            >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" />
                </svg>
            </button>
            <span class="w-px h-6 bg-gray-300 mx-1" />
            
            <!-- Blockquote & Code -->
            <button
                type="button"
                :class="['p-2 rounded hover:bg-gray-200', editor.isActive('blockquote') && 'bg-gray-300']"
                @click="editor.chain().focus().toggleBlockquote().run()"
                title="คำพูด"
            >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V8a1 1 0 112 0v2.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
            <button
                type="button"
                :class="['p-2 rounded hover:bg-gray-200', editor.isActive('code') && 'bg-gray-300']"
                @click="editor.chain().focus().toggleCode().run()"
                title="โค้ด"
            >
                <span class="text-xs font-mono">&lt;/&gt;</span>
            </button>
            <button
                type="button"
                :class="['p-2 rounded hover:bg-gray-200', editor.isActive('codeBlock') && 'bg-gray-300']"
                @click="editor.chain().focus().toggleCodeBlock().run()"
                title="บล็อกโค้ด"
            >
                <span class="text-xs font-mono">{ }</span>
            </button>
            <button
                type="button"
                class="p-2 rounded hover:bg-gray-200"
                @click="editor.chain().focus().setHorizontalRule().run()"
                title="เส้นคั่น"
            >
                <span class="text-sm">─</span>
            </button>
            <span class="w-px h-6 bg-gray-300 mx-1" />
            
            <!-- แถบเปลี่ยนสีตัวอักษรด่วน (คลิกเดียวเปลี่ยนสีทันที ไม่หลุดโฟกัส) -->
            <div class="flex items-center gap-1 bg-white px-2 py-1 rounded-lg border border-gray-200 shadow-2xs">
                <span class="text-[11px] font-bold text-gray-500 mr-0.5">สี:</span>
                <button
                    v-for="qc in quickColors"
                    :key="qc.color"
                    type="button"
                    class="w-5 h-5 rounded-full border transition-transform hover:scale-125 flex items-center justify-center"
                    :style="{
                        backgroundColor: qc.color,
                        borderColor: editor.isActive('textStyle', { color: qc.color }) ? '#000' : 'transparent',
                    }"
                    :class="editor.isActive('textStyle', { color: qc.color }) ? 'ring-2 ring-offset-1 ring-gray-900 scale-110' : ''"
                    :title="`สี${qc.label}`"
                    @mousedown.prevent="applyColor(qc.color)"
                />

                <!-- ปุ่มลัด: ตัวหนา + สีแดง (สำหรับเน้นเลขเด็ด) -->
                <button
                    type="button"
                    class="ml-1 px-2 py-0.5 rounded bg-red-50 hover:bg-red-100 text-red-600 font-extrabold text-xs border border-red-200 transition-colors"
                    title="ทำตัวหนา + สีแดงในคลิกเดียว (เหมาะสำหรับเน้นตัวเลข)"
                    @mousedown.prevent="applyBoldRed"
                >
                    🔥 เน้นแดง
                </button>

                <!-- ปุ่มล้างสี -->
                <button
                    type="button"
                    class="px-1.5 py-0.5 rounded text-[11px] text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                    title="ล้างสีกลับเป็นค่าเริ่มต้น"
                    @mousedown.prevent="clearColor"
                >
                    ล้างสี
                </button>

                <!-- ถาดสีเพิ่มเติม -->
                <div class="relative">
                    <button
                        type="button"
                        class="px-1.5 py-0.5 rounded hover:bg-gray-100 text-xs font-bold text-gray-700 flex items-center gap-0.5"
                        title="เลือกสีอื่นๆ เพิ่มเติม"
                        @mousedown.prevent="showColorPicker = !showColorPicker"
                    >
                        <span>🎨</span>
                        <span
                            class="inline-block w-3 h-3 rounded-xs border border-gray-300"
                            :style="{ backgroundColor: editor.getAttributes('textStyle').color || '#111827' }"
                        />
                    </button>
                    <div
                        v-if="showColorPicker"
                        class="absolute top-full left-0 mt-2 p-3 bg-white border border-gray-200 rounded-xl shadow-xl z-30 w-56 space-y-2.5"
                    >
                        <div class="text-xs font-bold text-gray-700">เลือกสีตัวอักษร</div>
                        <div class="grid grid-cols-6 gap-1.5">
                            <button
                                v-for="c in colors"
                                :key="c"
                                type="button"
                                class="w-6 h-6 rounded-md border hover:scale-110 transition-transform"
                                :style="{ backgroundColor: c, borderColor: c === '#ffffff' ? '#d1d5db' : c }"
                                @mousedown.prevent="applyColor(c)"
                            />
                        </div>
                        <div class="pt-2 border-t border-gray-100 flex items-center justify-between gap-2">
                            <label class="text-xs text-gray-600 flex items-center gap-1.5 cursor-pointer">
                                <span>ผสมสีเอง:</span>
                                <input
                                    type="color"
                                    class="w-6 h-6 p-0 border-0 rounded cursor-pointer"
                                    :value="editor.getAttributes('textStyle').color || '#dc2626'"
                                    @input="(e) => applyColor(e.target.value)"
                                />
                            </label>
                            <button
                                type="button"
                                class="text-xs font-semibold text-red-600 hover:underline"
                                @mousedown.prevent="clearColor"
                            >
                                รีเซ็ตสี
                            </button>
                        </div>
                    </div>
                    <div v-if="showColorPicker" class="fixed inset-0 z-10" @click="showColorPicker = false" />
                </div>
            </div>
            <span class="w-px h-6 bg-gray-300 mx-1" />
            
            <!-- Text Align -->
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
            
            <!-- Link -->
            <div class="relative">
                <button
                    type="button"
                    :class="['p-2 rounded hover:bg-gray-200', editor.isActive('link') && 'bg-gray-300']"
                    title="ลิงก์"
                    @click="openLinkDialog"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                </button>
                <div
                    v-if="showLinkDialog"
                    class="absolute top-full right-0 mt-1 p-3 bg-white border rounded-lg shadow-lg z-20 w-80"
                >
                    <div class="space-y-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">URL</label>
                            <input
                                v-model="linkUrl"
                                type="url"
                                class="w-full text-sm rounded border-gray-300 px-2 py-1"
                                placeholder="https://..."
                                @keyup.enter="insertLink"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">ข้อความ (ไม่บังคับ)</label>
                            <input
                                v-model="linkText"
                                type="text"
                                class="w-full text-sm rounded border-gray-300 px-2 py-1"
                                placeholder="ข้อความลิงก์"
                                @keyup.enter="insertLink"
                            />
                        </div>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="flex-1 px-3 py-1.5 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700"
                                @click="insertLink"
                            >
                                เพิ่ม
                            </button>
                            <button
                                v-if="editor.isActive('link')"
                                type="button"
                                class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700"
                                @click="removeLink"
                            >
                                ลบ
                            </button>
                            <button
                                type="button"
                                class="px-3 py-1.5 text-sm bg-gray-200 rounded hover:bg-gray-300"
                                @click="showLinkDialog = false"
                            >
                                ยกเลิก
                            </button>
                        </div>
                    </div>
                </div>
                <div v-if="showLinkDialog" class="fixed inset-0 z-10" @click="showLinkDialog = false" />
            </div>
            <span class="w-px h-6 bg-gray-300 mx-1" />
            
            <!-- Image -->
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
                class="px-2.5 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 disabled:opacity-50 text-xs font-bold inline-flex items-center gap-1 border border-indigo-200"
                title="แทรกรูปภาพประกอบบทความ"
                @click="addImage"
            >
                <span>{{ isUploading ? '⏳ กำลังอัปโหลด...' : '🖼️ แทรกรูปภาพ' }}</span>
            </button>
        </div>

        <!-- แถบจัดระเบียบรูปภาพ (แสดงอัตโนมัติเมื่อคลิกที่รูปภาพในบทความ) -->
        <div
            v-if="editor && editor.isActive('image')"
            class="flex flex-wrap items-center justify-between gap-2 px-3 py-2 bg-indigo-50 border-b border-indigo-200 text-xs"
        >
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-bold text-indigo-900">🖼️ จัดระเบียบรูปภาพที่เลือก:</span>

                <!-- ขนาดรูปภาพ -->
                <div class="inline-flex rounded-lg bg-white p-0.5 border border-indigo-200 shadow-2xs">
                    <button
                        type="button"
                        :class="[
                            'px-2.5 py-1 rounded-md font-bold transition-colors',
                            (editor.getAttributes('image').size || 'medium') === 'small'
                                ? 'bg-indigo-600 text-white'
                                : 'text-gray-700 hover:bg-gray-100',
                        ]"
                        @mousedown.prevent="setImageSize('small')"
                    >
                        เล็ก (40%)
                    </button>
                    <button
                        type="button"
                        :class="[
                            'px-2.5 py-1 rounded-md font-bold transition-colors',
                            (editor.getAttributes('image').size || 'medium') === 'medium'
                                ? 'bg-indigo-600 text-white'
                                : 'text-gray-700 hover:bg-gray-100',
                        ]"
                        @mousedown.prevent="setImageSize('medium')"
                    >
                        ขนาดพอดี (65%)
                    </button>
                    <button
                        type="button"
                        :class="[
                            'px-2.5 py-1 rounded-md font-bold transition-colors',
                            editor.getAttributes('image').size === 'full'
                                ? 'bg-indigo-600 text-white'
                                : 'text-gray-700 hover:bg-gray-100',
                        ]"
                        @mousedown.prevent="setImageSize('full')"
                    >
                        เต็มกรอบ (100%)
                    </button>
                </div>

                <!-- ตำแหน่งรูปภาพ -->
                <div class="inline-flex rounded-lg bg-white p-0.5 border border-indigo-200 shadow-2xs">
                    <button
                        type="button"
                        :class="[
                            'px-2.5 py-1 rounded-md font-bold transition-colors',
                            editor.getAttributes('image').align === 'left'
                                ? 'bg-indigo-600 text-white'
                                : 'text-gray-700 hover:bg-gray-100',
                        ]"
                        @mousedown.prevent="setImageAlign('left')"
                    >
                        ⬅️ ชิดซ้าย
                    </button>
                    <button
                        type="button"
                        :class="[
                            'px-2.5 py-1 rounded-md font-bold transition-colors',
                            (editor.getAttributes('image').align || 'center') === 'center'
                                ? 'bg-indigo-600 text-white'
                                : 'text-gray-700 hover:bg-gray-100',
                        ]"
                        @mousedown.prevent="setImageAlign('center')"
                    >
                        ↔️ กึ่งกลาง
                    </button>
                    <button
                        type="button"
                        :class="[
                            'px-2.5 py-1 rounded-md font-bold transition-colors',
                            editor.getAttributes('image').align === 'right'
                                ? 'bg-indigo-600 text-white'
                                : 'text-gray-700 hover:bg-gray-100',
                        ]"
                        @mousedown.prevent="setImageAlign('right')"
                    >
                        ➡️ ชิดขวา
                    </button>
                </div>
            </div>

            <button
                type="button"
                class="px-2.5 py-1 rounded-lg bg-red-600 text-white hover:bg-red-700 font-bold"
                @mousedown.prevent="deleteSelectedImage"
            >
                🗑️ ลบรูปนี้
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
.ProseMirror ul,
.ProseMirror ol {
    padding-left: 1.5rem;
    margin-bottom: 0.75rem;
}
.ProseMirror ul {
    list-style-type: disc;
}
.ProseMirror ol {
    list-style-type: decimal;
}
.ProseMirror li {
    margin-bottom: 0.25rem;
}
.ProseMirror blockquote {
    border-left: 4px solid #e5e7eb;
    padding-left: 1rem;
    margin: 1rem 0;
    color: #6b7280;
    font-style: italic;
}
.ProseMirror code {
    background-color: #f3f4f6;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
    font-family: 'Courier New', monospace;
    color: #dc2626;
}
.ProseMirror pre {
    background-color: #1f2937;
    color: #f9fafb;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin: 1rem 0;
}
.ProseMirror pre code {
    background-color: transparent;
    padding: 0;
    color: inherit;
    font-size: 0.875rem;
}
.ProseMirror hr {
    border: none;
    border-top: 2px solid #e5e7eb;
    margin: 1.5rem 0;
}
.ProseMirror img {
    display: block;
    max-width: 68%;
    max-height: 460px;
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: 0.75rem;
    margin: 1.25rem auto;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    border: 1px solid #e5e7eb;
    cursor: pointer;
    transition: all 0.2s ease;
}
.ProseMirror img.ProseMirror-selectednode {
    outline: 3px solid #4f46e5;
    outline-offset: 2px;
}
.ProseMirror img[data-size="small"] {
    max-width: 40%;
    max-height: 320px;
}
.ProseMirror img[data-size="medium"] {
    max-width: 68%;
    max-height: 460px;
}
.ProseMirror img[data-size="full"] {
    max-width: 100%;
    max-height: 640px;
}
.ProseMirror img[data-align="left"] {
    margin-left: 0;
    margin-right: auto;
}
.ProseMirror img[data-align="center"] {
    margin-left: auto;
    margin-right: auto;
}
.ProseMirror img[data-align="right"] {
    margin-left: auto;
    margin-right: 0;
}
.ProseMirror strong {
    color: inherit;
    font-weight: 700;
}
.ProseMirror a {
    color: #2563eb;
    text-decoration: underline;
    cursor: pointer;
}
.ProseMirror a:hover {
    color: #1e40af;
}
</style>
