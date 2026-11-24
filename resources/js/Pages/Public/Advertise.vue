<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const packages = [
    {
        name: 'แพ็กเกจทดลอง (Starter)',
        badge: '🥉 ทดลองปักหมุด',
        duration: '1 งวด (15 วัน)',
        originalPrice: '1,000',
        price: '490',
        perDraw: 'เฉลี่ย 490 บาท/งวด',
        popular: false,
        features: [
            'ปักหมุดแถวบนสุดในหน้า "รวมเลขเด็ด" ตลอด 1 งวด',
            'แสดงในโซนพิเศษ "สำนักแนะนำ" บนหน้าแรกของเว็บ',
            'ตกแต่งกรอบทอง + ป้ายรับรอง ⭐ สำนักแนะนำ',
            'เปิดโชว์เลข 2 ตัว / 3 ตัว / เลขวิ่ง ให้ผู้ชมเห็นฟรี 100%',
            'ใส่คำโปรย ชื่อเพจ หรือ Line ID ใต้ชื่อสำนักบนการ์ดได้',
        ],
    },
    {
        name: 'แพ็กเกจยอดนิยม (Pro)',
        badge: '🥈 ขายดีที่สุด 🔥',
        duration: '2 งวด (1 เดือน)',
        originalPrice: '1,800',
        price: '890',
        perDraw: 'เฉลี่ยเพียง 445 บาท/งวด',
        popular: true,
        features: [
            'ปักหมุดแถวบนสุดในหน้า "รวมเลขเด็ด" ตลอด 2 งวดติด',
            'แสดงในโซนพิเศษ "สำนักแนะนำ" บนหน้าแรกของเว็บ',
            'ตกแต่งกรอบทอง + ป้ายรับรอง ⭐ สำนักแนะนำ',
            'เปิดโชว์เลข 2 ตัว / 3 ตัว / เลขวิ่ง ให้ผู้ชมเห็นฟรี 100%',
            'ใส่คำโปรย ชื่อเพจ หรือ Line ID ใต้ชื่อสำนักบนการ์ดได้',
            'แถมฟรี: บันทึกสถิติความแม่นยำขึ้นตารางจัดอันดับสำนักดัง',
        ],
    },
    {
        name: 'แพ็กเกจเจ้าสำนัก (VIP Partner)',
        badge: '🥇 คุ้มค่าระยะยาว',
        duration: '6 งวด (3 เดือน)',
        originalPrice: '4,500',
        price: '2,290',
        perDraw: 'เฉลี่ยเพียง 381 บาท/งวด',
        popular: false,
        features: [
            'ปักหมุดแถวบนสุดในหน้า "รวมเลขเด็ด" ยาวต่อเนื่อง 6 งวด',
            'แสดงในโซนพิเศษ "สำนักแนะนำ" บนหน้าแรกของเว็บทุกงวด',
            'ตกแต่งกรอบทอง + ป้ายรับรอง ⭐ สำนักแนะนำ',
            'เปิดโชว์เลข 2 ตัว / 3 ตัว / เลขวิ่ง ให้ผู้ชมเห็นฟรี 100%',
            'ใส่คำโปรย ชื่อเพจ หรือ Line ID ใต้ชื่อสำนักบนการ์ดได้',
            'แถมฟรี: บทความรีวิวสำนักลงหน้าบทความ SEO ของเว็บ 1 บทความ',
        ],
    },
];

const benefits = [
    {
        icon: '🚀',
        title: 'ปักหมุดแถวบนสุด & ขึ้นหน้าแรกเสมอ',
        desc: 'ไม่ว่าผู้ชมจะเรียงลำดับตามความนิยมหรืออัปเดตล่าสุด การ์ดของสำนักที่โปรโมทจะถูกดันขึ้นแถวแรกสุดเสมอ พร้อมแสดงในโซนพิเศษบนหน้าแรกของเว็บ',
    },
    {
        icon: '🔓',
        title: 'เปิดโชว์ตัวเลข 100% ไม่มีล็อก VIP',
        desc: 'ในขณะที่สำนักดังอื่นๆ ถูกล็อกเลข 2 ตัวและ 3 ตัวไว้สำหรับสมาชิก VIP เท่านั้น แต่สำนักสปอนเซอร์จะเปิดโชว์ตัวเลขครบทุกหลักให้ผู้เข้าชมทุกคนเห็นฟรีทันที',
    },
    {
        icon: '💬',
        title: 'ใส่ชื่อเพจ / Line ID ดึงคนเข้ากลุ่มได้ตรงๆ',
        desc: 'สามารถใส่ข้อความแนะนำสำนัก ชื่อเพจ Facebook หรือ Line ID ใต้ชื่อสำนักบนการ์ดได้โดยตรง ช่วยเพิ่มผู้ติดตามและลูกศิษย์เข้าสำนักของคุณทุกงวด',
    },
    {
        icon: '🏆',
        title: 'สร้างความน่าเชื่อถือด้วยป้ายรับรอง & สถิติจริง',
        desc: 'โดดเด่นด้วยกรอบสีทองและป้าย "⭐ สำนักแนะนำ" พร้อมระบบตรวจผลและคำนวณสถิติความแม่นยำอัตโนมัติหลังหวยออก',
    },
];
</script>

<template>
    <Head>
        <title>ลงโฆษณา / โปรโมทสำนักหวย - แจกตัง | พื้นที่ประชาสัมพันธ์สำหรับสำนักเลขเด็ดและร้านค้าสายมู</title>
        <meta
            name="description"
            content="โปรโมทสำนักหวย เพจใบ้หวย และร้านค้าวัตถุมงคลบนเว็บแจกตัง ปักหมุดการ์ดกรอบทองแถวแรกสุด เปิดเลขให้คอหวยเห็นครบ 100% พร้อมใส่ช่องทางติดต่อสำนัก"
        />
    </Head>

    <PublicLayout>
        <div class="pt-20">
            <!-- Hero Section -->
            <section class="bg-gradient-to-b from-red-700 via-red-600 to-amber-600 text-white py-16 md:py-20">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <div class="inline-flex items-center gap-2 rounded-full bg-yellow-400 px-4 py-1.5 text-sm font-bold text-gray-900 shadow-md mb-6">
                        ⭐ พื้นที่ประชาสัมพันธ์สำหรับสำนักหวย & เพจแนวทางเลขเด็ด
                    </div>
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight leading-tight mb-4">
                        โปรโมทสำนักของคุณให้คอหวยเห็นเป็นอันดับแรก
                    </h1>
                    <p class="text-lg sm:text-xl text-red-100 max-w-3xl mx-auto mb-8">
                        ดึงดูดผู้ติดตามใหม่ เพิ่มคนเข้ากลุ่ม LINE / เพจ Facebook และสร้างความน่าเชื่อถือให้สำนักของคุณ
                        ด้วยการปักหมุดกรอบทองบนหน้าแรกและตารางรวมเลขเด็ดของเว็บ <strong class="text-yellow-300">"แจกตัง"</strong>
                    </p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <a
                            href="#pricing"
                            class="px-8 py-4 rounded-xl bg-yellow-400 text-gray-900 font-extrabold text-lg shadow-xl hover:bg-yellow-300 transition-all"
                        >
                            ดูแพ็กเกจและราคาโปรโมชั่น ↓
                        </a>
                        <a
                            href="#contact"
                            class="px-8 py-4 rounded-xl bg-white/15 backdrop-blur-sm border-2 border-white text-white font-bold text-lg hover:bg-white/25 transition-all"
                        >
                            ติดต่อทีมงานเพื่อจองสิทธิ์
                        </a>
                    </div>
                    <p class="text-xs sm:text-sm text-yellow-200 mt-4 font-medium">
                        * รับจำกัดเพียง 3–6 สำนักต่องวดเท่านั้น เพื่อให้ทุกสำนักที่โปรโมทได้รับยอดการมองเห็นสูงสุด
                    </p>
                </div>
            </section>

            <!-- เปรียบเทียบการ์ดธรรมดา vs การ์ดสำนักแนะนำ -->
            <section class="py-16 bg-amber-50/60 border-b border-amber-100">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-12">
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
                            เห็นความแตกต่างชัดเจน ดึงดูดสายตาคอหวยทันที
                        </h2>
                        <p class="text-gray-600">
                            เปรียบเทียบการแสดงผลระหว่างสำนักทั่วไป กับสำนักที่เข้าร่วมโปรโมท (⭐ สำนักแนะนำ)
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center max-w-4xl mx-auto">
                        <!-- การ์ดทั่วไป -->
                        <div class="bg-white rounded-2xl p-6 border-2 border-gray-200 shadow-sm opacity-85">
                            <div class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">
                                การ์ดสำนักทั่วไป (ไม่โปรโมท)
                            </div>
                            <div class="bg-gradient-to-br from-gray-50 to-slate-100 rounded-2xl p-5 border-2 border-gray-300">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="text-xs font-medium text-gray-600">สำนัก</span>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-600 px-2.5 py-0.5 text-xs font-bold text-white">
                                        👑 VIP
                                    </span>
                                </div>
                                <div class="text-xl font-bold text-gray-900 mb-3">สำนักตัวอย่างทั่วไป</div>
                                <div class="text-xs text-gray-500 pb-3 mb-3 border-b border-gray-300">
                                    งวดวันที่ 16 พฤศจิกายน 2569
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="rounded-xl bg-white p-3 text-center border">
                                        <div class="text-xs text-gray-500 mb-1">2 ตัว</div>
                                        <div class="text-sm font-bold text-amber-700 py-1">🔒 VIP</div>
                                    </div>
                                    <div class="rounded-xl bg-white p-3 text-center border">
                                        <div class="text-xs text-gray-500 mb-1">3 ตัว</div>
                                        <div class="text-sm font-bold text-amber-700 py-1">🔒 VIP</div>
                                    </div>
                                    <div class="rounded-xl bg-white p-3 text-center border">
                                        <div class="text-xs text-gray-500 mb-1">วิ่ง</div>
                                        <div class="text-sm font-bold text-red-600">5, 8</div>
                                    </div>
                                </div>
                            </div>
                            <ul class="mt-4 space-y-2 text-sm text-gray-600">
                                <li>• แสดงเรียงตามลำดับปกติ อาจอยู่หน้าถัดไป</li>
                                <li>• ไม่มีพื้นที่แสดงชื่อเพจหรือ Line ID ติดต่อสำนัก</li>
                                <li>• หากติดอันดับสำนักแม่นยำ เลข 2 ตัว / 3 ตัวจะถูกล็อกไว้เฉพาะสมาชิก VIP</li>
                            </ul>
                        </div>

                        <!-- การ์ดสำนักแนะนำ (Sponsored) -->
                        <div class="bg-white rounded-2xl p-6 border-2 border-yellow-400 shadow-xl relative">
                            <div class="absolute -top-3.5 right-6 bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow">
                                แสดงหน้าแรก + แถวบนสุดเสมอ!
                            </div>
                            <div class="text-xs font-bold uppercase tracking-wider text-amber-700 mb-3">
                                ⭐ การ์ดสำนักแนะนำ (เมื่อลงโปรโมท)
                            </div>
                            <div class="bg-gradient-to-br from-amber-50 via-yellow-50 to-orange-50 rounded-2xl p-5 border-4 border-yellow-400 ring-2 ring-yellow-300/60 shadow-md">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="text-xs font-medium text-gray-600">สำนัก</span>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-yellow-400 px-2.5 py-0.5 text-xs font-bold text-gray-900 shadow-sm">
                                        ⭐ สำนักแนะนำ
                                    </span>
                                </div>
                                <div class="text-xl font-bold text-gray-900">อาจารย์ดัง พารวย</div>
                                <p class="mt-1.5 mb-3 text-xs text-gray-800 bg-white/80 rounded-lg px-2.5 py-1.5 border border-yellow-300 font-medium">
                                    💬 แนวทางแม่นๆ เข้ากลุ่มฟรีแอด Line: @ajarnparuay หรือเพจ: อาจารย์ดังพารวย
                                </p>
                                <div class="text-xs text-gray-600 pb-3 mb-3 border-b border-yellow-200">
                                    งวดวันที่ 16 พฤศจิกายน 2569
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="rounded-xl bg-white p-3 text-center border border-yellow-200 shadow-sm">
                                        <div class="text-xs text-gray-500 mb-1">2 ตัว</div>
                                        <div class="text-2xl font-bold text-red-600">89</div>
                                    </div>
                                    <div class="rounded-xl bg-white p-3 text-center border border-yellow-200 shadow-sm">
                                        <div class="text-xs text-gray-500 mb-1">3 ตัว</div>
                                        <div class="text-2xl font-bold text-red-600">589</div>
                                    </div>
                                    <div class="rounded-xl bg-white p-3 text-center border border-yellow-200 shadow-sm">
                                        <div class="text-xs text-gray-500 mb-1">วิ่ง</div>
                                        <div class="text-sm font-bold text-red-600">8, 9</div>
                                    </div>
                                </div>
                            </div>
                            <ul class="mt-4 space-y-2 text-sm text-gray-800 font-medium">
                                <li class="flex items-center gap-2 text-green-700">
                                    <span>✅</span> ขึ้นโชว์ทั้ง "หน้าแรกของเว็บ" และ "แถวบนสุด" ของตารางเลขเด็ด
                                </li>
                                <li class="flex items-center gap-2 text-green-700">
                                    <span>✅</span> เปิดเลข 2 ตัว / 3 ตัว ให้คนเข้าเว็บทุกคนเห็นครบ 100%
                                </li>
                                <li class="flex items-center gap-2 text-green-700">
                                    <span>✅</span> มีกล่องข้อความโปรโมทเพจ / Line ID ใต้ชื่อสำนักชัดเจน
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- จุดเด่น 4 ข้อ -->
            <section class="py-16 bg-white">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-12">
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
                            ทำไมสำนักหวยและเพจดังถึงเลือกโปรโมทกับ "แจกตัง"
                        </h2>
                        <p class="text-gray-600">
                            ออกแบบมาเพื่อช่วยเพิ่มฐานแฟนคลับและความน่าเชื่อถือให้สำนักของคุณโดยเฉพาะ
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div
                            v-for="(b, idx) in benefits"
                            :key="idx"
                            class="bg-gray-50 rounded-2xl p-6 border border-gray-200 flex items-start gap-4 hover:shadow-md transition-shadow"
                        >
                            <div class="text-4xl bg-white p-3 rounded-xl shadow-sm border border-gray-100">
                                {{ b.icon }}
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">{{ b.title }}</h3>
                                <p class="text-sm text-gray-600 leading-relaxed">{{ b.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ตารางเรทราคา (Rate Card) -->
            <section id="pricing" class="py-16 bg-gradient-to-b from-gray-900 to-gray-800 text-white">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-12">
                        <div class="inline-flex items-center gap-1.5 rounded-full bg-yellow-400/20 border border-yellow-400/40 px-4 py-1 text-xs font-bold text-yellow-300 mb-3">
                            🔥 โปรโมชั่นพิเศษช่วงเปิดตัว (Early Bird)
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-extrabold mb-3">
                            แพ็กเกจราคาโปรโมทสำนักหวย
                        </h2>
                        <p class="text-gray-300 max-w-2xl mx-auto">
                            คุ้มค่าที่สุด! จ่ายหลักร้อยแต่ได้พื้นที่แสดงผลระดับพรีเมียมตลอดทั้งงวด (รับจำกัดเพียง 3–6 สำนักต่องวด)
                        </p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
                        <div
                            v-for="(pkg, i) in packages"
                            :key="i"
                            :class="[
                                'rounded-2xl p-6 sm:p-8 flex flex-col justify-between transition-all',
                                pkg.popular
                                    ? 'bg-gradient-to-b from-amber-500/20 to-gray-800 border-4 border-yellow-400 shadow-2xl lg:-translate-y-2'
                                    : 'bg-gray-800/90 border border-gray-700 shadow-lg',
                            ]"
                        >
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-4">
                                    <span
                                        :class="[
                                            'inline-block rounded-full px-3 py-1 text-xs font-bold',
                                            pkg.popular ? 'bg-yellow-400 text-gray-900' : 'bg-gray-700 text-gray-200',
                                        ]"
                                    >
                                        {{ pkg.badge }}
                                    </span>
                                    <span class="text-sm font-semibold text-gray-300">{{ pkg.duration }}</span>
                                </div>

                                <h3 class="text-xl font-bold text-white mb-4">{{ pkg.name }}</h3>

                                <div class="mb-6 pb-6 border-b border-gray-700">
                                    <div class="text-sm text-gray-400 line-through">
                                        ราคาปกติ {{ pkg.originalPrice }} บาท
                                    </div>
                                    <div class="flex items-baseline gap-2 mt-1">
                                        <span class="text-4xl sm:text-5xl font-extrabold text-yellow-400">{{ pkg.price }}</span>
                                        <span class="text-lg text-gray-300">บาท</span>
                                    </div>
                                    <div class="text-xs text-amber-300 mt-1 font-medium">
                                        ({{ pkg.perDraw }})
                                    </div>
                                </div>

                                <ul class="space-y-3 mb-8 text-sm text-gray-200">
                                    <li v-for="(feat, fIdx) in pkg.features" :key="fIdx" class="flex items-start gap-2.5">
                                        <span class="text-yellow-400 font-bold mt-0.5">✓</span>
                                        <span>{{ feat }}</span>
                                    </li>
                                </ul>
                            </div>

                            <a
                                href="#contact"
                                :class="[
                                    'w-full py-3.5 rounded-xl font-bold text-center transition-all block',
                                    pkg.popular
                                        ? 'bg-yellow-400 text-gray-900 hover:bg-yellow-300 shadow-lg'
                                        : 'bg-red-600 text-white hover:bg-red-700',
                                ]"
                            >
                                สนใจจองแพ็กเกจนี้
                            </a>
                        </div>
                    </div>

                    <!-- ส่วนเสริมสำหรับร้านค้าสายมู -->
                    <div class="mt-12 bg-gray-800/80 border border-gray-700 rounded-2xl p-6 sm:p-8 flex flex-col md:flex-row items-center justify-between gap-6">
                        <div>
                            <div class="text-xs font-bold text-yellow-400 uppercase tracking-wider mb-1">
                                🧿 สำหรับร้านค้าวัตถุมงคล & สินค้าสายมู
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">
                                ต้องการนำสินค้ามงคลขึ้นโชว์บนหน้าแรกของเว็บแจกตัง?
                            </h3>
                            <p class="text-sm text-gray-300">
                                เรามีโซนแสดงสินค้าสายมูและของนำโชคบนหน้าแรก พร้อมลิงก์ส่งลูกค้าไปยังร้าน Shopee, Lazada, TikTok Shop หรือเพจของคุณโดยตรง
                            </p>
                        </div>
                        <a
                            href="#contact"
                            class="shrink-0 px-6 py-3 rounded-xl bg-white text-gray-900 font-bold text-sm hover:bg-gray-100 transition-colors"
                        >
                            สอบถามเรทวางสินค้า →
                        </a>
                    </div>
                </div>
            </section>

            <!-- ขั้นตอนการลงโฆษณา & ช่องทางติดต่อ -->
            <section id="contact" class="py-16 bg-white">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="bg-gradient-to-br from-red-50 to-amber-50 rounded-3xl p-8 sm:p-10 border-2 border-red-200 shadow-lg text-center">
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-3">
                            ขั้นตอนการจองพื้นที่โปรโมทง่ายๆ ใน 5 นาที
                        </h2>
                        <p class="text-gray-600 mb-8">
                            ติดต่อทีมงานเพื่อตรวจสอบที่ว่างในงวดปัจจุบันและเปิดระบบปักหมุดกรอบทองได้ทันที
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-left mb-8">
                            <div class="bg-white rounded-xl p-4 border border-red-100 shadow-sm">
                                <div class="text-xs font-bold text-red-600 mb-1">ขั้นตอนที่ 1</div>
                                <div class="font-bold text-gray-900 text-sm mb-1">เลือกแพ็กเกจที่ต้องการ</div>
                                <p class="text-xs text-gray-600">เลือกโปรโมท 1 งวด (490฿), 2 งวด (890฿) หรือ 6 งวด (2,290฿)</p>
                            </div>
                            <div class="bg-white rounded-xl p-4 border border-red-100 shadow-sm">
                                <div class="text-xs font-bold text-red-600 mb-1">ขั้นตอนที่ 2</div>
                                <div class="font-bold text-gray-900 text-sm mb-1">ส่งชื่อสำนัก & คำโปรย</div>
                                <p class="text-xs text-gray-600">แจ้งชื่อสำนัก ช่องทางติดต่อ (เพจ/Line ID) และแนวทางเลขเด็ดประจำงวด</p>
                            </div>
                            <div class="bg-white rounded-xl p-4 border border-red-100 shadow-sm">
                                <div class="text-xs font-bold text-red-600 mb-1">ขั้นตอนที่ 3</div>
                                <div class="font-bold text-gray-900 text-sm mb-1">ขึ้นระบบกรอบทองทันที</div>
                                <p class="text-xs text-gray-600">ทีมงานเปิดสถานะ ⭐ สำนักแนะนำ ขึ้นหน้าแรกและแถวบนสุดให้ทันที</p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                            <a
                                href="tel:0834871695"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-red-600 text-white font-bold text-lg shadow-lg hover:bg-red-700 transition-all"
                            >
                                📞 โทรติดต่อ: 083-487-1695
                            </a>
                            <a
                                href="mailto:chaektang@gmail.com?subject=สนใจโปรโมทสำนักหวยบนเว็บแจกตัง"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-gray-900 text-white font-bold text-lg shadow-lg hover:bg-gray-800 transition-all"
                            >
                                ✉️ อีเมล: chaektang@gmail.com
                            </a>
                        </div>

                        <!-- นโยบายการรับลงโฆษณาอย่างถูกกฎหมาย -->
                        <div class="mt-8 text-left bg-white/90 rounded-2xl p-4 border border-red-200 text-xs text-gray-700 space-y-1 leading-relaxed">
                            <div class="font-bold text-red-700">⚠️ ข้อกำหนดและนโยบายการรับประชาสัมพันธ์ (สำคัญ)</div>
                            <p>
                                • เว็บไซต์แจกตังรับประชาสัมพันธ์เฉพาะ <strong>เพจแนวทางวิเคราะห์สลากกินแบ่งรัฐบาล อาจารย์สายมู และร้านค้าวัตถุมงคล/สินค้าถูกกฎหมาย</strong> เท่านั้น
                            </p>
                            <p>
                                • <strong>งดรับโฆษณาเว็บพนันออนไลน์ เว็บแทงหวยใต้ดิน หรือลิงก์/Line ID ที่ชักชวนเล่นการพนันทุกชนิด</strong> หากตรวจพบภายหลังว่าช่องทางติดต่อที่แจ้งไว้มีการชักชวนเล่นการพนัน ทางทีมงานขอสงวนสิทธิ์ในการถอดป้ายโปรโมททันทีโดยไม่คืนเงิน เพื่อให้เป็นไปตามกฎหมายอย่างเคร่งครัด
                            </p>
                        </div>

                        <div class="mt-6">
                            <Link
                                :href="route('lucky-numbers.index')"
                                class="text-sm font-semibold text-red-600 hover:text-red-800 underline"
                            >
                                ← กลับไปดูหน้าตารางรวมเลขเด็ดทุกสำนัก
                            </Link>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>

