<template>
    <DashboardLayout title="Expense Reports" :user="user">
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Expense Reports</h1>
                    <p class="text-gray-600 mt-2">Summary of expenses by month and category.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500">Report Period</p>
                <p class="text-lg font-semibold text-gray-900">
                    {{ formatDate(summary.period_start) }} to {{ formatDate(summary.period_end) }}
                </p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500">Total Expenses</p>
                <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(summary.total_expenses || 0) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500">Categories with Spend</p>
                <p class="text-2xl font-bold text-gray-900">{{ categoryTotals.length }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Monthly Expenses</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Month
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="row in monthlyTotals" :key="row.month">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ row.month }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ formatCurrency(row.total || 0) }}
                                </td>
                            </tr>
                            <tr v-if="monthlyTotals.length === 0">
                                <td colspan="2" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No expense activity for this period.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">By Category</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Category
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="row in categoryTotals" :key="row.name">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <span class="w-3 h-3 rounded-full"
                                              :style="`background-color: ${row.color || '#3b82f6'}`"></span>
                                        <span class="text-sm text-gray-900">{{ row.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ formatCurrency(row.total || 0) }}
                                </td>
                            </tr>
                            <tr v-if="categoryTotals.length === 0">
                                <td colspan="2" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No category totals available.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatCurrency } from '@/Utils/currency.js'

const props = defineProps({
    user: Object,
    summary: Object,
    monthlyTotals: Array,
    categoryTotals: Array,
})

const summary = computed(() => props.summary || {})
const monthlyTotals = computed(() => props.monthlyTotals || [])
const categoryTotals = computed(() => props.categoryTotals || [])

const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}
</script>
