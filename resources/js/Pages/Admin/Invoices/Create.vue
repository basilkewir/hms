<template>
    <DashboardLayout title="Create Invoice" :user="user" :navigation="navigation">
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Create Invoice</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Create invoice for checked-in guests or outsiders.</p>
                </div>
                <button @click="$inertia.visit(route('admin.invoices.index'))"
                        class="px-4 py-2 rounded-md font-medium transition-colors"
                        :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                    ← Back to Invoices
                </button>
            </div>

            <!-- Form -->
            <div class="rounded-lg border p-6 shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <form @submit.prevent="submitInvoice" class="space-y-6">
                    <!-- Invoice Type Selection -->
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                            Invoice Type *
                        </label>
                        <div class="flex gap-4">
                            <label class="flex items-center">
                                <input type="radio" v-model="form.invoice_type" value="guest" 
                                       class="mr-2" @change="switchInvoiceType">
                                <span :style="{ color: themeColors.textPrimary }">Checked-in Guest</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" v-model="form.invoice_type" value="outsider" 
                                       class="mr-2" @change="switchInvoiceType">
                                <span :style="{ color: themeColors.textPrimary }">Outsider</span>
                            </label>
                        </div>
                    </div>

                    <!-- Guest Selection (for checked-in guests) -->
                    <div v-if="form.invoice_type === 'guest'">
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                            Select Checked-in Guest *
                        </label>
                        <select v-model="form.reservation_id" required 
                                class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                            <option value="">Select checked-in guest</option>
                            <option v-for="reservation in reservations" :key="reservation.id" :value="reservation.id">
                                {{ reservation.guest_name }} • Room {{ reservation.room_number }} • Check-in: {{ reservation.check_in_date }}
                            </option>
                        </select>
                        <div v-if="errors.reservation_id" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                            {{ errors.reservation_id }}
                        </div>
                    </div>

                    <!-- Outsider Information -->
                    <div v-if="form.invoice_type === 'outsider'" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                    Customer Name *
                                </label>
                                <input v-model="form.customer_name" type="text" required
                                       class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                       :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                       placeholder="Enter customer name">
                                <div v-if="errors.customer_name" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ errors.customer_name }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                    Email
                                </label>
                                <input v-model="form.customer_email" type="email"
                                       class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                       :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                       placeholder="customer@example.com">
                                <div v-if="errors.customer_email" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ errors.customer_email }}
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                Phone
                            </label>
                            <input v-model="form.customer_phone" type="tel"
                                   class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                   placeholder="+1234567890">
                            <div v-if="errors.customer_phone" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                {{ errors.customer_phone }}
                            </div>
                        </div>

                        <!-- Custom Items -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium" :style="{ color: themeColors.textPrimary }">
                                    Invoice Items *
                                </label>
                                <button type="button" @click="addItem"
                                        class="px-3 py-1 text-sm rounded-md transition-colors"
                                        :style="{ backgroundColor: themeColors.primary, color: 'white' }">
                                    + Add Item
                                </button>
                            </div>
                            <div v-if="form.items.length === 0" class="text-center py-4 border-2 border-dashed rounded-md"
                                 :style="{ borderColor: themeColors.border }">
                                <p class="text-sm" :style="{ color: themeColors.textSecondary }">No items added. Click "Add Item" to add invoice items.</p>
                            </div>
                            <div v-for="(item, index) in form.items" :key="index" class="space-y-2 mb-3 p-3 border rounded-md"
                                 :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">Item {{ index + 1 }}</h4>
                                    <button type="button" @click="removeItem(index)"
                                            class="text-sm transition-colors"
                                            :style="{ color: themeColors.danger }">
                                        Remove
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">
                                            Description *
                                        </label>
                                        <input v-model="item.description" type="text" required
                                               class="w-full px-2 py-1 border rounded text-sm focus:outline-none"
                                               :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                               placeholder="Item description">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">
                                            Amount *
                                        </label>
                                        <input v-model="item.amount" type="number" step="0.01" min="0" required
                                               class="w-full px-2 py-1 border rounded text-sm focus:outline-none"
                                               :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                               placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                            <div v-if="errors.items" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                {{ errors.items }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                            Notes
                        </label>
                        <textarea v-model="form.notes" rows="4" 
                                  class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                  :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                  placeholder="Additional notes for this invoice..."></textarea>
                        <div v-if="errors.notes" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                            {{ errors.notes }}
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" :disabled="processing"
                                class="px-4 py-2 rounded-md font-medium text-white transition-colors disabled:opacity-50"
                                :style="{ backgroundColor: themeColors.primary }">
                            <span v-if="processing">Creating...</span>
                            <span v-else>Create Invoice</span>
                        </button>
                        <button type="button" @click="$inertia.visit(route('admin.invoices.index'))"
                                class="px-4 py-2 rounded-md font-medium transition-colors"
                                :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useTheme } from '@/Composables/useTheme.js';

const { loadTheme } = useTheme();
loadTheme();

const themeColors = computed(() => ({
    primary: 'var(--kotel-primary)',
    success: 'var(--kotel-success)',
    danger: 'var(--kotel-danger)',
    warning: 'var(--kotel-warning)',
    background: 'var(--kotel-background)',
    card: 'var(--kotel-card)',
    border: 'var(--kotel-border)',
    textPrimary: 'var(--kotel-text-primary)',
    textSecondary: 'var(--kotel-text-secondary)'
}));

const props = defineProps({
    user: Object,
    navigation: Array,
    reservations: Array,
    errors: Object
});

const form = useForm({
    invoice_type: 'guest',
    reservation_id: '',
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    items: [],
    notes: ''
});

const processing = ref(false);

const switchInvoiceType = () => {
    // Clear form fields when switching type
    form.reservation_id = '';
    form.customer_name = '';
    form.customer_email = '';
    form.customer_phone = '';
    form.items = [];
};

const addItem = () => {
    form.items.push({
        description: '',
        amount: 0
    });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const submitInvoice = () => {
    processing.value = true;
    
    // Validate based on invoice type
    if (form.invoice_type === 'guest' && !form.reservation_id) {
        processing.value = false;
        return;
    }
    if (form.invoice_type === 'outsider') {
        if (!form.customer_name) {
            processing.value = false;
            return;
        }
        if (form.items.length === 0) {
            processing.value = false;
            return;
        }
        // Validate items
        for (let item of form.items) {
            if (!item.description || item.amount <= 0) {
                processing.value = false;
                return;
            }
        }
    }
    
    form.post(route('admin.invoices.store'), {
        onSuccess: () => {
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        }
    });
};
</script>
