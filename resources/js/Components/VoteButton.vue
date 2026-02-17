<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    sourceId: {
        type: Number,
        required: true,
    },
    drawDate: {
        type: String,
        required: true,
    },
    initialVoteCount: {
        type: Number,
        default: 0,
    },
});

const voteCount = ref(props.initialVoteCount);
const isVoting = ref(false);
const errorMessage = ref('');
const isLoggedIn = ref(false);
const remainingVotes = ref(0);
const userVoteCount = ref(0);

const checkVoteStatus = async () => {
    try {
        const { data } = await axios.get(route('vote.check'), {
            params: {
                source_id: props.sourceId,
                draw_date: props.drawDate,
            },
        });
        isLoggedIn.value = data.is_logged_in || false;
        voteCount.value = data.vote_count || 0;
        remainingVotes.value = data.remaining_votes || 0;
        userVoteCount.value = data.user_vote_count || 0;
    } catch (err) {
        console.error('Error checking vote status:', err);
    }
};

const handleVote = async () => {
    if (!isLoggedIn.value || remainingVotes.value <= 0 || isVoting.value) return;

    isVoting.value = true;
    errorMessage.value = '';

    try {
        const csrf = document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] 
            ? decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)[1]) 
            : '';

        const { data } = await axios.post(
            route('vote.store'),
            {
                source_id: props.sourceId,
                draw_date: props.drawDate,
            },
            {
                headers: {
                    'X-XSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
            }
        );

        if (data.success) {
            voteCount.value = data.vote_count;
            remainingVotes.value = data.remaining_votes;
            userVoteCount.value += 1;
            errorMessage.value = '';
        } else {
            errorMessage.value = data.message || 'เกิดข้อผิดพลาด';
        }
    } catch (err) {
        if (err.response?.data?.message) {
            errorMessage.value = err.response.data.message;
        } else {
            errorMessage.value = 'เกิดข้อผิดพลาดในการโหวต';
        }
    } finally {
        isVoting.value = false;
    }
};

const canVote = computed(() => {
    return isLoggedIn.value && remainingVotes.value > 0 && !isVoting.value;
});

onMounted(() => {
    // ตรวจสอบสถานะการโหวตเมื่อ component โหลด
    checkVoteStatus();
});
</script>

<template>
    <div class="flex flex-col items-center gap-2">
        <!-- ปุ่มโหวต -->
        <button
            v-if="isLoggedIn"
            @click="handleVote"
            :disabled="!canVote"
            :class="[
                'px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200 flex items-center gap-2',
                !canVote
                    ? 'bg-gray-200 text-gray-500 cursor-not-allowed'
                    : isVoting
                    ? 'bg-gray-200 text-gray-600 cursor-wait'
                    : 'bg-red-600 text-white hover:bg-red-700 active:scale-95 shadow-md hover:shadow-lg'
            ]"
            :title="!canVote ? (remainingVotes === 0 ? 'โหวตครบ 3 ครั้งแล้ว' : 'ไม่สามารถโหวตได้') : 'โหวตให้คะแนนความแม่น'"
        >
            <svg 
                v-if="isVoting"
                class="w-5 h-5 animate-spin" 
                fill="none" 
                viewBox="0 0 24 24"
            >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg 
                v-else
                class="w-5 h-5" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
            </svg>
            <span>{{ isVoting ? 'กำลังโหวต...' : 'โหวต' }}</span>
        </button>
        
        <!-- ข้อความสำหรับผู้ใช้ที่ไม่ได้ login -->
        <div v-else class="text-xs text-gray-500 text-center px-2">
            <Link :href="route('login')" class="text-red-600 hover:underline">
                ล็อกอินเพื่อโหวต
            </Link>
        </div>
        
        <!-- จำนวนคะแนน -->
        <div class="text-xs text-gray-600 font-medium">
            <span class="text-red-600 font-bold">{{ voteCount }}</span> คะแนน
        </div>

        <!-- จำนวนครั้งที่เหลือ (สำหรับ user ที่ login) -->
        <div v-if="isLoggedIn" class="text-xs text-gray-500">
            <span v-if="remainingVotes > 0" class="text-green-600 font-medium">
                เหลือ {{ remainingVotes }} ครั้ง
            </span>
            <span v-else class="text-gray-400">
                โหวตครบแล้ว
            </span>
        </div>

        <!-- ข้อความ error -->
        <div v-if="errorMessage" class="text-xs text-red-600 text-center max-w-[120px]">
            {{ errorMessage }}
        </div>
    </div>
</template>
