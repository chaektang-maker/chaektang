<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <img
                            :src="`${route('home').replace(/\/$/, '')}/logo-29012026.jpg`" 
                            alt="แจกตัง" 
                            class="h-20 md:h-24 w-auto object-contain"
                        />
                    </div>
                    <p class="text-sm text-gray-400">
                        รวมเลขเด็ดจากทุกสำนัก หวยย้อนหลัง ตารางคะแนนความแม่นยำ
                    </p>
                </div>
                <!-- <div>
                    <h3 class="text-white font-semibold mb-4">เกี่ยวกับเรา</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">เกี่ยวกับเรา</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">ทีมงาน</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">ข่าวสาร</a></li>
                    </ul>
                </div> -->
                <div>
                    <h3 class="text-white font-semibold mb-4">บริการ</h3>
                    <ul class="space-y-2 text-sm">
                        <li><Link :href="route('lucky-numbers.index')" class="hover:text-white transition-colors">รวมเลขเด็ด</Link></li>
                        <li><Link :href="route('statistics.index')" class="hover:text-white transition-colors">สถิติหวย</Link></li>
                        <li><Link :href="route('advertise.index')" class="text-yellow-400 hover:text-yellow-300 font-medium transition-colors">⭐ ลงโฆษณา / โปรโมทสำนัก</Link></li>
                        <!-- <li><Link :href="route('accuracy.index')" class="hover:text-white transition-colors">ตารางคะแนน</Link></li> -->
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
            <div class="border-t border-gray-800 mt-8 pt-8 text-xs text-gray-400 space-y-3">
                <div class="bg-gray-800/70 rounded-xl p-4 border border-gray-700/80 text-gray-300 leading-relaxed">
                    <strong class="text-yellow-400">ข้อตกลงและคำเตือน (Disclaimer):</strong>
                    เว็บไซต์ chaektang.com จัดทำขึ้นเพื่อให้บริการตรวจผลสลากกินแบ่งรัฐบาล ประมวลผลสถิติย้อนหลัง และรวบรวมแนวทางตัวเลขเชิงสถิติเท่านั้น
                    <strong>ทางเว็บไซต์ไม่มีระบบรับแทงหวย และไม่สนับสนุนการพนันหรือสิ่งผิดกฎหมายทุกชนิด</strong>
                    ข้อมูลตัวเลขและการวิเคราะห์ทั้งหมดเป็นเพียงแนวทางทางสถิติและความเชื่อส่วนบุคคล โปรดใช้วิจารณญาณในการรับชม
                </div>
                <p class="text-center text-sm">&copy; 2026 chaektang.com สงวนลิขสิทธิ์</p>
            </div>
        </div>
    </footer>
</template>
