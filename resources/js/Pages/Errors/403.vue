<template>
    <div class="min-h-screen" :style="{ backgroundColor: themeColors.background }">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8"
             :style="{ 
                 backgroundColor: themeColors.card,
                 borderColor: themeColors.border 
             }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2"
                        :style="{ color: themeColors.textPrimary }">Access Denied</h1>
                    <p class="mt-2"
                       :style="{ color: themeColors.textSecondary }">You do not have permission to access this page.</p>
                </div>
                <div class="flex space-x-3">
                    <Link
                        :href="route('dashboard')"
                        class="px-4 py-2 rounded-md transition-colors flex items-center"
                        :style="{ 
                            backgroundColor: themeColors.primary,
                            color: '#ffffff'
                        }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Go to Dashboard
                    </Link>
                    <button
                        @click="$router.go(-1)"
                        class="px-4 py-2 rounded-md transition-colors flex items-center"
                        :style="{ 
                            backgroundColor: themeColors.secondary,
                            color: themeColors.textPrimary
                        }"
                        @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                        @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Go Back
                    </button>
                </div>
            </div>
        </div>

        <!-- Error Content -->
        <div class="max-w-4xl mx-auto px-6">
            <div class="shadow rounded-lg p-8 text-center"
                 :style="{ 
                     backgroundColor: themeColors.card,
                     borderColor: themeColors.border 
                 }">
                <!-- Error Icon -->
                <div class="flex justify-center mb-8">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-32 h-32 rounded-full animate-pulse"
                                 :style="{ backgroundColor: 'rgba(239, 68, 68, 0.1)' }"></div>
                        </div>
                        <div class="relative">
                            <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 :style="{ color: themeColors.danger }">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Error Details -->
                <div class="space-y-6">
                    <div>
                        <h1 class="text-6xl font-bold mb-2"
                            :style="{ color: themeColors.textPrimary }">403</h1>
                        <h2 class="text-3xl font-extrabold"
                            :style="{ color: themeColors.textPrimary }">Access Denied</h2>
                    </div>
                    
                    <p class="text-lg max-w-2xl mx-auto"
                       :style="{ color: themeColors.textSecondary }">
                        {{ message || 'You do not have permission to access this page.' }}
                    </p>

                    <!-- Alert Box -->
                    <div class="rounded-lg p-4 text-left"
                         :style="{ 
                             backgroundColor: 'rgba(245, 158, 11, 0.1)',
                             borderLeftWidth: '4px',
                             borderLeftColor: themeColors.warning
                         }">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"
                                     :style="{ color: themeColors.warning }">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium"
                                   :style="{ color: themeColors.warning }">
                                    If you believe you should have access to this page, please contact your administrator.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Actions -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center pt-6 border-t"
                         :style="{ borderColor: themeColors.border }">
                        <Link
                            :href="route('dashboard')"
                            class="inline-flex items-center px-6 py-3 rounded-md font-medium transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.primary,
                                color: '#ffffff'
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Go to Dashboard
                        </Link>
                        
                        <button
                            @click="$router.go(-1)"
                            class="inline-flex items-center px-6 py-3 rounded-md font-medium transition-colors"
                            :style="{ 
                                backgroundColor: themeColors.secondary,
                                color: themeColors.textPrimary
                            }"
                            @mouseenter="$event.target.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.target.style.backgroundColor = themeColors.secondary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Go Back
                        </button>
                    </div>

                    <!-- Help Text -->
                    <div class="pt-6"
                         :style="{ borderColor: themeColors.border }">
                        <p class="text-sm"
                           :style="{ color: themeColors.textTertiary }">
                            Need help? <a href="mailto:support@example.com" 
                                         class="font-medium hover:underline"
                                         :style="{ color: themeColors.primary }">Contact Support</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useTheme } from '@/Composables/useTheme.js';

const props = defineProps({
    message: String,
    status: Number
});

const page = usePage();

const { loadTheme } = useTheme();
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
}));

loadTheme();
</script>

<style scoped>
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
