<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Footer from '@/Components/Footer.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="flex flex-col min-h-screen">
        <div class="flex-grow bg-gray-100">
            <nav
                class="border-b border-gray-100 bg-white"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex min-h-16 flex-wrap items-center justify-between gap-y-2 py-2">
                        <div class="flex flex-wrap items-center gap-x-1 gap-y-0">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="($page.props.auth.user?.is_source_owner && !$page.props.auth.user?.is_admin) ? route('backportal.index') : route('backoffice.dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden sm:flex sm:flex-wrap sm:items-center sm:gap-x-2 sm:gap-y-1 sm:ms-6"
                            >
                                <NavLink
                                    v-if="!$page.props.auth.user?.is_source_owner || $page.props.auth.user?.is_admin"
                                    :href="route('backoffice.dashboard')"
                                    :active="route().current('backoffice.dashboard')"
                                >
                                    Dashboard
                                </NavLink>

                                <NavLink
                                    v-if="$page.props.auth.user?.is_source_owner || $page.props.auth.user?.is_admin"
                                    :href="route('backportal.index')"
                                    :active="route().current('backportal.*') || route().current('backoffice.my-portal.*')"
                                >
                                    พอร์ทัลเจ้าสำนัก
                                </NavLink>

                                <NavLink
                                    v-if="$page.props.auth.user?.can_access?.sources"
                                    :href="route('backoffice.sources.index')"
                                    :active="route().current('backoffice.sources.*')"
                                >
                                    สำนัก
                                </NavLink>

                                <NavLink
                                    v-if="$page.props.auth.user?.can_access?.numbers"
                                    :href="route('backoffice.numbers.index')"
                                    :active="route().current('backoffice.numbers.index') || route().current('backoffice.numbers.create') || route().current('backoffice.numbers.edit')"
                                >
                                    เลขสำนัก
                                </NavLink>

                                <NavLink
                                    v-if="$page.props.auth.user?.can_access?.numbers"
                                    :href="route('backoffice.numbers.bulk')"
                                    :active="route().current('backoffice.numbers.bulk')"
                                >
                                    ⚡ กรอกเลขด่วน
                                </NavLink>

                                <!-- Dropdown: ข้อมูลผลหวย -->
                                <div
                                    v-if="$page.props.auth.user?.can_access?.lottoData || $page.props.auth.user?.can_access?.lottoDetails || $page.props.auth.user?.can_access?.results"
                                    class="relative"
                                >
                                    <Dropdown align="left" width="48">
                                        <template #trigger>
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-2 py-1 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none"
                                                :class="route().current('backoffice.lotto-data.*') || route().current('backoffice.lotto-details-admin.*') || route().current('backoffice.results.*') ? 'text-gray-900 font-bold border-b-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700'"
                                            >
                                                <span>ข้อมูลผลหวย</span>
                                                <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </template>
                                        <template #content>
                                            <DropdownLink
                                                v-if="$page.props.auth.user?.can_access?.lottoData"
                                                :href="route('backoffice.lotto-data.index')"
                                            >
                                                📅 จัดการงวด & ดึงผลหวย
                                            </DropdownLink>
                                            <DropdownLink
                                                v-if="$page.props.auth.user?.can_access?.results"
                                                :href="route('backoffice.results.index')"
                                            >
                                                🎯 ตารางผลหวย & คำนวณความแม่น
                                            </DropdownLink>
                                        </template>
                                    </Dropdown>
                                </div>

                                <!-- Dropdown: คอนเทนต์ & สายมู (Admin) -->
                                <div v-if="$page.props.auth.user?.is_admin" class="relative">
                                    <Dropdown align="left" width="48">
                                        <template #trigger>
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-2 py-1 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none"
                                                :class="route().current('backoffice.platforms.*') || route().current('backoffice.affiliate-products.*') || route().current('backoffice.posts.*') || route().current('backoffice.phone-pairs.*') || route().current('backoffice.dream-dictionary.*') ? 'text-gray-900 font-bold border-b-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700'"
                                            >
                                                <span>คอนเทนต์ & สายมู</span>
                                                <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </template>
                                        <template #content>
                                            <DropdownLink :href="route('backoffice.dream-dictionary.index')">
                                                🌙 ตำราทำนายฝัน (ก-ฮ)
                                            </DropdownLink>
                                            <DropdownLink :href="route('backoffice.phone-pairs.index')">
                                                📱 ตำราเบอร์มงคล (00-99)
                                            </DropdownLink>
                                            <DropdownLink :href="route('backoffice.affiliate-products.index')">
                                                🧿 สินค้าวัตถุมงคล (Affiliate)
                                            </DropdownLink>
                                            <DropdownLink :href="route('backoffice.platforms.index')">
                                                🛒 แพลตฟอร์มร้านค้า
                                            </DropdownLink>
                                            <DropdownLink :href="route('backoffice.posts.index')">
                                                📝 บทความ SEO
                                            </DropdownLink>
                                        </template>
                                    </Dropdown>
                                </div>

                                <!-- Dropdown: สมาชิก & ทีมงาน (Admin) -->
                                <div v-if="$page.props.auth.user?.is_admin" class="relative">
                                    <Dropdown align="left" width="48">
                                        <template #trigger>
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-2 py-1 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none"
                                                :class="route().current('backoffice.customers.*') || route().current('backoffice.users.*') || route().current('backoffice.permissions.*') ? 'text-gray-900 font-bold border-b-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700'"
                                            >
                                                <span>สมาชิก & ทีมงาน</span>
                                                <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </template>
                                        <template #content>
                                            <DropdownLink :href="route('backoffice.customers.index')">
                                                👥 สมาชิกหน้าเว็บ (Customers)
                                            </DropdownLink>
                                            <DropdownLink :href="route('backoffice.users.index')">
                                                🛡️ ทีมงานหลังบ้าน (Staff/Admin)
                                            </DropdownLink>
                                        </template>
                                    </Dropdown>
                                </div>

                                <!-- ปุ่ม VIP แสดงเด่นพร้อมตัวเลขแจ้งเตือน -->
                                <NavLink
                                    v-if="$page.props.auth.user?.is_admin"
                                    :href="route('backoffice.vip-requests.index')"
                                    :active="route().current('backoffice.vip-requests.*')"
                                    class="inline-flex items-center gap-1.5"
                                >
                                    👑 VIP
                                    <span
                                        v-if="$page.props.auth.user?.pending_vip_requests_count > 0"
                                        class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 text-xs font-bold text-white bg-red-600 rounded-full"
                                    >
                                        {{ $page.props.auth.user.pending_vip_requests_count }}
                                    </span>
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink href="/">
                                            🌐 หน้าเว็บไซต์หลัก
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            ข้อมูลส่วนตัว
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            ออกจากระบบ
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            v-if="!$page.props.auth.user?.is_source_owner || $page.props.auth.user?.is_admin"
                            :href="route('backoffice.dashboard')"
                            :active="route().current('backoffice.dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user?.is_source_owner || $page.props.auth.user?.is_admin"
                            :href="route('backportal.index')"
                            :active="route().current('backportal.*') || route().current('backoffice.my-portal.*')"
                        >
                            พอร์ทัลเจ้าสำนัก
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user?.can_access?.sources"
                            :href="route('backoffice.sources.index')"
                            :active="route().current('backoffice.sources.*')"
                        >
                            สำนัก
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user?.can_access?.numbers"
                            :href="route('backoffice.numbers.index')"
                            :active="route().current('backoffice.numbers.index') || route().current('backoffice.numbers.create') || route().current('backoffice.numbers.edit')"
                        >
                            เลขสำนัก
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user?.can_access?.numbers"
                            :href="route('backoffice.numbers.bulk')"
                            :active="route().current('backoffice.numbers.bulk')"
                        >
                            ⚡ กรอกเลขด่วน
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user?.can_access?.lottoData"
                            :href="route('backoffice.lotto-data.index')"
                            :active="route().current('backoffice.lotto-data.*')"
                        >
                            📅 จัดการงวด & ดึงผลหวย
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user?.can_access?.results"
                            :href="route('backoffice.results.index')"
                            :active="route().current('backoffice.results.*')"
                        >
                            🎯 ตารางผลหวย & ความแม่น
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user?.is_admin"
                            :href="route('backoffice.dream-dictionary.index')"
                            :active="route().current('backoffice.dream-dictionary.*')"
                        >
                            🌙 ตำราทำนายฝัน (ก-ฮ)
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user?.is_admin"
                            :href="route('backoffice.phone-pairs.index')"
                            :active="route().current('backoffice.phone-pairs.*')"
                        >
                            📱 ตำราเบอร์มงคล (00-99)
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user?.is_admin"
                            :href="route('backoffice.platforms.index')"
                            :active="route().current('backoffice.platforms.*')"
                        >
                            แพลตฟอร์ม
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user?.is_admin"
                            :href="route('backoffice.affiliate-products.index')"
                            :active="route().current('backoffice.affiliate-products.*')"
                        >
                            วัตถุมงคล
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user?.is_admin"
                            :href="route('backoffice.posts.index')"
                            :active="route().current('backoffice.posts.*')"
                        >
                            บทความ
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user?.is_admin"
                            :href="route('backoffice.customers.index')"
                            :active="route().current('backoffice.customers.*')"
                        >
                            สมาชิก
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user?.is_admin"
                            :href="route('backoffice.users.index')"
                            :active="route().current('backoffice.users.*') || route().current('backoffice.permissions.*')"
                        >
                            ทีมงาน
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user?.is_admin"
                            :href="route('backoffice.vip-requests.index')"
                            :active="route().current('backoffice.vip-requests.*')"
                        >
                            <span class="inline-flex items-center gap-2">
                                <span>VIP</span>
                                <span
                                    v-if="$page.props.auth.user?.pending_vip_requests_count > 0"
                                    class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 text-xs font-bold text-white bg-red-600 rounded-full"
                                >
                                    {{ $page.props.auth.user.pending_vip_requests_count }}
                                </span>
                            </span>
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink href="/">
                                🌐 หน้าเว็บไซต์หลัก
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('profile.edit')">
                                ข้อมูลส่วนตัว
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                ออกจากระบบ
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white shadow"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
        
        <!-- Footer -->
        <Footer />
    </div>
</template>
