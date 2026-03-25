<template>
    <DashboardLayout title="Quick Check-In" :user="user" :navigation="navigation">
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Quick Check-In</h1>
                    <p :style="{ color: themeColors.textSecondary }">Create or pick a guest, assign a room, and check them in from one page.</p>
                </div>
                <div class="flex gap-3">
                    <Link :href="route(`${routePrefix}.reservations.create`)"
                          class="px-4 py-2 rounded-md transition-colors"
                          :style="{ backgroundColor: themeColors.background, color: themeColors.textPrimary, border: `1px solid ${themeColors.border}` }">
                        Full Reservation
                    </Link>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="xl:col-span-2 space-y-6">
                    <section class="shadow rounded-lg p-6"
                             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold" :style="{ color: themeColors.textPrimary }">Guest</h2>
                            <div class="inline-flex rounded-md overflow-hidden border" :style="{ borderColor: themeColors.border }">
                                <button type="button"
                                        class="px-4 py-2 text-sm transition-colors"
                                        :style="guestMode === 'existing' ? activeToggleStyle : inactiveToggleStyle"
                                        @click="setGuestMode('existing')">
                                    Existing Guest
                                </button>
                                <button type="button"
                                        class="px-4 py-2 text-sm transition-colors"
                                        :style="guestMode === 'new' ? activeToggleStyle : inactiveToggleStyle"
                                        @click="setGuestMode('new')">
                                    New Walk-In
                                </button>
                            </div>
                        </div>

                        <div v-if="guestMode === 'existing'" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Search Guest</label>
                                <input v-model="guestSearch"
                                       type="text"
                                       placeholder="Search by name, email, or phone"
                                       class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                       :style="inputStyle" />
                            </div>
                            <div v-if="searching" class="text-sm" :style="{ color: themeColors.textSecondary }">Searching guests...</div>
                            <div v-else-if="guestSearch.trim().length >= 2 && searchResults.length" class="space-y-2">
                                <button v-for="guest in searchResults"
                                        :key="guest.id"
                                        type="button"
                                        class="w-full text-left rounded-md p-3 transition-colors border"
                                        :style="selectedGuest?.id === guest.id ? selectedCardStyle : resultCardStyle"
                                        @click="selectGuest(guest)">
                                    <div class="font-medium" :style="{ color: themeColors.textPrimary }">{{ guest.name }}</div>
                                    <div class="text-sm" :style="{ color: themeColors.textSecondary }">{{ guest.email || guest.phone || 'No contact info' }}</div>
                                </button>
                            </div>
                            <div v-else-if="guestSearch.trim().length >= 2" class="text-sm" :style="{ color: themeColors.textSecondary }">No guests found. Switch to New Walk-In to create one.</div>
                            <div v-if="selectedGuest" class="rounded-md p-4 border" :style="selectedCardStyle">
                                <div class="font-semibold" :style="{ color: themeColors.textPrimary }">Selected Guest</div>
                                <div class="mt-2 text-sm space-y-1" :style="{ color: themeColors.textSecondary }">
                                    <p>{{ selectedGuest.name }}</p>
                                    <p>{{ selectedGuest.email || 'No email' }}</p>
                                    <p>{{ selectedGuest.phone || 'No phone' }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">First Name</label>
                                <input v-model="form.first_name" type="text" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Last Name</label>
                                <input v-model="form.last_name" type="text" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Phone</label>
                                <input v-model="form.phone" type="text" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Email</label>
                                <input v-model="form.email" type="email" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Nationality</label>
                                <input v-model="form.nationality" type="text" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">ID Type</label>
                                <select v-model="form.id_type" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle">
                                    <option value="other">Other</option>
                                    <option value="passport">Passport</option>
                                    <option value="national_id">National ID</option>
                                    <option value="drivers_license">Driver's License</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">ID Number</label>
                                <input v-model="form.id_number" type="text" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Address</label>
                                <textarea v-model="form.address" rows="3" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle"></textarea>
                            </div>
                        </div>
                    </section>

                    <section class="shadow rounded-lg p-6"
                             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                        <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Stay Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Room</label>
                                <select v-model="form.room_id" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle">
                                    <option value="">Select available room</option>
                                    <option v-for="room in availableRooms" :key="room.id" :value="room.id">
                                        {{ room.room_number }} - {{ room.room_type }} - {{ formatMoney(room.room_rate) }}/night
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Check-In Date</label>
                                <input v-model="form.check_in_date" type="date" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Check-Out Date</label>
                                <input v-model="form.check_out_date" type="date" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Adults</label>
                                <input v-model.number="form.number_of_adults" type="number" min="1" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Children</label>
                                <input v-model.number="form.number_of_children" type="number" min="0" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Room Rate</label>
                                <input v-model.number="form.room_rate" type="number" min="0" step="0.01" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Key Card</label>
                                <select v-model="form.key_card_id" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle">
                                    <option value="">No key card</option>
                                    <option v-for="card in availableKeyCards" :key="card.id" :value="card.id">
                                        {{ card.card_number }} - {{ card.card_type || 'Standard' }}
                                    </option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Special Requests</label>
                                <textarea v-model="form.special_requests" rows="3" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle"></textarea>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="space-y-6">
                    <section class="shadow rounded-lg p-6"
                             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                        <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Payment</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Payment Method</label>
                                <select v-model="form.payment_method" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">Amount to Pay Now</label>
                                <input v-model.number="form.payment_amount" type="number" min="0" step="0.01" class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors" :style="inputStyle" />
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="px-3 py-1 text-xs rounded border" :style="shortcutStyle" @click="form.payment_amount = 0">No Payment</button>
                                <button type="button" class="px-3 py-1 text-xs rounded border" :style="shortcutStyle" @click="form.payment_amount = estimatedTotal / 2">50%</button>
                                <button type="button" class="px-3 py-1 text-xs rounded border" :style="shortcutStyle" @click="form.payment_amount = estimatedTotal">Full</button>
                            </div>
                        </div>
                    </section>

                    <section class="shadow rounded-lg p-6"
                             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                        <h2 class="text-lg font-semibold mb-4" :style="{ color: themeColors.textPrimary }">Summary</h2>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span :style="{ color: themeColors.textSecondary }">Room</span>
                                <span :style="{ color: themeColors.textPrimary }">{{ selectedRoom?.room_number || 'Not selected' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span :style="{ color: themeColors.textSecondary }">Nights</span>
                                <span :style="{ color: themeColors.textPrimary }">{{ nights }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span :style="{ color: themeColors.textSecondary }">Room Charges</span>
                                <span :style="{ color: themeColors.textPrimary }">{{ formatMoney(roomCharges) }}</span>
                            </div>
                            <div class="flex justify-between font-semibold pt-3 border-t" :style="{ borderTopColor: themeColors.border, color: themeColors.textPrimary }">
                                <span>Estimated Total</span>
                                <span>{{ formatMoney(estimatedTotal) }}</span>
                            </div>
                            <p class="text-xs" :style="{ color: themeColors.textTertiary }">Estimated total uses current room rate. Guest-specific discounts and final folio charges are applied during check-in.</p>
                        </div>
                        <div class="mt-6 flex flex-col gap-3">
                            <button type="submit"
                                    :disabled="form.processing"
                                    class="w-full px-4 py-3 rounded-md transition-colors font-medium"
                                    :style="{ backgroundColor: themeColors.primary, color: themeColors.background, opacity: form.processing ? 0.7 : 1 }">
                                <span v-if="form.processing">Checking In...</span>
                                <span v-else>Complete Quick Check-In</span>
                            </button>
                        </div>
                    </section>
                </div>
            </div>
        </form>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'

const props = defineProps({
    user: Object,
    navigation: Array,
    routePrefix: String,
    availableRooms: { type: Array, default: () => [] },
    availableKeyCards: { type: Array, default: () => [] },
    selectedRoomId: { type: Number, default: null },
    selectedGuest: { type: Object, default: null },
})

const { loadTheme } = useTheme()
loadTheme()

const themeColors = computed(() => ({
    background: 'var(--kotel-background)',
    card: 'var(--kotel-card)',
    border: 'var(--kotel-border)',
    textPrimary: 'var(--kotel-text-primary)',
    textSecondary: 'var(--kotel-text-secondary)',
    textTertiary: 'var(--kotel-text-tertiary)',
    primary: 'var(--kotel-primary)',
    success: 'var(--kotel-success)',
}))

const today = new Date().toISOString().slice(0, 10)
const tomorrow = new Date(Date.now() + 86400000).toISOString().slice(0, 10)

const guestMode = ref('existing')
const guestSearch = ref('')
const searchResults = ref([])
const selectedGuest = ref(null)
const searching = ref(false)
let searchTimer = null

const form = useForm({
    guest_mode: props.selectedGuest ? 'existing' : 'existing',
    guest_id: props.selectedGuest?.id || '',
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    nationality: '',
    id_type: 'other',
    id_number: '',
    address: '',
    room_id: props.selectedRoomId || '',
    check_in_date: today,
    check_out_date: tomorrow,
    number_of_adults: 1,
    number_of_children: 0,
    room_rate: 0,
    special_requests: '',
    key_card_id: '',
    payment_amount: 0,
    payment_method: 'cash',
})

if (props.selectedGuest) {
    selectedGuest.value = props.selectedGuest
    guestSearch.value = props.selectedGuest.name
}

const inputStyle = computed(() => ({
    backgroundColor: themeColors.value.background,
    borderColor: themeColors.value.border,
    color: themeColors.value.textPrimary,
    borderWidth: '1px',
    borderStyle: 'solid',
}))

const activeToggleStyle = computed(() => ({
    backgroundColor: themeColors.value.primary,
    color: themeColors.value.background,
}))

const inactiveToggleStyle = computed(() => ({
    backgroundColor: themeColors.value.background,
    color: themeColors.value.textSecondary,
}))

const selectedCardStyle = computed(() => ({
    backgroundColor: 'rgba(59, 130, 246, 0.12)',
    borderColor: themeColors.value.primary,
}))

const resultCardStyle = computed(() => ({
    backgroundColor: themeColors.value.background,
    borderColor: themeColors.value.border,
}))

const shortcutStyle = computed(() => ({
    borderColor: themeColors.value.border,
    color: themeColors.value.textPrimary,
}))

const selectedRoom = computed(() => props.availableRooms.find((room) => room.id === Number(form.room_id)) || null)

const nights = computed(() => {
    if (!form.check_in_date || !form.check_out_date) return 1
    const start = new Date(`${form.check_in_date}T00:00:00`)
    const end = new Date(`${form.check_out_date}T00:00:00`)
    const diff = Math.round((end - start) / 86400000)
    return diff > 0 ? diff : 1
})

const roomCharges = computed(() => (Number(form.room_rate || 0) * nights.value))
const estimatedTotal = computed(() => roomCharges.value)

watch(() => form.room_id, (roomId) => {
    const room = props.availableRooms.find((entry) => entry.id === Number(roomId))
    if (room) {
        form.room_rate = Number(room.room_rate || 0)
    }
})

watch(guestMode, (mode) => {
    form.guest_mode = mode
    if (mode === 'existing') {
        form.first_name = ''
        form.last_name = ''
        form.email = ''
        form.phone = ''
        form.nationality = ''
        form.id_number = ''
        form.address = ''
    } else {
        form.guest_id = ''
        selectedGuest.value = null
        guestSearch.value = ''
        searchResults.value = []
    }
})

watch(guestSearch, (value) => {
    if (guestMode.value !== 'existing') return
    clearTimeout(searchTimer)
    if (value.trim().length < 2) {
        searchResults.value = []
        return
    }
    searchTimer = setTimeout(async () => {
        searching.value = true
        try {
            const response = await fetch(route(`${props.routePrefix}.guests.search`, { q: value.trim() }))
            searchResults.value = await response.json()
        } catch {
            searchResults.value = []
        } finally {
            searching.value = false
        }
    }, 250)
})

const setGuestMode = (mode) => {
    guestMode.value = mode
}

const selectGuest = (guest) => {
    selectedGuest.value = guest
    form.guest_id = guest.id
    guestSearch.value = guest.name
    searchResults.value = []
}

const formatMoney = (amount) => formatCurrency(Number(amount || 0))

const submit = () => {
    form.post(route(`${props.routePrefix}.quick-checkin.store`))
}
</script>