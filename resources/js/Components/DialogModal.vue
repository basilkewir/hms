<script setup>
import Modal from './Modal.vue';
import { computed } from 'vue';
import { useTheme } from '@/Composables/useTheme.js';

// Theme system
const { loadTheme } = useTheme();
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    primaryHover: `var(--kotel-primary-hover)`,
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    successHover: `var(--kotel-success-hover)`,
    danger: `var(--kotel-danger)`,
    dangerHover: `var(--kotel-danger-hover)`,
    warning: `var(--kotel-warning)`,
    warningHover: `var(--kotel-warning-hover)`,
    hover: `rgba(255, 255, 255, 0.1)`,
    textOnPrimary: `var(--kotel-text-on-primary)`
}));

// Load theme on mount
loadTheme();

const emit = defineEmits(['close']);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal
        :show="show"
        :max-width="maxWidth"
        :closeable="closeable"
        @close="close"
    >
        <div class="px-6 py-4">
            <div class="text-lg font-medium"
                 :style="{ color: themeColors.textPrimary }">
                <slot name="title" />
            </div>

            <div class="mt-4 text-sm"
                 :style="{ color: themeColors.textSecondary }">
                <slot name="content" />
            </div>
        </div>

        <div class="flex flex-row justify-end px-6 py-4 text-end"
             :style="{ 
                 borderTop: `1px solid ${themeColors.border}`,
                 backgroundColor: themeColors.background
             }">
            <slot name="footer" />
        </div>
    </Modal>
</template>
