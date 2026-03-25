<template>
    <DashboardLayout title="Products Management" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderWidth: '1px',
                 borderStyle: 'solid'
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">📦 Products Management</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Manage inventory with cost & profit tracking.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="showCreateModal = true" 
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        ➕ Add Product
                    </button>
                    <button @click="deleteAllProducts"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ backgroundColor: '#dc2626' }"
                            @mouseenter="$event.target.style.backgroundColor = '#b91c1c'"
                            @mouseleave="$event.target.style.backgroundColor = '#dc2626'">
                        🗑️ Delete All
                    </button>
                    <button @click="exportProducts"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ 
                                backgroundColor: themeColors.success,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.success + 'dd'"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                        📥 Export Products
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         style="background-color: rgba(59,130,246,0.1)">
                        <ArchiveBoxIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Total Products</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ props.products.length }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         style="background-color: rgba(34,197,94,0.1)">
                        <CurrencyDollarIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Total Value</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(totalValue) }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         style="background-color: rgba(250,204,21,0.1)">
                        <ExclamationTriangleIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Low Stock</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ lowStockCount }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         style="background-color: rgba(139,92,246,0.1)">
                        <ArrowTrendingUpIcon class="h-6 w-6" style="color: #8b5cf6" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Total Profit</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ formatCurrency(totalProfit) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderWidth: '1px',
                 borderStyle: 'solid'
             }">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <input 
                            v-model="searchQuery" 
                            type="text" 
                            placeholder="Search by name, code, or barcode..." 
                            class="w-full px-4 py-2 pr-10 rounded-md border"
                            :style="{ 
                                borderColor: themeColors.border,
                                backgroundColor: themeColors.background,
                                color: themeColors.textPrimary
                            }"
                            @focus="handleBarcodeFocus"
                        />
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <span class="text-sm">🔍</span>
                        </div>
                    </div>
                </div>
                <select 
                    v-model="selectedCategory" 
                    class="px-4 py-2 rounded-md border"
                    :style="{ 
                        borderColor: themeColors.border,
                        backgroundColor: themeColors.background,
                        color: themeColors.textPrimary
                    }">
                    <option value="">All Categories</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>
                <select 
                    v-model="stockFilter" 
                    class="px-4 py-2 rounded-md border"
                    :style="{ 
                        borderColor: themeColors.border,
                        backgroundColor: themeColors.background,
                        color: themeColors.textPrimary
                    }">
                    <option value="">All Stock Levels</option>
                    <option value="low">Low Stock</option>
                    <option value="out">Out of Stock</option>
                    <option value="available">Available</option>
                </select>
            </div>
        </div>

        <!-- Products Table -->
        <div class="shadow rounded-lg overflow-hidden"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderWidth: '1px',
                 borderStyle: 'solid'
             }">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="product in paginatedProducts" :key="product.id"
                            class="border-t transition-colors hover:bg-opacity-50"
                            :style="{ 
                                borderColor: themeColors.border,
                                backgroundColor: product.stock_quantity <= 10 ? '#fef2f2' : 'transparent'
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.background + '50'"
                            @mouseleave="$event.target.style.backgroundColor = product.stock_quantity <= 10 ? '#fef2f2' : 'transparent'">
                            <td class="px-6 py-4">
                                <div>
                                    <div class="font-medium"
                                         :style="{ color: themeColors.textPrimary }">{{ product.name }}</div>
                                    <div class="text-sm"
                                         :style="{ color: themeColors.textSecondary }">{{ product.description || 'No description' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4"
                                :style="{ color: themeColors.textSecondary }">{{ product.category?.name || 'Uncategorized' }}</td>
                            <td class="px-6 py-4 font-medium"
                                :style="{ color: themeColors.textPrimary }">{{ formatCurrency(product.price) }}</td>
                            <td class="px-6 py-4">
                                <span class="font-medium"
                                      :class="product.stock_quantity <= 10 ? 'text-red-600' : 'text-green-600'">
                                    {{ product.stock_quantity }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium"
                                :style="{ color: themeColors.textPrimary }">{{ formatCurrency(product.price * product.stock_quantity) }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full"
                                      :class="{
                                          'bg-green-100 text-green-800': product.stock_quantity > 10,
                                          'bg-yellow-100 text-yellow-800': product.stock_quantity > 0 && product.stock_quantity <= 10,
                                          'bg-red-100 text-red-800': product.stock_quantity === 0
                                      }">
                                    {{ product.stock_quantity === 0 ? 'Out of Stock' : product.stock_quantity <= 10 ? 'Low Stock' : 'Available' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button @click="editProduct(product)" 
                                            class="text-blue-600 hover:text-blue-800 text-sm">
                                        Edit
                                    </button>
                                    <button @click="adjustStock(product)" 
                                            class="text-green-600 hover:text-green-800 text-sm">
                                        Adjust
                                    </button>
                                    <button @click="deleteProduct(product.id)"
                                            class="text-red-500 hover:text-red-700 text-sm">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div v-if="totalPages > 1" class="px-6 py-4 border-t flex items-center justify-between"
                 :style="{ borderColor: themeColors.border }">
                <div class="text-sm text-gray-700">
                    Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, filteredProducts.length) }} 
                    of {{ filteredProducts.length }} results
                </div>
                <div class="flex space-x-2">
                    <button @click="currentPage = Math.max(1, currentPage - 1)"
                            :disabled="currentPage === 1"
                            class="px-3 py-1 text-sm border rounded-md transition-colors"
                            :style="{
                                backgroundColor: currentPage === 1 ? themeColors.background : themeColors.card,
                                borderColor: themeColors.border,
                                color: currentPage === 1 ? themeColors.textTertiary : themeColors.textPrimary,
                                opacity: currentPage === 1 ? 0.5 : 1
                            }">
                        Previous
                    </button>
                    <span class="px-3 py-1 text-sm"
                          :style="{ color: themeColors.textPrimary }">
                        Page {{ currentPage }} of {{ totalPages }}
                    </span>
                    <button @click="currentPage = Math.min(totalPages, currentPage + 1)"
                            :disabled="currentPage === totalPages"
                            class="px-3 py-1 text-sm border rounded-md transition-colors"
                            :style="{
                                backgroundColor: currentPage === totalPages ? themeColors.background : themeColors.card,
                                borderColor: themeColors.border,
                                color: currentPage === totalPages ? themeColors.textTertiary : themeColors.textPrimary,
                                opacity: currentPage === totalPages ? 0.5 : 1
                            }">
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- Create Product Modal -->
        <div v-if="showCreateModal" 
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             @click="closeAllModals">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
                 :style="{ backgroundColor: themeColors.card }"
                 @click.stop>
                <div class="p-6 border-b"
                     :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Add New Product</h3>
                    <p class="text-sm mt-1"
                       :style="{ color: themeColors.textSecondary }">Create a new product for inventory management</p>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Basic Information -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Product Name *</label>
                            <input type="text" 
                                   v-model="newProduct.name"
                                   class="w-full px-3 py-2 rounded-md border"
                                   :style="{ 
                                       borderColor: themeColors.border, 
                                       backgroundColor: themeColors.background,
                                       color: themeColors.textPrimary
                                   }"
                                   placeholder="Enter product name">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Product Code</label>
                            <input type="text" 
                                   v-model="newProduct.code"
                                   class="w-full px-3 py-2 rounded-md border"
                                   :style="{ 
                                       borderColor: themeColors.border, 
                                       backgroundColor: themeColors.background,
                                       color: themeColors.textPrimary
                                   }"
                                   placeholder="SKU-001">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Barcode</label>
                            <input type="text" 
                                   v-model="newProduct.barcode"
                                   class="w-full px-3 py-2 rounded-md border"
                                   :style="{ 
                                       borderColor: themeColors.border, 
                                       backgroundColor: themeColors.background,
                                       color: themeColors.textPrimary
                                   }"
                                   placeholder="1234567890123"
                                   @focus="handleBarcodeFocus">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Description</label>
                            <textarea v-model="newProduct.description"
                                      class="w-full px-3 py-2 rounded-md border"
                                      :style="{ 
                                          borderColor: themeColors.border, 
                                          backgroundColor: themeColors.background,
                                          color: themeColors.textPrimary
                                      }"
                                      rows="3"
                                      placeholder="Product description"></textarea>
                        </div>
                        
                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Category</label>
                            <select v-model="newProduct.category_id"
                                    class="w-full px-3 py-2 rounded-md border"
                                    :style="{ 
                                        borderColor: themeColors.border, 
                                        backgroundColor: themeColors.background,
                                        color: themeColors.textPrimary
                                    }">
                                <option value="">Select Category</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>
                        
                        <!-- Pricing -->
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Sale Price *</label>
                            <input type="number" 
                                   v-model="newProduct.price"
                                   step="0.01"
                                   class="w-full px-3 py-2 rounded-md border"
                                   :style="{ 
                                       borderColor: themeColors.border, 
                                       backgroundColor: themeColors.background,
                                       color: themeColors.textPrimary
                                   }"
                                   placeholder="0.00">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Cost Price (Purchase Price)</label>
                            <input type="number" 
                                   v-model="newProduct.cost_price"
                                   step="0.01"
                                   class="w-full px-3 py-2 rounded-md border"
                                   :style="{ 
                                       borderColor: themeColors.border, 
                                       backgroundColor: themeColors.background,
                                       color: themeColors.textPrimary
                                   }"
                                   placeholder="0.00">
                        </div>

                        <!-- Live Margin Display -->
                        <div class="md:col-span-2">
                            <div v-if="newProductMargin !== null" class="flex items-center gap-3 px-4 py-2 rounded-lg"
                                 :style="{ backgroundColor: themeColors.success + '18', borderColor: themeColors.success, borderWidth: '1px', borderStyle: 'solid' }">
                                <span class="text-xs font-medium" :style="{ color: themeColors.textSecondary }">Markup on Cost:</span>
                                <span class="text-sm font-bold" :style="{ color: themeColors.success }">{{ newProductMargin }}%</span>
                                <span class="text-xs" :style="{ color: themeColors.textSecondary }">— this % will be preserved when PO prices change</span>
                            </div>
                            <p v-else class="text-xs" :style="{ color: themeColors.textTertiary }">Enter both cost and sale price to see markup %</p>
                        </div>

                        <!-- Stock -->
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Stock Quantity *</label>
                            <input type="number" 
                                   v-model="newProduct.stock_quantity"
                                   class="w-full px-3 py-2 rounded-md border"
                                   :style="{ 
                                       borderColor: themeColors.border, 
                                       backgroundColor: themeColors.background,
                                       color: themeColors.textPrimary
                                   }"
                                   placeholder="0">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Min Stock Level</label>
                            <input type="number" 
                                   v-model="newProduct.min_stock_level"
                                   class="w-full px-3 py-2 rounded-md border"
                                   :style="{ 
                                       borderColor: themeColors.border, 
                                       backgroundColor: themeColors.background,
                                       color: themeColors.textPrimary
                                   }"
                                   placeholder="5">
                        </div>
                        
                        <!-- Additional Fields -->
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Unit</label>
                            <select v-model="newProduct.unit"
                                    class="w-full px-3 py-2 rounded-md border"
                                    :style="{ 
                                        borderColor: themeColors.border, 
                                        backgroundColor: themeColors.background,
                                        color: themeColors.textPrimary
                                    }">
                                <option value="">Select Unit</option>
                                <option value="pieces">Pieces</option>
                                <option value="kg">Kilograms</option>
                                <option value="liters">Liters</option>
                                <option value="meters">Meters</option>
                                <option value="boxes">Boxes</option>
                                <option value="bottles">Bottles</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Tax Rate (%)</label>
                            <input type="number" 
                                   v-model="newProduct.tax_rate"
                                   step="0.01"
                                   class="w-full px-3 py-2 rounded-md border"
                                   :style="{ 
                                       borderColor: themeColors.border, 
                                       backgroundColor: themeColors.background,
                                       color: themeColors.textPrimary
                                   }"
                                   placeholder="0.00">
                        </div>
                        
                        <!-- Emoji and Status -->
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Emoji</label>
                            <input type="text" 
                                   v-model="newProduct.emoji"
                                   maxlength="2"
                                   class="w-full px-3 py-2 rounded-md border"
                                   :style="{ 
                                       borderColor: themeColors.border, 
                                       backgroundColor: themeColors.background,
                                       color: themeColors.textPrimary
                                   }"
                                   placeholder="📦">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Status</label>
                            <select v-model="newProduct.is_active"
                                    class="w-full px-3 py-2 rounded-md border"
                                    :style="{ 
                                        borderColor: themeColors.border, 
                                        backgroundColor: themeColors.background,
                                        color: themeColors.textPrimary
                                    }">
                                <option :value="true">Active</option>
                                <option :value="false">Inactive</option>
                            </select>
                        </div>
                        
                        <!-- Service Type -->
                        <div class="md:col-span-2">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       v-model="newProduct.is_service"
                                       class="mr-2">
                                <span class="text-sm font-medium"
                                      :style="{ color: themeColors.textSecondary }">This is a service (not a physical product)</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="p-6 border-t flex justify-end space-x-3"
                     :style="{ borderColor: themeColors.border }">
                    <button @click="closeAllModals"
                            class="px-4 py-2 rounded-md font-medium transition-colors"
                            :style="{ 
                                backgroundColor: 'transparent',
                                color: themeColors.textSecondary,
                                border: `1px solid ${themeColors.border}`
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.background"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                        Cancel
                    </button>
                    <button @click="createProduct"
                            class="px-4 py-2 rounded-md font-medium text-white transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        Add Product
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Product Modal -->
        <div v-if="showEditModal && selectedProduct" 
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             @click="closeAllModals">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
                 :style="{ backgroundColor: themeColors.card }"
                 @click.stop>
                <div class="p-6 border-b"
                     :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-semibold"
                        :style="{ color: themeColors.textPrimary }">Edit Product</h3>
                    <p class="text-sm mt-1"
                       :style="{ color: themeColors.textSecondary }">Update product information and inventory details</p>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Basic Information -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Product Name *</label>
                            <input type="text" 
                                   v-model="selectedProduct.name"
                                   class="w-full px-3 py-2 rounded-md border"
                                   :style="{ 
                                       borderColor: themeColors.border, 
                                       backgroundColor: themeColors.background,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Product Code</label>
                            <input type="text" 
                                   v-model="selectedProduct.code"
                                   class="w-full px-3 py-2 rounded-md border"
                                   :style="{ 
                                       borderColor: themeColors.border, 
                                       backgroundColor: themeColors.background,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Barcode</label>
                            <input type="text" 
                                   v-model="selectedProduct.barcode"
                                   class="w-full px-3 py-2 rounded-md border"
                                   :style="{ 
                                       borderColor: themeColors.border, 
                                       backgroundColor: themeColors.background,
                                       color: themeColors.textPrimary
                                   }"
                                   @focus="handleBarcodeFocus">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Description</label>
                            <textarea v-model="selectedProduct.description"
                                      class="w-full px-3 py-2 rounded-md border"
                                      :style="{ 
                                          borderColor: themeColors.border, 
                                          backgroundColor: themeColors.background,
                                          color: themeColors.textPrimary
                                      }"
                                      rows="3"></textarea>
                        </div>
                        
                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Category</label>
                            <select v-model="selectedProduct.category_id"
                                    class="w-full px-3 py-2 rounded-md border"
                                    :style="{ 
                                        borderColor: themeColors.border, 
                                        backgroundColor: themeColors.background,
                                        color: themeColors.textPrimary
                                    }">
                                <option value="">Select Category</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>
                        
                        <!-- Pricing -->
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Sale Price *</label>
                            <input type="number" 
                                   v-model="selectedProduct.price"
                                   step="0.01"
                                   class="w-full px-3 py-2 rounded-md border"
                                   :style="{ 
                                       borderColor: themeColors.border, 
                                       backgroundColor: themeColors.background,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Cost Price (Purchase Price)</label>
                            <input type="number" 
                                   v-model="selectedProduct.cost_price"
                                   step="0.01"
                                   class="w-full px-3 py-2 rounded-md border"
                                   :style="{ 
                                       borderColor: themeColors.border, 
                                       backgroundColor: themeColors.background,
                                       color: themeColors.textPrimary
                                   }">
                        </div>

                        <!-- Live Margin Display (Edit) -->
                        <div class="md:col-span-2">
                            <div v-if="editProductMargin !== null" class="flex items-center gap-3 px-4 py-2 rounded-lg"
                                 :style="{ backgroundColor: themeColors.success + '18', borderColor: themeColors.success, borderWidth: '1px', borderStyle: 'solid' }">
                                <span class="text-xs font-medium" :style="{ color: themeColors.textSecondary }">Markup on Cost:</span>
                                <span class="text-sm font-bold" :style="{ color: themeColors.success }">{{ editProductMargin }}%</span>
                                <span class="text-xs" :style="{ color: themeColors.textSecondary }">— this % will be preserved when PO prices change</span>
                            </div>
                            <p v-else class="text-xs" :style="{ color: themeColors.textTertiary }">Enter both cost and sale price to see markup %</p>
                        </div>

                        <!-- Stock -->
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Stock Quantity *</label>
                            <input type="number" 
                                   v-model="selectedProduct.stock_quantity"
                                   class="w-full px-3 py-2 rounded-md border"
                                   :style="{ 
                                       borderColor: themeColors.border, 
                                       backgroundColor: themeColors.background,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Min Stock Level</label>
                            <input type="number" 
                                   v-model="selectedProduct.min_stock_level"
                                   class="w-full px-3 py-2 rounded-md border"
                                   :style="{ 
                                       borderColor: themeColors.border, 
                                       backgroundColor: themeColors.background,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        
                        <!-- Additional Fields -->
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Unit</label>
                            <select v-model="selectedProduct.unit"
                                    class="w-full px-3 py-2 rounded-md border"
                                    :style="{ 
                                        borderColor: themeColors.border, 
                                        backgroundColor: themeColors.background,
                                        color: themeColors.textPrimary
                                    }">
                                <option value="">Select Unit</option>
                                <option value="pieces">Pieces</option>
                                <option value="kg">Kilograms</option>
                                <option value="liters">Liters</option>
                                <option value="meters">Meters</option>
                                <option value="boxes">Boxes</option>
                                <option value="bottles">Bottles</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Tax Rate (%)</label>
                            <input type="number" 
                                   v-model="selectedProduct.tax_rate"
                                   step="0.01"
                                   class="w-full px-3 py-2 rounded-md border"
                                   :style="{ 
                                       borderColor: themeColors.border, 
                                       backgroundColor: themeColors.background,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        
                        <!-- Emoji and Status -->
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Emoji</label>
                            <input type="text" 
                                   v-model="selectedProduct.emoji"
                                   maxlength="2"
                                   class="w-full px-3 py-2 rounded-md border"
                                   :style="{ 
                                       borderColor: themeColors.border, 
                                       backgroundColor: themeColors.background,
                                       color: themeColors.textPrimary
                                   }">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                   :style="{ color: themeColors.textSecondary }">Status</label>
                            <select v-model="selectedProduct.is_active"
                                    class="w-full px-3 py-2 rounded-md border"
                                    :style="{ 
                                        borderColor: themeColors.border, 
                                        backgroundColor: themeColors.background,
                                        color: themeColors.textPrimary
                                    }">
                                <option :value="true">Active</option>
                                <option :value="false">Inactive</option>
                            </select>
                        </div>
                        
                        <!-- Service Type -->
                        <div class="md:col-span-2">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       v-model="selectedProduct.is_service"
                                       class="mr-2">
                                <span class="text-sm font-medium"
                                      :style="{ color: themeColors.textSecondary }">This is a service (not a physical product)</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="p-6 border-t flex justify-end space-x-3"
                     :style="{ borderColor: themeColors.border }">
                    <button @click="closeAllModals"
                            class="px-4 py-2 rounded-md font-medium transition-colors"
                            :style="{ 
                                backgroundColor: 'transparent',
                                color: themeColors.textSecondary,
                                border: `1px solid ${themeColors.border}`
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.background"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                        Cancel
                    </button>
                    <button @click="updateProduct"
                            class="px-4 py-2 rounded-md font-medium text-white transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        Update Product
                    </button>
                </div>
            </div>
        </div>

        <!-- Adjust Stock Modal -->
        <div v-if="showAdjustModal && selectedProduct" 
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             @click="closeAllModals">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4"
                 :style="{ backgroundColor: themeColors.card }"
                 @click.stop>
                <h3 class="text-lg font-semibold mb-4"
                    :style="{ color: themeColors.textPrimary }">Adjust Stock</h3>
                <div class="mb-4">
                    <p class="font-medium"
                       :style="{ color: themeColors.textPrimary }">{{ selectedProduct.name }}</p>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Current Stock: {{ selectedProduct.stock_quantity }}</p>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1"
                               :style="{ color: themeColors.textSecondary }">Adjustment Type</label>
                        <select class="w-full px-3 py-2 border rounded-md"
                                :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background }">
                            <option value="add">Add Stock</option>
                            <option value="remove">Remove Stock</option>
                            <option value="set">Set Stock Level</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1"
                               :style="{ color: themeColors.textSecondary }">Quantity</label>
                        <input type="number" 
                               class="w-full px-3 py-2 border rounded-md"
                               :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background }"
                               placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1"
                               :style="{ color: themeColors.textSecondary }">Reason (Optional)</label>
                        <textarea class="w-full px-3 py-2 border rounded-md"
                                  :style="{ borderColor: themeColors.border, backgroundColor: themeColors.background }"
                                  rows="3"
                                  placeholder="Reason for adjustment"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button @click="closeAllModals"
                            class="px-4 py-2 rounded-md text-gray-600 hover:text-gray-800">
                        Cancel
                    </button>
                    <button @click="stockAdjustment"
                            class="px-4 py-2 rounded-md text-white font-medium"
                            :style="{ backgroundColor: themeColors.primary }">
                        Adjust Stock
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { ArchiveBoxIcon, CurrencyDollarIcon, ExclamationTriangleIcon, ArrowTrendingUpIcon } from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
    user: Object,
    navigation: Array,
    products: Array,
    categories: Array,
    brands: Array,
    units: Array,
    settings: Object
})
import { useTheme } from '@/Composables/useTheme.js'

// Initialize theme
const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    sidebar: `var(--kotel-sidebar)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    hover: `var(--kotel-primary-hover)`
}))

const page = usePage()

// Data - use props instead of empty refs
const searchQuery = ref('')
const selectedCategory = ref('')
const stockFilter = ref('')

// Stock adjustment form
const newStockLevel = ref('')
const adjustmentNotes = ref('')

// Modal states
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showAdjustModal = ref(false)
const selectedProduct = ref(null)

// Pagination state
const currentPage = ref(1)
const perPage = ref(10)

// New product form data
const newProduct = ref({
    name: '',
    code: '',
    category_id: '',
    description: '',
    price: '',
    cost_price: '',
    stock_quantity: '',
    min_stock_level: '',
    unit: '',
    barcode: '',
    emoji: '',
    is_active: true,
    is_service: false,
    tax_rate: ''
})

// Currency from settings
const currency = computed(() => {
    const settings = page.props.settings || {}
    return settings.currency || 'USD'
})

const currencySymbol = computed(() => {
    const currencyMap = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'JPY': '¥',
        'CNY': '¥',
        'INR': '₹',
        'AUD': 'A$',
        'CAD': 'C$',
        'CHF': 'CHF',
        'SEK': 'kr',
        'NOK': 'kr',
        'DKK': 'kr',
        'PLN': 'zł',
        'CZK': 'Kč',
        'HUF': 'Ft',
        'RON': 'lei',
        'BGN': 'лв',
        'HRK': 'kn',
        'RUB': '₽',
        'TRY': '₺',
        'XAF': 'FCFA',
        'XOF': 'CFA',
        'ILS': '₪',
        'SAR': '﷼',
        'AED': 'د.إ',
        'QAR': '﷼',
        'KWD': 'د.ك',
        'BHD': 'د.ب',
        'OMR': 'ر.ع.',
        'JOD': 'د.ا',
        'LBP': 'ل.ل',
        'EGP': 'ج.م',
        'MAD': 'د.م.',
        'TND': 'د.ت',
        'DZD': 'د.ج',
        'LYD': 'د.ل',
        'SDG': 'ج.س.'
    }
    return currencyMap[currency.value] || '$'
})

const currencyPosition = computed(() => {
    const settings = page.props.settings || {}
    return settings.currency_position || 'before'
})

const decimalSeparator = computed(() => {
    const settings = page.props.settings || {}
    return settings.decimal_separator || '.'
})

const thousandSeparator = computed(() => {
    const settings = page.props.settings || {}
    return settings.thousand_separator || ','
})

const currencyDecimals = computed(() => {
    const settings = page.props.settings || {}
    return parseInt(settings.currency_decimals) || 2
})

// Computed properties
const filteredProducts = computed(() => {
    let filtered = props.products

    // Search filter (includes name, description, code, and barcode)
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(product => 
            product.name.toLowerCase().includes(query) ||
            (product.description && product.description.toLowerCase().includes(query)) ||
            (product.code && product.code.toLowerCase().includes(query)) ||
            (product.barcode && product.barcode.toLowerCase().includes(query))
        )
    }

    // Category filter
    if (selectedCategory.value) {
        filtered = filtered.filter(product => product.category_id == selectedCategory.value)
    }

    // Stock filter
    if (stockFilter.value) {
        filtered = filtered.filter(product => {
            if (stockFilter.value === 'low') return product.stock_quantity > 0 && product.stock_quantity <= 10
            if (stockFilter.value === 'out') return product.stock_quantity === 0
            if (stockFilter.value === 'available') return product.stock_quantity > 10
            return true
        })
    }

    return filtered
})

// Pagination computed properties
const totalPages = computed(() => {
    return Math.ceil(filteredProducts.value.length / perPage.value)
})

const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * perPage.value
    const end = start + perPage.value
    return filteredProducts.value.slice(start, end)
})

// Watch for filter changes and reset pagination
watch([searchQuery, selectedCategory, stockFilter], () => {
    currentPage.value = 1
}, { deep: true })

const totalValue = computed(() => {
    return props.products.reduce((total, product) => total + (product.price * product.stock_quantity), 0)
})

const lowStockCount = computed(() => {
    return props.products.filter(product => product.stock_quantity <= (product.min_stock_level || 10) && product.stock_quantity > 0).length
})

// Live margin helpers
const calcMargin = (cost, price) => {
    const c = parseFloat(cost) || 0
    const p = parseFloat(price) || 0
    if (c <= 0 || p <= 0) return null
    return ((p - c) / c * 100).toFixed(2)
}
const newProductMargin   = computed(() => calcMargin(newProduct.value.cost_price, newProduct.value.price))
const editProductMargin  = computed(() => selectedProduct.value ? calcMargin(selectedProduct.value.cost_price, selectedProduct.value.price) : null)

const totalProfit = computed(() => {
    return props.products.reduce((total, product) => {
        return total + ((product.price - (product.cost_price || 0)) * product.stock_quantity)
    }, 0)
})

// Format currency method
const formatCurrency = (value) => {
    if (value === null || value === undefined) return currencySymbol.value + '0.00'
    
    const num = parseFloat(value)
    const decimals = currencyDecimals.value
    const formatted = num.toFixed(decimals)
    
    // Apply thousand separator
    const parts = formatted.split('.')
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator.value)
    
    // Apply decimal separator
    const formattedNumber = parts.join(decimalSeparator.value)
    
    // Apply currency position
    if (currencyPosition.value === 'before') {
        return currencySymbol.value + formattedNumber
    } else {
        return formattedNumber + ' ' + currencySymbol.value
    }
}

// Methods
const editProduct = (product) => {
    selectedProduct.value = product
    showEditModal.value = true
}

const adjustStock = (product) => {
    selectedProduct.value = product
    showAdjustModal.value = true
}

const closeAllModals = () => {
    showCreateModal.value = false
    showEditModal.value = false
    showAdjustModal.value = false
    selectedProduct.value = null
    resetNewProductForm()
}

const createProduct = () => {
    // Validate required fields
    if (!newProduct.value.name || !newProduct.value.price || newProduct.value.stock_quantity === '' || newProduct.value.stock_quantity === null || newProduct.value.stock_quantity === undefined) {
        alert('Please fill in all required fields (Name, Price, Stock Quantity)')
        return
    }

    // Compute margin and include in submission
    const cost  = parseFloat(newProduct.value.cost_price) || 0
    const price = parseFloat(newProduct.value.price) || 0
    const margin = cost > 0 && price > 0 ? +((price - cost) / cost * 100).toFixed(4) : null

    const productData = {
        ...newProduct.value,
        price,
        stock_quantity: parseFloat(newProduct.value.stock_quantity) || 0,
        cost_price: cost,
        min_stock_level: parseFloat(newProduct.value.min_stock_level) || 0,
        margin_percentage: margin
    }

    // Make API call to create product
    router.post(route('pos.products.store'), productData, {
        onSuccess: () => {
            closeAllModals()
            // Show success message
            const flash = page.props.flash || {}
            if (flash.success) {
                // Success message will be shown by the framework
            }
        },
        onError: (errors) => {
            console.error('Error creating product:', errors)
            alert('Error creating product. Please check the form and try again.')
        }
    })
}

const updateProduct = () => {
    // Validate required fields
    if (!selectedProduct.value.name || !selectedProduct.value.price || selectedProduct.value.stock_quantity === '' || selectedProduct.value.stock_quantity === null || selectedProduct.value.stock_quantity === undefined) {
        alert('Please fill in all required fields (Name, Price, Stock Quantity)')
        return
    }

    // Compute margin and include in submission
    const eCost  = parseFloat(selectedProduct.value.cost_price) || 0
    const ePrice = parseFloat(selectedProduct.value.price) || 0
    const eMargin = eCost > 0 && ePrice > 0 ? +((ePrice - eCost) / eCost * 100).toFixed(4) : null

    const productData = {
        ...selectedProduct.value,
        price: ePrice,
        stock_quantity: parseFloat(selectedProduct.value.stock_quantity) || 0,
        cost_price: eCost,
        min_stock_level: parseFloat(selectedProduct.value.min_stock_level) || 0,
        margin_percentage: eMargin
    }

    // Make API call to update product
    router.put(route('pos.products.update', selectedProduct.value.id), productData, {
        onSuccess: () => {
            closeAllModals()
            // Show success message
            const flash = page.props.flash || {}
            if (flash.success) {
                // Success message will be shown by the framework
            }
        },
        onError: (errors) => {
            console.error('Error updating product:', errors)
            alert('Error updating product. Please check the form and try again.')
        }
    })
}

const deleteProduct = (id) => {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(route('pos.products.destroy', id))
    }
}

const deleteAllProducts = () => {
    if (confirm('Are you sure you want to delete ALL products? This cannot be undone.')) {
        router.delete(route('pos.products.destroy-all'))
    }
}

const stockAdjustment = () => {
    if (!selectedProduct.value || !newStockLevel.value) {
        alert('Please enter a valid stock level')
        return
    }

    // Make API call to adjust stock
    router.post('/pos/adjust-stock', {
        product_id: selectedProduct.value.id,
        quantity: newStockLevel.value,
        notes: adjustmentNotes.value
    }, {
        onSuccess: () => {
            closeAllModals()
            // Reset form
            newStockLevel.value = ''
            adjustmentNotes.value = ''
            // Show success message
            const flash = page.props.flash || {}
            if (flash.success) {
                // Success message will be shown by the framework
            }
        },
        onError: (errors) => {
            console.error('Error adjusting stock:', errors)
            alert('Error adjusting stock. Please try again.')
        }
    })
}

const handleBarcodeFocus = () => {
    // This will allow barcode scanners to input directly when the search field is focused
    // Most barcode scanners simulate keyboard input, so this should work automatically
    console.log('Search field focused - ready for barcode scanning')
}

const resetNewProductForm = () => {
    newProduct.value = {
        name: '',
        code: '',
        category_id: '',
        description: '',
        price: '',
        cost_price: '',
        stock_quantity: '',
        min_stock_level: '',
        unit: '',
        barcode: '',
        emoji: '',
        is_active: true,
        is_service: false,
        tax_rate: ''
    }
}

// Load data
onMounted(() => {
    // Products and categories are passed from the controller via props
})

// Initialize data immediately from props if available
// Data is already available via props, no need to reassign

// Export products
const exportProducts = () => {
    const headers = ['Name', 'Code', 'Category', 'Brand', 'Unit', 'Cost Price', 'Sale Price', 'Stock Quantity', 'Min Stock Level', 'Status']
    const rows = props.products.map(product => [
        product.name || '',
        product.code || '',
        product.category?.name || '',
        product.brand?.name || '',
        product.unit?.name || '',
        product.cost_price || 0,
        product.price || 0,
        product.stock_quantity || 0,
        product.min_stock_level || 0,
        product.stock_quantity > 0 ? 'In Stock' : (product.stock_quantity > 0 ? 'In Stock' : 'Out of Stock')
    ])
    
    const csvContent = [headers, ...rows]
        .map(row => row.map(cell => `"${cell}"`).join(','))
        .join('\n')
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    
    link.setAttribute('href', url)
    link.setAttribute('download', `products-${new Date().toISOString().split('T')[0]}.csv`)
    link.style.visibility = 'hidden'
    
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
}
// Data is already available via props, no need to reassign
</script>

<style scoped>
/* Additional custom styles if needed */
</style>
