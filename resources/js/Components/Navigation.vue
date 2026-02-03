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
        default: false,
    },
    canRegister: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo และชื่อบริษัท -->
                <Link href="/" class="flex items-center gap-2">
                    <div class="text-2xl font-extrabold">
                       <!-- <span class="text-red-600">แจก</span><span class="text-gray-800">ตัง</span>-->
                                        <!-- Logo และชื่อบริษัท -->
                    <img 
                        src="/logo-29012026.jpg" 
                        alt="แจกตัง" 
                        class="h-16 md:h-20 w-auto object-contain"
                    />
                    </div>
                    <div class="text-2xl font-extrabold">
                        <span class="text-red-600">แจก</span><span class="text-gray-800">ตัง</span>
                    </div>
                </Link>
                
                <!-- เมนูกลาง -->
                <div class="hidden lg:flex items-center gap-8">
                    <Link href="/" class="text-gray-700 hover:text-red-600 transition-colors font-medium text-sm">
                        หน้าแรก
                    </Link>
                    <Link :href="route('results.index')" class="text-gray-700 hover:text-red-600 transition-colors font-medium text-sm">
                        ตรวจผลรางวัลย้อนหลัง
                    </Link>
                    <Link :href="route('lucky-numbers.index')" class="text-gray-700 hover:text-red-600 transition-colors font-medium text-sm">
                        เลขเด็ด
                    </Link>
                    <Link :href="route('statistics.index')" class="text-gray-700 hover:text-red-600 transition-colors font-medium text-sm">
                        สถิติหวย
                    </Link>
                    <!-- <Link :href="route('accuracy.index')" class="text-gray-700 hover:text-red-600 transition-colors font-medium text-sm">
                        ตารางคะแนน
                    </Link> -->
                    <!-- <Link :href="route('dream-interpretation.index')" class="text-gray-700 hover:text-red-600 transition-colors font-medium text-sm">
                        ทำนายฝัน
                    </Link> -->
                    <Link :href="route('blog.index')" class="text-gray-700 hover:text-red-600 transition-colors font-medium text-sm">
                        บทความ
                    </Link>
                    <!-- <Link href="#" class="text-gray-700 hover:text-red-600 transition-colors font-medium text-sm">
                        ติดต่อเรา
                    </Link> -->
                </div>
                
                <!-- ปุ่มขวาสุด: PC = แดชบอร์ด/ลูกค้า/เข้าสู่ระบบ | Mobile = แค่ปุ่มเมนู (auth อยู่ใน dropdown) -->
                <div class="flex items-center gap-3">
                    <!-- บล็อก auth แสดงบน PC เท่านั้น (มือถือใช้เมนู dropdown ด้านล่าง) -->
                    <div class="hidden lg:flex items-center gap-3">
                        <template v-if="page.props.auth?.customer">
                            <Link
                                v-if="!page.props.auth.customer.is_vip"
                                :href="route('vip-request.create')"
                                class="px-4 py-2 text-gray-700 hover:text-red-600 transition-colors font-medium text-sm"
                            >
                                ขอ VIP
                            </Link>
                            <span class="px-2 text-gray-700 font-medium text-sm">
                                {{ page.props.auth.customer.name }}
                                <span
                                    v-if="page.props.auth.customer.is_vip"
                                    class="ml-2 inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700"
                                >
                                    VIP
                                </span>
                            </span>
                            <button
                                type="button"
                                @click="logout"
                                class="px-4 py-2 text-gray-700 hover:text-red-600 transition-colors font-medium text-sm"
                            >
                                ออกจากระบบ
                            </button>
                        </template>
                        <template v-else-if="page.props.auth?.user">
                            <span class="px-2 text-gray-700 font-medium text-sm">
                                {{ page.props.auth.user.name }}
                            </span>
                            <button
                                type="button"
                                @click="logout"
                                class="px-4 py-2 text-gray-700 hover:text-red-600 transition-colors font-medium text-sm"
                            >
                                ออกจากระบบ
                            </button>
                        </template>
                        <template v-else>
                            <Link
                                v-if="canLogin"
                                :href="route('login')"
                                class="px-4 py-2 text-gray-700 hover:text-red-600 transition-colors font-medium text-sm"
                            >
                                เข้าสู่ระบบ
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-all font-medium text-sm shadow-md"
                            >
                                สมัครสมาชิก
                            </Link>
                        </template>
                    </div>
                    <!-- ปุ่มเมนูมือถือ -->
                    <button @click="toggleMobileMenu" class="lg:hidden px-4 py-2 text-gray-700 hover:text-red-600 transition-colors font-medium text-sm">
                        {{ mobileMenuOpen ? 'ปิด' : 'เมนู' }}
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div v-show="mobileMenuOpen" class="lg:hidden bg-white border-t border-gray-200">
            <div class="px-4 py-4 space-y-3">
                <Link href="/" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                    หน้าแรก
                </Link>
                <Link :href="route('results.index')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                    ตรวจผลรางวัลย้อนหลัง
                </Link>
                <Link :href="route('lucky-numbers.index')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                    เลขเด็ด
                </Link>
                <Link :href="route('statistics.index')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                    สถิติหวย
                </Link>
                <!-- <Link :href="route('accuracy.index')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                    ตารางคะแนน
                </Link> -->
                <!-- <Link :href="route('dream-interpretation.index')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                    ทำนายฝัน
                </Link> -->
                <Link :href="route('blog.index')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                    บทความ
                </Link>
                <div v-if="page.props.auth?.customer" class="pt-4 border-t border-gray-200 space-y-2">
                    <Link
                        v-if="!page.props.auth.customer.is_vip"
                        :href="route('vip-request.create')"
                        class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2"
                    >
                        ขอ VIP
                    </Link>
                    <div class="text-gray-700 font-medium text-sm py-2">
                        {{ page.props.auth.customer.name }}
                        <span
                            v-if="page.props.auth.customer.is_vip"
                            class="ml-2 inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700"
                        >
                            VIP
                        </span>
                    </div>
                    <button type="button" @click="logout" class="block w-full text-left text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                        ออกจากระบบ
                    </button>
                </div>
                <div v-else-if="page.props.auth?.user" class="pt-4 border-t border-gray-200 space-y-2">
                    <div class="text-gray-700 font-medium text-sm py-2">{{ page.props.auth.user.name }}</div>
                    <button type="button" @click="logout" class="block w-full text-left text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                        ออกจากระบบ
                    </button>
                </div>
                <div v-else class="pt-4 border-t border-gray-200 space-y-2">
                    <Link  :href="route('login')" class="block text-gray-700 hover:text-red-600 transition-colors font-medium text-sm py-2">
                        เข้าสู่ระบบ
                    </Link>
                    <Link  :href="route('register')" class="block px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-all font-medium text-sm text-center">
                        สมัครสมาชิก
                    </Link>
                </div>
            </div>
        </div>
    </nav>
</template>
