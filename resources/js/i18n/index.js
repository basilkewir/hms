import { createI18n } from 'vue-i18n'
import en from './en.js'
import fr from './fr.js'

// Detect saved locale from localStorage, fallback to browser language, then 'en'
function detectLocale() {
    const saved = localStorage.getItem('kotel_locale')
    if (saved && ['en', 'fr'].includes(saved)) return saved

    const browser = (navigator.language || navigator.userLanguage || 'en').slice(0, 2).toLowerCase()
    return ['en', 'fr'].includes(browser) ? browser : 'en'
}

export const i18n = createI18n({
    legacy: false,          // use Composition API mode
    globalInjection: true,  // inject $t, $tc, $d, $n globally in all components
    locale: detectLocale(),
    fallbackLocale: 'en',
    messages: {
        en,
        fr,
    },
    // Silence missing key warnings in production
    missingWarn: import.meta.env.DEV,
    fallbackWarn: import.meta.env.DEV,
})

/**
 * Change the active locale and persist it.
 * Call this from the language switcher.
 */
export function setLocale(locale) {
    if (!['en', 'fr'].includes(locale)) return
    i18n.global.locale.value = locale
    localStorage.setItem('kotel_locale', locale)
    document.documentElement.setAttribute('lang', locale)
}

export default i18n
