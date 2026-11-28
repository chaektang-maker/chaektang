<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    sources: Object,
    filters: Object,
});

const page = usePage();

// Popup Modal ยืนยันการซ่อน/เลิกซ่อนสำนัก (กันมือลั่น)
const confirmingSource = ref(null);
const isToggling = ref(false);

const openConfirmToggle = (source) => {
    confirmingSource.value = source;
};

const closeConfirmToggle = () => {
    if (isToggling.value) return;
    confirmingSource.value = null;
};

const confirmToggleStatus = () => {
    if (!confirmingSource.value || isToggling.value) return;
    isToggling.value = true;
    router.post(
        route('backoffice.sources.toggle-status', confirmingSource.value.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isToggling.value = false;
                confirmingSource.value = null;
            },
        }
    );
};

const applyFilters = (e) => {
    e?.preventDefault?.();
    router.get(route('backoffice.sources.index'), {
        q: props.filters?.q || '',
        status: props.filters?.status || '',
        has_owner: props.filters?.has_owner || '',
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    router.get(route('backoffice.sources.index'), {}, { preserveState: true, replace: true });
};

const destroySource = (id) => {
    if (!confirm('ยืนยันลบสำนักนี้?')) return;
    router.delete(route('backoffice.sources.destroy', id));
};
</script>

<template>
    <Head title="จัดการสำนักหวย" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    จัดการสำนักหวย
                </h2>
                <Link :href="route('backoffice.sources.create')">
                    <PrimaryButton>+ เพิ่มสำนัก</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
                <div v-if="page.props.flash?.success" class="rounded-lg bg-green-50 border border-green-200 p-4 text-green-800">
                    {{ page.props.flash.success }}
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <form class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end" @submit="applyFilters">
                        <div>
                            <InputLabel value="ค้นหา (ชื่อสำนัก / ชื่อหรือเบอร์เจ้าสำนัก)" />
                            <TextInput v-model="props.filters.q" placeholder="พิมพ์ชื่อสำนัก หรือ เบอร์/อีเมล" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="สถานะการแสดงผล" />
                            <select v-model="props.filters.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">ทั้งหมด</option>
                                <option value="active">แสดงบนหน้าเว็บ (Active)</option>
                                <option value="suspended">ซ่อนไว้ / รอเปิดแสดง (Inactive)</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="บัญชีเจ้าของสำนัก" />
                            <select v-model="props.filters.has_owner" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">ทั้งหมด</option>
                                <option value="yes">มีบัญชีเจ้าสำนักดูแลเอง</option>
                                <option value="no">ยังไม่มีบัญชีเจ้าสำนัก (แอดมินดูแล)</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <PrimaryButton type="submit">กรอง</PrimaryButton>
                            <SecondaryButton type="button" @click="clearFilters">ล้าง</SecondaryButton>
                        </div>
                    </form>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สำนัก</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">บัญชีเจ้าสำนัก</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ยอดการมองเห็น (Views)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">โปรโมท</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="s in sources.data" :key="s.id" :class="s.status !== 'active' && s.owners?.length ? 'bg-amber-50/40' : ''">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-900">{{ s.name }}</span>
                                            <span
                                                v-if="s.status !== 'active' && s.owners?.length"
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300"
                                            >
                                                เจ้าสำนักสมัครใหม่ (รอเปิดแสดง)
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500 line-clamp-2 mt-0.5">{{ s.description || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="s.owners && s.owners.length > 0" class="space-y-1.5">
                                            <div
                                                v-for="owner in s.owners"
                                                :key="owner.id"
                                                class="inline-flex flex-col rounded-xl bg-amber-50 border border-amber-200 px-3 py-1.5 text-xs"
                                            >
                                                <div class="font-extrabold text-amber-950 flex items-center gap-1.5">
                                                    <span>👤 {{ owner.name }}</span>
                                                    <Link
                                                        :href="route('backoffice.users.edit', owner.id)"
                                                        class="text-[11px] text-indigo-600 hover:underline font-semibold"
                                                    >
                                                        (แก้ไขบัญชี)
                                                    </Link>
                                                </div>
                                                <div class="text-amber-800 font-medium">
                                                    ล็อกอิน: {{ owner.email }}
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="text-xs text-gray-400">
                                            แอดมินดูแล (ไม่มีเจ้าสำนักผูก)
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button
                                            type="button"
                                            @click="openConfirmToggle(s)"
                                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold border transition shadow-2xs"
                                            :class="s.status === 'active'
                                                ? 'bg-emerald-50 text-emerald-800 border-emerald-300 hover:bg-emerald-100'
                                                : 'bg-gray-100 text-gray-600 border-gray-300 hover:bg-gray-200'"
                                            :title="s.status === 'active' ? 'คลิกเพื่อซ่อนสำนักนี้จากหน้าเว็บ' : 'คลิกเพื่อเปิดแสดงสำนักนี้บนหน้าเว็บ'"
                                        >
                                            <span
                                                class="w-2.5 h-2.5 rounded-full"
                                                :class="s.status === 'active' ? 'bg-emerald-500' : 'bg-gray-400'"
                                            ></span>
                                            <span>{{ s.status === 'active' ? 'แสดงอยู่ (Active)' : 'ซ่อนอยู่ (Inactive)' }}</span>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700 border border-blue-200">
                                            👁️ {{ (s.view_count || 0).toLocaleString() }} ครั้ง
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span v-if="s.is_promoted" class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800 border border-amber-300">⭐ สำนักแนะนำ</span>
                                        <span v-else class="text-sm text-gray-400">-</span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap space-x-2">
                                        <Link
                                            :href="route('backportal.index', { source_id: s.id })"
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 transition"
                                        >
                                            เปิดพอร์ทัลสำนัก
                                        </Link>
                                        <button
                                            type="button"
                                            @click="openConfirmToggle(s)"
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold border transition"
                                            :class="s.status === 'active'
                                                ? 'bg-amber-50 hover:bg-amber-100 text-amber-800 border-amber-300'
                                                : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border-emerald-300'"
                                        >
                                            {{ s.status === 'active' ? 'ซ่อนสำนัก' : 'เลิกซ่อน (แสดง)' }}
                                        </button>
                                        <Link class="text-indigo-600 hover:text-indigo-800 font-medium" :href="route('backoffice.sources.edit', s.id)">แก้ไข</Link>
                                        <button class="text-red-600 hover:text-red-800 font-medium" type="button" @click="destroySource(s.id)">ลบ</button>
                                    </td>
                                </tr>
                                <tr v-if="!sources.data?.length">
                                    <td class="px-6 py-6 text-center text-sm text-gray-500" colspan="6">ยังไม่มีข้อมูล</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 flex flex-wrap gap-2">
                        <Link
                            v-for="link in sources.links"
                            :key="link.label"
                            :href="link.url || ''"
                            class="px-3 py-1 rounded border text-sm"
                            :class="link.active ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-200'"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Popup Modal ยืนยันการซ่อน / เลิกซ่อนสำนัก (กันมือลั่น) -->
        <div
            v-if="confirmingSource"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
            @click.self="closeConfirmToggle"
        >
            <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden p-6 space-y-4">
                <div class="flex items-start gap-3.5">
                    <div
                        class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
                        :class="confirmingSource.status === 'active' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'"
                    >
                        <svg v-if="confirmingSource.status === 'active'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-extrabold text-gray-900">
                            {{ confirmingSource.status === 'active' ? 'ยืนยันการซ่อนสำนักนี้?' : 'ยืนยันการเลิกซ่อน (เปิดแสดง) สำนักนี้?' }}
                        </h3>
                        <p class="text-sm font-bold text-indigo-700 mt-0.5">
                            สำนัก: "{{ confirmingSource.name }}"
                        </p>
                    </div>
                </div>

                <div
                    class="p-3.5 rounded-xl text-xs leading-relaxed border"
                    :class="confirmingSource.status === 'active' ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-emerald-50 border-emerald-200 text-emerald-900'"
                >
                    <template v-if="confirmingSource.status === 'active'">
                        เมื่อกดซ่อน สำนักนี้จะไม่ปรากฏบนหน้าแรก หน้าเลขเด็ด หน้าตารางความแม่น และหน้าโหวตชั่วคราว (ข้อมูลเลขและสถิติเดิมยังอยู่ครบ สามารถกดเลิกซ่อนได้ตลอดเวลา)
                    </template>
                    <template v-else>
                        เมื่อกดเลิกซ่อน สำนักนี้จะกลับมาแสดงบนหน้าเว็บตารางรวมเลขเด็ดและตารางสถิติตามปกติทันที
                    </template>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-gray-100">
                    <SecondaryButton type="button" :disabled="isToggling" @click="closeConfirmToggle">
                        ยกเลิก
                    </SecondaryButton>
                    <button
                        type="button"
                        :disabled="isToggling"
                        @click="confirmToggleStatus"
                        class="px-5 py-2 rounded-lg font-extrabold text-xs sm:text-sm text-white shadow transition disabled:opacity-50"
                        :class="confirmingSource.status === 'active' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700'"
                    >
                        {{ isToggling ? 'กำลังบันทึก...' : (confirmingSource.status === 'active' ? 'ยืนยันซ่อนสำนัก' : 'ยืนยันเปิดแสดงสำนัก') }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

