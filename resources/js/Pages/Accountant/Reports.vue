<template>
    <DashboardLayout title="Financial Reports" :user="user">
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Financial Reports</h1>
                    <p class="text-gray-600 mt-2">Open official financial statements.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <Link :href="route('accountant.reports.balance-sheet')" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center mb-4">
                    <ScaleIcon class="h-8 w-8 text-blue-500 mr-3" />
                    <h3 class="text-lg font-semibold text-gray-900">Balance Sheet</h3>
                </div>
                <p class="text-gray-600 text-sm">Assets, liabilities, and equity snapshot.</p>
            </Link>

            <Link :href="route('accountant.reports.cash-flow')" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center mb-4">
                    <ArrowsRightLeftIcon class="h-8 w-8 text-green-500 mr-3" />
                    <h3 class="text-lg font-semibold text-gray-900">Cash Flow</h3>
                </div>
                <p class="text-gray-600 text-sm">Operating, investing, and financing cash flows.</p>
            </Link>

            <Link :href="route('accountant.reports.profit-loss')" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center mb-4">
                    <ChartBarSquareIcon class="h-8 w-8 text-purple-500 mr-3" />
                    <h3 class="text-lg font-semibold text-gray-900">Profit & Loss</h3>
                </div>
                <p class="text-gray-600 text-sm">Income and expense performance over time.</p>
            </Link>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {
    ScaleIcon,
    ArrowsRightLeftIcon,
    ChartBarSquareIcon,
} from '@heroicons/vue/24/outline'

defineProps({
    user: Object,
})
</script>
