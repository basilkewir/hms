<template>
    <DashboardLayout title="Create Building Wing" :user="user" :navigation="navigation">
        <div class="bg-kotel-dark border border-kotel-yellow/30 rounded-lg p-6">
            <h1 class="text-2xl font-bold text-kotel-yellow mb-6">Create New Building Wing</h1>
            <form @submit.prevent="submit">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-kotel-sky-blue mb-2">Name *</label>
                        <input v-model="form.name" type="text" required 
                               class="w-full bg-kotel-black/50 border border-kotel-yellow/30 rounded-md px-3 py-2 text-white placeholder-kotel-sky-blue/60 focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:border-kotel-yellow">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-kotel-sky-blue mb-2">Code</label>
                        <input v-model="form.code" type="text" 
                               class="w-full bg-kotel-black/50 border border-kotel-yellow/30 rounded-md px-3 py-2 text-white placeholder-kotel-sky-blue/60 focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:border-kotel-yellow"
                               placeholder="e.g., MAIN, NORTH">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-kotel-sky-blue mb-2">Description</label>
                        <textarea v-model="form.description" rows="3"
                                  class="w-full bg-kotel-black/50 border border-kotel-yellow/30 rounded-md px-3 py-2 text-white placeholder-kotel-sky-blue/60 focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:border-kotel-yellow"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-kotel-sky-blue mb-2">Sort Order</label>
                        <input v-model="form.sort_order" type="number" 
                               class="w-full bg-kotel-black/50 border border-kotel-yellow/30 rounded-md px-3 py-2 text-white placeholder-kotel-sky-blue/60 focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:border-kotel-yellow">
                    </div>
                    <div class="flex items-center">
                        <input v-model="form.is_active" type="checkbox" class="mr-2 w-4 h-4 text-kotel-yellow bg-kotel-black/50 border-kotel-yellow/30 rounded focus:ring-kotel-yellow focus:border-kotel-yellow">
                        <label class="text-sm font-medium text-kotel-sky-blue">Active</label>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="submit" :disabled="form.processing" 
                            class="bg-kotel-yellow text-kotel-black px-4 py-2 rounded hover:bg-kotel-yellow/90 disabled:opacity-50 transition-colors">
                        Create Building Wing
                    </button>
                    <Link :href="route('admin.building-wings.index')" 
                          class="bg-kotel-gray text-kotel-sky-blue px-4 py-2 rounded hover:bg-kotel-gray/80 transition-colors">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'

const props = defineProps({
    user: Object,
})

const navigation = computed(() => getNavigationForRole('admin'))

const form = useForm({
    name: '',
    code: '',
    description: '',
    is_active: true,
    sort_order: 0,
})

const submit = () => {
    form.post(route('admin.building-wings.store'))
}
</script>
