<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    platforms: Array,
    defaultPlatformId: [Number, null],
});

const form = useForm({
    platform_id: props.defaultPlatformId ?? (props.platforms[0]?.id ?? null),
    title: '',
    description: '',
    image_url: '',
    affiliate_url: '',
    sort_order: 0,
    is_active: true,
});

const submit = () => {
    form.post(route('backoffice.affiliate-products.store'));
};
</script>

<template>
    <Head title="เพิ่มสินค้าวัตถุมงคล" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                เพิ่มสินค้าวัตถุมงคล
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <label for="platform_id" class="block text-sm font-medium text-gray-700">แพลตฟอร์ม</label>
                            <select
                                id="platform_id"
                                v-model="form.platform_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="">-- เลือกแพลตฟอร์ม --</option>
                                <option v-for="p in platforms" :key="p.id" :value="p.id">
                                    {{ p.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.platform_id" class="mt-2" />
                        </div>

                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">ชื่อสินค้า</label>
                            <TextInput
                                id="title"
                                v-model="form.title"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                placeholder="ชื่อวัตถุมงคล / สินค้า"
                            />
                            <InputError :message="form.errors.title" class="mt-2" />
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">คำอธิบาย (ไม่บังคับ)</label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="คำอธิบายสั้นๆ"
                            />
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>

                        <div>
                            <label for="image_url" class="block text-sm font-medium text-gray-700">URL รูปภาพ</label>
                            <TextInput
                                id="image_url"
                                v-model="form.image_url"
                                type="url"
                                class="mt-1 block w-full"
                                placeholder="https://..."
                            />
                            <InputError :message="form.errors.image_url" class="mt-2" />
                        </div>

                        <div>
                            <label for="affiliate_url" class="block text-sm font-medium text-gray-700">ลิงก์ Affiliate</label>
                            <TextInput
                                id="affiliate_url"
                                v-model="form.affiliate_url"
                                type="url"
                                class="mt-1 block w-full"
                                required
                                placeholder="https://..."
                            />
                            <InputError :message="form.errors.affiliate_url" class="mt-2" />
                        </div>

                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700">ลำดับแสดง</label>
                            <TextInput
                                id="sort_order"
                                v-model.number="form.sort_order"
                                type="number"
                                min="0"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.sort_order" class="mt-2" />
                        </div>

                        <div class="flex items-center">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            <label for="is_active" class="ml-2 text-sm text-gray-700">แสดงในหน้าแรก</label>
                        </div>
                        <InputError :message="form.errors.is_active" class="mt-2" />

                        <div class="flex items-center justify-end gap-4">
                            <Link :href="route('backoffice.affiliate-products.index')">
                                <SecondaryButton type="button">ยกเลิก</SecondaryButton>
                            </Link>
                            <PrimaryButton :disabled="form.processing">เพิ่มสินค้า</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
