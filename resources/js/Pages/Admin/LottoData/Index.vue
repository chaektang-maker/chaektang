<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    lottoData: Object,
    filters: Object,
    stats: Object,
});

const page = usePage();

// Filter states
const search = ref(props.filters?.q || '');
const statusFilter = ref(props.filters?.status || '');

// Crawler input states
const pageInput = ref(1);
const batchLimitInput = ref(10);
const singleLottoIdInput = ref('');

// Loading & alert states
const isScraping = ref(false);
const statusAlert = ref({ message: '', type: '' });
const showCrawlerTools = ref(false);

const showAlert = (message, type = 'info') => {
    statusAlert.value = { message, type };
    if (type !== 'error') {
        setTimeout(() => {
            if (statusAlert.value.message === message) {
                statusAlert.value = { message: '', type: '' };
            }
        }, 6000);
    }
};

const applyFilters = (e) => {
    e?.preventDefault?.();
    router.get(route('backoffice.lotto-data.index'), {
        q: search.value,
        status: statusFilter.value,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    router.get(route('backoffice.lotto-data.index'), {}, { preserveState: true, replace: true });
};

const destroyRecord = (id) => {
    if (!confirm('ยืนยันว่าต้องการลบข้อมูลงวดหวยนี้? การลบข้อมูลนี้จะลบผลรางวัลและเลขวิ่งทั้งหมดที่เกี่ยวข้องไปด้วยแบบถาวร (Cascade Delete)')) return;
    router.delete(route('backoffice.lotto-data.destroy', id), {
        onSuccess: () => {
            showAlert('ลบข้อมูลเรียบร้อยแล้ว', 'success');
        }
    });
};

// --- Scraping Handlers (Disabled) ---
// const runFetchAllData = async () => {
//     if (!confirm('คุณต้องการดึงข้อมูลทั้งหมด 23 หน้าหรือไม่? กระบวนการนี้อาจใช้เวลาสักครู่')) return;
//     isScraping.value = true;
//     showAlert('กำลังเริ่มต้นดึงข้อมูลรายการหวยทั้งหมดจากภายนอก...', 'info');
//     try {
//         const response = await axios.post(route('backoffice.lotto-data.fetch-all'));
//         const result = response.data;
//         if (result.success) {
//             let message = `ดึงข้อมูลสำเร็จ! เพิ่มรายการใหม่: ${result.total_inserted} รายการ, อัปเดต: ${result.total_updated} รายการ`;
//             if (result.total_errors > 0) message += `, ล้มเหลว: ${result.total_errors} รายการ`;
//             showAlert(message, 'success');
//             router.reload({ only: ['lottoData', 'stats'] });
//         } else {
//             showAlert('เกิดข้อผิดพลาด: ' + (result.error || 'เกิดข้อผิดพลาดที่ไม่รู้จัก'), 'error');
//         }
//     } catch (error) {
//         showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + (error.response?.data?.error || error.message), 'error');
//     } finally {
//         isScraping.value = false;
//     }
// };
// 
// const runFetchSinglePage = async () => {
//     const pageNum = parseInt(pageInput.value);
//     if (!pageNum || pageNum < 1 || pageNum > 23) {
//         showAlert('กรุณากรอกหน้าเว็บที่ถูกต้อง (ช่วง 1-23)', 'error');
//         return;
//     }
//     isScraping.value = true;
//     showAlert(`กำลังดึงข้อมูลหวยหน้ารายการที่ ${pageNum}...`, 'info');
//     try {
//         const response = await axios.post(route('backoffice.lotto-data.fetch-single'), { page: pageNum });
//         const result = response.data;
//         if (result.success) {
//             let message = `ดึงข้อมูลหน้า ${pageNum} สำเร็จ! เพิ่มรายการใหม่: ${result.inserted} รายการ, อัปเดต: ${result.updated} รายการ`;
//             if (result.errors && result.errors.length > 0) message += `, ข้อผิดพลาด: ${result.errors.length} รายการ`;
//             showAlert(message, 'success');
//             router.reload({ only: ['lottoData', 'stats'] });
//         } else {
//             showAlert('เกิดข้อผิดพลาด: ' + (result.error || 'เกิดข้อผิดพลาดที่ไม่รู้จัก'), 'error');
//         }
//     } catch (error) {
//         showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + (error.response?.data?.error || error.message), 'error');
//     } finally {
//         isScraping.value = false;
//     }
// };

const runFetchAllLottoDetails = async () => {
    if (!confirm('คุณต้องการดึงรายละเอียดของงวดหวยทั้งหมดที่ยังค้างอยู่ใช่หรือไม่? กระบวนการนี้อาจใช้เวลาค่อนข้างนาน')) return;
    isScraping.value = true;
    showAlert('กำลังดำเนินการดึงข้อมูลรายละเอียดงวดหวยทั้งหมด...', 'info');
    try {
        const response = await axios.post(route('backoffice.lotto-details.fetch-all-pending'));
        const result = response.data;
        if (result.success) {
            let message = `ดึงรายละเอียดสำเร็จ! ประมวลผล: ${result.total_processed} รายการ, สำเร็จ: ${result.total_success} รายการ, ล้มเหลว: ${result.total_failed} รายการ`;
            showAlert(message, 'success');
            router.reload({ only: ['lottoData', 'stats'] });
        } else {
            showAlert('เกิดข้อผิดพลาด: ' + (result.error || 'เกิดข้อผิดพลาดที่ไม่รู้จัก'), 'error');
        }
    } catch (error) {
        showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + (error.response?.data?.error || error.message), 'error');
    } finally {
        isScraping.value = false;
    }
};

const runFetchBatchLottoDetails = async () => {
    const limit = parseInt(batchLimitInput.value);
    if (!limit || limit < 1 || limit > 100) {
        showAlert('กรุณากรอกจำนวนชุดข้อมูลงวดที่ถูกต้อง (1-100)', 'error');
        return;
    }
    isScraping.value = true;
    showAlert(`กำลังดึงรายละเอียดข้อมูลหวยแบบกลุ่ม (Batch) จำนวน ${limit} รายการ...`, 'info');
    try {
        const response = await axios.post(route('backoffice.lotto-details.fetch-batch'), { limit });
        const result = response.data;
        if (result.success) {
            let message = `ประมวลผลกลุ่มสำเร็จ! ประมวลผล: ${result.total_processed} รายการ, สำเร็จ: ${result.total_success} รายการ, ล้มเหลว: ${result.total_failed} รายการ`;
            showAlert(message, 'success');
            router.reload({ only: ['lottoData', 'stats'] });
        } else {
            showAlert('เกิดข้อผิดพลาด: ' + (result.error || 'เกิดข้อผิดพลาดที่ไม่รู้จัก'), 'error');
        }
    } catch (error) {
        showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + (error.response?.data?.error || error.message), 'error');
    } finally {
        isScraping.value = false;
    }
};

const runFetchSingleLottoDetail = async (overrideLottoId = null) => {
    const lottoId = (overrideLottoId || singleLottoIdInput.value || '').trim();
    if (!lottoId) {
        showAlert('กรุณากรอก Lotto ID ให้ถูกต้อง', 'error');
        return;
    }
    isScraping.value = true;
    showAlert(`กำลังดึงรายละเอียดข้อมูล Lotto ID: ${lottoId} ...`, 'info');
    try {
        const response = await axios.post(route('backoffice.lotto-details.fetch-single'), { lotto_id: lottoId });
        const result = response.data;
        if (result.success) {
            showAlert(`ดึงข้อมูลงวด ${result.lotto_id} สำเร็จ! (มีรางวัล: ${result.prizes_count} รายการ, เลขวิ่ง: ${result.running_numbers_count} รายการ)`, 'success');
            router.reload({ only: ['lottoData', 'stats'] });
        } else {
            showAlert('เกิดข้อผิดพลาด: ' + (result.error || 'เกิดข้อผิดพลาดที่ไม่รู้จัก'), 'error');
        }
    } catch (error) {
        showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + (error.response?.data?.error || error.message), 'error');
    } finally {
        isScraping.value = false;
    }
};

// ฟอร์มกรอกผลรางวัลด้วยตนเอง (กรณี API กองสลากล่ม 503 หรือทดสอบงวดล่วงหน้า)
const showManualPrizeModal = ref(false);
const isSavingManual = ref(false);
const manualPrizeForm = ref({
    lotto_id: '',
    first_prize: '',
    front_three: '',
    back_three: '',
    back_two: '',
});

const openManualPrizeModal = (lottoId = '') => {
    manualPrizeForm.value = {
        lotto_id: lottoId || singleLottoIdInput.value.trim() || '',
        first_prize: '',
        front_three: '',
        back_three: '',
        back_two: '',
    };
    showManualPrizeModal.value = true;
};

const submitManualPrize = async () => {
    isSavingManual.value = true;
    try {
        const { data } = await axios.post(route('backoffice.lotto-details.save-manual'), manualPrizeForm.value);
        if (data.success) {
            showManualPrizeModal.value = false;
            showAlert(`บันทึกผลรางวัลงวด ${data.lotto_id} และคำนวณความแม่นยำสำเร็จ!`, 'success');
            router.reload({ only: ['lottoData', 'stats'] });
        } else {
            alert(data.error || 'ไม่สามารถบันทึกข้อมูลได้');
        }
    } catch (err) {
        const msg = err.response?.data?.message || err.response?.data?.error || err.message;
        alert('ข้อมูลไม่ถูกต้อง: ' + msg);
    } finally {
        isSavingManual.value = false;
    }
};

const isSyncingAccuracy = ref(false);
const syncAllAccuracy = () => {
    isSyncingAccuracy.value = true;
    router.post(
        route('backoffice.results.sync'),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showAlert('ซิงค์ผลหวยและคำนวณความแม่นยำของทุกสำนักเรียบร้อยแล้ว!', 'success');
            },
            onFinish: () => {
                isSyncingAccuracy.value = false;
            },
        }
    );
};
</script>

<template>
    <Head title="จัดการข้อมูลหวย (lotto_data)" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    จัดการข้อมูลหวย (lotto_data)
                </h2>
                <div class="flex flex-wrap items-center gap-2">
                    <SecondaryButton type="button" :disabled="isSyncingAccuracy" @click="syncAllAccuracy">
                        {{ isSyncingAccuracy ? 'กำลังคำนวณ...' : '🔄 ซิงค์ & คำนวณความแม่นสำนัก' }}
                    </SecondaryButton>
                    <SecondaryButton @click="showCrawlerTools = !showCrawlerTools">
                        {{ showCrawlerTools ? 'ซ่อนเครื่องมือดึงข้อมูล' : '🤖 เครื่องมือดึงข้อมูล API' }}
                    </SecondaryButton>
                    <SecondaryButton type="button" @click="openManualPrizeModal('')">
                        ✍️ กรอกผลรางวัลด่วน (สำรอง API ล่ม)
                    </SecondaryButton>
                    <Link :href="route('backoffice.lotto-data.create')">
                        <PrimaryButton>+ เพิ่มงวดหวยใหม่</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Flash / Script Alerts -->
                <div v-if="page.props.flash?.success" class="rounded-lg bg-green-50 border border-green-200 p-4 text-green-800 shadow-sm flex items-center justify-between">
                    <span>{{ page.props.flash.success }}</span>
                </div>
                <div v-if="statusAlert.message" class="rounded-lg border p-4 shadow-sm flex items-center justify-between transition-all duration-300"
                     :class="{
                         'bg-blue-50 border-blue-200 text-blue-800': statusAlert.type === 'info',
                         'bg-green-50 border-green-200 text-green-800': statusAlert.type === 'success',
                         'bg-red-50 border-red-200 text-red-800': statusAlert.type === 'error'
                     }">
                    <div class="flex items-center gap-2">
                        <span v-if="isScraping && statusAlert.type === 'info'" class="inline-block w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></span>
                        <span>{{ statusAlert.message }}</span>
                    </div>
                    <button @click="statusAlert.message = ''" class="text-sm font-semibold hover:opacity-75">ปิด</button>
                </div>

                <!-- Stats Dashboard -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-6 flex flex-col justify-between">
                        <span class="text-sm font-semibold text-gray-500 uppercase tracking-wider">ข้อมูลงวดทั้งหมด</span>
                        <div class="flex items-baseline mt-4">
                            <span class="text-4xl font-extrabold text-indigo-600">{{ stats?.total ?? 0 }}</span>
                            <span class="ml-2 text-sm text-gray-400">งวด</span>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-6 flex flex-col justify-between">
                        <span class="text-sm font-semibold text-gray-500 uppercase tracking-wider">ดึงข้อมูลรางวัลแล้ว</span>
                        <div class="flex items-baseline mt-4">
                            <span class="text-4xl font-extrabold text-emerald-600">{{ stats?.fetched ?? 0 }}</span>
                            <span class="ml-2 text-sm text-gray-400">งวด</span>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-6 flex flex-col justify-between">
                        <span class="text-sm font-semibold text-gray-500 uppercase tracking-wider">ยังไม่ได้ดึงรายละเอียด</span>
                        <div class="flex items-baseline mt-4">
                            <span class="text-4xl font-extrabold text-amber-500">{{ stats?.pending ?? 0 }}</span>
                            <span class="ml-2 text-sm text-gray-400">งวด</span>
                        </div>
                    </div>
                </div>

                <!-- Crawler Tools Control Panel (Collapsible) -->
                <div v-show="showCrawlerTools" class="bg-white border border-gray-200 shadow-sm sm:rounded-lg p-6 space-y-6">
                    <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-2">🤖 เครื่องมือดึงข้อมูลอัตโนมัติ (Scraper & Fetcher Tools)</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Panel 2: Fetch Details (Batch) -->
                        <div class="bg-gray-50 rounded-xl p-5 border border-gray-100 flex flex-col justify-between space-y-4">
                            <div>
                                <h4 class="font-bold text-gray-700">2. ดึงรายละเอียดรางวัล (กลุ่ม/ทั้งหมด)</h4>
                                <p class="text-xs text-gray-500 mt-1">ดึงรางวัลที่ 1, เลขท้าย 2 ตัว/3 ตัว จากเว็ปต้นทางของงวดที่ค้างอยู่</p>
                            </div>
                            <div class="space-y-2">
                                <PrimaryButton :disabled="isScraping" class="w-full justify-center bg-indigo-600 hover:bg-indigo-700" @click="runFetchAllLottoDetails">
                                    ดึงรายละเอียดงวดที่ค้างทั้งหมด
                                </PrimaryButton>
                                <div class="flex gap-2">
                                    <div class="w-24">
                                        <TextInput v-model="batchLimitInput" type="number" min="1" max="100" class="w-full text-center" placeholder="จำนวนงวด" :disabled="isScraping" />
                                    </div>
                                    <SecondaryButton :disabled="isScraping" class="flex-1 justify-center" @click="runFetchBatchLottoDetails">
                                        ดึงทีละกลุ่ม (Batch)
                                    </SecondaryButton>
                                </div>
                            </div>
                        </div>

                        <!-- Panel 3: Fetch Details (Single) -->
                        <div class="bg-gray-50 rounded-xl p-5 border border-gray-100 flex flex-col justify-between space-y-4">
                            <div>
                                <h4 class="font-bold text-gray-700">3. ดึงรายละเอียดรางวัล (รายงวด)</h4>
                                <p class="text-xs text-gray-500 mt-1">เจาะจงดึงข้อมูลรางวัลและเลขวิ่งสำหรับ Lotto ID เฉพาะเจาะจง</p>
                            </div>
                            <div class="space-y-2">
                                <div class="flex flex-col gap-2">
                                    <TextInput v-model="singleLottoIdInput" type="text" class="w-full text-center" placeholder="Lotto ID (เช่น 16082566)" :disabled="isScraping" />
                                    <PrimaryButton :disabled="isScraping" class="w-full justify-center bg-emerald-600 hover:bg-emerald-700" @click="runFetchSingleLottoDetail">
                                        ดึงรายละเอียดรายงวด
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- List Table & Filters -->
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden border border-gray-200">
                    <!-- Filters Header -->
                    <div class="p-6 border-b border-gray-100 bg-gray-50">
                        <form class="flex flex-col md:flex-row md:items-end gap-4" @submit="applyFilters">
                            <div class="flex-1">
                                <InputLabel value="ค้นหาข้อมูลงวด" />
                                <TextInput v-model="search" placeholder="ค้นหาด้วย Lotto ID หรือ ข้อความวันที่..." class="mt-1 block w-full" />
                            </div>
                            <div class="w-full md:w-64">
                                <InputLabel value="สถานะดึงข้อมูล" />
                                <select v-model="statusFilter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">ทั้งหมด</option>
                                    <option value="fetched">ดึงข้อมูลรางวัลแล้ว</option>
                                    <option value="pending">ยังไม่ได้ดึงรายละเอียด</option>
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <PrimaryButton type="submit">กรองข้อมูล</PrimaryButton>
                                <SecondaryButton type="button" @click="clearFilters">ล้างตัวกรอง</SecondaryButton>
                            </div>
                        </form>
                    </div>

                    <!-- Table Data -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Lotto ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">วันออกรางวัล</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ข้อความวันที่</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">URL แหล่งอ้างอิง</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">รายละเอียดรางวัล</th>
                                    <th class="px-6 py-3 text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="s in lottoData?.data" :key="s.lotto_id" class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ s.lotto_id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ s.draw_date ? new Date(s.draw_date).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' }) : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ s.date_text }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-xs">
                                        <a :href="s.url" target="_blank" class="text-indigo-600 hover:text-indigo-800 hover:underline inline-flex items-center gap-1">
                                            <span>เปิดลิงก์</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="s.is_fetched" class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5 animate-pulse"></span>
                                            ดึงข้อมูลสำเร็จ
                                        </span>
                                        <span v-else class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 bg-amber-400 rounded-full mr-1.5"></span>
                                            ยังไม่ได้ดึงรายละเอียด
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <button
                                            v-if="!s.is_fetched"
                                            type="button"
                                            :disabled="isScraping"
                                            class="px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-bold border border-emerald-200"
                                            @click="runFetchSingleLottoDetail(s.lotto_id)"
                                        >
                                            🔄 ดึง API
                                        </button>
                                        <button
                                            type="button"
                                            class="px-2.5 py-1 rounded-md bg-amber-50 text-amber-800 hover:bg-amber-100 text-xs font-bold border border-amber-200"
                                            @click="openManualPrizeModal(s.lotto_id)"
                                        >
                                            ✍️ กรอกผลรางวัล
                                        </button>
                                        <Link class="text-indigo-600 hover:text-indigo-900" :href="route('backoffice.lotto-data.edit', s.lotto_id)">แก้ไข</Link>
                                        <button class="text-red-600 hover:text-red-900 font-semibold" type="button" @click="destroyRecord(s.lotto_id)">ลบ</button>
                                    </td>
                                </tr>
                                <tr v-if="!lottoData?.data?.length">
                                    <td class="px-6 py-10 text-center text-sm text-gray-500" colspan="6">ไม่พบค้างข้อมูลในระบบ</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    <div v-if="lottoData?.links?.length > 3" class="p-6 bg-gray-50 border-t border-gray-100 flex flex-wrap items-center justify-between gap-4">
                        <div class="text-sm text-gray-500">
                            แสดงรายการ {{ lottoData?.from || 0 }} - {{ lottoData?.to || 0 }} จากทั้งหมด {{ lottoData?.total }} รายการ
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <Link
                                v-for="link in lottoData?.links"
                                :key="link.label"
                                :href="link.url || ''"
                                class="px-3 py-1 rounded border text-sm transition duration-150"
                                :class="link.active 
                                    ? 'bg-gray-900 text-white border-gray-900 font-medium' 
                                    : (link.url ? 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' : 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed')"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal กรอกผลรางวัลด้วยตนเอง -->
        <div
            v-if="showManualPrizeModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="showManualPrizeModal = false"
        >
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-5 border border-gray-200">
                <div class="flex items-start justify-between gap-2 border-b pb-3">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">
                            ✍️ กรอกผลรางวัลด้วยตนเอง (สำรองกรณี API กองสลากล่ม)
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            ระบบจะบันทึกรางวัลหลัก คำนวณรางวัลข้างเคียง และคำนวณความแม่นยำของทุกสำนักให้อัตโนมัติทันที
                        </p>
                    </div>
                    <button
                        type="button"
                        class="text-gray-400 hover:text-gray-600 text-lg font-bold"
                        @click="showManualPrizeModal = false"
                    >
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitManualPrize" class="space-y-4">
                    <div>
                        <InputLabel value="รหัสงวด 8 หลัก (วันเดือนปี พ.ศ. เช่น 16112569) *" />
                        <TextInput
                            v-model="manualPrizeForm.lotto_id"
                            type="text"
                            maxlength="8"
                            placeholder="16112569"
                            class="mt-1 block w-full font-mono font-bold"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="รางวัลที่ 1 (6 หลัก) *" />
                            <TextInput
                                v-model="manualPrizeForm.first_prize"
                                type="text"
                                maxlength="6"
                                placeholder="เช่น 123456"
                                class="mt-1 block w-full text-lg font-extrabold text-red-600 tracking-widest text-center"
                                required
                            />
                        </div>
                        <div>
                            <InputLabel value="เลขท้าย 2 ตัว (2 หลัก) *" />
                            <TextInput
                                v-model="manualPrizeForm.back_two"
                                type="text"
                                maxlength="2"
                                placeholder="เช่น 89"
                                class="mt-1 block w-full text-lg font-extrabold text-blue-600 tracking-widest text-center"
                                required
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="เลขหน้า 3 ตัว (2 รางวัล คั่นด้วยเว้นวรรค)" />
                            <TextInput
                                v-model="manualPrizeForm.front_three"
                                type="text"
                                placeholder="เช่น 111 222"
                                class="mt-1 block w-full text-center font-bold"
                            />
                        </div>
                        <div>
                            <InputLabel value="เลขท้าย 3 ตัว (2 รางวัล คั่นด้วยเว้นวรรค)" />
                            <TextInput
                                v-model="manualPrizeForm.back_three"
                                type="text"
                                placeholder="เช่น 333 444"
                                class="mt-1 block w-full text-center font-bold"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t">
                        <SecondaryButton type="button" @click="showManualPrizeModal = false">
                            ยกเลิก
                        </SecondaryButton>
                        <PrimaryButton :disabled="isSavingManual">
                            {{ isSavingManual ? 'กำลังบันทึก...' : 'บันทึกผลรางวัล & คำนวณความแม่น' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
