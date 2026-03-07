<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const page = usePage()
const user = computed(() => page.props.auth.user)
const navigation = computed(() => page.props.navigation)

const props = defineProps({
    licenseKey:  { type: String, default: '' },
    licenseInfo: { type: Object, default: null },
    isActivated: { type: Boolean, default: false },
})

const themeColors = computed(() => ({
    background:    'var(--kotel-background)',
    card:          'var(--kotel-card)',
    border:        'var(--kotel-border)',
    textPrimary:   'var(--kotel-text-primary)',
    textSecondary: 'var(--kotel-text-secondary)',
    textTertiary:  'var(--kotel-text-tertiary)',
    primary:       'var(--kotel-primary)',
    success:       'var(--kotel-success)',
    warning:       'var(--kotel-warning)',
    danger:        'var(--kotel-danger)',
}))

const flash = computed(() => page.props.flash ?? {})

const form = useForm({ license_key: props.licenseKey || '' })

const activating  = ref(false)
const refreshing  = ref(false)
const deactivating = ref(false)

const activate = () => {
    form.post(route('admin.settings.license.activate'), {
        onStart:  () => activating.value = true,
        onFinish: () => activating.value = false,
    })
}

const refresh = () => {
    refreshing.value = true
    router.post(route('admin.settings.license.refresh'), {}, {
        onFinish: () => refreshing.value = false,
    })
}

const deactivate = () => {
    if (!confirm('Deactivate the license on this server? You can re-activate with the same key later.')) return
    deactivating.value = true
    router.post(route('admin.settings.license.deactivate'), {}, {
        onFinish: () => deactivating.value = false,
    })
}

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '—'

const licenseTypeColor = computed(() => {
    const t = props.licenseInfo?.license_type
    if (t === 'perpetual' || t === 'enterprise') return themeColors.value.success
    if (t === 'premium') return themeColors.value.primary
    if (t === 'basic') return themeColors.value.warning
    return themeColors.value.textSecondary
})

const statusColor = computed(() => {
    if (!props.licenseInfo) return themeColors.value.textTertiary
    return props.licenseInfo.status === 'active' ? themeColors.value.success : themeColors.value.danger
})
</script>

<template>
    <DashboardLayout title="License Management" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">License Management</h1>
            <p :style="{ color: themeColors.textSecondary }">Activate and manage your KotelHMS software license</p>
        </div>

        <!-- Flash messages -->
        <div v-if="flash.success" class="mb-6 px-4 py-3 rounded-lg border text-sm"
             :style="{ backgroundColor: themeColors.success + '15', borderColor: themeColors.success + '40', color: themeColors.success }">
            {{ flash.success }}
        </div>
        <div v-if="flash.error" class="mb-6 px-4 py-3 rounded-lg border text-sm"
             :style="{ backgroundColor: themeColors.danger + '15', borderColor: themeColors.danger + '40', color: themeColors.danger }">
            {{ flash.error }}
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: activation form -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Activation card -->
                <div class="rounded-xl border p-6" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                    <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">License Key</h2>

                    <div class="flex gap-3">
                        <input v-model="form.license_key"
                               type="text"
                               placeholder="XXXX-XXXX-XXXX-XXXX"
                               class="flex-1 px-4 py-2 rounded-lg border text-sm font-mono"
                               :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
                               :disabled="isActivated" />
                        <button v-if="!isActivated"
                                @click="activate"
                                :disabled="activating || !form.license_key"
                                class="px-5 py-2 rounded-lg text-sm font-medium text-white disabled:opacity-50"
                                :style="{ backgroundColor: themeColors.primary }">
                            {{ activating ? 'Activating…' : 'Activate' }}
                        </button>
                        <button v-else
                                @click="deactivate"
                                :disabled="deactivating"
                                class="px-5 py-2 rounded-lg text-sm font-medium text-white disabled:opacity-50"
                                :style="{ backgroundColor: themeColors.danger }">
                            {{ deactivating ? 'Deactivating…' : 'Deactivate' }}
                        </button>
                    </div>
                    <p v-if="form.errors.license_key" class="text-xs mt-2" :style="{ color: themeColors.danger }">
                        {{ form.errors.license_key }}
                    </p>

                    <!-- Token status -->
                    <div class="mt-4 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full inline-block"
                              :style="{ backgroundColor: isActivated ? themeColors.success : themeColors.textTertiary }"></span>
                        <span class="text-sm" :style="{ color: themeColors.textSecondary }">
                            {{ isActivated ? 'License token is valid and active on this server' : 'Not activated on this server' }}
                        </span>
                        <button v-if="isActivated"
                                @click="refresh"
                                :disabled="refreshing"
                                class="ml-auto text-xs px-3 py-1 rounded border disabled:opacity-50"
                                :style="{ borderColor: themeColors.border, color: themeColors.textSecondary }">
                            {{ refreshing ? 'Refreshing…' : 'Refresh Token' }}
                        </button>
                    </div>
                </div>

                <!-- Features table -->
                <div v-if="licenseInfo" class="rounded-xl border overflow-hidden"
                     :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                    <div class="p-5 border-b" :style="{ borderColor: themeColors.border }">
                        <h2 class="font-semibold" :style="{ color: themeColors.textPrimary }">Included Features</h2>
                    </div>
                    <div class="divide-y" :style="{ borderColor: themeColors.border }">
                        <div v-for="(val, key) in licenseInfo.features" :key="key"
                             class="flex items-center justify-between px-5 py-3">
                            <span class="text-sm capitalize" :style="{ color: themeColors.textSecondary }">
                                {{ key.replace(/_/g, ' ') }}
                            </span>
                            <span class="text-sm font-medium" :style="{ color: val === true ? themeColors.success : (val === false ? themeColors.danger : themeColors.textPrimary) }">
                                <template v-if="val === true">✓ Included</template>
                                <template v-else-if="val === false">✗ Not included</template>
                                <template v-else>{{ val }}</template>
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right: license info -->
            <div class="space-y-6">

                <!-- Status card -->
                <div class="rounded-xl border p-5" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                    <h2 class="font-semibold mb-4" :style="{ color: themeColors.textPrimary }">License Status</h2>

                    <div v-if="licenseInfo" class="space-y-3 text-sm">
                        <div class="flex justify-between items-center">
                            <span :style="{ color: themeColors.textSecondary }">Status</span>
                            <span class="px-2 py-0.5 rounded text-xs font-medium capitalize"
                                  :style="{ backgroundColor: statusColor + '20', color: statusColor }">
                                {{ licenseInfo.status }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span :style="{ color: themeColors.textSecondary }">Type</span>
                            <span class="font-medium capitalize" :style="{ color: licenseTypeColor }">{{ licenseInfo.license_type }}</span>
                        </div>
                        <div v-if="licenseInfo.hotel_name" class="flex justify-between">
                            <span :style="{ color: themeColors.textSecondary }">Registered to</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ licenseInfo.hotel_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span :style="{ color: themeColors.textSecondary }">Expires</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ licenseInfo.expires_at === null ? 'Never (Perpetual)' : formatDate(licenseInfo.expires_at) }}</span>
                        </div>
                        <div v-if="licenseInfo.device_usage" class="flex justify-between">
                            <span :style="{ color: themeColors.textSecondary }">Devices</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ licenseInfo.device_usage.current }} / {{ licenseInfo.device_usage.maximum }}</span>
                        </div>
                        <div v-if="licenseInfo.last_validation" class="flex justify-between">
                            <span :style="{ color: themeColors.textSecondary }">Last validated</span>
                            <span class="text-xs" :style="{ color: themeColors.textTertiary }">{{ formatDate(licenseInfo.last_validation) }}</span>
                        </div>
                    </div>

                    <div v-else class="text-center py-6">
                        <svg class="w-10 h-10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" :style="{ color: themeColors.textTertiary }">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">No license info</p>
                        <p class="text-xs mt-1" :style="{ color: themeColors.textTertiary }">Enter a license key to activate.</p>
                    </div>
                </div>

                <!-- Help card -->
                <div class="rounded-xl border p-5" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                    <h2 class="font-semibold mb-3" :style="{ color: themeColors.textPrimary }">Need Help?</h2>
                    <div class="space-y-2 text-sm" :style="{ color: themeColors.textSecondary }">
                        <p>Purchase or renew licenses at:</p>
                        <a href="https://kewirdev.com" target="_blank"
                           class="block font-medium hover:underline" :style="{ color: themeColors.primary }">
                            kewirdev.com
                        </a>
                        <p class="mt-2">Support:</p>
                        <a href="mailto:support@kewirdev.com"
                           class="block hover:underline" :style="{ color: themeColors.primary }">
                            support@kewirdev.com
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </DashboardLayout>
</template>
