<template>
    <DashboardLayout title="Guest Satisfaction Reports" :user="user">
        <ReportPage
            title="Guest Satisfaction Reports"
            description="Guest feedback, satisfaction scores, and service quality analysis."
            category="guest"
        />
    </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import ReportPage from '@/Components/ReportPage.vue'

const props = defineProps({
    user: Object,
})
</script>
