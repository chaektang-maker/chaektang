<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center">
                            <span class="text-white text-lg font-bold">L</span>
                        </div>
                        <div class="text-2xl font-bold text-red-400">
                            Lotto
                        </div>
                    </div>
                    <p class="text-sm text-gray-400">
                        ระบบลอตเตอรี่ออนไลน์ที่ทันสมัยและปลอดภัย
                    </p>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-4">เกี่ยวกับเรา</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">เกี่ยวกับเรา</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">ทีมงาน</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">ข่าวสาร</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-4">บริการ</h3>
                    <ul class="space-y-2 text-sm">
                        <li><Link :href="route('lucky-numbers.index')" class="hover:text-white transition-colors">รวมเลขเด็ด</Link></li>
                        <li><Link :href="route('statistics.index')" class="hover:text-white transition-colors">สถิติหวย</Link></li>
                        <li><Link :href="route('accuracy.index')" class="hover:text-white transition-colors">ตารางคะแนน</Link></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-4">ติดต่อเรา</h3>
                    <ul class="space-y-2 text-sm">
                        <li>อีเมล: chaektang@gmail.com</li>
                        <li>โทรศัพท์: 083-487-1695</li>
                        <!-- <li>Line: @lotto</li> -->
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm text-gray-400">
                <p>&copy; 2024 Lotto. สงวนลิขสิทธิ์</p>
            </div>
        </div>
    </footer>
</template>
