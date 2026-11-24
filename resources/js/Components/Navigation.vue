<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const page = usePage();
const mobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const logout = () => {
    router.post(route('logout'));
};

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo และชื่อบริษัท -->
                <Link :href="route('home')" class="flex items-center gap-2">
                    <div class="text-2xl font-extrabold">
                       <!-- <span class="text-red-600">แจก</span><span class="text-gray-800">ตัง</span>-->
                                        <!-- Logo และชื่อบริษัท -->
                    <img 
                        :src="`${route('home').replace(/\/$/, '')}/logo-29012026.jpg`" 
                        alt="แจกตัง" 
                        class="h-16 md:h-20 w-auto object-contain"
                    />
                    </div>
                    <div class="text-2xl font-extrabold">
                        <span class="text-red-600">แจก</span><span class="text-gray-800">ตัง</span>
                    </div>
                </Link>
                
                <!-- เมนูกลาง (แบ่งเป็น 2 แถว แถวละสูงสุด 6 เมนู เพื่อไม่ให้เบียดหรือทับกัน) -->
                <div class="hidden lg:flex flex-col items-center justify-center gap-1.5 whitespace-nowrap">
                    <!-- แถวที่ 1: 6 เมนูหลัก (หวย & สถิติ) -->
                    <div class="flex items-center justify-center gap-3 xl:gap-5">
                        <Link
                            :href="route('home')"
                            :class="route().current('home') ? 'text-red-600 font-bold bg-red-50' : 'text-gray-700 hover:text-red-600 hover:bg-gray-50 font-medium'"
                            class="px-2.5 py-1 rounded-md transition-colors text-sm"
                        >
                            หน้าแรก
                        </Link>
                        <Link
                            :href="route('results.index')"
                            :class="route().current('results.*') ? 'text-red-600 font-bold bg-red-50' : 'text-gray-700 hover:text-red-600 hover:bg-gray-50 font-medium'"
                            class="px-2.5 py-1 rounded-md transition-colors text-sm"
                        >
                            ตรวจหวยย้อนหลัง
                        </Link>
                        <Link
                            :href="route('lucky-numbers.index')"
                            :class="route().current('lucky-numbers.*') ? 'text-red-600 font-bold bg-red-50' : 'text-gray-700 hover:text-red-600 hover:bg-gray-50 font-medium'"
                            class="px-2.5 py-1 rounded-md transition-colors text-sm"
                        >
                            เลขเด็ด
                        </Link>
                        <Link
                            :href="route('accuracy.index')"
                            :class="route().current('accuracy.*') ? 'text-red-600 font-bold bg-red-50' : 'text-gray-700 hover:text-red-600 hover:bg-gray-50 font-medium'"
                            class="px-2.5 py-1 rounded-md transition-colors text-sm"
                        >
                            ตารางความแม่น
                        </Link>
                        <Link
                            :href="route('vote.leaderboard')"
                            :class="route().current('vote.*') ? 'text-red-600 font-bold bg-red-50' : 'text-gray-700 hover:text-red-600 hover:bg-gray-50 font-medium'"
                            class="px-2.5 py-1 rounded-md transition-colors text-sm"
                        >
                            คะแนนโหวต
                        </Link>
                        <Link
                            :href="route('statistics.index')"
                            :class="route().current('statistics.*') ? 'text-red-600 font-bold bg-red-50' : 'text-gray-700 hover:text-red-600 hover:bg-gray-50 font-medium'"
                            class="px-2.5 py-1 rounded-md transition-colors text-sm"
                        >
                            สถิติหวย
                        </Link>
                    </div>

                    <!-- แถวที่ 2: 6 เมนูวิเคราะห์สายมู & คอนเทนต์ -->
                    <div class="flex items-center justify-center gap-3 xl:gap-5 border-t border-gray-100 pt-1">
                        <Link
                            :href="route('lotto-analyzer.index')"
                            :class="route().current('lotto-analyzer.*') ? 'text-red-600 font-bold bg-red-50' : 'text-gray-700 hover:text-red-600 hover:bg-gray-50 font-medium'"
                            class="px-2.5 py-0.5 rounded-md transition-colors text-sm"
                        >
                            วิเคราะห์เลข
                        </Link>
                        <Link
                            :href="route('phone-analyzer.index')"
                            :class="route().current('phone-analyzer.*') ? 'text-red-600 font-bold bg-red-50' : 'text-gray-700 hover:text-red-600 hover:bg-gray-50 font-medium'"
                            class="px-2.5 py-0.5 rounded-md transition-colors text-sm"
                        >
                            เบอร์มงคล
                        </Link>
                        <Link
                            :href="route('dream-interpretation.index')"
                            :class="route().current('dream-interpretation.*') ? 'text-red-600 font-bold bg-red-50' : 'text-gray-700 hover:text-red-600 hover:bg-gray-50 font-medium'"
                            class="px-2.5 py-0.5 rounded-md transition-colors text-sm"
                        >
                            ทำนายฝัน
                        </Link>
                        <Link
                            :href="route('undrawn-first-prizes.index')"
                            :class="route().current('undrawn-first-prizes.*') ? 'text-red-600 font-bold bg-red-50' : 'text-gray-700 hover:text-red-600 hover:bg-gray-50 font-medium'"
                            class="px-2.5 py-0.5 rounded-md transition-colors text-sm"
                        >
                            เลขยังไม่เคยออก
                        </Link>
                        <Link
                            :href="route('blog.index')"
                            :class="route().current('blog.*') ? 'text-red-600 font-bold bg-red-50' : 'text-gray-700 hover:text-red-600 hover:bg-gray-50 font-medium'"
                            class="px-2.5 py-0.5 rounded-md transition-colors text-sm"
                        >
                            บทความ
                        </Link>
                        <Link
                            :href="route('advertise.index')"
                            :class="route().current('advertise.*') ? 'text-red-600 font-bold bg-red-50' : 'text-gray-700 hover:text-red-600 hover:bg-gray-50 font-medium'"
                            class="px-2.5 py-0.5 rounded-md transition-colors text-sm"
                        >
                            ลงโฆษณา
                        </Link>
                    </div>
                </div>
                
                <!-- ปุ่มขวาสุด: PC = แดชบอร์ด/ลูกค้า/เข้าสู่ระบบ | Mobile = แค่ปุ่มเมนู (auth อยู่ใน dropdown) -->
                <div class="flex items-center gap-2 xl:gap-3 whitespace-nowrap">
                    <!-- บล็อก auth แสดงบน PC เท่านั้น (มือถือใช้เมนู dropdown ด้านล่าง) -->
                    <div class="hidden lg:flex items-center gap-2 xl:gap-3">
                        <template v-if="page.props.auth?.customer">
                            <Link
                                v-if="!page.props.auth.customer.is_vip"
                                :href="route('vip-request.create')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-gradient-to-r from-amber-400 to-yellow-500 text-gray-900 hover:from-amber-500 hover:to-yellow-600 transition-all font-extrabold text-xs xl:text-sm shadow-sm"
                            >
                                <svg class="w-4 h-4 text-gray-900 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z" />
                                </svg>
                                <span>สมัคร VIP</span>
                            </Link>
                            <Link
                                v-else
                                :href="route('vip-request.create')"
                                :class="[
                                    'px-3 py-1.5 rounded-lg font-bold text-xs xl:text-sm inline-flex items-center gap-1.5 transition-all',
                                    page.props.auth.customer.vip_days_left <= 3
                                        ? 'bg-red-100 text-red-700 border border-red-300 hover:bg-red-200 animate-pulse'
                                        : 'bg-amber-100 text-amber-900 border border-amber-300 hover:bg-amber-200'
                                ]"
                                title="คลิกเพื่อดูสถานะหรือต่ออายุ VIP"
                            >
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z" />
                                </svg>
                                <span>VIP ({{ page.props.auth.customer.vip_days_left }} วัน)</span>
                                <span v-if="page.props.auth.customer.vip_days_left <= 3" class="underline ml-0.5">ต่ออายุ</span>
                            </Link>
                            <span class="px-2 text-gray-700 font-medium text-sm max-w-[120px] truncate">
                                {{ page.props.auth.customer.name }}
                            </span>
                            <button
                                type="button"
                                @click="logout"
                                class="px-2.5 py-2 text-gray-600 hover:text-red-600 transition-colors font-medium text-sm"
                            >
                                ออกจากระบบ
                            </button>
                        </template>
                        <template v-else-if="page.props.auth?.user">
                            <Link
                                :href="route('backoffice.dashboard')"
                                class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-800 hover:bg-gray-200 font-semibold text-xs"
                            >
                                หลังบ้าน
                            </Link>
                            <span class="px-2 text-gray-700 font-medium text-sm max-w-[120px] truncate">
                                {{ page.props.auth.user.name }}
                            </span>
                            <button
                                type="button"
                                @click="logout"
                                class="px-2.5 py-2 text-gray-600 hover:text-red-600 transition-colors font-medium text-sm"
                            >
                                ออกจากระบบ
                            </button>
                        </template>
                        <template v-else>
                            <Link
                                :href="route('vip-request.create')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-gradient-to-r from-amber-400 to-yellow-500 text-gray-900 hover:from-amber-500 hover:to-yellow-600 transition-all font-extrabold text-xs xl:text-sm shadow-sm"
                            >
                                <svg class="w-4 h-4 text-gray-900 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z" />
                                </svg>
                                <span>สมัคร VIP</span>
                            </Link>
                            <Link
                                v-if="canLogin"
                                :href="route('login')"
                                class="px-2.5 py-2 text-gray-700 hover:text-red-600 transition-colors font-medium text-sm"
                            >
                                เข้าสู่ระบบ
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-3.5 xl:px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-all font-medium text-sm shadow-md"
                            >
                                สมัครสมาชิก
                            </Link>
                        </template>
                    </div>
                    <!-- ปุ่มเมนูมือถือ -->
                    <button
                        @click="toggleMobileMenu"
                        aria-label="เปิด/ปิดเมนู"
                        class="lg:hidden inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 text-gray-700 hover:text-red-600 hover:bg-red-50 transition-colors font-medium text-sm"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>{{ mobileMenuOpen ? 'ปิด' : 'เมนู' }}</span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div v-show="mobileMenuOpen" class="lg:hidden bg-white border-t border-gray-200 max-h-[calc(100vh-5rem)] overflow-y-auto shadow-lg">
            <div class="px-4 py-4 space-y-2">
                <Link :href="route('home')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                    หน้าแรก
                </Link>
                <Link :href="route('results.index')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                    ตรวจผลรางวัลย้อนหลัง
                </Link>
                <Link :href="route('lucky-numbers.index')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                    เลขเด็ด
                </Link>
                <Link :href="route('accuracy.index')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                    ตารางความแม่น
                </Link>
                <Link :href="route('vote.leaderboard')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                    คะแนนโหวต
                </Link>
                <Link :href="route('statistics.index')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                    สถิติหวย
                </Link>
                <Link :href="route('lotto-analyzer.index')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                    วิเคราะห์เลขนำโชค
                </Link>
                <Link :href="route('phone-analyzer.index')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                    วิเคราะห์เบอร์มงคล
                </Link>
                <Link :href="route('dream-interpretation.index')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                    ทำนายฝัน
                </Link>
                <Link :href="route('undrawn-first-prizes.index')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                    รางวัลที่ 1 ที่ยังไม่เคยออก
                </Link>
                <Link :href="route('blog.index')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                    บทความ
                </Link>
                <Link :href="route('advertise.index')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                    ติดต่อลงโฆษณา / โปรโมทสำนัก
                </Link>
                <div v-if="page.props.auth?.customer" class="pt-4 border-t border-gray-200 space-y-2">
                    <div class="flex items-center justify-between py-2">
                        <span class="text-gray-800 font-bold text-sm">{{ page.props.auth.customer.name }}</span>
                        <Link
                            v-if="page.props.auth.customer.is_vip"
                            :href="route('vip-request.create')"
                            class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-900 border border-amber-300"
                        >
                            VIP (เหลือ {{ page.props.auth.customer.vip_days_left }} วัน)
                        </Link>
                    </div>
                    <Link
                        v-if="!page.props.auth.customer.is_vip"
                        :href="route('vip-request.create')"
                        class="block w-full text-center px-4 py-2.5 rounded-lg bg-gradient-to-r from-amber-400 to-yellow-500 text-gray-900 font-extrabold text-sm shadow-sm"
                    >
                        สมัคร VIP เพื่อเปิดดูเลขล็อก
                    </Link>
                    <Link
                        v-else-if="page.props.auth.customer.vip_days_left <= 3"
                        :href="route('vip-request.create')"
                        class="block w-full text-center px-4 py-2 rounded-lg bg-red-50 border border-red-200 text-red-700 font-bold text-xs"
                    >
                        VIP ใกล้หมดอายุ กดเพื่อต่ออายุล่วงหน้า →
                    </Link>
                    <button type="button" @click="logout" class="block w-full text-left text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                        ออกจากระบบ
                    </button>
                </div>
                <div v-else-if="page.props.auth?.user" class="pt-4 border-t border-gray-200 space-y-2">
                    <div class="text-gray-700 font-medium text-sm py-2">{{ page.props.auth.user.name }}</div>
                    <Link :href="route('backoffice.dashboard')" class="block text-indigo-600 font-semibold text-sm py-2">
                        เข้าสู่ระบบหลังบ้าน →
                    </Link>
                    <button type="button" @click="logout" class="block w-full text-left text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                        ออกจากระบบ
                    </button>
                </div>
                <div v-else class="pt-4 border-t border-gray-200 space-y-2">
                    <Link
                        :href="route('vip-request.create')"
                        class="block px-4 py-2.5 bg-gradient-to-r from-amber-400 to-yellow-500 text-gray-900 rounded-md font-extrabold text-sm text-center shadow-sm"
                    >
                        สมัคร VIP เปิดดูเลขแม่น
                    </Link>
                    <Link :href="route('login')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2 text-center border border-gray-200 rounded-md">
                        เข้าสู่ระบบ
                    </Link>
                    <Link :href="route('register')" class="block px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-all font-medium text-sm text-center">
                        สมัครสมาชิกฟรี
                    </Link>
                </div>
            </div>
        </div>
    </nav>
</template>
