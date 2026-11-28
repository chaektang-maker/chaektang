<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const page = usePage();

const contact = computed(() => page.props.contactInfo || {
    line_id: '@chaektang',
    line_url: 'https://line.me/ti/p/~@chaektang',
    phone: '083-487-1695',
    email: 'chaektang@gmail.com',
});

const cleanPhone = computed(() => String(contact.value.phone || '0834871695').replace(/[^0-9+]/g, ''));

const sourcePackages = [
    {
        name: 'แพ็กเกจทดลอง (Starter)',
        badge: 'ทดลองปักหมุด',
        duration: '1 งวด (15 วัน)',
        originalPrice: '1,000',
        price: '490',
        perDraw: 'เฉลี่ย 490 บาท/งวด',
        popular: false,
        features: [
            'ปักหมุดแถวบนสุดในหน้า "รวมเลขเด็ด" ตลอด 1 งวด',
            'แสดงในโซนพิเศษ "สำนักแนะนำ" บนหน้าแรกของเว็บ',
            'ตกแต่งกรอบทอง + ป้ายรับรอง สำนักแนะนำ',
            'เปิดโชว์เลข 2 ตัว / 3 ตัว / เลขวิ่ง ให้ผู้ชมเห็นฟรี 100%',
            'ใส่คำโปรย ชื่อเพจ หรือ Line ID ใต้ชื่อสำนักบนการ์ดได้',
        ],
    },
    {
        name: 'แพ็กเกจยอดนิยม (Pro)',
        badge: 'ขายดีที่สุด',
        duration: '2 งวด (1 เดือน)',
        originalPrice: '1,800',
        price: '890',
        perDraw: 'เฉลี่ยเพียง 445 บาท/งวด',
        popular: true,
        features: [
            'ปักหมุดแถวบนสุดในหน้า "รวมเลขเด็ด" ตลอด 2 งวดติด',
            'แสดงในโซนพิเศษ "สำนักแนะนำ" บนหน้าแรกของเว็บ',
            'ตกแต่งกรอบทอง + ป้ายรับรอง สำนักแนะนำ',
            'เปิดโชว์เลข 2 ตัว / 3 ตัว / เลขวิ่ง ให้ผู้ชมเห็นฟรี 100%',
            'ใส่คำโปรย ชื่อเพจ หรือ Line ID ใต้ชื่อสำนักบนการ์ดได้',
            'แถมฟรี: บันทึกสถิติความแม่นยำขึ้นตารางจัดอันดับสำนักดัง',
        ],
    },
    {
        name: 'แพ็กเกจเจ้าสำนัก (VIP Partner)',
        badge: 'คุ้มค่าระยะยาว',
        duration: '6 งวด (3 เดือน)',
        originalPrice: '4,500',
        price: '2,290',
        perDraw: 'เฉลี่ยเพียง 381 บาท/งวด',
        popular: false,
        features: [
            'ปักหมุดแถวบนสุดในหน้า "รวมเลขเด็ด" ยาวต่อเนื่อง 6 งวด',
            'แสดงในโซนพิเศษ "สำนักแนะนำ" บนหน้าแรกของเว็บทุกงวด',
            'ตกแต่งกรอบทอง + ป้ายรับรอง สำนักแนะนำ',
            'เปิดโชว์เลข 2 ตัว / 3 ตัว / เลขวิ่ง ให้ผู้ชมเห็นฟรี 100%',
            'ใส่คำโปรย ชื่อเพจ หรือ Line ID ใต้ชื่อสำนักบนการ์ดได้',
            'แถมฟรี: บทความรีวิวสำนักลงหน้าบทความ SEO ของเว็บ 1 บทความ',
        ],
    },
];

const specialPlacements = [
    {
        category: 'สำหรับร้านขายซิมเบอร์มงคล & อาจารย์จัดเบอร์',
        title: 'สปอนเซอร์หน้า "วิเคราะห์เบอร์มงคล"',
        price: '990',
        unit: 'บาท / เดือน (หรือ 2,490 บาท / 3 เดือน)',
        highlight: 'เจาะกลุ่มคนที่กำลังเช็กเบอร์และต้องการเปลี่ยนเบอร์ใหม่โดยตรง!',
        features: [
            'แสดงแบนเนอร์/การ์ดแนะนำร้านซิมของคุณในหน้า "วิเคราะห์เบอร์มงคล (/phone-analyzer)"',
            'ดึงดูดลูกค้าที่เช็กเบอร์ตัวเองแล้วได้เกรดต่ำ หรือมีคู่เลขเสียและกำลังมองหาซิมมงคลเกรด A+',
            'ใส่ลิงก์เชื่อมตรงไปยัง LINE Official, เพจ Facebook หรือเว็บไซต์ร้านซิมของคุณ',
            'รับจำกัดเพียง 1–2 ร้านต่อเดือนเพื่อไม่ให้แย่งลูกค้ากันเอง',
        ],
    },
    {
        category: 'สำหรับร้านวัตถุมงคล & เครื่องรางสายมู',
        title: 'พื้นที่แนะนำสินค้าสายมู & ของแก้เคล็ด (3 หน้าหลัก)',
        price: '590',
        unit: 'บาท / เดือน (ลงสินค้าได้สูงสุด 4 รายการ)',
        highlight: 'แสดงสินค้าของคุณในจังหวะที่ลูกค้ากำลังต้องการเสริมดวงแก้เคล็ด!',
        features: [
            'แสดงการ์ดรูปสินค้า + ชื่อสินค้า + ปุ่มคลิกสั่งซื้อใน 3 หน้าหลัก: หน้าแรก, หน้าวิเคราะห์เบอร์มงคล (โซนของแก้เคล็ด), และหน้าทำนายฝัน',
            'ส่งลูกค้าคลิกตรงไปยังร้าน Shopee, Lazada, TikTok Shop, เพจ หรือ LINE ของร้านคุณโดยตรง',
            'เหมาะสำหรับ แผ่นทองมงคล, กำไลหินนำโชค, จี้ท้าวเวสสุวรรณ, พญานาค, ธูปมงคล ฯลฯ',
            'เปลี่ยนรูปหรืออัปเดตรายการสินค้าได้เดือนละ 2 ครั้ง',
        ],
    },
];
</script>

<template>
    <Head>
        <title>ลงโฆษณา / โปรโมทสำนักหวย ร้านเบอร์มงคล และสินค้าสายมู - แจกตัง</title>
        <meta
            name="description"
            content="โปรโมทสำนักหวย เพจใบ้หวย ร้านขายซิมเบอร์มงคล และร้านค้าวัตถุมงคลบนเว็บแจกตัง ปักหมุดการ์ดกรอบทองแถวแรกสุด เปิดเลขให้คอหวยเห็นครบ 100% พร้อมใส่ช่องทางติดต่อ"
        />
    </Head>

    <PublicLayout>
        <div class="pt-4">
            <!-- Hero Section -->
            <section class="bg-gradient-to-b from-red-700 via-red-600 to-amber-600 text-white py-14 md:py-20">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <div class="inline-flex items-center gap-2 rounded-full bg-yellow-400 px-4 py-1.5 text-xs sm:text-sm font-extrabold text-gray-900 shadow-md mb-6">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                        </svg>
                        <span>พื้นที่ประชาสัมพันธ์สำหรับสำนักหวย • ร้านซิมเบอร์มงคล • ร้านวัตถุมงคลสายมู</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight leading-tight mb-4">
                        โปรโมทสำนักและร้านค้าสายมูของคุณ<br class="hidden sm:inline" />ให้กลุ่มเป้าหมายเห็นเป็นอันดับแรก
                    </h1>
                    <p class="text-base sm:text-xl text-red-100 max-w-3xl mx-auto mb-8 leading-relaxed">
                        เข้าถึงกลุ่มคอหวยและผู้ที่ชื่นชอบศาสตร์ตัวเลข/ดูดวงโดยตรง ช่วยดึงคนเข้ากลุ่ม LINE, เพิ่มยอดผู้ติดตามเพจ
                        และสร้างยอดขายด้วยพื้นที่ปักหมุดระดับพรีเมียมบนเว็บ <strong class="text-yellow-300">"แจกตัง"</strong>
                    </p>
                    <div class="flex flex-wrap justify-center gap-3.5">
                        <a
                            :href="contact.line_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2.5 px-7 py-4 rounded-xl bg-[#06C755] hover:bg-[#05b34c] text-white font-extrabold text-base sm:text-lg shadow-xl transition-all border-2 border-white/80"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <span>ทักแชท LINE จองพื้นที่ ({{ contact.line_id }})</span>
                        </a>
                        <a
                            href="#pricing"
                            class="px-7 py-4 rounded-xl bg-yellow-400 text-gray-900 font-extrabold text-base sm:text-lg shadow-xl hover:bg-yellow-300 transition-all"
                        >
                            ดูแพ็กเกจและราคาโปรโมชั่น ↓
                        </a>
                        <Link
                            :href="route('backportal.register')"
                            class="px-6 py-4 rounded-xl bg-white/15 hover:bg-white/25 text-white font-extrabold text-base sm:text-lg shadow-lg border border-white/40 transition-all"
                        >
                            สมัครเปิดบัญชีเจ้าสำนัก (ลงเลขเอง) →
                        </Link>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center justify-center gap-4 text-xs sm:text-sm text-yellow-200 font-medium">
                        <span>* รับจำกัดเพียง 3–6 สำนักต่องวดเท่านั้น เพื่อให้ทุกสปอนเซอร์ได้รับยอดการมองเห็นสูงสุด</span>
                        <span>•</span>
                        <Link :href="route('backportal.login')" class="underline hover:text-white font-bold">
                            มีบัญชีเจ้าสำนักแล้ว? เข้าสู่ระบบพอร์ทัลเจ้าสำนักที่นี่
                        </Link>
                    </div>
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
                            เปรียบเทียบการแสดงผลระหว่างสำนักทั่วไป กับสำนักที่เข้าร่วมโปรโมท (สำนักแนะนำกรอบทอง)
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
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z" />
                                        </svg>
                                        <span>VIP</span>
                                    </span>
                                </div>
                                <div class="text-xl font-bold text-gray-900 mb-3">สำนักตัวอย่างทั่วไป</div>
                                <div class="text-xs text-gray-500 pb-3 mb-3 border-b border-gray-300">
                                    งวดวันที่ 16 พฤศจิกายน 2569
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="rounded-xl bg-white p-3 text-center border">
                                        <div class="text-xs text-gray-500 mb-1">2 ตัว</div>
                                        <div class="inline-flex items-center justify-center gap-1 text-xs font-bold text-amber-700 py-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            <span>VIP</span>
                                        </div>
                                    </div>
                                    <div class="rounded-xl bg-white p-3 text-center border">
                                        <div class="text-xs text-gray-500 mb-1">3 ตัว</div>
                                        <div class="inline-flex items-center justify-center gap-1 text-xs font-bold text-amber-700 py-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            <span>VIP</span>
                                        </div>
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
                            <div class="text-xs font-bold uppercase tracking-wider text-amber-700 mb-3 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-amber-500" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                </svg>
                                <span>การ์ดสำนักแนะนำ (เมื่อลงโปรโมท)</span>
                            </div>
                            <div class="bg-gradient-to-br from-amber-50 via-yellow-50 to-orange-50 rounded-2xl p-5 border-4 border-yellow-400 ring-2 ring-yellow-300/60 shadow-md">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="text-xs font-medium text-gray-600">สำนัก</span>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-yellow-400 px-2.5 py-0.5 text-xs font-bold text-gray-900 shadow-sm">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                        </svg>
                                        <span>สำนักแนะนำ</span>
                                    </span>
                                </div>
                                <div class="text-xl font-bold text-gray-900">อาจารย์ดัง พารวย</div>
                                <p class="mt-1.5 mb-3 text-xs text-gray-800 bg-white/80 rounded-lg px-2.5 py-1.5 border border-yellow-300 font-medium">
                                    แนวทางแม่นๆ เข้ากลุ่มฟรีแอด Line: @ajarnparuay หรือเพจ: อาจารย์ดังพารวย
                                </p>
                                <div class="text-xs text-gray-600 pb-3 mb-3 border-b border-yellow-200 flex items-center justify-between">
                                    <span>งวดวันที่ 16 พฤศจิกายน 2569</span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white text-gray-700 font-semibold border border-yellow-200">
                                        <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>4,820 ครั้ง</span>
                                    </span>
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
                                <li class="flex items-center gap-2 text-emerald-700">
                                    <span class="font-bold">✓</span> ขึ้นโชว์ทั้ง "หน้าแรกของเว็บ" และ "แถวบนสุด" ของตารางเลขเด็ด
                                </li>
                                <li class="flex items-center gap-2 text-emerald-700">
                                    <span class="font-bold">✓</span> เปิดเลข 2 ตัว / 3 ตัว ให้คนเข้าเว็บทุกคนเห็นครบ 100%
                                </li>
                                <li class="flex items-center gap-2 text-emerald-700">
                                    <span class="font-bold">✓</span> มีกล่องข้อความโปรโมทเพจ / Line ID ใต้ชื่อสำนักชัดเจน
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- จุดเด่น 4 ข้อ (SVG Vector Icons) -->
            <section class="py-16 bg-white">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-12">
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
                            ทำไมสำนักหวยและร้านค้าสายมูถึงเลือกโปรโมทกับ "แจกตัง"
                        </h2>
                        <p class="text-gray-600">
                            ออกแบบมาเพื่อช่วยเพิ่มฐานแฟนคลับ ยอดแอด LINE และความน่าเชื่อถือให้แบรนด์ของคุณโดยเฉพาะ
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-200 flex items-start gap-4 hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-red-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">ปักหมุดแถวบนสุด & ขึ้นหน้าแรกเสมอ</h3>
                                <p class="text-sm text-gray-600 leading-relaxed">
                                    ไม่ว่าผู้ชมจะเรียงลำดับตามความนิยมหรืออัปเดตล่าสุด การ์ดของสำนักที่โปรโมทจะถูกดันขึ้นแถวแรกสุดเสมอ พร้อมแสดงในโซนพิเศษบนหน้าแรกของเว็บ
                                </p>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-200 flex items-start gap-4 hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-amber-500 text-gray-950 flex items-center justify-center shrink-0 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">เปิดโชว์ตัวเลข 100% ไม่มีล็อก VIP</h3>
                                <p class="text-sm text-gray-600 leading-relaxed">
                                    ในขณะที่สำนักดังอื่นๆ ถูกล็อกเลข 2 ตัวและ 3 ตัวไว้สำหรับสมาชิก VIP เท่านั้น แต่สำนักสปอนเซอร์จะเปิดโชว์ตัวเลขครบทุกหลักให้ผู้เข้าชมทุกคนเห็นฟรีทันที
                                </p>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-200 flex items-start gap-4 hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">ใส่ชื่อเพจ / Line ID ดึงคนเข้ากลุ่มได้ตรงๆ</h3>
                                <p class="text-sm text-gray-600 leading-relaxed">
                                    สามารถใส่ข้อความแนะนำสำนัก ชื่อเพจ Facebook หรือ Line ID ใต้ชื่อสำนักบนการ์ดได้โดยตรง ช่วยเพิ่มผู้ติดตามและลูกศิษย์เข้าสำนักของคุณทุกงวด
                                </p>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-200 flex items-start gap-4 hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">มีตัวนับยอดคนดู (Views) & สถิติความแม่นจริง</h3>
                                <p class="text-sm text-gray-600 leading-relaxed">
                                    บนการ์ดทุกใบมีตัวเลขแสดงจำนวนครั้งที่คนเปิดดูสำนักของคุณแบบโปร่งใส พร้อมระบบตรวจผลและคำนวณสถิติความแม่นยำอัตโนมัติหลังหวยออก
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ตารางเรทราคา (Rate Card) -->
            <section id="pricing" class="py-16 bg-gradient-to-b from-gray-900 to-gray-800 text-white">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-14">
                    <!-- หมวดที่ 1: แพ็กเกจสำนักหวย -->
                    <div>
                        <div class="text-center mb-12">
                            <div class="inline-flex items-center gap-1.5 rounded-full bg-yellow-400/20 border border-yellow-400/40 px-4 py-1 text-xs font-bold text-yellow-300 mb-3">
                                หมวดที่ 1 • สำหรับสำนักหวย & เพจแนวทางเลขเด็ด
                            </div>
                            <h2 class="text-3xl sm:text-4xl font-extrabold mb-3">
                                แพ็กเกจปักหมุดสำนักแนะนำกรอบทอง
                            </h2>
                            <p class="text-gray-300 max-w-2xl mx-auto">
                                คุ้มค่าที่สุด! จ่ายหลักร้อยแต่ได้พื้นที่แสดงผลระดับพรีเมียมตลอดทั้งงวด (รับจำกัดเพียง 3–6 สำนักต่องวด)
                            </p>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
                            <div
                                v-for="(pkg, i) in sourcePackages"
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
                                                'inline-block rounded-full px-3 py-1 text-xs font-extrabold',
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
                                    :href="contact.line_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    :class="[
                                        'w-full py-3.5 rounded-xl font-extrabold text-center transition-all block',
                                        pkg.popular
                                            ? 'bg-yellow-400 text-gray-900 hover:bg-yellow-300 shadow-lg'
                                            : 'bg-[#06C755] text-white hover:bg-[#05b34c]',
                                    ]"
                                >
                                    ทัก LINE จองแพ็กเกจนี้
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- หมวดที่ 2 & 3: สำหรับร้านซิมเบอร์มงคล & ร้านวัตถุมงคลสายมู -->
                    <div class="pt-6 border-t border-gray-700">
                        <div class="text-center mb-10">
                            <div class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/20 border border-emerald-400/40 px-4 py-1 text-xs font-bold text-emerald-300 mb-3">
                                หมวดที่ 2 & 3 • สำหรับร้านขายซิมเบอร์มงคล & ร้านค้าวัตถุมงคลสายมู
                            </div>
                            <h2 class="text-2xl sm:text-3xl font-extrabold text-white">
                                พื้นที่โฆษณาเจาะกลุ่มลูกค้าสายมู & เบอร์มงคลโดยตรง
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div
                                v-for="(sp, sIdx) in specialPlacements"
                                :key="sIdx"
                                class="rounded-2xl bg-gray-800/90 border-2 border-amber-400/60 p-6 sm:p-8 flex flex-col justify-between gap-6 shadow-xl"
                            >
                                <div class="space-y-4">
                                    <div class="text-xs font-extrabold text-yellow-400 uppercase tracking-wider">
                                        {{ sp.category }}
                                    </div>
                                    <h3 class="text-xl sm:text-2xl font-extrabold text-white">
                                        {{ sp.title }}
                                    </h3>
                                    <div class="flex items-baseline gap-2 pb-4 border-b border-gray-700">
                                        <span class="text-4xl font-black text-yellow-400">{{ sp.price }}</span>
                                        <span class="text-sm text-gray-300">{{ sp.unit }}</span>
                                    </div>
                                    <div class="p-3 rounded-xl bg-amber-500/15 border border-amber-400/30 text-xs font-bold text-amber-200">
                                        {{ sp.highlight }}
                                    </div>
                                    <ul class="space-y-2.5 text-sm text-gray-200">
                                        <li v-for="(f, fi) in sp.features" :key="fi" class="flex items-start gap-2">
                                            <span class="text-emerald-400 font-bold mt-0.5">✓</span>
                                            <span>{{ f }}</span>
                                        </li>
                                    </ul>
                                </div>

                                <a
                                    :href="contact.line_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="w-full py-3.5 rounded-xl bg-white hover:bg-gray-100 text-gray-900 font-extrabold text-sm text-center shadow transition"
                                >
                                    ทัก LINE สอบถาม / จองพื้นที่นี้ →
                                </a>
                            </div>
                        </div>
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
                            ติดต่อทีมงานทาง LINE หรือโทรศัพท์เพื่อตรวจสอบคิวว่างและเปิดระบบปักหมุดได้ทันที
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-left mb-8">
                            <div class="bg-white rounded-xl p-4 border border-red-100 shadow-sm">
                                <div class="text-xs font-bold text-red-600 mb-1">ขั้นตอนที่ 1</div>
                                <div class="font-bold text-gray-900 text-sm mb-1">เลือกแพ็กเกจที่ต้องการ</div>
                                <p class="text-xs text-gray-600">เลือกโปรโมทสำนักหวย (490฿ / 890฿ / 2,290฿) หรือพื้นที่ร้านซิม/วัตถุมงคล</p>
                            </div>
                            <div class="bg-white rounded-xl p-4 border border-red-100 shadow-sm">
                                <div class="text-xs font-bold text-red-600 mb-1">ขั้นตอนที่ 2</div>
                                <div class="font-bold text-gray-900 text-sm mb-1">ส่งข้อมูลทาง LINE แอดมิน</div>
                                <p class="text-xs text-gray-600">แจ้งชื่อสำนัก/ร้านค้า ช่องทางติดต่อ (เพจ/Line ID) และข้อมูลที่ต้องการแสดง</p>
                            </div>
                            <div class="bg-white rounded-xl p-4 border border-red-100 shadow-sm">
                                <div class="text-xs font-bold text-red-600 mb-1">ขั้นตอนที่ 3</div>
                                <div class="font-bold text-gray-900 text-sm mb-1">ขึ้นระบบกรอบทองทันที</div>
                                <p class="text-xs text-gray-600">ทีมงานเปิดสถานะ สำนักแนะนำ ขึ้นหน้าแรกและแถวบนสุดให้ทันที</p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                            <a
                                :href="contact.line_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-xl bg-[#06C755] hover:bg-[#05b34c] text-white font-extrabold text-base sm:text-lg shadow-lg transition-all"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span>แอด LINE: {{ contact.line_id }}</span>
                            </a>
                            <a
                                :href="`tel:${cleanPhone}`"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-xl bg-red-600 text-white font-extrabold text-base sm:text-lg shadow-lg hover:bg-red-700 transition-all"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span>โทร: {{ contact.phone }}</span>
                            </a>
                        </div>

                        <!-- นโยบายการรับลงโฆษณาอย่างถูกกฎหมาย -->
                        <div class="mt-8 text-left bg-white/90 rounded-2xl p-4 border border-red-200 text-xs text-gray-700 space-y-1 leading-relaxed">
                            <div class="font-bold text-red-700">ข้อกำหนดและนโยบายการรับประชาสัมพันธ์ (สำคัญ)</div>
                            <p>
                                • เว็บไซต์แจกตังรับประชาสัมพันธ์เฉพาะ <strong>เพจแนวทางวิเคราะห์สลากกินแบ่งรัฐบาล อาจารย์สายมู ร้านซิมเบอร์มงคล และร้านค้าวัตถุมงคล/สินค้าถูกกฎหมาย</strong> เท่านั้น
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
