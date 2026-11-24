<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    lottoDetails: Object,
    filters: Object,
    stats: Object,
});

const page = usePage();

// Filter states
const search = ref(props.filters?.q || '');

const applyFilters = (e) => {
    e?.preventDefault?.();
    router.get(route('backoffice.lotto-details-admin.index'), {
        q: search.value,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    search.value = '';
    router.get(route('backoffice.lotto-details-admin.index'), {}, { preserveState: true, replace: true });
};

const destroyRecord = (id) => {
    if (!confirm('ยืนยันว่าต้องการลบรายละเอียดงวดหวยนี้? ผลรางวัลย่อยและสถิติวิ่งในงวดนี้จะถูกลบไปด้วยหากตั้งค่า CASCADEไว้')) return;
    router.delete(route('backoffice.lotto-details-admin.destroy', id), {
        onSuccess: () => {
            // Flash messages are automatically handled by Inertia
        }
    });
};

const isSyncing = ref(false);
const syncAccuracy = () => {
    isSyncing.value = true;
    router.post(route('backoffice.results.sync'), {}, {
        onFinish: () => {
            isSyncing.value = false;
        },
    });
};
</script>

<template>
    <Head title="จัดการรายละเอียดหวย (lotto_details)" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    จัดการรายละเอียดหวย (lotto_details)
                </h2>
                <div class="flex flex-wrap items-center gap-2">
                    <SecondaryButton type="button" :disabled="isSyncing" @click="syncAccuracy">
                        {{ isSyncing ? 'กำลังซิงค์และคำนวณ...' : '🔄 ซิงค์ผลหวย & คำนวณความแม่นสำนัก' }}
                    </SecondaryButton>
                    <Link :href="route('backoffice.lotto-details-admin.create')">
                        <PrimaryButton>+ เพิ่มรายละเอียดงวดหวย</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Flash Status Alert -->
                <div v-if="$page.props.flash.success" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-2 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $page.props.flash.success }}</span>
                </div>

                <!-- Stats Dashboard -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-6 flex flex-col justify-between">
                        <span class="text-sm font-semibold text-gray-500 uppercase tracking-wider">รายละเอียดทั้งหมด</span>
                        <div class="flex items-baseline mt-4">
                            <span class="text-4xl font-extrabold text-indigo-600">{{ stats?.total ?? 0 }}</span>
                            <span class="ml-2 text-sm text-gray-400">รายการ</span>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-6 flex flex-col justify-between">
                        <span class="text-sm font-semibold text-gray-500 uppercase tracking-wider">งวดหวยหลักทั้งหมด</span>
                        <div class="flex items-baseline mt-4">
                            <span class="text-4xl font-extrabold text-emerald-600">{{ stats?.totalLottoData ?? 0 }}</span>
                            <span class="ml-2 text-sm text-gray-400">งวด</span>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-6 flex flex-col justify-between">
                        <span class="text-sm font-semibold text-gray-500 uppercase tracking-wider">ค้างดึงรายละเอียด</span>
                        <div class="flex items-baseline mt-4">
                            <span class="text-4xl font-extrabold text-amber-500">{{ stats?.missing ?? 0 }}</span>
                            <span class="ml-2 text-sm text-gray-400">งวด</span>
                        </div>
                    </div>
                </div>

                <!-- Filters Control Bar -->
                <div class="bg-white p-6 shadow-sm sm:rounded-lg border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <form @submit="applyFilters" class="flex-1 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="flex-1 max-w-md">
                            <TextInput
                                v-model="search"
                                type="text"
                                class="w-full text-sm"
                                placeholder="ค้นหาด้วย Lotto ID, วันที่ หรือ Endpoint..."
                            />
                        </div>
                        <div class="flex gap-2">
                            <PrimaryButton type="submit" class="text-sm">ค้นหา</PrimaryButton>
                            <SecondaryButton type="button" @click="clearFilters" class="text-sm">ล้างค่า</SecondaryButton>
                        </div>
                    </form>
                </div>

                <!-- Lotto Details Table List -->
                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Lotto ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">วันที่ดึง</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Endpoint</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">วันที่บันทึกรายละเอียด</th>
                                    <th class="px-6 py-3 text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="d in lottoDetails?.data" :key="d.id" class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#{{ d.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                        <Link :href="route('backoffice.lotto-data.index', { q: d.lotto_id })" class="text-indigo-600 hover:text-indigo-900">
                                            {{ d.lotto_id }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ d.date || '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <span class="font-mono text-xs px-2 py-1 bg-gray-100 rounded text-gray-700">
                                            {{ d.endpoint || '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ d.created_at ? new Date(d.created_at).toLocaleString('th-TH') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                        <Link class="text-indigo-600 hover:text-indigo-900" :href="route('backoffice.lotto-details-admin.edit', d.id)">แก้ไข</Link>
                                        <button class="text-red-600 hover:text-red-900 font-semibold" type="button" @click="destroyRecord(d.id)">ลบ</button>
                                    </td>
                                </tr>
                                <tr v-if="!lottoDetails?.data?.length">
                                    <td class="px-6 py-10 text-center text-sm text-gray-500" colspan="6">ไม่พบข้อมูลรายละเอียดงวดหวยในระบบ</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    <div v-if="lottoDetails?.links?.length > 3" class="p-6 bg-gray-50 border-t border-gray-100 flex flex-wrap items-center justify-between gap-4">
                        <div class="text-sm text-gray-500">
                            แสดงรายการ {{ lottoDetails?.from || 0 }} - {{ lottoDetails?.to || 0 }} จากทั้งหมด {{ lottoDetails?.total }} รายการ
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <Link
                                v-for="link in lottoDetails?.links"
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
    </AuthenticatedLayout>
</template>
