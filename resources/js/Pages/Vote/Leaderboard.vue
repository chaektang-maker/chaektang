<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    voteCounts: Array,
    availableDrawDates: Array,
    selectedDrawDate: String,
});

const selectedDrawDate = ref(props.selectedDrawDate);

const changeDrawDate = () => {
    router.get(route('vote.leaderboard'), {
        draw_date: selectedDrawDate.value,
    }, { preserveState: true, replace: true });
};
</script>

<template>
    <Head>
        <title>คะแนนโหวตรวม - แจกตัง | อันดับสำนักที่ได้รับคะแนนโหวตมากที่สุด</title>
        <meta name="description" content="ดูคะแนนโหวตรวมของทุกสำนัก เปรียบเทียบความนิยมของสำนักหวย" />
    </Head>

    <PublicLayout>
        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                        <span class="text-red-600">คะแนนโหวตรวม</span> — อันดับสำนัก
                    </h1>
                    <p class="text-xl text-gray-600">ดูคะแนนโหวตที่สมาชิกให้กับสำนักต่างๆ</p>
                </div>

                <!-- Filter -->
                <div class="bg-white rounded-2xl shadow-lg border border-red-100 p-6">
                    <div class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="flex-1">
                            <InputLabel value="เลือกงวด" />
                            <select 
                                v-model="selectedDrawDate" 
                                @change="changeDrawDate"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                            >
                                <option v-for="date in availableDrawDates" :key="date.value" :value="date.value">
                                    {{ date.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Leaderboard Table -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-red-50 to-red-100">
                        <h2 class="text-xl font-semibold text-gray-900">อันดับคะแนนโหวต</h2>
                        <p class="text-sm text-gray-600 mt-1">งวดวันที่ {{ selectedDrawDate }}</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">อันดับ</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สำนัก</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">คะแนนโหวต</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr 
                                    v-for="(item, index) in voteCounts" 
                                    :key="item.source_id" 
                                    class="hover:bg-gray-50 transition-colors"
                                    :class="{
                                        'bg-yellow-50': index === 0,
                                        'bg-gray-50': index === 1,
                                        'bg-orange-50': index === 2,
                                    }"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span 
                                                class="text-lg font-bold"
                                                :class="{
                                                    'text-yellow-600': index === 0,
                                                    'text-gray-600': index === 1,
                                                    'text-orange-600': index === 2,
                                                    'text-gray-400': index > 2,
                                                }"
                                            >
                                                {{ index + 1 }}
                                            </span>
                                            <span v-if="index === 0" class="text-2xl">🏆</span>
                                            <span v-else-if="index === 1" class="text-2xl">🥈</span>
                                            <span v-else-if="index === 2" class="text-2xl">🥉</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900">{{ item.source_name }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span 
                                            class="text-lg font-bold"
                                            :class="{
                                                'text-yellow-600': index === 0,
                                                'text-gray-600': index === 1,
                                                'text-orange-600': index === 2,
                                                'text-red-600': index > 2,
                                            }"
                                        >
                                            {{ item.vote_count }}
                                        </span>
                                        <span class="text-sm text-gray-500 ml-1">คะแนน</span>
                                    </td>
                                </tr>
                                <tr v-if="voteCounts.length === 0">
                                    <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">
                                        ยังไม่มีคะแนนโหวตในงวดนี้
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Back Link -->
                <div class="text-center">
                    <Link 
                        :href="route('lucky-numbers.index')" 
                        class="inline-flex items-center text-red-600 hover:text-red-700 font-medium"
                    >
                        ← กลับไปหน้าเลขเด็ด
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
