<template>
    <div v-if="links && links.length > 3" class="px-4 py-3 border-t sm:px-6"
         :style="{
            backgroundColor: themeColors.card,
            borderColor: themeColors.border
         }">
        <div class="flex items-center justify-between">
            <!-- Mobile pagination -->
            <div class="flex-1 flex justify-between sm:hidden">
                <Link 
                    v-if="links[0].url" 
                    :href="links[0].url" 
                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium rounded-md transition-colors"
                    :style="{
                        backgroundColor: themeColors.background,
                        borderColor: themeColors.border,
                        color: themeColors.textPrimary
                    }"
                >
                    Previous
                </Link>
                <Link 
                    v-if="links[links.length - 1].url" 
                    :href="links[links.length - 1].url" 
                    class="ml-3 relative inline-flex items-center px-4 py-2 border text-sm font-medium rounded-md transition-colors"
                    :style="{
                        backgroundColor: themeColors.background,
                        borderColor: themeColors.border,
                        color: themeColors.textPrimary
                    }"
                >
                    Next
                </Link>
            </div>
            
            <!-- Desktop pagination -->
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div v-if="meta">
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        Showing 
                        <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ meta.from || 0 }}</span> 
                        to 
                        <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ meta.to || 0 }}</span> 
                        of 
                        <span class="font-medium" :style="{ color: themeColors.textPrimary }">{{ meta.total || 0 }}</span> 
                        results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <Link 
                            v-for="(link, index) in links" 
                            :key="index"
                            :href="link.url || '#'" 
                            v-html="link.label"
                            :class="[
                                'relative inline-flex items-center px-4 py-2 border text-sm font-medium transition-colors',
                                !link.url ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'
                            ]"
                            :style="getLinkStyle(link)"
                        />
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue'

const props = defineProps({
    links: {
        type: Array,
        default: () => []
    },
    meta: {
        type: Object,
        default: null
    }
});

const themeColors = computed(() => ({
    background: `var(--kotel-background, #f8fafc)`,
    card: `var(--kotel-card, #ffffff)`,
    border: `var(--kotel-border, #e2e8f0)`,
    textPrimary: `var(--kotel-text-primary, #1e293b)`,
    textSecondary: `var(--kotel-text-secondary, #64748b)`,
    primary: `var(--kotel-primary, #3b82f6)`,
}))

const getLinkStyle = (link) => {
    if (link.active) {
        return {
            backgroundColor: themeColors.value.primary,
            borderColor: themeColors.value.primary,
            color: '#ffffff',
            zIndex: 10,
        }
    }

    return {
        backgroundColor: themeColors.value.background,
        borderColor: themeColors.value.border,
        color: themeColors.value.textSecondary,
    }
}
</script>
