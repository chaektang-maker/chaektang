<script setup>
import { ref } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: 'เลขเด็ดงวดนี้ - เว็บแจกตัง',
    },
    text: {
        type: String,
        default: '',
    },
    url: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: 'แชร์ให้เพื่อน:',
    },
    compact: {
        type: Boolean,
        default: false,
    },
});

const copied = ref(false);

const getTargetUrl = () => {
    if (props.url) {
        if (props.url.startsWith('http')) return props.url;
        if (typeof window !== 'undefined') return `${window.location.origin}${props.url}`;
    }
    return typeof window !== 'undefined' ? window.location.href : '';
};

const getShareMessage = () => {
    const u = getTargetUrl();
    const baseText = props.text || props.title;
    return `${baseText}\n👉 ดูเพิ่มเติมที่: ${u}`;
};

const shareToLine = () => {
    const u = getTargetUrl();
    const msg = props.text || props.title;
    const lineUrl = `https://social-plugins.line.me/lineit/share?url=${encodeURIComponent(u)}&text=${encodeURIComponent(msg)}`;
    window.open(lineUrl, '_blank', 'noopener,noreferrer,width=600,height=600');
};

const shareToFacebook = () => {
    const u = getTargetUrl();
    const fbUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(u)}&quote=${encodeURIComponent(props.text || props.title)}`;
    window.open(fbUrl, '_blank', 'noopener,noreferrer,width=600,height=600');
};

const copyShareText = async () => {
    const msg = getShareMessage();
    try {
        await navigator.clipboard.writeText(msg);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2500);
    } catch (e) {
        // fallback
    }
};
</script>

<template>
    <div :class="['flex flex-wrap items-center gap-2', compact ? 'text-xs' : 'text-sm']">
        <span v-if="label && !compact" class="font-semibold text-gray-700 mr-1">
            {{ label }}
        </span>

        <!-- ปุ่มแชร์ LINE -->
        <button
            type="button"
            @click="shareToLine"
            :class="[
                'inline-flex items-center gap-1.5 font-bold rounded-lg bg-[#06C755] text-white hover:bg-[#05b34c] transition-all shadow-sm',
                compact ? 'px-2.5 py-1 text-xs' : 'px-3.5 py-1.5 text-xs sm:text-sm',
            ]"
            title="แชร์ไปที่ LINE"
        >
            <span>💬</span>
            <span>{{ compact ? 'แชร์ LINE' : 'แชร์ไปที่ LINE' }}</span>
        </button>

        <!-- ปุ่มแชร์ Facebook -->
        <button
            type="button"
            @click="shareToFacebook"
            :class="[
                'inline-flex items-center gap-1.5 font-bold rounded-lg bg-[#1877F2] text-white hover:bg-[#166fe5] transition-all shadow-sm',
                compact ? 'px-2.5 py-1 text-xs' : 'px-3.5 py-1.5 text-xs sm:text-sm',
            ]"
            title="แชร์ไปที่ Facebook"
        >
            <span>📘</span>
            <span>{{ compact ? 'Facebook' : 'แชร์ Facebook' }}</span>
        </button>

        <!-- ปุ่มคัดลอกข้อความและลิงก์ -->
        <button
            type="button"
            @click="copyShareText"
            :class="[
                'inline-flex items-center gap-1.5 font-bold rounded-lg border transition-all',
                copied
                    ? 'bg-emerald-50 border-emerald-300 text-emerald-700'
                    : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50',
                compact ? 'px-2.5 py-1 text-xs' : 'px-3.5 py-1.5 text-xs sm:text-sm',
            ]"
            title="คัดลอกข้อความและลิงก์เพื่อส่งให้เพื่อน"
        >
            <span>{{ copied ? '✓' : '📋' }}</span>
            <span>{{ copied ? 'คัดลอกแล้ว!' : (compact ? 'คัดลอก' : 'คัดลอกลิงก์') }}</span>
        </button>
    </div>
</template>

