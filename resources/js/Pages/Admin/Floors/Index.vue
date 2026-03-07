<template>
    <DashboardLayout title="Floors" :user="user" :navigation="navigation">
        <div class="bg-kotel-dark border border-kotel-yellow/30 rounded-lg p-6 mb-6"
             style="background-color: #1a1a1a; border: 1px solid rgba(255, 215, 0, 0.3);">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-kotel-yellow"
                        style="color: #FFD700;">Floors Management</h1>
                    <p class="text-kotel-sky-blue mt-1"
                       style="color: #87CEEB;">Manage building floors</p>
                </div>
                <Link :href="route('admin.floors.create')" 
                      class="bg-kotel-yellow text-kotel-black px-4 py-2 rounded hover:bg-kotel-yellow/90 transition-colors"
                      style="background-color: #FFD700; color: #000000;">
                    Add Floor
                </Link>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-kotel-yellow/20"
                       style="background-color: rgba(26, 26, 26, 0.9); border: 1px solid rgba(255, 215, 0, 0.2);">
                    <thead class="bg-kotel-black/50"
                           style="background-color: rgba(0, 0, 0, 0.5);">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-sky-blue uppercase tracking-wider"
                                style="color: #87CEEB;">Floor Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-sky-blue uppercase tracking-wider"
                                style="color: #87CEEB;">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-sky-blue uppercase tracking-wider"
                                style="color: #87CEEB;">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-sky-blue uppercase tracking-wider"
                                style="color: #87CEEB;">Rooms</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-sky-blue uppercase tracking-wider"
                                style="color: #87CEEB;">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-kotel-sky-blue uppercase tracking-wider"
                                style="color: #87CEEB;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-kotel-black/30 divide-y divide-kotel-yellow/20"
                           style="background-color: rgba(0, 0, 0, 0.3); border-top: 1px solid rgba(255, 215, 0, 0.2);">
                        <tr v-for="floor in floors" :key="floor.id" 
                            class="hover:bg-kotel-black/50 transition-colors"
                            style="transition: background-color 0.2s;">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white"
                                style="color: #ffffff;">{{ floor.floor_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-kotel-sky-blue/80"
                                style="color: rgba(135, 206, 235, 0.8);">{{ floor.name }}</td>
                            <td class="px-6 py-4 text-sm text-kotel-sky-blue/60"
                                style="color: rgba(135, 206, 235, 0.6);">{{ floor.description || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-kotel-sky-blue/80"
                                style="color: rgba(135, 206, 235, 0.8);">{{ floor.room_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border" 
                                      :class="floor.is_active ? 'bg-emerald-800/50 text-emerald-300 border-emerald-400/50' : 'bg-red-800/50 text-red-300 border-red-400/50'"
                                      :style="floor.is_active ? {
                                          backgroundColor: 'rgba(16, 185, 129, 0.5)',
                                          color: '#86efac',
                                          border: '1px solid rgba(52, 211, 153, 0.5)'
                                      } : {
                                          backgroundColor: 'rgba(153, 27, 27, 0.5)',
                                          color: '#fca5a5',
                                          border: '1px solid rgba(248, 113, 113, 0.5)'
                                      }">
                                    {{ floor.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="route('admin.floors.edit', floor.id)" 
                                      class="text-kotel-yellow hover:text-kotel-yellow/80 mr-3 transition-colors"
                                      style="color: #FFD700;">Edit</Link>
                                <button @click="deleteFloor(floor)" 
                                        class="text-red-400 hover:text-red-300 transition-colors"
                                        style="color: #f87171;">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'

const props = defineProps({
    user: Object,
    floors: Array,
})

const navigation = computed(() => getNavigationForRole('admin'))

const deleteFloor = (floor) => {
    if (confirm(`Are you sure you want to delete ${floor.name || 'Floor ' + floor.floor_number}?`)) {
        router.delete(route('admin.floors.destroy', floor.id))
    }
}
</script>
