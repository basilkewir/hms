import { ref, reactive } from 'vue'

export function useTheme() {
    const currentTheme = ref({
        theme_mode: 'dark',
        theme_primary_color: '#0891ab',
        theme_secondary_color: '#3b82f6',
        theme_success_color: '#22c55e',
        theme_warning_color: '#f59e0b',
        theme_danger_color: '#ef4444',
        theme_background_color: '#0b0b0b',
        theme_sidebar_color: '#0f172a',
        theme_card_color: '#111827',
        theme_text_primary: '#f3f4f6',
        theme_text_secondary: '#9ca3af',
        theme_text_tertiary: '#6b7280',
        theme_border_color: '#374151',
        theme_radius: '0.5rem',
        theme_shadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
        theme_transition: 'all 0.3s ease-in-out'
    })

    const applyTheme = (theme) => {
        const root = document.documentElement

        // Set CSS custom properties
        root.style.setProperty('--kotel-primary', theme.theme_primary_color)
        root.style.setProperty('--kotel-secondary', theme.theme_secondary_color)
        root.style.setProperty('--kotel-success', theme.theme_success_color)
        root.style.setProperty('--kotel-warning', theme.theme_warning_color)
        root.style.setProperty('--kotel-danger', theme.theme_danger_color)
        root.style.setProperty('--kotel-background', theme.theme_background_color)
        root.style.setProperty('--kotel-sidebar', theme.theme_sidebar_color)
        root.style.setProperty('--kotel-card', theme.theme_card_color)
        root.style.setProperty('--kotel-text-primary', theme.theme_text_primary)
        root.style.setProperty('--kotel-text-secondary', theme.theme_text_secondary)
        root.style.setProperty('--kotel-text-tertiary', theme.theme_text_tertiary)
        root.style.setProperty('--kotel-border', theme.theme_border_color)
        root.style.setProperty('--kotel-radius', theme.theme_radius)
        root.style.setProperty('--kotel-shadow', theme.theme_shadow)
        root.style.setProperty('--kotel-transition', theme.theme_transition)

        // Set theme mode class
        root.classList.remove('theme-dark', 'theme-light', 'theme-auto')
        root.classList.add(`theme-${theme.theme_mode}`)

        // Store theme in localStorage
        localStorage.setItem('kotel_theme', JSON.stringify(theme))

        // Update current theme ref
        currentTheme.value = theme
    }

    const loadTheme = async () => {
        try {
            // Try to load from localStorage first
            const savedTheme = localStorage.getItem('kotel_theme')
            if (savedTheme) {
                const theme = JSON.parse(savedTheme)

                // Migrate old primary color to new brand color so var(--kotel-primary) updates
                if (!theme.theme_primary_color || theme.theme_primary_color === '#facc15') {
                    theme.theme_primary_color = '#0891ab'
                }

                currentTheme.value = theme
                applyTheme(theme)
                return
            }

            // If no saved theme, try to load from server
            try {
                const response = await fetch('/api/theme')
                if (response.ok) {
                    const theme = await response.json()
                    currentTheme.value = theme
                    applyTheme(theme)
                    return
                }
            } catch (serverError) {
                // Server endpoint doesn't exist or failed, continue to default
            }

            // Use default theme
            applyTheme(currentTheme.value)
        } catch (error) {
            console.error('❌ Error loading theme:', error)
            // Fallback to default theme
            applyTheme(currentTheme.value)
        }
    }

    const updateTheme = (newTheme) => {
        currentTheme.value = { ...currentTheme.value, ...newTheme }
        applyTheme(currentTheme.value)
    }

    const resetTheme = () => {
        const defaultTheme = {
            theme_mode: 'dark',
            theme_primary_color: '#0891ab',
            theme_secondary_color: '#3b82f6',
            theme_success_color: '#22c55e',
            theme_warning_color: '#f59e0b',
            theme_danger_color: '#ef4444',
            theme_background_color: '#0b0b0b',
            theme_sidebar_color: '#0f172a',
            theme_card_color: '#111827',
            theme_text_primary: '#f3f4f6',
            theme_text_secondary: '#9ca3af',
            theme_text_tertiary: '#6b7280',
            theme_border_color: '#374151',
            theme_radius: '0.5rem',
            theme_shadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
            theme_transition: 'all 0.3s ease-in-out'
        }
        currentTheme.value = defaultTheme
        applyTheme(defaultTheme)
    }

    return {
        currentTheme,
        applyTheme,
        loadTheme,
        updateTheme,
        resetTheme
    }
}
