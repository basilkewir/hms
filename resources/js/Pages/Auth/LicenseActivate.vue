<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'

const page  = usePage()
const flash = computed(() => page.props.flash ?? {})

const form = useForm({
    license_key: '',
    hotel_name:  '',
})

const step         = ref('enter')   // 'enter' | 'activating' | 'success'
const hardwareId   = ref('...')
const showKey      = ref(false)

onMounted(() => {
    const info = {
        host:     window.location.hostname,
        tz:       Intl.DateTimeFormat().resolvedOptions().timeZone,
        screen:   `${screen.width}x${screen.height}`,
        ua:       navigator.userAgent.substring(0, 60),
    }
    hardwareId.value = btoa(JSON.stringify(info)).substring(0, 20)
})

const maskedKey = computed(() => {
    const v = form.license_key
    if (!v || showKey.value) return v
    return v.replace(/./g, (c, i) => (i < 4 || c === '-') ? c : '•')
})

const submit = () => {
    step.value = 'activating'
    form.post(route('license.activate.store'), {
        onSuccess: () => { step.value = 'success' },
        onError:   () => { step.value = 'enter'   },
    })
}
</script>

<template>
    <div class="min-h-screen flex" style="background: #0a0a0f;">

        <!-- Left decorative panel -->
        <div class="hidden lg:flex lg:w-1/2 flex-col items-center justify-center p-12 relative overflow-hidden"
             style="background: linear-gradient(135deg, #0d1117 0%, #161b22 50%, #1a1f2e 100%);">
            <!-- Grid background -->
            <div class="absolute inset-0 opacity-10"
                 style="background-image: linear-gradient(#f5c518 1px, transparent 1px), linear-gradient(90deg, #f5c518 1px, transparent 1px); background-size: 40px 40px;"></div>
            <!-- Glow -->
            <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 rounded-full opacity-10 blur-3xl"
                 style="background: #f5c518;"></div>

            <div class="relative z-10 text-center">
                <div class="w-24 h-24 rounded-2xl flex items-center justify-center mx-auto mb-8 shadow-2xl"
                     style="background: linear-gradient(135deg, #f5c518, #e6b800);">
                    <svg class="w-14 h-14" fill="#1a1a1a" viewBox="0 0 24 24">
                        <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-extrabold mb-3" style="color: #f5c518;">KotelHMS</h1>
                <p class="text-lg mb-2" style="color: #8b949e;">Hotel Management System</p>
                <p class="text-sm" style="color: #484f58;">Enterprise • Secure • Reliable</p>

                <div class="mt-12 space-y-4 text-left">
                    <div v-for="(item, i) in [
                        { icon: '🔒', title: 'Hardware-Bound License', desc: 'Tied to your server fingerprint' },
                        { icon: '🌐', title: 'Online Validation', desc: 'Real-time check with KewirDev servers' },
                        { icon: '🔄', title: 'Auto Token Refresh', desc: 'Seamless renewal before expiry' },
                        { icon: '📋', title: 'Audit Logging', desc: 'All activations are logged securely' },
                    ]" :key="i"
                        class="flex items-start gap-3 px-4 py-3 rounded-xl"
                        style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);">
                        <span class="text-xl mt-0.5">{{ item.icon }}</span>
                        <div>
                            <p class="text-sm font-semibold" style="color: #e6edf3;">{{ item.title }}</p>
                            <p class="text-xs mt-0.5" style="color: #8b949e;">{{ item.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right activation panel -->
        <div class="flex-1 flex flex-col items-center justify-center p-6 lg:p-12">

            <!-- Mobile logo -->
            <div class="lg:hidden text-center mb-8">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4"
                     style="background: linear-gradient(135deg, #f5c518, #e6b800);">
                    <svg class="w-9 h-9" fill="#1a1a1a" viewBox="0 0 24 24">
                        <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold" style="color: #f5c518;">KotelHMS</h1>
            </div>

            <div class="w-full max-w-md">

                <!-- Success state -->
                <div v-if="step === 'success'" class="text-center">
                    <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6"
                         style="background: rgba(63,185,80,0.15); border: 2px solid #3fb950;">
                        <svg class="w-10 h-10" fill="#3fb950" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold mb-2" style="color: #3fb950;">License Activated!</h2>
                    <p class="mb-6" style="color: #8b949e;">Your system is now licensed. Redirecting to login…</p>
                    <div class="w-8 h-8 border-2 rounded-full animate-spin mx-auto" style="border-color: #3fb950; border-top-color: transparent;"></div>
                </div>

                <!-- Enter / activating state -->
                <template v-else>
                    <div class="mb-8">
                        <h2 class="text-3xl font-bold mb-2" style="color: #e6edf3;">License Activation</h2>
                        <p style="color: #8b949e;">Enter your license key to unlock the system.</p>
                    </div>

                    <!-- Flash error from previous attempt -->
                    <div v-if="flash.error" class="mb-5 px-4 py-3 rounded-xl text-sm flex items-center gap-2"
                         style="background: rgba(248,81,73,0.1); border: 1px solid rgba(248,81,73,0.3); color: #f85149;">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ flash.error }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- License key field -->
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #8b949e;">
                                License Key <span style="color: #f85149;">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    :type="showKey ? 'text' : 'password'"
                                    v-model="form.license_key"
                                    placeholder="XXXX-XXXX-XXXX-XXXX"
                                    autocomplete="off"
                                    spellcheck="false"
                                    class="w-full px-4 py-3 pr-12 rounded-xl font-mono text-base outline-none transition-all"
                                    style="background: #161b22; border: 1px solid #30363d; color: #e6edf3; letter-spacing: 0.05em;"
                                    :class="{ 'border-red-500': form.errors.license_key }"
                                    @focus="($el) => $el.style.borderColor = '#f5c518'"
                                    @blur="($el) => $el.style.borderColor = form.errors.license_key ? '#f85149' : '#30363d'"
                                />
                                <button type="button" @click="showKey = !showKey"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 p-1 rounded"
                                        style="color: #8b949e;" tabindex="-1">
                                    <svg v-if="!showKey" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            <p v-if="form.errors.license_key" class="mt-1.5 text-xs" style="color: #f85149;">
                                {{ form.errors.license_key }}
                            </p>
                        </div>

                        <!-- Hotel name field -->
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: #8b949e;">Hotel / Organization Name</label>
                            <input
                                type="text"
                                v-model="form.hotel_name"
                                placeholder="Grand Hotel"
                                class="w-full px-4 py-3 rounded-xl outline-none transition-all"
                                style="background: #161b22; border: 1px solid #30363d; color: #e6edf3;"
                            />
                        </div>

                        <!-- Submit button -->
                        <button type="submit" :disabled="form.processing || !form.license_key"
                                class="w-full py-3 rounded-xl font-semibold text-base transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                style="background: linear-gradient(135deg, #f5c518, #e6b800); color: #0d1117;">
                            <svg v-if="form.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            {{ form.processing ? 'Validating with license server…' : 'Activate License' }}
                        </button>
                    </form>

                    <!-- Hardware info -->
                    <div class="mt-8 pt-6" style="border-top: 1px solid #21262d;">
                        <p class="text-xs font-medium mb-3" style="color: #484f58; text-transform: uppercase; letter-spacing: 0.08em;">Server Fingerprint</p>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="px-3 py-2 rounded-lg" style="background: #161b22; border: 1px solid #21262d;">
                                <p style="color: #484f58;">Device ID</p>
                                <p class="font-mono mt-0.5" style="color: #8b949e;">{{ hardwareId }}</p>
                            </div>
                            <div class="px-3 py-2 rounded-lg" style="background: #161b22; border: 1px solid #21262d;">
                                <p style="color: #484f58;">Host</p>
                                <p class="font-mono mt-0.5 truncate" style="color: #8b949e;">{{ $page.props.ziggy?.location ? new URL($page.props.ziggy.location).hostname : 'localhost' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Support footer -->
                    <p class="text-center mt-6 text-xs" style="color: #484f58;">
                        Purchase or manage licenses at
                        <a href="https://kewirdev.com" target="_blank" class="hover:underline" style="color: #f5c518;">kewirdev.com</a>
                        &nbsp;·&nbsp;
                        <a href="mailto:support@kewirdev.com" class="hover:underline" style="color: #f5c518;">support@kewirdev.com</a>
                    </p>
                </template>
            </div>
        </div>
    </div>
</template>
