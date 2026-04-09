<template>
    <DashboardLayout title="Weather Management" :user="user" :navigation="navigation">
        <Head title="Weather Management â€” IPTV" />

        <div class="p-6">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-1">
                    <Link :href="route('admin.iptv.devices.index')"
                          class="text-sm text-secondary hover:underline">
                        IPTV
                    </Link>
                    <span class="text-secondary text-sm">/</span>
                    <span class="text-sm font-medium">Weather</span>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold flex items-center gap-3">
                            ðŸŒ¤ Weather Management
                        </h1>
                        <p class="text-secondary text-sm mt-1">
                            Configure the weather widget shown on all Android TV devices
                        </p>
                    </div>
                    <button @click="fetchNow" :disabled="fetching"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold disabled:opacity-50 transition-colors">
                        <span :class="fetching ? 'animate-spin inline-block' : 'inline-block'">âŸ³</span>
                        {{ fetching ? 'Fetchingâ€¦' : 'Fetch Now' }}
                    </button>
                </div>
            </div>

            <!-- Flash messages -->
            <transition name="fade">
                <div v-if="flash"
                     :class="flash.type === 'success'
                         ? 'bg-green-500/10 border-green-500/30 text-green-400'
                         : 'bg-red-500/10 border-red-500/30 text-red-400'"
                     class="rounded-xl border px-4 py-3 mb-6 text-sm flex items-center gap-2">
                    <span>{{ flash.type === 'success' ? 'âœ“' : 'âœ•' }}</span>
                    {{ flash.message }}
                </div>
            </transition>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- â”€â”€ Settings Card â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
                <div class="card rounded-xl border p-6">
                    <h2 class="font-bold mb-5 flex items-center gap-2">
                        <span class="text-blue-400">âš™</span> Configuration
                    </h2>

                    <form @submit.prevent="saveSettings" class="space-y-4">
                        <!-- Enable toggle -->
                        <div class="flex items-center justify-between py-2 border-b border-color">
                            <div>
                                <p class="text-sm font-medium">Enable weather widget</p>
                                <p class="text-secondary text-xs">Show live weather on all TV screens</p>
                            </div>
                            <button type="button" @click="form.weather_enabled = !form.weather_enabled"
                                    :class="form.weather_enabled ? 'bg-blue-600' : 'bg-gray-600'"
                                    class="relative inline-flex h-6 w-11 rounded-full transition-colors focus:outline-none">
                                <span :class="form.weather_enabled ? 'translate-x-5' : 'translate-x-0.5'"
                                      class="inline-block h-5 w-5 mt-0.5 rounded-full bg-white shadow transition-transform"/>
                            </button>
                        </div>

                        <!-- API Key -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5 text-secondary">
                                OpenWeatherMap API Key
                            </label>
                            <input v-model="form.weather_api_key"
                                   type="password"
                                   placeholder="e.g. 229e33773d97df905â€¦"
                                   autocomplete="off"
                                   class="w-full px-3 py-2 rounded-lg border border-color bg-base text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                            <p class="text-secondary text-xs mt-1">
                                Free at
                                <a href="https://openweathermap.org/api" target="_blank" class="text-blue-400 hover:underline">openweathermap.org/api</a>
                            </p>
                        </div>

                        <!-- City -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5 text-secondary">
                                City
                            </label>
                            <input v-model="form.weather_city"
                                   placeholder="e.g. Douala"
                                   class="w-full px-3 py-2 rounded-lg border border-color bg-base text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                            <p class="text-secondary text-xs mt-1">
                                Use the city name as OpenWeatherMap recognises it (e.g. "London", "Douala", "New York")
                            </p>
                        </div>

                        <!-- Units -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5 text-secondary">
                                Temperature Units
                            </label>
                            <div class="flex gap-2">
                                <button type="button" @click="form.weather_units = 'metric'"
                                        :class="form.weather_units === 'metric'
                                            ? 'bg-blue-600 text-white border-blue-600'
                                            : 'bg-base text-secondary border-color'"
                                        class="flex-1 py-2 rounded-lg border text-sm font-semibold transition-colors">
                                    Â°C â€” Celsius
                                </button>
                                <button type="button" @click="form.weather_units = 'imperial'"
                                        :class="form.weather_units === 'imperial'
                                            ? 'bg-blue-600 text-white border-blue-600'
                                            : 'bg-base text-secondary border-color'"
                                        class="flex-1 py-2 rounded-lg border text-sm font-semibold transition-colors">
                                    Â°F â€” Fahrenheit
                                </button>
                            </div>
                        </div>

                        <button type="submit" :disabled="saving"
                                class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm transition-colors disabled:opacity-50">
                            {{ saving ? 'Savingâ€¦' : 'ðŸ’¾ Save Settings' }}
                        </button>
                    </form>
                </div>

                <!-- â”€â”€ Live Preview Card â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
                <div class="space-y-6">
                    <div class="card rounded-xl border p-6">
                        <h2 class="font-bold mb-4 flex items-center gap-2">
                            <span class="text-yellow-400">ðŸ“¡</span> Live Weather Cache
                            <span class="text-secondary text-xs font-normal ml-auto">Updates every 15 min</span>
                        </h2>

                        <div v-if="!weather" class="text-center py-8 text-secondary">
                            <div class="text-4xl mb-3">ðŸŒ«</div>
                            <p class="text-sm">No weather data cached yet.</p>
                            <p class="text-xs mt-1">Save settings then click "Fetch Now".</p>
                        </div>

                        <div v-else>
                            <!-- TV widget mockup -->
                            <div class="rounded-xl p-5 mb-4"
                                 style="background: linear-gradient(135deg, #0f1e3d 0%, #1a3560 100%);">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-blue-300 text-xs uppercase tracking-widest mb-1">
                                            ðŸ“ {{ weather.city }}, {{ weather.country }}
                                        </p>
                                        <p class="text-white text-5xl font-light">
                                            {{ weather.temperature }}{{ weather.unit_symbol }}
                                        </p>
                                        <p class="text-blue-200 text-sm mt-1">{{ weather.description }}</p>
                                    </div>
                                    <div class="text-right">
                                        <img v-if="weather.icon_url" :src="weather.icon_url"
                                             class="w-16 h-16 ml-auto" alt="weather icon" />
                                        <p class="text-blue-300 text-xs mt-1">
                                            Feels {{ weather.feels_like }}{{ weather.unit_symbol }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex gap-4 mt-4 text-xs text-blue-300 border-t border-blue-800 pt-3">
                                    <span>ðŸ’§ {{ weather.humidity }}%</span>
                                    <span>ðŸ’¨ {{ weather.wind_speed }} m/s</span>
                                    <span class="ml-auto">{{ formatFetchedAt(weather.fetched_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- How it works -->
                    <div class="card rounded-xl border p-5">
                        <h3 class="font-semibold text-sm mb-3 flex items-center gap-2">â„¹ How it works</h3>
                        <ol class="text-secondary text-xs space-y-2 list-decimal list-inside">
                            <li>The server fetches weather from OpenWeatherMap every <strong>15 minutes</strong> via the scheduler.</li>
                            <li>Android TV devices poll <code class="text-blue-400">/api/android/weather</code> every 10 minutes.</li>
                            <li>No API calls are made from the TV â€” only the server calls OpenWeatherMap.</li>
                            <li>Click <strong>"Fetch Now"</strong> above to manually refresh immediately.</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Push to all devices -->
            <div class="card rounded-xl border p-6 mt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-bold flex items-center gap-2">
                            ðŸ“¡ Push Settings to All Devices
                        </h2>
                        <p class="text-secondary text-xs mt-1">
                            Forces all {{ deviceCount }} registered TVs to re-sync settings and reload weather immediately.
                        </p>
                    </div>
                    <button @click="pushAll" :disabled="pushingAll"
                            class="px-5 py-2.5 rounded-xl bg-green-600 hover:bg-green-500 text-white text-sm font-bold transition-colors disabled:opacity-50">
                        {{ pushingAll ? 'Pushingâ€¦' : 'â¬† Push to All TVs' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'

const props = defineProps({
    user:        Object,
    navigation:  Array,
    settings:    Object,
    weather:     Object,
    deviceCount: { type: Number, default: 0 },
})

const { currentTheme } = useTheme()

// â”€â”€ Form state â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const form = ref({
    weather_api_key: props.settings?.weather_api_key ?? '',
    weather_city:    props.settings?.weather_city    ?? '',
    weather_units:   props.settings?.weather_units   ?? 'metric',
    weather_enabled: props.settings?.weather_enabled !== '0' && props.settings?.weather_enabled !== false,
})

// â”€â”€ Live weather state â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const weather = ref(props.weather ?? null)

// â”€â”€ UI state â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const saving     = ref(false)
const fetching   = ref(false)
const pushingAll = ref(false)
const flash      = ref(null)
let   flashTimer = null

function showFlash(type, message) {
    flash.value = { type, message }
    if (flashTimer) clearTimeout(flashTimer)
    flashTimer = setTimeout(() => { flash.value = null }, 5000)
}

// â”€â”€ Actions â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

function saveSettings() {
    saving.value = true
    router.post(route('admin.iptv.weather.save'), form.value, {
        preserveScroll: true,
        onSuccess: () => {
            showFlash('success', 'Weather settings saved.')
            saving.value = false
            fetchNow()
        },
        onError: (errors) => {
            showFlash('error', Object.values(errors)[0] ?? 'Failed to save settings.')
            saving.value = false
        },
    })
}

async function fetchNow() {
    fetching.value = true
    try {
        const res = await fetch(route('admin.iptv.weather.fetch'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Accept': 'application/json',
            },
        })
        const data = await res.json()
        if (data.success) {
            weather.value = data.weather
            showFlash('success', data.message)
        } else {
            showFlash('error', data.message ?? 'Fetch failed.')
        }
    } catch (e) {
        showFlash('error', 'Network error: ' + e.message)
    } finally {
        fetching.value = false
    }
}

function pushAll() {
    pushingAll.value = true
    router.post(route('admin.iptv.devices.push-all'), {}, {
        preserveScroll: true,
        onFinish:  () => { pushingAll.value = false },
        onSuccess: () => showFlash('success', 'Settings pushed to all active TV devices.'),
        onError:   () => showFlash('error', 'Push failed â€” check server logs.'),
    })
}

function formatFetchedAt(iso) {
    if (!iso) return 'â€”'
    try {
        const d = new Date(iso)
        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + ' Â· ' +
               d.toLocaleDateString([], { day: 'numeric', month: 'short' })
    } catch { return iso }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.4s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }

/* Follow admin theme CSS variables */
.card        { background-color: var(--kotel-card, #111827); border-color: var(--kotel-border, #1f2937); }
.bg-base     { background-color: var(--kotel-background, #0a0f1e); }
.border-color { border-color: var(--kotel-border, #1f2937); }
.text-secondary { color: var(--kotel-text-secondary, #9ca3af); }
</style>

