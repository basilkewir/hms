<template>
    <div class="min-h-screen bg-white text-gray-900 p-8">
        <div class="flex items-center justify-between mb-6 print:hidden">
            <div>
                <h1 class="text-2xl font-bold">Weekly Roster</h1>
                <p class="text-gray-600">Work program for {{ currentWeek }}</p>
            </div>
            <button
                @click="printPage"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
            >
                Print
            </button>
        </div>

        <div class="mb-4 text-sm text-gray-600 print:text-black">
            Generated on: {{ generatedOn }}
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-300 text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-2 py-1 text-left">Employee</th>
                        <th v-for="day in weekDays" :key="day" class="border border-gray-300 px-2 py-1 text-center">
                            {{ day }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="employee in scheduleData" :key="employee.id">
                        <td class="border border-gray-300 px-2 py-1">
                            <div class="font-semibold">{{ employee.name }}</div>
                            <div class="text-[10px] text-gray-600">{{ formatDepartment(employee.department) }}</div>
                        </td>
                        <td
                            v-for="(shift, idx) in employee.shifts"
                            :key="idx"
                            class="border border-gray-300 px-1 py-1 align-top"
                        >
                            <div v-if="shift" class="gantt-cell">
                                <div class="gantt-bar">
                                    <div class="gantt-bar-label">
                                        {{ shift.start }} - {{ shift.end }}
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-[10px] text-gray-400 text-center">
                                Off
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'

const props = defineProps({
    scheduleData: Array,
    currentWeek: String,
    weekDays: Array,
})

const generatedOn = computed(() => new Date().toLocaleString())

const formatDepartment = (department) => {
    if (!department) return 'General'
    return department.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const printPage = () => {
    window.print()
}

onMounted(() => {
    // Auto-open print dialog when opened in a new tab/window
    setTimeout(() => {
        window.print()
    }, 300)
})
</script>

<style scoped>
.gantt-cell {
    @apply w-full;
}

.gantt-bar {
    width: 100%;
    background: linear-gradient(to right, #bfdbfe, #93c5fd);
    border-radius: 9999px;
    padding: 2px 4px;
    border: 1px solid #60a5fa;
}

.gantt-bar-label {
    font-size: 10px;
    font-weight: 600;
    color: #1e3a8a;
    text-align: center;
    white-space: nowrap;
}

@media print {
    .print:hidden {
        display: none;
    }

    body {
        background: #ffffff !important;
    }
}
</style>

