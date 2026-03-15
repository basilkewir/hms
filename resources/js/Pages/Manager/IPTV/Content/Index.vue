<template>
    <DashboardLayout title="IPTV Content" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">IPTV Content Management</h1>
                    <p class="text-gray-600 mt-2">Manage video content, movies, and on-demand services.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="uploadContent" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <PlusIcon class="h-4 w-4 mr-2 inline" />
                        Upload Content
                    </button>
                </div>
            </div>
        </div>

        <!-- Content Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <FilmIcon class="h-8 w-8 text-blue-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Content</p>
                        <p class="text-2xl font-bold text-gray-900">{{ contentStats.total }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <PlayIcon class="h-8 w-8 text-green-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Active Content</p>
                        <p class="text-2xl font-bold text-gray-900">{{ contentStats.active }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <EyeIcon class="h-8 w-8 text-purple-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Views Today</p>
                        <p class="text-2xl font-bold text-gray-900">{{ contentStats.viewsToday }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <CloudArrowUpIcon class="h-8 w-8 text-orange-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Storage Used</p>
                        <p class="text-2xl font-bold text-gray-900">{{ contentStats.storageUsed }}GB</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Library -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Content Library</h3>
                    <div class="flex space-x-2">
                        <select v-model="selectedCategory" 
                                class="border border-gray-300 rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Categories</option>
                            <option value="movies">Movies</option>
                            <option value="tv_shows">TV Shows</option>
                            <option value="documentaries">Documentaries</option>
                            <option value="music">Music</option>
                            <option value="kids">Kids</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div v-for="content in filteredContent" :key="content.id" 
                         class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="aspect-w-16 aspect-h-9 mb-4">
                            <div class="w-full h-32 bg-gray-300 rounded-lg flex items-center justify-center">
                                <FilmIcon class="h-12 w-12 text-gray-500" />
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <h4 class="font-medium text-gray-900 truncate">{{ content.title }}</h4>
                            <p class="text-sm text-gray-600 line-clamp-2">{{ content.description }}</p>
                            
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                      :class="getCategoryColor(content.category)">
                                    {{ formatCategory(content.category) }}
                                </span>
                                <span class="text-xs text-gray-500">{{ content.duration }}</span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center text-xs text-gray-500">
                                    <EyeIcon class="h-3 w-3 mr-1" />
                                    <span>{{ content.views }} views</span>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                      :class="getStatusColor(content.status)">
                                    {{ formatStatus(content.status) }}
                                </span>
                            </div>
                            
                            <div class="flex space-x-2 pt-2">
                                <button @click="editContent(content)" 
                                        class="flex-1 bg-blue-600 text-white px-2 py-1 rounded text-xs hover:bg-blue-700">
                                    Edit
                                </button>
                                <button @click="toggleStatus(content)" 
                                        :class="content.status === 'active' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
                                        class="flex-1 text-white px-2 py-1 rounded text-xs">
                                    {{ content.status === 'active' ? 'Disable' : 'Enable' }}
                                </button>
                                <button @click="deleteContent(content)" 
                                        class="flex-1 bg-gray-600 text-white px-2 py-1 rounded text-xs hover:bg-gray-700">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import {
    PlusIcon,
    FilmIcon,
    PlayIcon,
    EyeIcon,
    CloudArrowUpIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
})

const navigation = computed(() => getNavigationForRole('admin'))
const selectedCategory = ref('')

const contentStats = ref({
    total: 1250,
    active: 1180,
    viewsToday: 3420,
    storageUsed: 2.4
})

const content = ref([
    {
        id: 1,
        title: 'The Great Adventure',
        description: 'An epic adventure movie about courage and friendship.',
        category: 'movies',
        duration: '2h 15m',
        views: 1250,
        status: 'active'
    },
    {
        id: 2,
        title: 'Nature Documentary',
        description: 'Exploring the wonders of wildlife around the world.',
        category: 'documentaries',
        duration: '1h 30m',
        views: 890,
        status: 'active'
    },
    {
        id: 3,
        title: 'Kids Cartoon Series',
        description: 'Fun and educational content for children.',
        category: 'kids',
        duration: '25m',
        views: 2100,
        status: 'active'
    },
    {
        id: 4,
        title: 'Classic Music Collection',
        description: 'A collection of timeless classical music pieces.',
        category: 'music',
        duration: '3h 45m',
        views: 450,
        status: 'inactive'
    }
])

const filteredContent = computed(() => {
    if (!selectedCategory.value) return content.value
    return content.value.filter(item => item.category === selectedCategory.value)
})

const getCategoryColor = (category) => {
    const colors = {
        movies: 'bg-blue-100 text-blue-800',
        tv_shows: 'bg-green-100 text-green-800',
        documentaries: 'bg-purple-100 text-purple-800',
        music: 'bg-pink-100 text-pink-800',
        kids: 'bg-yellow-100 text-yellow-800'
    }
    return colors[category] || 'bg-gray-100 text-gray-800'
}

const getStatusColor = (status) => {
    const colors = {
        active: 'bg-green-100 text-green-800',
        inactive: 'bg-red-100 text-red-800',
        processing: 'bg-yellow-100 text-yellow-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

const formatCategory = (category) => {
    return category.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1)
}

const uploadContent = () => {
    alert('Upload content functionality')
}

const editContent = (content) => {
    alert(`Edit content: ${content.title}`)
}

const toggleStatus = (content) => {
    const newStatus = content.status === 'active' ? 'inactive' : 'active'
    if (confirm(`${newStatus === 'active' ? 'Enable' : 'Disable'} content "${content.title}"?`)) {
        content.status = newStatus
        alert(`Content "${content.title}" ${newStatus === 'active' ? 'enabled' : 'disabled'}`)
    }
}

const deleteContent = (contentItem) => {
    if (confirm(`Delete content "${contentItem.title}"?`)) {
        const index = content.value.findIndex(c => c.id === contentItem.id)
        if (index > -1) {
            content.value.splice(index, 1)
            alert(`Content "${contentItem.title}" deleted`)
        }
    }
}
</script>
