<template>
    <DashboardLayout title="Test Budget Dashboard">
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-4">Test Budget Dashboard</h1>
            <p class="mb-4">This is a test to verify Vue component rendering works.</p>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                <p>If you can see this, Vue rendering is working!</p>
                <p>User: {{ user?.name || 'Not logged in' }}</p>
                <p>Summary Total Budgets: {{ summary?.total_budgets || 'N/A' }}</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const props = defineProps({
    user: Object,
    summary: Object,
    activeBudgets: Array,
    departmentUtilization: Array
})
</script>
