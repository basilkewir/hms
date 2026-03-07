<template>
    <DashboardLayout title="Messages" :user="user" :navigation="navigation">
        <!-- Messages Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Messages</h1>
                    <p class="text-gray-600 mt-2">Company announcements and communications.</p>
                </div>
                <div class="flex space-x-3">
                    <select v-model="filterType" 
                            class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Messages</option>
                        <option value="announcement">Announcements</option>
                        <option value="policy">Policy Updates</option>
                        <option value="schedule">Schedule Changes</option>
                        <option value="training">Training</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Message Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <EnvelopeIcon class="h-8 w-8 text-blue-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Messages</p>
                        <p class="text-2xl font-bold text-gray-900">{{ messageStats.total }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <EnvelopeOpenIcon class="h-8 w-8 text-green-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Read Messages</p>
                        <p class="text-2xl font-bold text-gray-900">{{ messageStats.read }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <ExclamationCircleIcon class="h-8 w-8 text-red-500 mr-4" />
                    <div>
                        <p class="text-sm font-medium text-gray-500">Unread Messages</p>
                        <p class="text-2xl font-bold text-gray-900">{{ messageStats.unread }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages List -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Messages</h3>
            <div class="space-y-4">
                <div v-for="message in filteredMessages" :key="message.id"
                     class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors cursor-pointer"
                     :class="{ 'bg-blue-50': !message.isRead }"
                     @click="markAsRead(message)">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center">
                            <component :is="getMessageIcon(message.type)" 
                                       class="h-5 w-5 mr-3" 
                                       :class="getMessageColor(message.type)" />
                            <h4 class="font-medium text-gray-900">{{ message.title }}</h4>
                            <span v-if="!message.isRead" class="ml-2 w-2 h-2 bg-blue-500 rounded-full"></span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs px-2 py-1 rounded-full"
                                  :class="getTypeColor(message.type)">
                                {{ formatType(message.type) }}
                            </span>
                            <span class="text-sm text-gray-500">{{ formatDate(message.date) }}</span>
                        </div>
                    </div>
                    
                    <p class="text-sm text-gray-600 mb-3">{{ message.preview }}</p>
                    
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>From: {{ message.sender }}</span>
                        <span v-if="message.priority === 'high'" class="text-red-600 font-medium">High Priority</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Detail Modal -->
        <div v-if="selectedMessage" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
             @click="closeMessage">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white"
                 @click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ selectedMessage.title }}</h3>
                    <button @click="closeMessage" class="text-gray-400 hover:text-gray-600">
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>
                
                <div class="mb-4">
                    <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                        <span>From: {{ selectedMessage.sender }}</span>
                        <span>{{ formatDate(selectedMessage.date) }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs px-2 py-1 rounded-full"
                              :class="getTypeColor(selectedMessage.type)">
                            {{ formatType(selectedMessage.type) }}
                        </span>
                        <span v-if="selectedMessage.priority === 'high'" 
                              class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-800">
                            High Priority
                        </span>
                    </div>
                </div>
                
                <div class="prose max-w-none">
                    <p class="text-gray-700 leading-relaxed">{{ selectedMessage.content }}</p>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button @click="closeMessage"
                            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        Close
                    </button>
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
    EnvelopeIcon,
    EnvelopeOpenIcon,
    ExclamationCircleIcon,
    SpeakerWaveIcon,
    DocumentTextIcon,
    CalendarIcon,
    AcademicCapIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
})

const navigation = computed(() => getNavigationForRole('staff'))

const filterType = ref('')
const selectedMessage = ref(null)

const messages = ref([
    {
        id: 1,
        title: 'New Safety Protocols Effective Immediately',
        preview: 'Important updates to our safety procedures that all staff must follow...',
        content: 'Dear Team,\n\nWe are implementing new safety protocols effective immediately. All staff members are required to follow these updated procedures to ensure the safety of our guests and colleagues.\n\nKey changes include:\n- Enhanced cleaning procedures\n- Updated emergency protocols\n- New personal protective equipment requirements\n\nPlease review the attached documentation and confirm your understanding by responding to this message.\n\nThank you for your cooperation.',
        type: 'policy',
        sender: 'HR Department',
        date: new Date(),
        isRead: false,
        priority: 'high'
    },
    {
        id: 2,
        title: 'Staff Meeting - June 25th',
        preview: 'Monthly all-hands meeting scheduled for next Tuesday at 2 PM...',
        content: 'All staff members are invited to attend our monthly meeting on June 25th at 2:00 PM in the main conference room.\n\nAgenda:\n- Monthly performance review\n- New initiatives\n- Q&A session\n\nRefreshments will be provided. Please confirm your attendance.',
        type: 'announcement',
        sender: 'Management',
        date: new Date(Date.now() - 86400000),
        isRead: true,
        priority: 'normal'
    },
    {
        id: 3,
        title: 'Schedule Change - Weekend Shifts',
        preview: 'Important changes to weekend scheduling starting next month...',
        content: 'Please note the following changes to weekend scheduling:\n\n- Saturday shifts will now start 30 minutes earlier\n- Sunday coverage has been extended\n- New break rotation schedule\n\nPlease check your updated schedule in the staff portal.',
        type: 'schedule',
        sender: 'Scheduling Department',
        date: new Date(Date.now() - 172800000),
        isRead: false,
        priority: 'normal'
    },
    {
        id: 4,
        title: 'Mandatory Training - Customer Service Excellence',
        preview: 'All staff must complete customer service training by month end...',
        content: 'All team members are required to complete the Customer Service Excellence training module by the end of this month.\n\nThe training covers:\n- Guest interaction best practices\n- Conflict resolution\n- Service recovery\n\nAccess the training portal using your employee credentials.',
        type: 'training',
        sender: 'Training Department',
        date: new Date(Date.now() - 259200000),
        isRead: true,
        priority: 'normal'
    }
])

const messageStats = computed(() => {
    return {
        total: messages.value.length,
        read: messages.value.filter(m => m.isRead).length,
        unread: messages.value.filter(m => !m.isRead).length,
    }
})

const filteredMessages = computed(() => {
    if (!filterType.value) return messages.value
    return messages.value.filter(message => message.type === filterType.value)
})

const markAsRead = (message) => {
    message.isRead = true
    selectedMessage.value = message
}

const closeMessage = () => {
    selectedMessage.value = null
}

const getMessageIcon = (type) => {
    const icons = {
        announcement: SpeakerWaveIcon,
        policy: DocumentTextIcon,
        schedule: CalendarIcon,
        training: AcademicCapIcon,
    }
    return icons[type] || EnvelopeIcon
}

const getMessageColor = (type) => {
    const colors = {
        announcement: 'text-blue-500',
        policy: 'text-red-500',
        schedule: 'text-yellow-500',
        training: 'text-green-500',
    }
    return colors[type] || 'text-gray-500'
}

const getTypeColor = (type) => {
    const colors = {
        announcement: 'bg-blue-100 text-blue-800',
        policy: 'bg-red-100 text-red-800',
        schedule: 'bg-yellow-100 text-yellow-800',
        training: 'bg-green-100 text-green-800',
    }
    return colors[type] || 'bg-gray-100 text-gray-800'
}

const formatType = (type) => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (date) => {
    return date.toLocaleDateString([], { 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>
