<template>
    <DashboardLayout :user="user" :navigation="navigation">
        <div class="min-h-screen p-6"
             :style="{ backgroundColor: themeColors.background }">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold mb-2"
                            :style="{ color: themeColors.textPrimary }">Revenue Reports</h1>
                        <p class="mt-2"
                           :style="{ color: themeColors.textSecondary }">Comprehensive revenue, expenses, and profitability insights for your hotel.</p>
                    </div>
                    <div class="flex space-x-3">
                        <button @click="exportReport"
                                class="px-4 py-2 rounded-md transition-colors flex items-center space-x-2"
                                :style="{ 
                                    backgroundColor: themeColors.primary,
                                    color: themeColors.textOnPrimary 
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.primaryHover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                            <DocumentArrowDownIcon class="h-4 w-4" />
                            <span>Export Report</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Date Range Filter -->
            <div class="rounded-lg p-6 mb-6"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderWidth: '1px',
                     borderStyle: 'solid'
                 }">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Start Date</label>
                        <input type="date" v-model="filters.startDate" @change="applyFilters" inputmode="none"
                               @keydown.prevent @keypress.prevent @paste.prevent @focus="tryShowPicker($event)" @click="tryShowPicker($event)"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">End Date</label>
                        <input type="date" v-model="filters.endDate" @change="applyFilters" inputmode="none"
                               @keydown.prevent @keypress.prevent @paste.prevent @focus="tryShowPicker($event)" @click="tryShowPicker($event)"
                               class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                               :style="{ 
                                   backgroundColor: themeColors.background,
                                   borderColor: themeColors.border,
                                   color: themeColors.textPrimary,
                                   borderWidth: '1px',
                                   borderStyle: 'solid'
                               }">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Quick Period</label>
                        <select v-model="quickPeriod" @change="setQuickPeriod"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="">Custom Range</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="quarter">This Quarter</option>
                            <option value="year">This Year</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Revenue Type</label>
                        <select v-model="filters.revenueType"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="">All Revenue Types</option>
                            <option v-for="option in revenueTypeOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                    <div v-if="canSelectEmployee">
                        <label class="block text-sm font-medium mb-2"
                               :style="{ color: themeColors.textSecondary }">Employee</label>
                        <select v-model="filters.employeeId" @change="applyFilters"
                                class="w-full rounded-md px-3 py-2 focus:outline-none transition-colors"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }">
                            <option value="">All Employees</option>
                            <option v-for="employee in employeeOptions" :key="employee.id" :value="String(employee.id)">
                                {{ employee.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <!-- Total Revenue -->
                <div class="rounded-lg p-6"
                     :style="{ 
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Total Revenue</p>
                            <p class="text-2xl font-bold mt-1"
                               :style="{ color: themeColors.success }">
                                {{ formatCurrency((stats?.total_revenue ?? revenueData?.total_revenue) || 0) }}
                            </p>
                            <p class="text-xs mt-1"
                               :style="{ color: themeColors.textTertiary }">
                                {{ revenueData?.currency?.code || 'USD' }}
                            </p>
                        </div>
                        <div class="p-3 rounded-full"
                             :style="{ backgroundColor: `${themeColors.success}20` }">
                            <CurrencyDollarIcon class="h-6 w-6"
                                               :style="{ color: themeColors.success }" />
                        </div>
                    </div>
                </div>

                <!-- Total Expenses -->
                <div class="rounded-lg p-6"
                     :style="{ 
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Total Expenses</p>
                            <p class="text-2xl font-bold mt-1"
                               :style="{ color: themeColors.danger }">
                                {{ formatCurrency(expenseData?.total_expenses || 0) }}
                            </p>
                            <p class="text-xs mt-1"
                               :style="{ color: themeColors.textTertiary }">
                                {{ expenseData?.currency?.code || 'USD' }}
                            </p>
                        </div>
                        <div class="p-3 rounded-full"
                             :style="{ backgroundColor: `${themeColors.danger}20` }">
                            <ChartBarSquareIcon class="h-6 w-6"
                                              :style="{ color: themeColors.danger }" />
                        </div>
                    </div>
                </div>

                <!-- Net Profit -->
                <div class="rounded-lg p-6"
                     :style="{ 
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Net Profit</p>
                            <p class="text-2xl font-bold mt-1"
                               :style="{ color: profitLossData?.net_profit >= 0 ? themeColors.success : themeColors.danger }">
                                {{ formatCurrency(profitLossData?.net_profit || 0) }}
                            </p>
                            <p class="text-xs mt-1"
                               :style="{ color: themeColors.textTertiary }">
                                {{ profitLossData?.net_margin?.toFixed(1) || 0 }}% margin
                            </p>
                        </div>
                        <div class="p-3 rounded-full"
                             :style="{ backgroundColor: profitLossData?.net_profit >= 0 ? `${themeColors.success}20` : `${themeColors.danger}20` }">
                            <ArrowTrendingUpIcon class="h-6 w-6"
                                          :style="{ color: profitLossData?.net_profit >= 0 ? themeColors.success : themeColors.danger }" />
                        </div>
                    </div>
                </div>

                <!-- Average Daily Rate -->
                <div class="rounded-lg p-6"
                     :style="{ 
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium"
                               :style="{ color: themeColors.textSecondary }">Average Daily Rate</p>
                            <p class="text-2xl font-bold mt-1"
                               :style="{ color: themeColors.primary }">
                                {{ formatCurrency(revenueData?.average_daily_rate || 0) }}
                            </p>
                            <p class="text-xs mt-1"
                               :style="{ color: themeColors.textTertiary }">
                                Per room night
                            </p>
                        </div>
                        <div class="p-3 rounded-full"
                             :style="{ backgroundColor: `${themeColors.primary}20` }">
                            <CalendarIcon class="h-6 w-6"
                                          :style="{ color: themeColors.primary }" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Breakdown -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Revenue by Category -->
                <div class="rounded-lg p-6"
                     :style="{ 
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <h3 class="text-lg font-semibold mb-4 flex items-center"
                        :style="{ color: themeColors.textPrimary }">
                        <ChartPieIcon class="h-5 w-5 mr-2" />
                        Revenue by Category
                    </h3>
                    <div class="space-y-3">
                            <div v-for="category in filteredRevenueCategories" :key="category.category" 
                             class="flex items-center justify-between p-3 rounded-md"
                             :style="{ backgroundColor: themeColors.background }">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 rounded-full"
                                     :style="{ backgroundColor: getCategoryColor(category.category) }"></div>
                                <span class="text-sm font-medium"
                                      :style="{ color: themeColors.textPrimary }">{{ category.category }}</span>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ formatCurrency(category.amount) }}</p>
                                <p class="text-xs"
                                   :style="{ color: themeColors.textTertiary }">
                                    {{ getPercentage(category.amount, revenueData?.total_revenue) }}%
                                </p>
                            </div>
                        </div>
                        <p v-if="!filteredRevenueCategories.length" class="text-sm" :style="{ color: themeColors.textSecondary }">No revenue data for this period</p>
                    </div>
                </div>

                <!-- Expenses by Category -->
                <div class="rounded-lg p-6"
                     :style="{ 
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <h3 class="text-lg font-semibold mb-4 flex items-center"
                        :style="{ color: themeColors.textPrimary }">
                        <ChartBarIcon class="h-5 w-5 mr-2" />
                        Expenses by Category
                    </h3>
                    <div class="space-y-3">
                        <div v-for="category in expenseData?.expenses_by_category || []" :key="category.category" 
                             class="flex items-center justify-between p-3 rounded-md"
                             :style="{ backgroundColor: themeColors.background }">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 rounded-full"
                                     :style="{ backgroundColor: getCategoryColor(category.category) }"></div>
                                <span class="text-sm font-medium"
                                      :style="{ color: themeColors.textPrimary }">{{ category.category }}</span>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium"
                                   :style="{ color: themeColors.textPrimary }">{{ formatCurrency(category.amount) }}</p>
                                <p class="text-xs"
                                   :style="{ color: themeColors.textTertiary }">
                                    {{ getPercentage(category.amount, expenseData?.total_expenses) }}%
                                </p>
                            </div>
                        </div>
                        <p v-if="!expenseData?.expenses_by_category?.length" class="text-sm" :style="{ color: themeColors.textSecondary }">No expense data for this period</p>
                    </div>
                </div>
            </div>

            <!-- Daily Revenue Breakdown -->
            <div v-if="daily && daily.length" class="rounded-lg overflow-hidden mb-6"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">
                        Daily Revenue Breakdown
                        <span class="text-sm font-normal ml-2" :style="{ color: themeColors.textSecondary }">
                            {{ stats?.start_date }} — {{ stats?.end_date }}
                        </span>
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr :style="{ backgroundColor: themeColors.background }">
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Orders</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Revenue</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Avg / Order</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                            <tr v-for="row in daily" :key="row.date" class="hover:opacity-80 transition-opacity">
                                <td class="px-6 py-3 text-sm" :style="{ color: themeColors.textPrimary }">{{ row.date }}</td>
                                <td class="px-6 py-3 text-sm text-right" :style="{ color: themeColors.textPrimary }">{{ row.orders }}</td>
                                <td class="px-6 py-3 text-sm text-right font-medium" :style="{ color: themeColors.success }">{{ formatCurrency(row.revenue) }}</td>
                                <td class="px-6 py-3 text-sm text-right" :style="{ color: themeColors.textSecondary }">
                                    {{ row.orders > 0 ? formatCurrency(row.revenue / row.orders) : '—' }}
                                </td>
                            </tr>
                            <tr class="font-semibold" :style="{ backgroundColor: themeColors.background }">
                                <td class="px-6 py-3 text-sm" :style="{ color: themeColors.textPrimary }">Total</td>
                                <td class="px-6 py-3 text-sm text-right" :style="{ color: themeColors.textPrimary }">{{ daily.reduce((s, r) => s + r.orders, 0) }}</td>
                                <td class="px-6 py-3 text-sm text-right" :style="{ color: themeColors.success }">{{ formatCurrency(stats?.total_revenue || 0) }}</td>
                                <td class="px-6 py-3 text-sm text-right" :style="{ color: themeColors.textSecondary }">{{ formatCurrency(stats?.avg_order_value || 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Sales -->
            <div v-if="recentSales && recentSales.length" class="rounded-lg overflow-hidden mb-6"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border, borderWidth: '1px', borderStyle: 'solid' }">
                <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">Recent Sales</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr :style="{ backgroundColor: themeColors.background }">
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Employee</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider" :style="{ color: themeColors.textSecondary }">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" :style="{ borderColor: themeColors.border }">
                            <tr v-for="s in recentSales" :key="s.id" class="hover:opacity-80 transition-opacity">
                                <td class="px-6 py-3 text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ s.sale_number }}</td>
                                <td class="px-6 py-3 text-sm" :style="{ color: themeColors.textSecondary }">{{ formatDate(s.sale_date) }}</td>
                                <td class="px-6 py-3 text-sm" :style="{ color: themeColors.textPrimary }">{{ s.user_name || '—' }}</td>
                                <td class="px-6 py-3 text-sm text-right font-medium" :style="{ color: themeColors.success }">{{ formatCurrency(s.total_amount) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detailed Table -->
            <div class="rounded-lg overflow-hidden"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderWidth: '1px',
                     borderStyle: 'solid'
                 }">
                <div class="px-6 py-4 border-b"
                     :style="{ borderColor: themeColors.border }">
                    <h3 class="text-lg font-medium"
                        :style="{ color: themeColors.textPrimary }">Financial Summary</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr :style="{ backgroundColor: themeColors.background }">
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Revenue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Expenses</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Net</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textSecondary }">Margin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y"
                              :style="{ borderColor: themeColors.border }">
                            <tr v-for="category in filteredRevenueCategories"
                                :key="category.category" class="hover:opacity-80 transition-opacity">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                    :style="{ color: themeColors.textPrimary }">{{ category.category }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm"
                                    :style="{ color: themeColors.textPrimary }">
                                    {{ category.formatted_amount }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm"
                                    :style="{ color: themeColors.textPrimary }">-</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                    :style="{ color: category.amount >= 0 ? themeColors.success : themeColors.danger }">
                                    {{ category.formatted_amount }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :style="{ 
                                              backgroundColor: `${(category.amount >= 0 ? themeColors.success : themeColors.danger)}20`,
                                              color: category.amount >= 0 ? themeColors.success : themeColors.danger
                                          }">{{ getPercentage(category.amount, revenueData?.total_revenue) }}%</span>
                                </td>
                            </tr>

                            <!-- Total Row -->
                            <tr class="font-semibold"
                                :style="{ backgroundColor: themeColors.background }">
                                <td class="px-6 py-4 whitespace-nowrap text-sm"
                                    :style="{ color: themeColors.textPrimary }">Total Revenue</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm"
                                    :style="{ color: themeColors.success }">
                                    {{ formatCurrency(revenueData?.total_revenue || 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm"
                                    :style="{ color: themeColors.danger }">
                                    {{ formatCurrency(expenseData?.total_expenses || 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm"
                                    :style="{ 
                                        color: (profitLossData?.net_profit || 0) >= 0 ? themeColors.success : themeColors.danger 
                                    }">
                                    {{ formatCurrency(profitLossData?.net_profit || 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :style="{ 
                                              backgroundColor: (profitLossData?.net_margin || 0) >= 0 ? `${themeColors.success}20` : `${themeColors.danger}20`,
                                              color: (profitLossData?.net_margin || 0) >= 0 ? themeColors.success : themeColors.danger
                                          }">
                                        {{ (profitLossData?.net_margin || 0).toFixed(1) }}%
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Export Modal -->
        <div v-if="showExportModal" class="fixed inset-0 z-50 overflow-y-auto"
             :style="{ backgroundColor: 'rgba(0, 0, 0, 0.5)' }">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="rounded-lg p-6 max-w-md w-full"
                     :style="{ 
                         backgroundColor: themeColors.card,
                         borderColor: themeColors.border,
                         borderWidth: '1px',
                         borderStyle: 'solid'
                     }">
                    <h3 class="text-lg font-semibold mb-4"
                        :style="{ color: themeColors.textPrimary }">Export Report</h3>
                    
                    <div class="space-y-3">
                        <button @click="exportToCSV"
                                class="w-full px-4 py-3 rounded-md transition-colors flex items-center justify-center space-x-2"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                            <DocumentArrowDownIcon class="h-4 w-4" />
                            <span>Export as CSV</span>
                        </button>
                        
                        <button @click="exportToPDF"
                                class="w-full px-4 py-3 rounded-md transition-colors flex items-center justify-center space-x-2"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                            <DocumentArrowDownIcon class="h-4 w-4" />
                            <span>Export as PDF</span>
                        </button>
                        
                        <button @click="exportToWord"
                                class="w-full px-4 py-3 rounded-md transition-colors flex items-center justify-center space-x-2"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                            <DocumentArrowDownIcon class="h-4 w-4" />
                            <span>Export as Word</span>
                        </button>
                        
                        <button @click="exportToExcel"
                                class="w-full px-4 py-3 rounded-md transition-colors flex items-center justify-center space-x-2"
                                :style="{ 
                                    backgroundColor: themeColors.background,
                                    borderColor: themeColors.border,
                                    color: themeColors.textPrimary,
                                    borderWidth: '1px',
                                    borderStyle: 'solid'
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.background">
                            <DocumentArrowDownIcon class="h-4 w-4" />
                            <span>Export as Excel</span>
                        </button>
                    </div>
                    
                    <div class="mt-6 flex space-x-3">
                        <button @click="showExportModal = false"
                                class="flex-1 px-4 py-2 rounded-md transition-colors"
                                :style="{ 
                                    backgroundColor: themeColors.secondary,
                                    color: themeColors.textPrimary 
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'
import {
    CurrencyDollarIcon,
    ChartBarSquareIcon,
    ArrowTrendingUpIcon,
    CalendarIcon,
    ChartPieIcon,
    ChartBarIcon,
    DocumentArrowDownIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    revenueData: Object,
    expenseData: Object,
    profitLossData: Object,
    dateRange: Object,
    // Backend stats + lists (new)
    stats: Object,
    daily: Array,
    recentSales: Array,
    employeeOptions: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    reportRouteName: {
        type: String,
        default: 'manager.reports.revenue'
    },
    canSelectEmployee: {
        type: Boolean,
        default: true
    },
})

const page = usePage()
const navigation = computed(() => {
    return page.props.navigation || []
})

// Theme system
const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    primaryHover: `var(--kotel-primary-hover)`,
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    successHover: `var(--kotel-success-hover)`,
    danger: `var(--kotel-danger)`,
    dangerHover: `var(--kotel-danger-hover)`,
    warning: `var(--kotel-warning)`,
    warningHover: `var(--kotel-warning-hover)`,
    hover: `rgba(255, 255, 255, 0.1)`,
    textOnPrimary: `var(--kotel-text-on-primary)`
}))

// Load theme on mount
loadTheme()

// Filters
const formatDate = (d) => {
    if (!d) return ''
    const dt = new Date(d)
    return isNaN(dt.getTime()) ? '' : dt.toLocaleDateString()
}

const filters = ref({
    startDate: (props.stats?.start_date || props.dateRange?.start) || '',
    endDate: (props.stats?.end_date || props.dateRange?.end) || '',
    revenueType: props.filters?.revenueType || props.filters?.revenue_type || '',
    employeeId: props.filters?.employeeId ? String(props.filters.employeeId) : ''
})

const quickPeriod = ref('')
const showExportModal = ref(false)

// Methods

const getPercentage = (value, total) => {
    if (!total || total === 0) return 0
    return ((value / total) * 100).toFixed(1)
}

const getCategoryColor = (category) => {
    const colors = [
        '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
        '#EC4899', '#14B8A6', '#F97316', '#06B6D4', '#84CC16'
    ]
    const index = category?.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0) || 0
    return colors[index % colors.length]
}

const getRevenueCategoryKey = (category) => {
    return (category || '').toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '') || 'other'
}

const revenueTypeOptions = computed(() => {
    return (props.revenueData?.revenue_by_category || [])
        .map(category => ({
            value: getRevenueCategoryKey(category.category),
            label: category.category,
        }))
        .filter((option, index, items) => items.findIndex(item => item.value === option.value) === index)
        .sort((left, right) => left.label.localeCompare(right.label))
})

const filteredRevenueCategories = computed(() => {
    const categories = props.revenueData?.revenue_by_category || []

    if (!filters.value.revenueType) {
        return categories
    }

    return categories.filter(category => getRevenueCategoryKey(category.category) === filters.value.revenueType)
})

const setQuickPeriod = () => {
    const now = new Date()
    let startDate, endDate

    switch (quickPeriod.value) {
        case 'today':
            startDate = endDate = now
            break
        case 'week':
            startDate = new Date(now.setDate(now.getDate() - now.getDay()))
            endDate = new Date(now.setDate(now.getDate() - now.getDay() + 6))
            break
        case 'month':
            startDate = new Date(now.getFullYear(), now.getMonth(), 1)
            endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0)
            break
        case 'quarter':
            const quarter = Math.floor(now.getMonth() / 3)
            startDate = new Date(now.getFullYear(), quarter * 3, 1)
            endDate = new Date(now.getFullYear(), quarter * 3 + 3, 0)
            break
        case 'year':
            startDate = new Date(now.getFullYear(), 0, 1)
            endDate = new Date(now.getFullYear(), 11, 31)
            break
        default:
            return
    }

    filters.value.startDate = startDate.toISOString().split('T')[0]
    filters.value.endDate = endDate.toISOString().split('T')[0]
    applyFilters()
}

const applyFilters = () => {
    const params = {
        start_date: filters.value.startDate,
        end_date: filters.value.endDate,
        employee_id: props.canSelectEmployee ? (filters.value.employeeId || undefined) : undefined,
    }
    router.get(route(props.reportRouteName), params, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    })
}

// Open native date picker on supported browsers
const tryShowPicker = (e) => {
    const el = e?.target
    if (el && typeof el.showPicker === 'function') {
        try { el.showPicker() } catch (_) { /* no-op */ }
    }
}

const exportReport = () => {
    // Show export options modal
    showExportModal.value = true
}

const exportToCSV = () => {
    const csvData = generateCSVData()
    downloadCSV(csvData, 'revenue-report.csv')
    showExportModal.value = false
}

const exportToPDF = () => {
    // For PDF export, we would typically use a library like jsPDF
    // For now, we'll create a simple text-based report
    const pdfContent = generatePDFContent()
    downloadAsText(pdfContent, 'revenue-report.txt', 'text/plain')
    showExportModal.value = false
}

const exportToWord = () => {
    const wordContent = generateWordContent()
    downloadAsText(wordContent, 'revenue-report.doc', 'application/msword')
    showExportModal.value = false
}

const exportToExcel = () => {
    // For Excel export, we'll create a CSV that Excel can open
    const csvData = generateCSVData()
    downloadCSV(csvData, 'revenue-report.xlsx')
    showExportModal.value = false
}

const generateCSVData = () => {
    const headers = ['Category', 'Revenue', 'Expenses', 'Net Profit', 'Margin %']
    const rows = []
    
    // Add revenue categories
    if (props.revenueData?.revenue_by_category) {
        props.revenueData.revenue_by_category.forEach(category => {
            rows.push([
                category.category,
                category.amount.toFixed(2),
                '0.00',
                category.amount.toFixed(2),
                '100.0'
            ])
        })
    }
    
    // Add total row
    rows.push([
        'Total Revenue',
        (props.revenueData?.total_revenue || 0).toFixed(2),
        (props.expenseData?.total_expenses || 0).toFixed(2),
        (props.profitLossData?.net_profit || 0).toFixed(2),
        (props.profitLossData?.net_margin || 0).toFixed(1)
    ])
    
    return [headers, ...rows]
}

const generatePDFContent = () => {
    const hotelName = 'Hotel Management System'
    const reportDate = new Date().toLocaleDateString()
    const startDate = filters.value.startDate || 'N/A'
    const endDate = filters.value.endDate || 'N/A'
    
    return `
${hotelName}
Revenue Report
Generated: ${reportDate}
Period: ${startDate} to ${endDate}

========================================
SUMMARY
========================================
Total Revenue: ${formatCurrency(props.revenueData?.total_revenue)}
Total Expenses: ${formatCurrency(props.expenseData?.total_expenses)}
Net Profit: ${formatCurrency(props.profitLossData?.net_profit)}
Net Margin: ${(props.profitLossData?.net_margin || 0).toFixed(1)}%

========================================
REVENUE BREAKDOWN
========================================
${props.revenueData?.revenue_by_category?.map(cat => 
    `${cat.category}: ${cat.formatted_amount}`
).join('\n') || 'No revenue data available'}

========================================
EXPENSE BREAKDOWN
========================================
${props.expenseData?.expenses_by_category?.map(cat => 
    `${cat.category}: ${cat.formatted_amount}`
).join('\n') || 'No expense data available'}

========================================
KEY METRICS
========================================
Average Daily Rate: ${formatCurrency(props.revenueData?.average_daily_rate)}
Growth Rate: ${props.revenueData?.growth_rate || 0}%
Pending Expenses: ${props.expenseData?.pending_expenses || 0}
`.trim()
}

const generateWordContent = () => {
    const hotelName = 'Hotel Management System'
    const reportDate = new Date().toLocaleDateString()
    
    return `
${hotelName}
Revenue Report
Date: ${reportDate}
Period: ${filters.value.startDate} to ${filters.value.endDate}

Executive Summary
----------------
Total Revenue: ${formatCurrency(props.revenueData?.total_revenue)}
Total Expenses: ${formatCurrency(props.expenseData?.total_expenses)}
Net Profit: ${formatCurrency(props.profitLossData?.net_profit)}
Net Margin: ${(props.profitLossData?.net_margin || 0).toFixed(1)}%

Revenue Analysis
----------------
${props.revenueData?.revenue_by_category?.map(cat => 
    `${cat.category}: ${cat.formatted_amount} (${getPercentage(cat.amount, props.revenueData?.total_revenue)}%)`
).join('\n') || 'No revenue data available'}

Expense Analysis
----------------
${props.expenseData?.expenses_by_category?.map(cat => 
    `${cat.category}: ${cat.formatted_amount} (${getPercentage(cat.amount, props.expenseData?.total_expenses)}%)`
).join('\n') || 'No expense data available'}

Key Performance Indicators
----------------
Average Daily Rate: ${formatCurrency(props.revenueData?.average_daily_rate)}
Growth Rate: ${props.revenueData?.growth_rate || 0}%
Pending Expenses: ${props.expenseData?.pending_expenses || 0}
`.trim()
}

const downloadCSV = (data, filename) => {
    const csvContent = data.map(row => row.join(',')).join('\n')
    const blob = new Blob([csvContent], { type: 'text/csv' })
    downloadFile(blob, filename)
}

const downloadAsText = (content, filename, mimeType) => {
    const blob = new Blob([content], { type: mimeType })
    downloadFile(blob, filename)
}

const downloadFile = (blob, filename) => {
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
}

// Initialize dates
onMounted(() => {
    if (props.dateRange) {
        filters.value.startDate = props.dateRange.start
        filters.value.endDate = props.dateRange.end
    }
})
</script>
