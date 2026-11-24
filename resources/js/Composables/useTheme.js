import { ref } from 'vue'

const defaultTheme = {
    theme_mode: 'dark',
    theme_primary_color: '#facc15',
    theme_secondary_color: '#3b82f6',
    theme_success_color: '#22c55e',
    theme_warning_color: '#f59e0b',
    theme_danger_color: '#ef4444',
    theme_info_color: '#3b82f6',
    theme_background_color: '#0b0b0b',
    theme_sidebar_color: '#0f172a',
    theme_card_color: '#111827',
    theme_text_primary: '#f3f4f6',
    theme_text_secondary: '#9ca3af',
    theme_text_tertiary: '#6b7280',
    theme_border_color: '#374151',
    theme_font_family: 'Figtree, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif',
    theme_radius: '0.5rem',
    theme_shadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
    theme_transition: 'all 0.3s ease-in-out'
}

export function useTheme() {
    const currentTheme = ref({ ...defaultTheme })

    const applyTheme = (theme) => {
        const root = document.documentElement

        // Set CSS custom properties
        root.style.setProperty('--kotel-primary', theme.theme_primary_color)
        root.style.setProperty('--kotel-yellow', theme.theme_primary_color)
        root.style.setProperty('--kotel-yellow-dark', `color-mix(in srgb, ${theme.theme_primary_color} 88%, black)`)
        root.style.setProperty('--kotel-yellow-light', `color-mix(in srgb, ${theme.theme_primary_color} 18%, white)`)
        root.style.setProperty('--kotel-secondary', theme.theme_secondary_color)
        root.style.setProperty('--kotel-sky-blue', theme.theme_secondary_color)
        root.style.setProperty('--kotel-sky-blue-dark', `color-mix(in srgb, ${theme.theme_secondary_color} 88%, black)`)
        root.style.setProperty('--kotel-sky-blue-light', `color-mix(in srgb, ${theme.theme_secondary_color} 18%, white)`)
        root.style.setProperty('--kotel-success', theme.theme_success_color)
        root.style.setProperty('--kotel-green', theme.theme_success_color)
        root.style.setProperty('--kotel-green-dark', `color-mix(in srgb, ${theme.theme_success_color} 88%, black)`)
        root.style.setProperty('--kotel-green-light', `color-mix(in srgb, ${theme.theme_success_color} 18%, white)`)
        root.style.setProperty('--kotel-warning', theme.theme_warning_color)
        root.style.setProperty('--kotel-orange', theme.theme_warning_color)
        root.style.setProperty('--kotel-orange-dark', `color-mix(in srgb, ${theme.theme_warning_color} 88%, black)`)
        root.style.setProperty('--kotel-orange-light', `color-mix(in srgb, ${theme.theme_warning_color} 18%, white)`)
        root.style.setProperty('--kotel-danger', theme.theme_danger_color)
        root.style.setProperty('--kotel-red', theme.theme_danger_color)
        root.style.setProperty('--kotel-red-dark', `color-mix(in srgb, ${theme.theme_danger_color} 88%, black)`)
        root.style.setProperty('--kotel-red-light', `color-mix(in srgb, ${theme.theme_danger_color} 18%, white)`)
        root.style.setProperty('--kotel-info', theme.theme_info_color)
        root.style.setProperty('--kotel-purple', theme.theme_info_color)
        root.style.setProperty('--kotel-background', theme.theme_background_color)
        root.style.setProperty('--kotel-sidebar', theme.theme_sidebar_color)
        root.style.setProperty('--kotel-card', theme.theme_card_color)
        root.style.setProperty('--kotel-bg-card', theme.theme_card_color)
        root.style.setProperty('--kotel-text-primary', theme.theme_text_primary)
        root.style.setProperty('--kotel-text-secondary', theme.theme_text_secondary)
        root.style.setProperty('--kotel-text-tertiary', theme.theme_text_tertiary)
        root.style.setProperty('--kotel-border', theme.theme_border_color)
        root.style.setProperty('--kotel-radius', theme.theme_radius)
        root.style.setProperty('--kotel-shadow', theme.theme_shadow)
        root.style.setProperty('--kotel-shadow-card', theme.theme_shadow)
        root.style.setProperty('--kotel-shadow-card-hover', theme.theme_shadow)
        root.style.setProperty('--kotel-shadow-button', theme.theme_shadow)
        root.style.setProperty('--kotel-transition', theme.theme_transition)
        root.style.setProperty('--kotel-font-family', theme.theme_font_family)
        root.style.fontFamily = theme.theme_font_family
        root.style.colorScheme = theme.theme_mode === 'auto' ? 'light dark' : theme.theme_mode

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
            const response = await fetch('/api/theme', {
                credentials: 'same-origin',
                cache: 'no-store'
            })

            if (!response.ok) {
                throw new Error(`Theme settings request failed with HTTP ${response.status}`)
            }

            const theme = await response.json()
            if (!theme || typeof theme !== 'object' || Array.isArray(theme)) {
                throw new Error('Theme settings response was invalid')
            }

            applyTheme({ ...defaultTheme, ...theme })
        } catch (error) {
            console.warn('Could not load saved theme settings from the server:', error)
            try {
                const cachedTheme = JSON.parse(localStorage.getItem('kotel_theme') || '{}')
                applyTheme({ ...defaultTheme, ...cachedTheme })
            } catch (cacheError) {
                console.warn('Could not load the cached theme settings:', cacheError)
                applyTheme({ ...defaultTheme })
            }
        }
    }

    const updateTheme = (newTheme) => {
        currentTheme.value = { ...currentTheme.value, ...newTheme }
        applyTheme(currentTheme.value)
    }

    const resetTheme = () => {
        currentTheme.value = { ...defaultTheme }
        applyTheme(currentTheme.value)
    }

    return {
        currentTheme,
        applyTheme,
        loadTheme,
        updateTheme,
        resetTheme
    }
}
