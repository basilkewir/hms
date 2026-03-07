<template>
    <div>
        <slot />
    </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'
import { useTheme } from '@/Composables/useTheme.js'

const { currentTheme, applyTheme, loadTheme } = useTheme()

let themeObserver = null

onMounted(async () => {
    // Load theme from service
    await loadTheme()

    // Apply initial theme
    applyTheme(currentTheme.value)

    // Watch for theme changes and apply them
    const applyThemeChanges = () => {
        applyTheme(currentTheme.value)
    }

    // Create a MutationObserver to watch for theme changes
    themeObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'data-theme') {
                applyThemeChanges()
            }
        })
    })

    // Observe the document root for theme changes
    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-theme']
    })

    // Listen for storage events (in case theme is changed in another tab)
    window.addEventListener('storage', (e) => {
        if (e.key === 'kotel_theme') {
            loadTheme()
        }
    })
})

onUnmounted(() => {
    if (themeObserver) {
        themeObserver.disconnect()
    }
})
</script>
