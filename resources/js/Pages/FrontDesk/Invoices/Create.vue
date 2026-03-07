<template>
    <DashboardLayout title="Create Invoice" :user="user" :navigation="navigation">
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Create Invoice</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Create invoice for checked-in guests or outsiders.</p>
                </div>
                <button @click="$inertia.visit(route('front-desk.invoices.index'))"
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
                        <button type="button" @click="$inertia.visit(route('front-desk.invoices.index'))"
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

<script>
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

export default {
    name: 'FrontDeskCreateInvoice',
    props: {
        user: Object,
        navigation: Array,
        reservations: Array,
        errors: Object
    },
    setup(props) {
        const page = usePage();
        const themeColors = computed(() => page.props.themeColors || {
            primary: '#3b82f6',
            success: '#10b981',
            danger: '#ef4444',
            warning: '#f59e0b',
            background: '#ffffff',
            card: '#ffffff',
            border: '#e5e7eb',
            textPrimary: '#111827',
            textSecondary: '#6b7280'
        });

        const form = useForm({
            invoice_type: 'guest',
            reservation_id: '',
            customer_name: '',
            customer_email: '',
            customer_phone: '',
            notes: ''
        });

        const processing = ref(false);

        const switchInvoiceType = () => {
            // Clear form fields when switching type
            form.reservation_id = '';
            form.customer_name = '';
            form.customer_email = '';
            form.customer_phone = '';
        };

        const submitInvoice = () => {
            processing.value = true;
            
            // Validate based on invoice type
            if (form.invoice_type === 'guest' && !form.reservation_id) {
                processing.value = false;
                return;
            }
            if (form.invoice_type === 'outsider' && !form.customer_name) {
                processing.value = false;
                return;
            }
            
            form.post(route('front-desk.invoices.store'), {
                onSuccess: () => {
                    processing.value = false;
                },
                onError: () => {
                    processing.value = false;
                }
            });
        };

        return {
            themeColors,
            form,
            processing,
            switchInvoiceType,
            submitInvoice
        };
    }
};
</script>
