<template>
    <div class="relative">
        <div class="pointer-events-none absolute inset-y-0 left-0 pl-3 flex items-center z-10"
             :style="{ color: themeColors.textTertiary }">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
        <input
            ref="inputRef"
            type="date"
            :id="id"
            :name="name"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class="w-full pl-9 pr-3 py-2 rounded-md focus:outline-none cursor-pointer transition-colors"
            :style="{
                backgroundColor: themeColors.background,
                borderColor: themeColors.border,
                color: modelValue ? themeColors.textPrimary : themeColors.textTertiary,
                borderWidth: '1px',
                borderStyle: 'solid'
            }"
            :min="min"
            :max="max"
            :required="required"
        >
        <!-- Full-coverage overlay ensures clicking anywhere opens the picker -->
        <div class="absolute inset-0 cursor-pointer" @click="openPicker"></div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useTheme } from '@/Composables/useTheme.js'

const { loadTheme } = useTheme()

const themeColors = computed(() => ({
    background:   `var(--kotel-background)`,
    border:       `var(--kotel-border)`,
    textPrimary:  `var(--kotel-text-primary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
}))

loadTheme()

defineProps({
    modelValue: { type: String, default: '' },
    id:         { type: String, default: null },
    name:       { type: String, default: null },
    min:        { type: String, default: null },
    max:        { type: String, default: null },
    required:   { type: Boolean, default: false },
})

defineEmits(['update:modelValue'])

const inputRef = ref(null)

const openPicker = () => {
    if (inputRef.value?.showPicker) {
        inputRef.value.showPicker()
    } else {
        inputRef.value?.focus()
    }
}
</script>

<style scoped>
/* Fix placeholder colors for date inputs */
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
