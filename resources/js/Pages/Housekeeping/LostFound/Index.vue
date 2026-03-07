<template>
    <DashboardLayout title="Lost & Found" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-6" :style="{ backgroundColor: themeColors.card }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Lost & Found</h1>
                    <p class="mt-1 text-sm" :style="{ color: themeColors.textSecondary }">Track items found in rooms and common areas.</p>
                </div>
                <button class="inline-flex items-center px-4 py-2 rounded-md text-white text-sm font-medium" :style="{ backgroundColor: themeColors.primary }" @click="showForm = !showForm">
                    <PlusIcon class="h-4 w-4 mr-2" /> Report Item
                </button>
            </div>
        </div>

        <div v-if="showForm" class="shadow rounded-lg p-6 mb-6" :style="{ backgroundColor: themeColors.card }">
            <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Report Found Item</h2>
            <form @submit.prevent="submitItem" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Item Description</label>
                    <input v-model="form.description" type="text" class="w-full rounded-md border px-3 py-2 text-sm" :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Found Location</label>
                    <input v-model="form.location" type="text" class="w-full rounded-md border px-3 py-2 text-sm" :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }" placeholder="Room number or area" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Date Found</label>
                    <input v-model="form.found_date" type="date" class="w-full rounded-md border px-3 py-2 text-sm" :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, borderColor: themeColors.border }" />
                </div>
                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 rounded-md text-white text-sm font-medium" :style="{ backgroundColor: themeColors.primary }">Submit</button>
                </div>
            </form>
        </div>

        <div class="shadow rounded-lg overflow-hidden" :style="{ backgroundColor: themeColors.card }">
            <table class="min-w-full divide-y" :style="{ borderColor: themeColors.border }">
                <thead :style="{ backgroundColor: themeColors.background }">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Date Found</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                    <tr v-if="!items || items.length === 0">
                        <td colspan="4" class="px-6 py-10 text-center text-sm" :style="{ color: themeColors.textSecondary }">No lost & found items recorded.</td>
                    </tr>
                    <tr v-for="item in items" :key="item.id">
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textPrimary }">{{ item.description }}</td>
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textSecondary }">{{ item.location }}</td>
                        <td class="px-6 py-4 text-sm" :style="{ color: themeColors.textSecondary }">{{ item.found_date }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs" :class="item.status === 'returned' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">{{ item.status ?? 'Stored' }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </DashboardLayout>
</template>
<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useTheme } from '@/Composables/useTheme';
import { PlusIcon } from '@heroicons/vue/24/outline';
const { themeColors } = useTheme();
defineProps({ user: Object, navigation: Array, items: { type: Array, default: () => [] } });
const showForm = ref(false);
const form = useForm({ description: '', location: '', found_date: '' });
const submitItem = () => form.post('/housekeeping/lost-found', { onSuccess: () => { showForm.value = false; form.reset(); } });
</script>
