<template>
    <DashboardLayout title="Create Quote" :user="user" :navigation="navigation">
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Create Quote</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">Create quote for checked-in guests or outsiders.</p>
                </div>
                <button @click="$inertia.visit(route('admin.quotes.index'))"
                        class="px-4 py-2 rounded-md font-medium transition-colors"
                        :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                    ← Back to Quotes
                </button>
            </div>

            <!-- Form -->
            <div class="rounded-lg border p-6 shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <form @submit.prevent="submitQuote" class="space-y-6">
                    <!-- Quote Type Selection -->
                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                            Quote Type *
                        </label>
                        <div class="flex gap-4">
                            <label class="flex items-center">
                                <input type="radio" v-model="form.quote_type" value="guest" 
                                       class="mr-2" @change="switchQuoteType">
                                <span :style="{ color: themeColors.textPrimary }">Checked-in Guest</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" v-model="form.quote_type" value="outsider" 
                                       class="mr-2" @change="switchQuoteType">
                                <span :style="{ color: themeColors.textPrimary }">Outsider</span>
                            </label>
                        </div>
                    </div>

                    <!-- Guest Selection (for checked-in guests) -->
                    <div v-if="form.quote_type === 'guest'">
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
                    <div v-if="form.quote_type === 'outsider'" class="space-y-4">
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
                                    Email *
                                </label>
                                <input v-model="form.customer_email" type="email" required
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

                    <!-- Quote Details -->
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                    Valid Until *
                                </label>
                                <input v-model="form.valid_until" type="date" required
                                       class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                       :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                                <div v-if="errors.valid_until" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ errors.valid_until }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                    Total Amount *
                                </label>
                                <input v-model="form.total_amount" type="number" step="0.01" min="0" required
                                       class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                       :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                       placeholder="0.00">
                                <div v-if="errors.total_amount" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                                    {{ errors.total_amount }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                    Status
                                </label>
                                <select v-model="form.status"
                                        class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                        :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                                    <option value="draft">Draft</option>
                                    <option value="sent">Sent</option>
                                </select>
                            </div>
                        </div>

                        <!-- Quote Items -->
                        <div>
                            <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                                Quote Items
                            </label>
                            <div v-for="(item, index) in form.items" :key="index" class="space-y-2 mb-4 p-4 border rounded-md"
                                 :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                                    <input v-model="item.description" type="text" placeholder="Item description"
                                           class="px-3 py-2 border rounded-md text-sm focus:outline-none"
                                           :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                                    <input v-model="item.quantity" type="number" min="1" placeholder="Qty"
                                           class="px-3 py-2 border rounded-md text-sm focus:outline-none"
                                           :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                                    <input v-model="item.unit_price" type="number" step="0.01" min="0" placeholder="Unit price"
                                           class="px-3 py-2 border rounded-md text-sm focus:outline-none"
                                           :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }">
                                    <button type="button" @click="removeItem(index)"
                                            class="px-3 py-2 rounded-md text-sm font-medium text-white"
                                            :style="{ backgroundColor: themeColors.danger }">
                                        Remove
                                    </button>
                                </div>
                            </div>
                            <button type="button" @click="addItem"
                                    class="px-4 py-2 rounded-md text-sm font-medium text-white"
                                    :style="{ backgroundColor: themeColors.primary }">
                                + Add Item
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" :style="{ color: themeColors.textPrimary }">
                            Notes
                        </label>
                        <textarea v-model="form.notes" rows="4" 
                                  class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none"
                                  :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid', color: themeColors.textPrimary }"
                                  placeholder="Additional notes for this quote..."></textarea>
                        <div v-if="errors.notes" class="mt-1 text-sm" :style="{ color: themeColors.danger }">
                            {{ errors.notes }}
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" :disabled="processing"
                                class="px-4 py-2 rounded-md font-medium text-white transition-colors disabled:opacity-50"
                                :style="{ backgroundColor: themeColors.primary }">
                            <span v-if="processing">Creating...</span>
                            <span v-else>Create Quote</span>
                        </button>
                        <button type="button" @click="$inertia.visit(route('admin.quotes.index'))"
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
    name: 'AdminCreateQuote',
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
            quote_type: 'guest',
            reservation_id: '',
            customer_name: '',
            customer_email: '',
            customer_phone: '',
            valid_until: '',
            total_amount: '',
            status: 'draft',
            notes: '',
            items: [
                { description: '', quantity: 1, unit_price: 0 }
            ]
        });

        const processing = ref(false);

        const switchQuoteType = () => {
            // Clear form fields when switching type
            form.reservation_id = '';
            form.customer_name = '';
            form.customer_email = '';
            form.customer_phone = '';
        };

        const addItem = () => {
            form.items.push({ description: '', quantity: 1, unit_price: 0 });
        };

        const removeItem = (index) => {
            if (form.items.length > 1) {
                form.items.splice(index, 1);
            }
        };

        const submitQuote = () => {
            processing.value = true;
            
            // Validate based on quote type
            if (form.quote_type === 'guest' && !form.reservation_id) {
                processing.value = false;
                return;
            }
            if (form.quote_type === 'outsider' && (!form.customer_name || !form.customer_email)) {
                processing.value = false;
                return;
            }
            
            form.post(route('admin.quotes.store'), {
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
            switchQuoteType,
            addItem,
            removeItem,
            submitQuote
        };
    }
};
</script>
