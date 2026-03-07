<template>
    <DashboardLayout title="Supplies" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Supplies</h1>
                    <p class="text-gray-600 mt-2">Manage inventory supplies and stock levels.</p>
                </div>
                <div class="flex gap-3">
                    <button
                        @click="showAddModal = true"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Add Supply
                    </button>
                    <button
                        @click="showImportModal = true"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Import Supplies
                    </button>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search supplies..."
                        class="w-full border-gray-300 rounded-md shadow-sm"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select v-model="categoryFilter" class="w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">All Categories</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                    <select v-model="supplierFilter" class="w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">All Suppliers</option>
                        <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Stock Status</label>
                    <select v-model="stockFilter" class="w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">All Statuses</option>
                        <option value="low">Low Stock</option>
                        <option value="out">Out of Stock</option>
                        <option value="good">Good Stock</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button @click="clearFilters" class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Supplies Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div v-for="supply in filteredSupplies" :key="supply.id" class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ supply.name }}</h3>
                            <p class="text-sm text-gray-600 mb-2">{{ supply.description }}</p>
                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                <span><strong>Code:</strong> {{ supply.code }}</span>
                                <span><strong>Category:</strong> {{ supply.category?.name }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-gray-900">{{ formatCurrency(supply.cost_price) }}</div>
                            <div class="text-sm text-gray-500">Unit Cost</div>
                        </div>
                    </div>

                    <!-- Stock Information -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="bg-gray-50 p-3 rounded">
                            <div class="text-sm font-medium text-gray-700">Current Stock</div>
                            <div class="text-lg font-semibold text-gray-900">{{ supply.stock_quantity }}</div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded">
                            <div class="text-sm font-medium text-gray-700">Reorder Level</div>
                            <div class="text-lg font-semibold text-gray-900">{{ supply.reorder_level }}</div>
                        </div>
                    </div>

                    <!-- Stock Status Badge -->
                    <div class="mb-4">
                        <span :class="getStockStatusClass(supply)" class="px-3 py-1 text-sm font-medium rounded-full">
                            {{ getStockStatusText(supply) }}
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <button @click="editSupply(supply)" class="flex-1 bg-blue-600 text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                            Edit
                        </button>
                        <button @click="adjustStock(supply)" class="flex-1 bg-yellow-600 text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-yellow-700">
                            Adjust Stock
                        </button>
                        <button @click="viewHistory(supply)" class="flex-1 bg-gray-600 text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">
                            History
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="filteredSupplies.length === 0" class="bg-white shadow rounded-lg p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-4h-2v4m0 0h-2m-4-4v4m-2-4v.01" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No supplies found</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by adding a new supply.</p>
            <div class="mt-6">
                <button
                    @click="showAddModal = true"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                >
                    Add Supply
                </button>
            </div>
        </div>

        <!-- Add/Edit Supply Modal -->
        <DialogModal :show="showAddModal || showEditModal" @close="closeModal" max-width="2xl">
            <template #title>{{ showAddModal ? 'Add Supply' : 'Edit Supply' }}</template>
            <template #content>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Supply Name *</label>
                            <input v-model="supplyForm.name" type="text" class="w-full border-gray-300 rounded-md shadow-sm" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Code *</label>
                            <input v-model="supplyForm.code" type="text" class="w-full border-gray-300 rounded-md shadow-sm" required />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea v-model="supplyForm.description" rows="3" class="w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select v-model="supplyForm.category_id" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select category...</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                            <select v-model="supplyForm.supplier_id" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select supplier...</option>
                                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Unit Cost</label>
                            <input v-model.number="supplyForm.cost_price" type="number" step="0.01" min="0" class="w-full border-gray-300 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Initial Stock</label>
                            <input v-model.number="supplyForm.stock_quantity" type="number" min="0" class="w-full border-gray-300 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reorder Level</label>
                            <input v-model.number="supplyForm.reorder_level" type="number" min="0" class="w-full border-gray-300 rounded-md shadow-sm" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Unit of Measure</label>
                            <select v-model="supplyForm.unit_of_measure" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="pieces">Pieces</option>
                                <option value="liters">Liters</option>
                                <option value="kilograms">Kilograms</option>
                                <option value="boxes">Boxes</option>
                                <option value="packs">Packs</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Barcode</label>
                            <input v-model="supplyForm.barcode" type="text" class="w-full border-gray-300 rounded-md shadow-sm" />
                        </div>
                    </div>
                </div>
            </template>
            <template #footer>
                <button @click="closeModal" class="mr-3 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button @click="saveSupply" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                    {{ showAddModal ? 'Create Supply' : 'Update Supply' }}
                </button>
            </template>
        </DialogModal>

        <!-- Adjust Stock Modal -->
        <DialogModal :show="showAdjustModal" @close="showAdjustModal = false">
            <template #title>Adjust Stock: {{ adjustingSupply?.name }}</template>
            <template #content>
                <div v-if="adjustingSupply" class="space-y-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Current Stock:</span>
                                <div class="font-medium">{{ adjustingSupply.stock_quantity }}</div>
                            </div>
                            <div>
                                <span class="text-gray-600">Reorder Level:</span>
                                <div class="font-medium">{{ adjustingSupply.reorder_level }}</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Adjustment Type</label>
                        <select v-model="adjustmentForm.type" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="add">Add Stock</option>
                            <option value="remove">Remove Stock</option>
                            <option value="set">Set Exact Quantity</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                        <input v-model.number="adjustmentForm.quantity" type="number" min="1" class="w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                        <textarea v-model="adjustmentForm.reason" rows="3" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Enter reason for adjustment..."></textarea>
                    </div>
                </div>
            </template>
            <template #footer>
                <button @click="showAdjustModal = false" class="mr-3 px-4 py-2 border border-gray-300 rounded-md">Cancel</button>
                <button @click="submitAdjustment" class="px-4 py-2 bg-green-600 text-white rounded-md">Adjust Stock</button>
            </template>
        </DialogModal>

        <!-- View History Modal -->
        <DialogModal :show="showHistoryModal" @close="showHistoryModal = false" max-width="3xl">
            <template #title>Stock History: {{ viewingSupply?.name }}</template>
            <template #content>
                <div v-if="viewingSupply" class="space-y-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="movement in viewingSupply.movements" :key="movement.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(movement.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getMovementTypeClass(movement.type)" class="px-2 py-1 text-xs font-medium rounded-full">
                                            {{ movement.type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ movement.quantity_change > 0 ? '+' : '' }}{{ movement.quantity_change }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ movement.new_balance }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ movement.reason || '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ movement.user?.name || 'System' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="viewingSupply.movements.length === 0" class="text-center py-4 text-gray-500">
                        No stock movements recorded.
                    </div>
                </div>
            </template>
            <template #footer>
                <button @click="showHistoryModal = false" class="px-4 py-2 border border-gray-300 rounded-md">Close</button>
            </template>
        </DialogModal>

        <!-- Import Supplies Modal -->
        <DialogModal :show="showImportModal" @close="showImportModal = false">
            <template #title>Import Supplies</template>
            <template #content>
                <div class="space-y-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                        <h4 class="font-medium text-blue-900 mb-2">Import Format</h4>
                        <p class="text-sm text-blue-800">Download the template file and fill it with your supply data. Required fields: name, code, category_id, supplier_id, cost_price, stock_quantity.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Template File</label>
                        <button @click="downloadTemplate" class="text-blue-600 hover:text-blue-900">Download Template (CSV)</button>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Import File</label>
                        <input type="file" @change="handleFileChange" accept=".csv,.xlsx" class="w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                </div>
            </template>
            <template #footer>
                <button @click="showImportModal = false" class="mr-3 px-4 py-2 border border-gray-300 rounded-md">Cancel</button>
                <button @click="importSupplies" class="px-4 py-2 bg-green-600 text-white rounded-md">Import</button>
            </template>
        </DialogModal>
    </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';
import { router, usePage } from '@inertiajs/vue3';
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { notify } from '@/Composables/useNotification.js';
import { formatCurrency, initializeCurrencySettings } from '@/Utils/currency.js';
import { getNavigationForRole } from '@/Utils/navigation.js';

const props = defineProps({
    user: Object,
    supplies: Array,
    categories: Array,
    suppliers: Array
});

const page = usePage();
// Use page user (from controller) or shared auth.user so layout always has user for all roles
const user = computed(() => props.user ?? page.props.auth?.user ?? {});

// Resolve role: from user.roles, or shared auth.roles (array of role name strings), or 'admin'
const roleFromUser = computed(() => {
    const u = user.value;
    let roleName = '';
    if (u?.roles?.length && u.roles[0]) {
        roleName = (u.roles[0].name ?? u.roles[0]).toString();
    } else if (page.props.auth?.roles?.length) {
        roleName = String(page.props.auth.roles[0] ?? '');
    }
    const normalized = roleName.toLowerCase().replace(/\s+/g, '_');
    return normalized || 'admin';
});

// Sidebar: always use getNavigationForRole; fallback to admin nav so menu is never empty
const navigation = computed(() => {
    const role = roleFromUser.value;
    const nav = getNavigationForRole(role);
    if (Array.isArray(nav) && nav.length > 0) return nav;
    return getNavigationForRole('admin');
});

const showAddModal = ref(false);
const showEditModal = ref(false);
const showAdjustModal = ref(false);
const showHistoryModal = ref(false);
const showImportModal = ref(false);
const adjustingSupply = ref(null);
const viewingSupply = ref(null);
const searchQuery = ref('');
const categoryFilter = ref('');
const supplierFilter = ref('');
const stockFilter = ref('');

const supplyForm = reactive({
    name: '',
    code: '',
    description: '',
    category_id: '',
    supplier_id: '',
    cost_price: 0,
    stock_quantity: 0,
    reorder_level: 0,
    unit_of_measure: 'pieces',
    barcode: ''
});

const adjustmentForm = reactive({
    type: 'add',
    quantity: 0,
    reason: ''
});

const importForm = reactive({
    file: null
});

const filteredSupplies = computed(() => {
    let filtered = props.supplies || [];

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(supply =>
            supply.name.toLowerCase().includes(query) ||
            supply.code.toLowerCase().includes(query) ||
            supply.description.toLowerCase().includes(query)
        );
    }

    if (categoryFilter.value) {
        filtered = filtered.filter(supply => supply.category_id === categoryFilter.value);
    }

    if (supplierFilter.value) {
        filtered = filtered.filter(supply => supply.supplier_id === supplierFilter.value);
    }

    if (stockFilter.value) {
        filtered = filtered.filter(supply => {
            const status = getStockStatus(supply);
            return status === stockFilter.value;
        });
    }

    return filtered;
});

const editSupply = (supply) => {
    Object.assign(supplyForm, {
        id: supply.id,
        name: supply.name,
        code: supply.code,
        description: supply.description,
        category_id: supply.category_id,
        supplier_id: supply.supplier_id,
        cost_price: supply.cost_price,
        stock_quantity: supply.stock_quantity,
        reorder_level: supply.reorder_level,
        unit_of_measure: supply.unit_of_measure,
        barcode: supply.barcode
    });
    showEditModal.value = true;
};

const adjustStock = (supply) => {
    adjustingSupply.value = supply;
    adjustmentForm.type = 'add';
    adjustmentForm.quantity = 0;
    adjustmentForm.reason = '';
    showAdjustModal.value = true;
};

const viewHistory = (supply) => {
    viewingSupply.value = supply;
    showHistoryModal.value = true;
};

const saveSupply = () => {
    if (showAddModal.value) {
        router.post(route('pos.supplies.store'), supplyForm, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                notify.success('Supply created successfully');
            }
        });
    } else {
        router.put(route('pos.supplies.update', supplyForm.id), supplyForm, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                notify.success('Supply updated successfully');
            }
        });
    }
};

const submitAdjustment = () => {
    router.post(route('pos.supplies.adjust-stock', adjustingSupply.value.id), adjustmentForm, {
        preserveScroll: true,
        onSuccess: () => {
            showAdjustModal.value = false;
            adjustingSupply.value = null;
            notify.success('Stock adjusted successfully');
        }
    });
};

const downloadTemplate = () => {
    // Create a CSV template
    const headers = ['name', 'code', 'description', 'category_id', 'supplier_id', 'cost_price', 'stock_quantity', 'reorder_level', 'unit_of_measure', 'barcode'];
    const csv = headers.join(',') + '\n';

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'supplies_template.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
};

const handleFileChange = (event) => {
    importForm.file = event.target.files[0];
};

const importSupplies = () => {
    const formData = new FormData();
    formData.append('file', importForm.file);

    router.post(route('pos.supplies.import'), formData, {
        preserveScroll: true,
        onSuccess: () => {
            showImportModal.value = false;
            importForm.file = null;
            notify.success('Supplies imported successfully');
        }
    });
};

const closeModal = () => {
    showAddModal.value = false;
    showEditModal.value = false;
    Object.assign(supplyForm, {
        id: null,
        name: '',
        code: '',
        description: '',
        category_id: '',
        supplier_id: '',
        cost_price: 0,
        stock_quantity: 0,
        reorder_level: 0,
        unit_of_measure: 'pieces',
        barcode: ''
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    categoryFilter.value = '';
    supplierFilter.value = '';
    stockFilter.value = '';
};

const getStockStatus = (supply) => {
    if (supply.stock_quantity === 0) return 'out';
    if (supply.stock_quantity <= supply.reorder_level) return 'low';
    return 'good';
};

const getStockStatusText = (supply) => {
    const status = getStockStatus(supply);
    const texts = {
        'out': 'Out of Stock',
        'low': 'Low Stock',
        'good': 'Good Stock'
    };
    return texts[status] || 'Unknown';
};

const getStockStatusClass = (supply) => {
    const status = getStockStatus(supply);
    const classes = {
        'out': 'bg-red-100 text-red-800',
        'low': 'bg-yellow-100 text-yellow-800',
        'good': 'bg-green-100 text-green-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getMovementTypeClass = (type) => {
    const classes = {
        'add': 'bg-green-100 text-green-800',
        'remove': 'bg-red-100 text-red-800',
        'adjust': 'bg-blue-100 text-blue-800'
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
};

// Initialize currency settings on mount
onMounted(() => {
    initializeCurrencySettings();
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};
</script>
