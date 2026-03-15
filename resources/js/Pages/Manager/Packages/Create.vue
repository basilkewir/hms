<template>
    <DashboardLayout title="Create Package" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2" :style="{ color: themeColors.textPrimary }">Create Package</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Add a new package for bookings.</p>
                </div>
                <Link
                    :href="route('admin.packages.index')"
                    class="px-4 py-2 rounded-md transition-colors font-medium text-white"
                    :style="{ backgroundColor: themeColors.primary }"
                    @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                    @mouseleave="$event.target.style.backgroundColor = themeColors.primary"
                >
                    Back
                </Link>
            </div>
        </div>

        <div class="rounded-lg overflow-hidden shadow border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Package Details</h3>
            </div>

            <form class="p-6" @submit.prevent="submit">
                <div v-if="form.hasErrors" class="mb-6 p-4 rounded-md border" :style="{ borderColor: themeColors.danger, backgroundColor: themeColors.danger + '10', color: themeColors.textPrimary }">
                    <p class="font-medium">Please fix the errors below.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Name</label>
                        <input v-model="form.name" type="text" class="w-full rounded-md border px-3 py-2" :style="inputStyle" />
                        <div v-if="form.errors.name" class="text-sm mt-1" :style="{ color: themeColors.danger }">{{ form.errors.name }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Code</label>
                        <input v-model="form.code" type="text" class="w-full rounded-md border px-3 py-2" :style="inputStyle" />
                        <div v-if="form.errors.code" class="text-sm mt-1" :style="{ color: themeColors.danger }">{{ form.errors.code }}</div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Description</label>
                        <textarea v-model="form.description" rows="3" class="w-full rounded-md border px-3 py-2" :style="inputStyle"></textarea>
                        <div v-if="form.errors.description" class="text-sm mt-1" :style="{ color: themeColors.danger }">{{ form.errors.description }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Price</label>
                        <input v-model.number="form.price" type="number" min="0" step="0.01" class="w-full rounded-md border px-3 py-2" :style="inputStyle" />
                        <div v-if="form.errors.price" class="text-sm mt-1" :style="{ color: themeColors.danger }">{{ form.errors.price }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Max Bookings</label>
                        <input v-model.number="form.max_bookings" type="number" min="0" step="1" class="w-full rounded-md border px-3 py-2" :style="inputStyle" />
                        <div v-if="form.errors.max_bookings" class="text-sm mt-1" :style="{ color: themeColors.danger }">{{ form.errors.max_bookings }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Included Features (one per line)</label>
                        <textarea v-model="includedFeaturesText" rows="3" class="w-full rounded-md border px-3 py-2" :style="inputStyle"></textarea>
                        <div v-if="form.errors.included_features" class="text-sm mt-1" :style="{ color: themeColors.danger }">{{ form.errors.included_features }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Optional Features (one per line)</label>
                        <textarea v-model="optionalFeaturesText" rows="3" class="w-full rounded-md border px-3 py-2" :style="inputStyle"></textarea>
                        <div v-if="form.errors.optional_features" class="text-sm mt-1" :style="{ color: themeColors.danger }">{{ form.errors.optional_features }}</div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Halls</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <label v-for="hall in halls || []" :key="hall.id" class="flex items-center gap-2">
                                <input type="checkbox" :value="hall.id" v-model="form.hall_ids" />
                                <span :style="{ color: themeColors.textSecondary }">{{ hall.name }} ({{ hall.code }})</span>
                            </label>
                        </div>
                        <div v-if="form.errors.hall_ids" class="text-sm mt-1" :style="{ color: themeColors.danger }">{{ form.errors.hall_ids }}</div>
                    </div>

                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" v-model="form.is_active" />
                            <span :style="{ color: themeColors.textPrimary }">Active</span>
                        </label>
                        <div v-if="form.errors.is_active" class="text-sm mt-1" :style="{ color: themeColors.danger }">{{ form.errors.is_active }}</div>
                    </div>

                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" v-model="form.is_available" />
                            <span :style="{ color: themeColors.textPrimary }">Available</span>
                        </label>
                        <div v-if="form.errors.is_available" class="text-sm mt-1" :style="{ color: themeColors.danger }">{{ form.errors.is_available }}</div>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-end gap-3">
                    <button type="button" @click="form.reset()" class="px-4 py-2 rounded-md border" :style="{ borderColor: themeColors.border, color: themeColors.textPrimary }">Reset</button>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded-md font-medium text-white" :style="{ backgroundColor: themeColors.primary, opacity: form.processing ? 0.7 : 1 }">Create</button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'
import { useNotification } from '@/Composables/useNotification'

const props = defineProps({
    user: Object,
    halls: Array,
})

const { currentTheme } = useTheme()
const navigation = computed(() => getNavigationForRole('admin'))
const notification = useNotification()

const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

const inputStyle = computed(() => ({
    backgroundColor: themeColors.value.background,
    borderColor: themeColors.value.border,
    color: themeColors.value.textPrimary,
}))

const includedFeaturesText = ref('')
const optionalFeaturesText = ref('')

const form = useForm({
    name: '',
    code: '',
    description: '',
    price: 0,
    included_features: null,
    optional_features: null,
    is_active: true,
    is_available: true,
    max_bookings: 0,
    hall_ids: [],
})

const submit = () => {
    const included = includedFeaturesText.value
        .split(/\r?\n/)
        .map(v => v.trim())
        .filter(Boolean)
    const optional = optionalFeaturesText.value
        .split(/\r?\n/)
        .map(v => v.trim())
        .filter(Boolean)

    form.included_features = included.length ? included : null
    form.optional_features = optional.length ? optional : null

    form.post(route('admin.packages.store'), {
        onSuccess: () => notification.success('Package created successfully'),
    })
}
</script>
