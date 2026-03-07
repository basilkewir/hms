<template>
    <DashboardLayout title="Create Location" :user="user">
        <div class="max-w-2xl mx-auto">
            <div class="rounded-lg shadow-sm p-6" :style="{ backgroundColor: themeColors.card }">
                <h1 class="text-2xl font-bold mb-6" :style="{ color: themeColors.textPrimary }">Create New Location</h1>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Location Name</label>
                        <input v-model="form.name" type="text" class="w-full px-4 py-2 rounded-md border" 
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
                               placeholder="e.g., Main Restaurant, Front Desk, Bar">
                        <span v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Location Type</label>
                        <select v-model="form.type" class="w-full px-4 py-2 rounded-md border"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                            <option value="">Select Type</option>
                            <option value="warehouse">Warehouse</option>
                            <option value="restaurant">Restaurant</option>
                            <option value="frontdesk">Front Desk</option>
                            <option value="bar">Bar</option>
                            <option value="kitchen">Kitchen</option>
                            <option value="other">Other</option>
                        </select>
                        <span v-if="form.errors.type" class="text-red-500 text-sm">{{ form.errors.type }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Warehouse (Optional)</label>
                        <select v-model="form.warehouse_id" class="w-full px-4 py-2 rounded-md border"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                            <option value="">Select Warehouse</option>
                            <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                                {{ warehouse.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textSecondary }">Description</label>
                        <textarea v-model="form.description" class="w-full px-4 py-2 rounded-md border" rows="4"
                                  :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
                                  placeholder="Enter location description..."></textarea>
                    </div>

                    <div class="flex items-center">
                        <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded">
                        <label class="ml-2 text-sm" :style="{ color: themeColors.textSecondary }">Active</label>
                    </div>

                    <div class="flex space-x-3">
                        <button type="submit" class="px-6 py-2 rounded-md font-medium" style="background-color: var(--kotel-primary); color: #000000;">
                            Create Location
                        </button>
                        <Link :href="route('pos.locations.index')" class="px-6 py-2 rounded-md font-medium border" :style="{ borderColor: themeColors.border, color: themeColors.textSecondary }">
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { Link } from '@inertiajs/vue3'
import { useTheme } from '@/Composables/useTheme'

const props = defineProps({
    user: Object,
    warehouses: Array
})

const { themeColors: themeColorsObj } = useTheme()

const themeColors = computed(() => ({
    card: `var(--kotel-card)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    background: `var(--kotel-background)`,
    border: `var(--kotel-border)`,
    primary: `var(--kotel-primary)`
}))

const form = useForm({
    name: '',
    type: '',
    warehouse_id: '',
    description: '',
    is_active: true
})

const submit = () => {
    form.post(route('pos.locations.store'))
}
</script>
