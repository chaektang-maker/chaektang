<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const isOpen = ref(false);

const contact = computed(() => page.props.contactInfo || {
    line_id: '@chaektang',
    line_url: 'https://line.me/ti/p/~@chaektang',
    phone: '083-487-1695',
    email: 'chaektang@gmail.com',
});

const cleanPhone = computed(() => String(contact.value.phone || '0834871695').replace(/[^0-9+]/g, ''));
</script>

<template>
    <div class="fixed bottom-5 right-4 sm:right-6 z-40 flex flex-col items-end">
        <!-- กล่องเมนูติดต่อเมื่อกดเปิด -->
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-4 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-4 scale-95"
        >
            <div
                v-if="isOpen"
                class="mb-3 w-72 sm:w-80 bg-white rounded-2xl shadow-2xl border-2 border-emerald-500 overflow-hidden"
            >
                <div class="bg-gradient-to-r from-[#06C755] to-emerald-600 px-4 py-3 text-white flex items-center justify-between">
                    <div>
                        <div class="font-extrabold text-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <span>ติดต่อทีมงานแจกตัง</span>
                        </div>
                        <div class="text-[11px] text-emerald-50">สอบถาม VIP หรือลงโฆษณาโปรโมทสำนัก</div>
                    </div>
                    <button
                        type="button"
                        class="text-white/80 hover:text-white text-lg font-bold leading-none"
                        @click="isOpen = false"
                    >
                        ✕
                    </button>
                </div>

                <div class="p-3.5 space-y-2.5 text-sm">
                    <!-- ปุ่มทัก LINE -->
                    <a
                        :href="contact.line_url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center justify-between p-3 rounded-xl bg-[#06C755]/10 hover:bg-[#06C755]/20 border border-[#06C755]/30 text-gray-900 transition-colors"
                    >
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-full bg-[#06C755] text-white flex items-center justify-center font-extrabold text-base shadow-sm">
                                L
                            </span>
                            <div>
                                <div class="font-bold text-gray-900 text-xs sm:text-sm">ทักแชท LINE แอดมิน</div>
                                <div class="text-xs text-emerald-700 font-semibold">LINE: {{ contact.line_id }}</div>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-emerald-700">แชทเลย →</span>
                    </a>

                    <!-- ปุ่มไปหน้าเรทโฆษณาโปรโมทสำนัก -->
                    <Link
                        :href="route('advertise.index')"
                        class="flex items-center justify-between p-3 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-200 text-gray-900 transition-colors"
                        @click="isOpen = false"
                    >
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-full bg-yellow-400 text-gray-900 flex items-center justify-center shadow-sm">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                </svg>
                            </span>
                            <div>
                                <div class="font-bold text-gray-900 text-xs sm:text-sm">โปรโมทสำนักหวย / โฆษณา</div>
                                <div class="text-[11px] text-amber-800">ปักหมุดกรอบทองขึ้นหน้าแรก</div>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-amber-800">ดูเรท →</span>
                    </Link>

                    <!-- ปุ่มสมัคร/แจ้งโอน VIP -->
                    <Link
                        :href="route('vip-request.create')"
                        class="flex items-center justify-between p-3 rounded-xl bg-red-50 hover:bg-red-100 border border-red-200 text-gray-900 transition-colors"
                        @click="isOpen = false"
                    >
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-full bg-red-600 text-white flex items-center justify-center shadow-sm">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z" />
                                </svg>
                            </span>
                            <div>
                                <div class="font-bold text-gray-900 text-xs sm:text-sm">สมัคร VIP / แจ้งโอนสลิป</div>
                                <div class="text-[11px] text-red-700">สแกน QR พร้อมเพย์เปิดดูเลขล็อก</div>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-red-700">คลิก →</span>
                    </Link>

                    <!-- ปุ่มโทรด่วน -->
                    <a
                        :href="`tel:${cleanPhone}`"
                        class="flex items-center justify-between px-3 py-2 rounded-xl bg-gray-50 hover:bg-gray-100 border border-gray-200 text-xs text-gray-700 transition-colors"
                    >
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span>โทรติดต่อด่วน: <strong>{{ contact.phone }}</strong></span>
                        </span>
                        <span class="font-semibold text-gray-900">โทรออก</span>
                    </a>
                </div>
            </div>
        </transition>

        <!-- ปุ่มลอยหลักมุมขวาล่าง -->
        <button
            type="button"
            @click="isOpen = !isOpen"
            class="group flex items-center gap-2.5 px-4 py-3 rounded-full bg-gradient-to-r from-[#06C755] to-emerald-600 text-white font-extrabold text-xs sm:text-sm shadow-xl hover:shadow-2xl hover:scale-105 transition-all border-2 border-white"
        >
            <svg v-if="!isOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <span v-else class="text-base leading-none">✕</span>
            <span>{{ isOpen ? 'ปิดหน้าต่าง' : 'ติดต่อแอดมิน / ลงโฆษณา' }}</span>
        </button>
    </div>
</template>
