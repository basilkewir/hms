<template>
    <DashboardLayout title="New Purchase" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Create New Purchase Order</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Add a new purchase order to the system.</p>
                </div>
                <Link :href="route('manager.purchases.index')"
                      class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity flex items-center"
                      :style="{ backgroundColor: themeColors.secondary, color: '#fff' }">
                    <ArrowLeftIcon class="h-4 w-4 mr-2" />
                    Back to Purchases
                </Link>
            </div>
        </div>

        <!-- Form Section -->
        <div class="shadow rounded-lg p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Purchase Information Section -->
                <div>
                    <h3 class="text-lg font-medium mb-4" :style="{ color: themeColors.textPrimary }">Purchase Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Supplier<span class="text-red-500">*</span></label>
                            <select v-model="form.supplier_id" required
                                    class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 transition-all"
                                    :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background, color: themeColors.textPrimary }">
                                <option value="">Select Supplier</option>
                                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                    {{ supplier.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.supplier_id" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.supplier_id }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Purchase Date<span class="text-red-500">*</span></label>
                            <input v-model="form.purchase_date" type="date" required
                                   class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 transition-all cursor-pointer"
                                   :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background, color: themeColors.textPrimary }">
                            <div v-if="form.errors.purchase_date" class="mt-1 text-sm" :style="{ color: themeColors.danger }">{{ form.errors.purchase_date }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Status</label>
                            <select v-model="form.status"
                                    class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 transition-all"
                                    :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background, color: themeColors.textPrimary }">
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="partially_received">Partially Received</option>
                                <option value="received">Received</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div>
                    <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Notes</label>
                    <textarea v-model="form.notes" rows="4"
                              class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 transition-all"
                              :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background, color: themeColors.textPrimary }"
                              placeholder="Additional notes about this purchase order..."></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-3 pt-4 border-t" :style="{ borderColor: themeColors.border }">
                    <Link :href="route('manager.purchases.index')"
                          class="px-4 py-2 rounded-md font-medium transition-all"
                          :style="{ backgroundColor: themeColors.border, color: themeColors.textPrimary }">
                        Cancel
                    </Link>
                    <button type="submit"
                            class="px-4 py-2 rounded-md font-medium text-white hover:opacity-90 transition-all"
                            :style="{ backgroundColor: themeColors.primary }">
                        Create Purchase Order
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    suppliers: {
        type: Array,
        default: () => []
    }
})

const navigation = computed(() => getNavigationForRole('manager'))

const { currentTheme, loadTheme } = useTheme()
loadTheme()

const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    primary: `var(--kotel-primary)`,
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

const form = useForm({
    supplier_id: '',
    purchase_date: new Date().toISOString().slice(0, 10),
    status: 'pending',
    notes: ''
})

const submit = () => {
    form.post(route('manager.purchases.store'), {
        preserveScroll: true
    })
}
</script>
