<template>
    <DashboardLayout title="Room Service Charge Settings" :user="user">

        <!-- Page Header -->
        <div class="bg-kotel-bg-card shadow rounded-lg p-6 mb-8 border border-kotel-border">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-kotel-text-primary flex items-center gap-3">
                        <span class="text-3xl">🛎️</span>
                        Room Service Charge Settings
                    </h1>
                    <p class="text-kotel-text-secondary mt-2">
                        Configure the default Room Service charge that can be applied to occupied rooms.
                        This charge will appear as a quick-select option when adding service charges from the room status panel.
                    </p>
                </div>
            </div>
        </div>

        <!-- Success / Error message -->
        <div v-if="flashMessage" class="mb-6 px-4 py-3 rounded-lg border text-sm font-medium"
             :class="flashIsError
                ? 'bg-red-900/30 border-red-500/40 text-red-300'
                : 'bg-emerald-900/30 border-emerald-500/40 text-emerald-300'">
            {{ flashMessage }}
        </div>

        <!-- Settings Card -->
        <div class="bg-kotel-bg-card shadow rounded-lg border border-kotel-border">
            <div class="border-b border-kotel-border px-6 py-4">
                <h2 class="text-lg font-semibold text-kotel-text-primary">Room Service Charge Configuration</h2>
                <p class="text-sm text-kotel-text-tertiary mt-1">Set the price and label that appears when staff select "Room Service" while adding a charge to an occupied room.</p>
            </div>

            <form @submit.prevent="save" class="p-6 space-y-6">

                <!-- Enable Toggle -->
                <div class="flex items-start gap-4">
                    <div class="mt-1">
                        <button type="button"
                                @click="form.enabled = !form.enabled"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:ring-offset-2 focus:ring-offset-kotel-black"
                                :class="form.enabled ? 'bg-kotel-yellow' : 'bg-kotel-darker'">
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition"
                                  :class="form.enabled ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-kotel-text-secondary">Enable Room Service Charge</label>
                        <p class="text-xs text-kotel-text-tertiary mt-0.5">
                            When enabled, a "Room Service" option will appear in the service charge dropdown on occupied rooms.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Name Field -->
                    <div>
                        <label class="block text-sm font-medium text-kotel-text-secondary mb-2">
                            Charge Label
                            <span class="text-red-400 ml-1">*</span>
                        </label>
                        <input v-model="form.name"
                               type="text"
                               maxlength="100"
                               placeholder="e.g. Room Service"
                               class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow placeholder-kotel-text-tertiary">
                        <p class="mt-1 text-xs text-kotel-text-tertiary">The label displayed to staff when selecting this charge type.</p>
                        <p v-if="errors.name" class="mt-1 text-xs text-red-400">{{ errors.name }}</p>
                    </div>

                    <!-- Price Field -->
                    <div>
                        <label class="block text-sm font-medium text-kotel-text-secondary mb-2">
                            Default Price
                            <span class="text-red-400 ml-1">*</span>
                        </label>
                        <div class="relative">
                            <input v-model.number="form.price"
                                   type="number"
                                   min="0"
                                   step="0.01"
                                   placeholder="0.00"
                                   class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow placeholder-kotel-text-tertiary">
                        </div>
                        <p class="mt-1 text-xs text-kotel-text-tertiary">This price will be pre-filled when staff selects "Room Service". They can still change it before submitting.</p>
                        <p v-if="errors.price" class="mt-1 text-xs text-red-400">{{ errors.price }}</p>
                    </div>

                </div>

                <!-- Live Preview -->
                <div class="bg-kotel-black/50 border border-kotel-border rounded-lg p-4 mt-2">
                    <p class="text-xs text-kotel-text-tertiary mb-3 font-medium uppercase tracking-wide">Preview — How it will appear in the room service charge dropdown</p>
                    <div class="flex items-center gap-3 text-sm text-kotel-text-secondary">
                        <span class="text-lg">🛎️</span>
                        <span :class="form.enabled ? 'text-kotel-text-primary' : 'line-through text-kotel-text-tertiary'">
                            {{ form.name || 'Room Service' }}
                        </span>
                        <span v-if="form.price > 0" class="text-kotel-yellow font-medium">— {{ formattedPrice }}</span>
                        <span v-if="!form.enabled" class="text-xs text-red-400 ml-2">(disabled)</span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3 pt-2 border-t border-kotel-border">
                    <button type="submit"
                            :disabled="isSaving"
                            class="bg-kotel-yellow text-kotel-black px-6 py-2 rounded-md hover:bg-kotel-yellow-dark disabled:opacity-50 font-medium transition-colors">
                        {{ isSaving ? 'Saving...' : 'Save Settings' }}
                    </button>
                    <a :href="backHref"
                       class="bg-kotel-darker text-kotel-text-secondary px-6 py-2 rounded-md hover:bg-kotel-dark hover:text-kotel-yellow transition-colors text-sm">
                        Back
                    </a>
                </div>

            </form>
        </div>

    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const props = defineProps({
    user: Object,
    roomServiceName:    { type: String,  default: 'Room Service' },
    roomServicePrice:   { type: Number,  default: 0 },
    roomServiceEnabled: { type: Boolean, default: true },
    saveRoute:          { type: String,  default: '' },
    backHref:           { type: String,  default: '#' },
})

const page = usePage()

const form = ref({
    name:    props.roomServiceName,
    price:   props.roomServicePrice,
    enabled: props.roomServiceEnabled,
})

const isSaving   = ref(false)
const errors     = ref({})
const flashMessage = ref(page.props.flash?.success || page.props.flash?.error || '')
const flashIsError = ref(!!page.props.flash?.error)

const formattedPrice = computed(() => {
    const v = parseFloat(form.value.price || 0)
    return isNaN(v) ? '0.00' : v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
})

const save = () => {
    errors.value = {}

    if (!form.value.name || !form.value.name.trim()) {
        errors.value.name = 'Charge label is required.'
    }
    if (form.value.price === null || form.value.price === '' || isNaN(form.value.price) || form.value.price < 0) {
        errors.value.price = 'Price must be 0 or greater.'
    }
    if (Object.keys(errors.value).length) return

    isSaving.value = true
    flashMessage.value = ''

    router.post(props.saveRoute, {
        name:    form.value.name.trim(),
        price:   parseFloat(form.value.price),
        enabled: form.value.enabled,
    }, {
        onSuccess: () => {
            isSaving.value = false
            flashMessage.value = 'Room service charge settings saved successfully.'
            flashIsError.value = false
        },
        onError: (errs) => {
            isSaving.value = false
            errors.value = errs
            flashMessage.value = 'Failed to save settings. Please check the fields below.'
            flashIsError.value = true
        },
    })
}
</script>
