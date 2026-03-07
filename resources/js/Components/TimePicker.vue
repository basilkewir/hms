<template>
    <div class="relative">
        <input
            ref="timeInput"
            type="time"
            :value="modelValue"
            @input="handleInput"
            @click="handleInputClick"
            class="w-full rounded-md px-3 py-2 pr-10 focus:outline-none cursor-pointer transition-colors"
            :class="{ 'opacity-70': !modelValue }"
            :style="{ 
                backgroundColor: themeColors.background,
                borderColor: themeColors.border,
                color: themeColors.textPrimary,
                borderWidth: '1px',
                borderStyle: 'solid'
            }"
            :placeholder="placeholder"
            step="60"
        >
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"
                 :style="{ color: themeColors.textTertiary }">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
            </svg>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useTheme } from '@/Composables/useTheme.js'

// Initialize theme
const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
}))

// Load theme on mount
loadTheme()

defineProps({
    modelValue: String,
    placeholder: {
        type: String,
        default: 'Select a time'
    }
})

const emit = defineEmits(['update:modelValue'])

const timeInput = ref(null)

const handleInput = (event) => {
    emit('update:modelValue', event.target.value)
}

const handleInputClick = (event) => {
    // Ensure the time picker opens when clicking anywhere on the input
    // Modern browsers support showPicker() API, but it requires a user gesture (click)
    // Only call showPicker on click events, not on focus
    if (timeInput.value && typeof timeInput.value.showPicker === 'function') {
        try {
            timeInput.value.showPicker().catch(err => {
                // Fallback: if showPicker fails, let the default behavior happen
                // This is expected in some browsers or contexts
            })
        } catch (err) {
            // Ignore errors from showPicker
        }
    }
}
</script>

<style scoped>
/* Fix placeholder colors for time inputs */
input::placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input::-webkit-input-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input::-moz-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}

input:-ms-input-placeholder {
    color: var(--kotel-text-tertiary) !important;
    opacity: 0.7;
}
</style>
