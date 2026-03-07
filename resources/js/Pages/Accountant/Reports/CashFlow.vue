<template>
    <DashboardLayout title="Cash Flow Statement" :user="user">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Cash Flow Statement</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">Statement of cash flows from operating, investing, and financing activities.</p>
                </div>
                <div class="flex space-x-3">
                    <select v-model="selectedPeriod" @change="applyPeriod"
                            class="rounded-md px-3 py-2 focus:outline-none transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.background,
                                borderColor: themeColors.border,
                                color: themeColors.textPrimary
                            }">
                        <option value="monthly">This Month</option>
                        <option value="quarterly">This Quarter</option>
                        <option value="yearly">This Year</option>
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

        <!-- Cash Flow Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
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
                           :style="{ color: themeColors.textSecondary }">Beginning Cash</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ cashFlowData.formatted_beginning_cash || formatCurrency(cashFlowData.beginningCash || 0) }}</p>
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
                         :style="{ backgroundColor: 'rgba(34, 197, 94, 0.1)' }">
                        <ArrowTrendingUpIcon class="h-6 w-6" :style="{ color: themeColors.success }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Operating Cash Flow</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.success }">{{ cashFlowData.formatted_net_operating_cash_flow || formatCurrency(cashFlowData.net_operating_cash_flow || 0) }}</p>
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
                         :style="{ backgroundColor: cashFlowData.net_cash_change >= 0 ? 'rgba(34, 197, 94, 0.1)' : 'rgba(239, 68, 68, 0.1)' }">
                        <ArrowTrendingDownIcon v-if="cashFlowData.net_cash_change >= 0" class="h-6 w-6" :style="{ color: themeColors.success }" />
                        <ArrowTrendingUpIcon v-else class="h-6 w-6" :style="{ color: themeColors.danger }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Net Cash Change</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: cashFlowData.net_cash_change >= 0 ? themeColors.success : themeColors.danger }">
                            {{ cashFlowData.formatted_net_cash_change || formatCurrency(cashFlowData.net_cash_change || 0) }}
                        </p>
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
                         :style="{ backgroundColor: 'rgba(139, 92, 246, 0.1)' }">
                        <BanknotesIcon class="h-6 w-6" :style="{ color: '#8b5cf6' }" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1"
                           :style="{ color: themeColors.textSecondary }">Ending Cash</p>
                        <p class="text-2xl font-bold"
                           :style="{ color: themeColors.textPrimary }">{{ cashFlowData.formatted_ending_cash || formatCurrency(cashFlowData.ending_cash || 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Cash Flow Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Operating Activities -->
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
                        :style="{ color: themeColors.textPrimary }">Operating Activities</h3>
                    <p class="text-sm mt-1"
                       :style="{ color: themeColors.textSecondary }">Cash flow from primary business operations</p>
                </div>
                
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textSecondary }">Room Revenue</span>
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.success }">{{ cashFlowData.formatted_room_cash_flow || formatCurrency(cashFlowData.room_cash_flow || 0) }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textSecondary }">POS Sales</span>
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.success }">{{ cashFlowData.formatted_pos_cash_flow || formatCurrency(cashFlowData.pos_cash_flow || 0) }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textSecondary }">Other Revenue</span>
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.success }">{{ cashFlowData.formatted_other_revenue_cash_flow || formatCurrency(cashFlowData.other_revenue_cash_flow || 0) }}</span>
                    </div>
                    
                    <div class="border-t pt-4"
                         :style="{ borderColor: themeColors.border }">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Total Operating Inflow</span>
                            <span class="text-sm font-bold"
                                  :style="{ color: themeColors.success }">{{ cashFlowData.formatted_total_operating_cash_inflow || formatCurrency(cashFlowData.total_operating_cash_inflow || 0) }}</span>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textSecondary }">Operating Expenses</span>
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.danger }">{{ cashFlowData.formatted_operating_expenses || formatCurrency(cashFlowData.operating_expenses || 0) }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textSecondary }">Payroll Expenses</span>
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.danger }">{{ cashFlowData.formatted_payroll_expenses || formatCurrency(cashFlowData.payroll_expenses || 0) }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textSecondary }">Cost of Goods Sold</span>
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.danger }">{{ cashFlowData.formatted_cogs || formatCurrency(cashFlowData.cogs || 0) }}</span>
                    </div>
                    
                    <div class="border-t pt-4"
                         :style="{ borderColor: themeColors.border }">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Total Operating Outflow</span>
                            <span class="text-sm font-bold"
                                  :style="{ color: themeColors.danger }">{{ cashFlowData.formatted_total_operating_cash_outflow || formatCurrency(cashFlowData.total_operating_cash_outflow || 0) }}</span>
                        </div>
                    </div>
                    
                    <div class="border-t pt-4"
                         :style="{ borderColor: themeColors.border }">
                        <div class="flex justify-between items-center">
                            <span class="text-base font-bold"
                                  :style="{ color: themeColors.textPrimary }">Net Operating Cash Flow</span>
                            <span class="text-base font-bold"
                                  :style="{ color: cashFlowData.net_operating_cash_flow >= 0 ? themeColors.success : themeColors.danger }">
                                {{ cashFlowData.formatted_net_operating_cash_flow || formatCurrency(cashFlowData.net_operating_cash_flow || 0) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Investing & Financing Activities -->
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
                        :style="{ color: themeColors.textPrimary }">Investing & Financing</h3>
                    <p class="text-sm mt-1"
                       :style="{ color: themeColors.textSecondary }">Capital and financing activities</p>
                </div>
                
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textSecondary }">Investing Cash Flow</span>
                        <span class="text-sm font-medium"
                              :style="{ color: cashFlowData.investing_cash_flow >= 0 ? themeColors.success : themeColors.danger }">
                            {{ cashFlowData.formatted_investing_cash_flow || formatCurrency(cashFlowData.investing_cash_flow || 0) }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium"
                              :style="{ color: themeColors.textSecondary }">Financing Cash Flow</span>
                        <span class="text-sm font-medium"
                              :style="{ color: cashFlowData.financing_cash_flow >= 0 ? themeColors.success : themeColors.danger }">
                            {{ cashFlowData.formatted_financing_cash_flow || formatCurrency(cashFlowData.financing_cash_flow || 0) }}
                        </span>
                    </div>
                    
                    <div class="border-t pt-4"
                         :style="{ borderColor: themeColors.border }">
                        <div class="flex justify-between items-center">
                            <span class="text-base font-bold"
                                  :style="{ color: themeColors.textPrimary }">Net Cash Change</span>
                            <span class="text-base font-bold"
                                  :style="{ color: cashFlowData.net_cash_change >= 0 ? themeColors.success : themeColors.danger }">
                                {{ cashFlowData.formatted_net_cash_change || formatCurrency(cashFlowData.net_cash_change || 0) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="border-t pt-4"
                         :style="{ borderColor: themeColors.border }">
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium"
                                      :style="{ color: themeColors.textSecondary }">Beginning Cash</span>
                                <span class="text-sm font-medium"
                                      :style="{ color: themeColors.textPrimary }">{{ cashFlowData.formatted_beginning_cash || formatCurrency(cashFlowData.beginning_cash || 0) }}</span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium"
                                      :style="{ color: themeColors.textPrimary }">Ending Cash</span>
                                <span class="text-lg font-bold"
                                      :style="{ color: themeColors.primary }">{{ cashFlowData.formatted_ending_cash || formatCurrency(cashFlowData.ending_cash || 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cash Flow Reconciliation -->
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
                    :style="{ color: themeColors.textPrimary }">Cash Flow Reconciliation</h3>
                <p class="text-sm mt-1"
                   :style="{ color: themeColors.textSecondary }">Reconciliation of net income to cash flow</p>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Net Income</span>
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textPrimary }">{{ cashFlowData.formatted_net_income || formatCurrency(cashFlowData.net_income || 0) }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Depreciation & Amortization</span>
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.success }">{{ cashFlowData.formatted_depreciation_amortization || formatCurrency(cashFlowData.depreciation_amortization || 0) }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Working Capital Changes</span>
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textPrimary }">{{ cashFlowData.formatted_working_capital_change || formatCurrency(cashFlowData.working_capital_change || 0) }}</span>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textSecondary }">Net Income Adjustments</span>
                            <span class="text-sm font-medium"
                                  :style="{ color: themeColors.textPrimary }">{{ formatCurrency((cashFlowData.depreciation_amortization || 0) + (cashFlowData.working_capital_change || 0)) }}</span>
                        </div>
                        
                        <div class="border-t pt-3"
                             :style="{ borderColor: themeColors.border }">
                            <div class="flex justify-between items-center">
                                <span class="text-base font-bold"
                                      :style="{ color: themeColors.textPrimary }">Reconciled Cash Flow</span>
                                <span class="text-base font-bold"
                                      :style="{ color: cashFlowData.net_operating_cash_flow >= 0 ? themeColors.success : themeColors.danger }">
                                    {{ cashFlowData.formatted_net_operating_cash_flow || formatCurrency(cashFlowData.net_operating_cash_flow || 0) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cash Flow Statement -->
        <div class="rounded-lg overflow-hidden shadow"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border,
                 borderStyle: 'solid',
                 borderWidth: '1px'
             }">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b"
                 :style="{ 
                     borderColor: themeColors.border,
                     borderBottomWidth: '1px'
                 }">
                <h3 class="text-lg font-medium"
                    :style="{ color: themeColors.textPrimary }">
                    Cash Flow Statement for {{ formatPeriod(selectedPeriod) }}
                </h3>
            </div>

            <div class="p-6">
                <!-- Operating Activities -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold mb-4 pb-2"
                        :style="{ color: themeColors.textPrimary, borderBottom: '1px solid ' + themeColors.border }">CASH FLOWS FROM OPERATING ACTIVITIES</h4>
                    <div class="space-y-2 ml-4">
                        <div class="flex justify-between py-1 font-medium">
                            <span :style="{ color: themeColors.textPrimary }">Net Income</span>
                            <span :style="{ color: themeColors.success }">{{ formatCurrency(cashFlowData.netIncome || 0) }}</span>
                        </div>

                        <div class="mt-4 mb-2">
                            <span class="font-medium" :style="{ color: themeColors.textSecondary }">Adjustments to reconcile net income:</span>
                        </div>

                        <div v-for="adjustment in cashFlowData.operatingAdjustments" :key="adjustment.item"
                             class="flex justify-between py-1 ml-4">
                            <span :style="{ color: themeColors.textPrimary }">{{ adjustment.item }}</span>
                            <span class="font-medium"
                                  :style="{ color: adjustment.amount >= 0 ? themeColors.success : themeColors.danger }">
                                {{ formatCurrency(Math.abs(adjustment.amount || 0)) }}
                            </span>
                        </div>

                        <div class="flex justify-between py-3 font-bold text-lg"
                             :style="{ borderTop: '2px solid ' + themeColors.border }">
                            <span :style="{ color: themeColors.textPrimary }">Net Cash from Operating Activities</span>
                            <span :style="{ color: themeColors.success }">{{ formatCurrency(cashFlowData.operatingCashFlow || 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Investing Activities -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold mb-4 pb-2"
                        :style="{ color: themeColors.textPrimary, borderBottom: '1px solid ' + themeColors.border }">CASH FLOWS FROM INVESTING ACTIVITIES</h4>
                    <div class="space-y-2 ml-4">
                        <div v-for="investment in cashFlowData.investingActivities" :key="investment.item"
                             class="flex justify-between py-1">
                            <span :style="{ color: themeColors.textPrimary }">{{ investment.item }}</span>
                            <span class="font-medium"
                                  :style="{ color: investment.amount >= 0 ? themeColors.success : themeColors.danger }">
                                {{ formatCurrency(Math.abs(investment.amount || 0)) }}
                            </span>
                        </div>
                        <div class="flex justify-between py-3 font-bold"
                             :style="{ borderTop: '2px solid ' + themeColors.border }">
                            <span :style="{ color: themeColors.textPrimary }">Net Cash from Investing Activities</span>
                            <span :style="{ color: cashFlowData.investingCashFlow >= 0 ? themeColors.success : themeColors.danger }">
                                {{ formatCurrency(Math.abs(cashFlowData.investingCashFlow || 0)) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Financing Activities -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold mb-4 pb-2"
                        :style="{ color: themeColors.textPrimary, borderBottom: '1px solid ' + themeColors.border }">CASH FLOWS FROM FINANCING ACTIVITIES</h4>
                    <div class="space-y-2 ml-4">
                        <div v-for="financing in cashFlowData.financingActivities" :key="financing.item"
                             class="flex justify-between py-1">
                            <span :style="{ color: themeColors.textPrimary }">{{ financing.item }}</span>
                            <span class="font-medium"
                                  :style="{ color: financing.amount >= 0 ? themeColors.success : themeColors.danger }">
                                {{ formatCurrency(Math.abs(financing.amount || 0)) }}
                            </span>
                        </div>
                        <div class="flex justify-between py-3 font-bold"
                             :style="{ borderTop: '2px solid ' + themeColors.border }">
                            <span :style="{ color: themeColors.textPrimary }">Net Cash from Financing Activities</span>
                            <span :style="{ color: cashFlowData.financingCashFlow >= 0 ? themeColors.success : themeColors.danger }">
                                {{ formatCurrency(Math.abs(cashFlowData.financingCashFlow || 0)) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Net Change in Cash -->
                <div class="mb-8">
                    <div class="flex justify-between py-3 font-bold text-lg px-4 rounded"
                         :style="{ 
                             borderTop: '2px solid ' + themeColors.border,
                             backgroundColor: themeColors.background
                         }">
                        <span :style="{ color: themeColors.textPrimary }">NET CHANGE IN CASH</span>
                        <span :style="{ color: cashFlowData.netCashChange >= 0 ? themeColors.success : themeColors.danger }">
                            {{ formatCurrency(Math.abs(cashFlowData.netCashChange || 0)) }}
                        </span>
                    </div>
                </div>

                <!-- Cash Reconciliation -->
                <div :style="{ borderTop: '4px solid ' + themeColors.border, paddingTop: '1rem' }">
                    <div class="space-y-2">
                        <div class="flex justify-between py-2 font-medium">
                            <span :style="{ color: themeColors.textPrimary }">Cash at Beginning of Period</span>
                            <span :style="{ color: themeColors.textPrimary }">{{ formatCurrency(cashFlowData.beginningCash || 0) }}</span>
                        </div>
                        <div class="flex justify-between py-2 font-medium">
                            <span :style="{ color: themeColors.textPrimary }">Net Change in Cash</span>
                            <span :style="{ color: cashFlowData.netCashChange >= 0 ? themeColors.success : themeColors.danger }">
                                {{ formatCurrency(Math.abs(cashFlowData.netCashChange || 0)) }}
                            </span>
                        </div>
                        <div class="flex justify-between py-3 font-bold text-xl"
                             :style="{ borderTop: '2px solid ' + themeColors.border }">
                            <span :style="{ color: themeColors.textPrimary }">CASH AT END OF PERIOD</span>
                            <span :style="{ color: themeColors.primary }">{{ formatCurrency(cashFlowData.endingCash || 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useTheme } from '@/Composables/useTheme.js'
import { formatCurrency } from '@/Utils/currency.js'
import {
    DocumentArrowDownIcon,
    CurrencyDollarIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    BanknotesIcon
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
    cashFlowData: Object,
    period: String,
    startDate: String,
    endDate: String,
    currency: Object,
})

const selectedPeriod = ref(props.period || 'monthly')
const selectedFormat = ref('xlsx')

const cashFlowData = computed(() => {
    const data = props.cashFlowData || {}
    return {
        beginningCash: data.beginning_cash || 0,
        endingCash: data.ending_cash || 0,
        netCashChange: data.net_cash_change || 0,
        operatingCashFlow: data.operating_cash_flow || 0,
        investingCashFlow: data.investing_cash_flow || 0,
        financingCashFlow: data.financing_cash_flow || 0,
        netIncome: data.net_income || 0,
        operatingAdjustments: data.operating_adjustments || [],
        investingActivities: data.investing_activities || [],
        financingActivities: data.financing_activities || []
    }
})

const formatPeriod = (period) => {
    const labels = {
        monthly: 'This Month',
        quarterly: 'This Quarter',
        yearly: 'This Year',
        custom: 'Custom Period'
    }
    return labels[period] || 'Current Period'
}

const exportReport = () => {
    const formData = new FormData()
    formData.append('type', 'cash-flow')
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
    formData.append('type', 'cash-flow')
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
    router.get(route('accountant.reports.cash-flow'), {
        period: selectedPeriod.value
    }, {
        preserveScroll: true,
        preserveState: true
    })
}
</script>
