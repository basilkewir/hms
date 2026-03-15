<template>
    <DashboardLayout title="Revenue Report" :user="user">
        <!-- Header Section -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Revenue Report</h1>
                    <p class="text-sm"
                       :style="{ color: themeColors.textSecondary }">Detailed analysis of revenue streams and performance.</p>
                </div>
                <div class="flex items-center gap-3">
                    <select v-model="selectedPeriod" @change="applyPeriod"
                            class="rounded-md px-3 py-2 focus:outline-none transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary
                            }">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="yearly">Yearly</option>
                        <option value="custom">Custom Range</option>
                    </select>
                    <select v-model="selectedFormat"
                            class="rounded-md px-3 py-2 focus:outline-none transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary
                            }">
                        <option value="xlsx">Excel (.xlsx)</option>
                        <option value="csv">CSV (.csv)</option>
                        <option value="pdf">PDF (.pdf)</option>
                    </select>
                    <button @click="exportReport"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export
                    </button>
                    <button @click="printReport"
                            class="px-4 py-2 rounded-md transition-colors font-medium text-white flex items-center"
                            :style="{ 
                                backgroundColor: themeColors.success,
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.success">
                        <PrinterIcon class="h-4 w-4 mr-2" />
                        Print
                    </button>
                </div>
            </div>
        </div>

        <!-- Admin/Manager Warning: Custom accountant overrides active -->
        <div v-if="customAccountantsWithOverrides && customAccountantsWithOverrides.length > 0"
             class="mb-6 bg-orange-900 border border-orange-500 rounded-lg p-4">
            <div class="flex items-center gap-2 text-orange-300 font-semibold mb-1">
                <ExclamationTriangleIcon class="h-5 w-5"/>
                Custom Report Data Active
            </div>
            <p class="text-orange-200 text-sm">
                The following accountant(s) have <strong>customized/overridden report data</strong> enabled.
                Their account shows different figures from real data. You are currently viewing <strong>real data</strong>.
            </p>
            <ul class="mt-2 space-y-1">
                <li v-for="a in customAccountantsWithOverrides" :key="a.id" class="text-orange-300 text-sm">
                    &bull; <strong>{{ a.name }}</strong> ({{ a.email }})
                </li>
            </ul>
        </div>

        <!-- Revenue Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-6 mb-8">
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(139, 92, 246, 0.1)' }">
                        <ChartBarIcon class="h-6 w-6" :style="{ color: '#8b5cf6' }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Average Daily Rate</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ revenueData.formatted_average_daily_rate }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: revenueData.growth_rate >= 0 ? 'rgba(34, 197, 94, 0.1)' : 'rgba(239, 68, 68, 0.1)' }">
                        <ArrowTrendingUpIcon class="h-6 w-6" :style="{ color: revenueData.growth_rate >= 0 ? themeColors.success : themeColors.danger }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Growth Rate</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: revenueData.growth_rate >= 0 ? themeColors.success : themeColors.danger }">{{ revenueData.growth_rate }}%</p>
                        <p class="text-xs"
                           :style="{ color: themeColors.textTertiary }">vs previous period</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Revenue Card -->
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(59, 130, 246, 0.1)' }">
                        <CurrencyDollarIcon class="h-6 w-6" :style="{ color: themeColors.primary }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Total Revenue</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ revenueData.formatted_total_revenue }}</p>
                        <p class="text-xs mt-1"
                           :style="{ color: themeColors.textTertiary }">
                            {{ revenueData.growth_rate > 0 ? '+' : '' }}{{ revenueData.growth_rate }}% growth
                        </p>
                    </div>
                </div>
            </div>

            <!-- Room Revenue Card -->
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <HomeIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Room Revenue</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ revenueData.formatted_room_revenue }}</p>
                        <p class="text-xs mt-1"
                           :style="{ color: themeColors.textTertiary }">
                            {{ Math.round(revenueData.room_revenue_percentage || 0) }}% of total
                        </p>
                    </div>
                </div>
            </div>

            <!-- POS Sales Card -->
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(139, 92, 246, 0.1)' }">
                        <ShoppingCartIcon class="h-6 w-6" :style="{ color: '#8b5cf6' }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">POS Sales</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ revenueData.formatted_pos_sales_revenue }}</p>
                        <p class="text-xs mt-1"
                           :style="{ color: themeColors.textTertiary }">
                            {{ Math.round(revenueData.pos_sales_percentage || 0) }}% of total
                        </p>
                    </div>
                </div>
            </div>

            <!-- Average Daily Rate Card -->
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                         :style="{ backgroundColor: 'rgba(250, 204, 21, 0.1)' }">
                        <ChartBarIcon class="h-6 w-6" :style="{ color: themeColors.warning }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Average Daily Rate</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ revenueData.formatted_average_daily_rate }}</p>
                        <p class="text-xs mt-1"
                           :style="{ color: themeColors.textTertiary }">Per occupied room</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Breakdown Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Revenue by Category -->
            <div class="rounded-lg overflow-hidden shadow"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="px-6 py-4 border-b"
                     :style="{ 
                         borderColor: themeColors.border,
                         borderBottomWidth: '1px'
                     }">
                    <h3 class="text-lg font-medium"
                        :style="{ color: themeColors.textPrimary }">Revenue by Category</h3>
                    <p class="text-sm mt-1"
                       :style="{ color: themeColors.textSecondary }">Detailed breakdown of all revenue sources</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr :style="{ backgroundColor: themeColors.background }">
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textTertiary }">
                                    Category
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textTertiary }">
                                    Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textTertiary }">
                                    %
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in revenueData.revenue_by_category" :key="item.category" 
                                class="transition-colors"
                                :style="{ 
                                    borderBottomStyle: 'solid',
                                    borderBottomWidth: '1px',
                                    borderColor: themeColors.border
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                    :style="{ color: themeColors.textPrimary }">
                                    {{ item.category }}
                                    <span v-if="item.charge_codes && item.charge_codes.length > 1" 
                                          class="ml-2 text-xs"
                                          :style="{ color: themeColors.textTertiary }">
                                        ({{ item.charge_codes.length }} codes)
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm"
                                    :style="{ color: item.amount > 0 ? themeColors.textSecondary : themeColors.textTertiary }">
                                    {{ item.formatted_amount }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm"
                                    :style="{ color: themeColors.textSecondary }">
                                    {{ revenueData.total_revenue > 0 ? Math.round((item.amount / revenueData.total_revenue) * 100) : 0 }}%
                                </td>
                            </tr>
                            
                            <tr v-if="revenueData.revenue_by_category.length === 0">
                                <td colspan="3" class="px-6 py-8 text-center text-sm"
                                    :style="{ color: themeColors.textSecondary }">
                                    No revenue categories found for the selected period.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- POS Sales by Product Category -->
            <div class="rounded-lg overflow-hidden shadow"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <div class="px-6 py-4 border-b"
                     :style="{ 
                         borderColor: themeColors.border,
                         borderBottomWidth: '1px'
                     }">
                    <h3 class="text-lg font-medium"
                        :style="{ color: themeColors.textPrimary }">POS Sales by Product</h3>
                    <p class="text-sm mt-1"
                       :style="{ color: themeColors.textSecondary }">Point of sales breakdown by product category</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr :style="{ backgroundColor: themeColors.background }">
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textTertiary }">
                                    Product Category
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textTertiary }">
                                    Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                    :style="{ color: themeColors.textTertiary }">
                                    %
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in revenueData.pos_sales_by_category" :key="item.category" 
                                class="transition-colors"
                                :style="{ 
                                    borderBottomStyle: 'solid',
                                    borderBottomWidth: '1px',
                                    borderColor: themeColors.border
                                }"
                                @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                                @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                    :style="{ color: themeColors.textPrimary }">
                                    {{ item.category }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm"
                                    :style="{ color: themeColors.textSecondary }">
                                    {{ item.formatted_amount }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm"
                                    :style="{ color: themeColors.textSecondary }">
                                    {{ revenueData.pos_sales_revenue > 0 ? Math.round((item.amount / revenueData.pos_sales_revenue) * 100) : 0 }}%
                                </td>
                            </tr>
                            
                            <tr v-if="revenueData.pos_sales_by_category.length === 0">
                                <td colspan="3" class="px-6 py-8 text-center text-sm"
                                    :style="{ color: themeColors.textSecondary }">
                                    No POS sales data found for the selected period.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Revenue Performance Metrics -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Revenue Distribution -->
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <h3 class="text-lg font-medium mb-4"
                    :style="{ color: themeColors.textPrimary }">Revenue Distribution</h3>
                
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Room Revenue</span>
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textPrimary }">{{ Math.round(revenueData.room_revenue_percentage || 0) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2"
                             :style="{ backgroundColor: themeColors.background }">
                            <div class="h-2 rounded-full transition-all duration-300"
                                 :style="{ 
                                     width: (revenueData.room_revenue_percentage || 0) + '%',
                                     backgroundColor: themeColors.success
                                 }"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">POS Sales</span>
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textPrimary }">{{ Math.round(revenueData.pos_sales_percentage || 0) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2"
                             :style="{ backgroundColor: themeColors.background }">
                            <div class="h-2 rounded-full transition-all duration-300"
                                 :style="{ 
                                     width: (revenueData.pos_sales_percentage || 0) + '%',
                                     backgroundColor: '#8b5cf6'
                                 }"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Other Revenue</span>
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textPrimary }">{{ Math.round(revenueData.other_revenue_percentage || 0) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2"
                             :style="{ backgroundColor: themeColors.background }">
                            <div class="h-2 rounded-full transition-all duration-300"
                                 :style="{ 
                                     width: (revenueData.other_revenue_percentage || 0) + '%',
                                     backgroundColor: themeColors.warning
                                 }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Growth Analysis -->
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <h3 class="text-lg font-medium mb-4"
                    :style="{ color: themeColors.textPrimary }">Growth Analysis</h3>
                
                <div class="flex items-center justify-center mb-4">
                    <div class="text-center">
                        <div class="text-3xl font-bold mb-2"
                             :style="{ 
                                 color: revenueData.growth_rate >= 0 ? themeColors.success : themeColors.danger 
                             }">
                            {{ revenueData.growth_rate > 0 ? '+' : '' }}{{ revenueData.growth_rate }}%
                        </div>
                        <p class="text-sm"
                           :style="{ color: themeColors.textSecondary }">vs previous period</p>
                    </div>
                </div>
                
                <div class="text-center">
                    <div class="flex items-center justify-center mb-2">
                        <ArrowTrendingUpIcon v-if="revenueData.growth_rate >= 0" 
                                            class="h-8 w-8" 
                                            :style="{ color: themeColors.success }" />
                        <ArrowTrendingDownIcon v-else 
                                             class="h-8 w-8" 
                                             :style="{ color: themeColors.danger }" />
                    </div>
                    <p class="text-xs"
                       :style="{ color: themeColors.textTertiary }">
                        {{ revenueData.growth_rate >= 0 ? 'Revenue is growing' : 'Revenue declining' }}
                    </p>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="rounded-lg p-6 border shadow-sm"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border,
                     borderStyle: 'solid',
                     borderWidth: '1px'
                 }">
                <h3 class="text-lg font-medium mb-4"
                    :style="{ color: themeColors.textPrimary }">Key Metrics</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm"
                              :style="{ color: themeColors.textSecondary }">Total Categories</span>
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textPrimary }">{{ revenueData.revenue_by_category?.length || 0 }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm"
                              :style="{ color: themeColors.textSecondary }">POS Categories</span>
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textPrimary }">{{ revenueData.pos_sales_by_category?.length || 0 }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm"
                              :style="{ color: themeColors.textSecondary }">Currency</span>
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textPrimary }">{{ revenueData.currency?.symbol || 'FCFA' }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm"
                              :style="{ color: themeColors.textSecondary }">Period</span>
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textPrimary }">{{ selectedPeriod }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Revenue Table -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <div class="px-6 py-4 border-b"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">Complete Revenue Analysis</h3>
                <p class="text-sm mt-1"
                   :style="{ color: themeColors.textSecondary }">Comprehensive breakdown of all revenue streams and categories</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Revenue Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Category
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                % of Total
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textTertiary }">
                                Trend
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Room Revenue Row -->
                        <tr class="transition-colors"
                            :style="{ 
                                borderBottomStyle: 'solid',
                                borderBottomWidth: '1px',
                                borderColor: themeColors.border
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                Room Revenue
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                Accommodation
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ revenueData.formatted_room_revenue }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                {{ Math.round(revenueData.room_revenue_percentage || 0) }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                      :style="{ 
                                          backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                          color: themeColors.success
                                      }">
                                    <ArrowTrendingUpIcon class="h-3 w-3 mr-1" />
                                    Stable
                                </span>
                            </td>
                        </tr>
                        
                        <!-- POS Sales Row -->
                        <tr class="transition-colors"
                            :style="{ 
                                borderBottomStyle: 'solid',
                                borderBottomWidth: '1px',
                                borderColor: themeColors.border
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                POS Sales
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                Retail & F&B
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ revenueData.formatted_pos_sales_revenue }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                {{ Math.round(revenueData.pos_sales_percentage || 0) }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                      :style="{ 
                                          backgroundColor: 'rgba(139, 92, 246, 0.1)',
                                          color: '#8b5cf6'
                                      }">
                                    <ArrowTrendingUpIcon class="h-3 w-3 mr-1" />
                                    Growing
                                </span>
                            </td>
                        </tr>
                        
                        <!-- Other Revenue Row -->
                        <tr class="transition-colors"
                            :style="{ 
                                borderBottomStyle: 'solid',
                                borderBottomWidth: '1px',
                                borderColor: themeColors.border
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                Other Revenue
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                Services & Fees
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :style="{ color: themeColors.textPrimary }">
                                {{ revenueData.formatted_other_revenue }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                {{ Math.round(revenueData.other_revenue_percentage || 0) }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                      :style="{ 
                                          backgroundColor: 'rgba(250, 204, 21, 0.1)',
                                          color: themeColors.warning
                                      }">
                                    <ChartBarIcon class="h-3 w-3 mr-1" />
                                    Variable
                                </span>
                            </td>
                        </tr>
                        
                        <!-- Total Row -->
                        <tr class="transition-colors font-semibold"
                            :style="{ 
                                backgroundColor: themeColors.background,
                                borderTopStyle: 'solid',
                                borderTopWidth: '2px',
                                borderTopColor: themeColors.border
                            }">
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textPrimary }">
                                Total Revenue
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                All Sources
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-lg"
                                :style="{ color: themeColors.primary }">
                                {{ revenueData.formatted_total_revenue }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                :style="{ color: themeColors.textSecondary }">
                                100%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                      :style="{ 
                                          backgroundColor: revenueData.growth_rate >= 0 ? 'rgba(34, 197, 94, 0.1)' : 'rgba(239, 68, 68, 0.1)',
                                          color: revenueData.growth_rate >= 0 ? themeColors.success : themeColors.danger
                                      }">
                                    <ArrowTrendingUpIcon v-if="revenueData.growth_rate >= 0" 
                                                        class="h-3 w-3 mr-1" />
                                    <ArrowTrendingDownIcon v-else 
                                                         class="h-3 w-3 mr-1" />
                                    {{ revenueData.growth_rate > 0 ? '+' : '' }}{{ revenueData.growth_rate }}%
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import {
    DocumentArrowDownIcon,
    PrinterIcon,
    CurrencyDollarIcon,
    HomeIcon,
    ShoppingCartIcon,
    ChartBarIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

// Initialize theme
const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
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
    hover: `rgba(255, 255, 255, 0.1)`
}))

// Load theme on mount
loadTheme()

const props = defineProps({
    user: Object,
    revenueData: Object,
    period: String,
    startDate: String,
    endDate: String,
    currency: Object,
    isCustomAccountant: { type: Boolean, default: false },
    customAccountantsWithOverrides: { type: Array, default: () => [] },
})

const selectedPeriod = ref(props.period || 'monthly')
const selectedFormat = ref('xlsx')

const formatPeriod = (period) => {
    const periods = {
        daily: 'Daily',
        weekly: 'Weekly',
        monthly: 'Monthly',
        quarterly: 'Quarterly',
        yearly: 'Yearly'
    }
    return periods[period] || period
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const exportReport = () => {
    const formData = new FormData()
    formData.append('type', 'revenue')
    formData.append('period', selectedPeriod.value)
    if (props.startDate) formData.append('start_date', props.startDate)
    if (props.endDate) formData.append('end_date', props.endDate)
    formData.append('format', selectedFormat.value)
    
    // Create a form and submit it to trigger file download
    const form = document.createElement('form')
    form.method = 'POST'
    form.action = route('accountant.reports.export')
    form.style.display = 'none'
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (csrfToken) {
        const csrfInput = document.createElement('input')
        csrfInput.type = 'hidden'
        csrfInput.name = '_token'
        csrfInput.value = csrfToken
        form.appendChild(csrfInput)
    }
    
    // Add form data
    for (const [key, value] of formData.entries()) {
        const input = document.createElement('input')
        input.type = 'hidden'
        input.name = key
        input.value = value
        form.appendChild(input)
    }
    
    document.body.appendChild(form)
    form.submit()
    document.body.removeChild(form)
}

const printReport = () => {
    const formData = new FormData()
    formData.append('type', 'revenue')
    formData.append('period', selectedPeriod.value)
    if (props.startDate) formData.append('start_date', props.startDate)
    if (props.endDate) formData.append('end_date', props.endDate)
    formData.append('format', 'print')
    
    // Create a form and submit it to open print view
    const form = document.createElement('form')
    form.method = 'POST'
    form.action = route('accountant.reports.export')
    form.target = '_blank'
    form.style.display = 'none'
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (csrfToken) {
        const csrfInput = document.createElement('input')
        csrfInput.type = 'hidden'
        csrfInput.name = '_token'
        csrfInput.value = csrfToken
        form.appendChild(csrfInput)
    }
    
    // Add form data
    for (const [key, value] of formData.entries()) {
        const input = document.createElement('input')
        input.type = 'hidden'
        input.name = key
        input.value = value
        form.appendChild(input)
    }
    
    document.body.appendChild(form)
    form.submit()
    document.body.removeChild(form)
}

const applyPeriod = () => {
    router.get(route('accountant.reports.revenue'), {
        period: selectedPeriod.value
    }, {
        preserveScroll: true,
        preserveState: true
    })
}
</script>
