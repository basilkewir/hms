import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { setLocale } from '../i18n/index.js'

export const availableLocales = [
    { code: 'en', label: 'English', flag: '🇬🇧' },
    { code: 'fr', label: 'Français', flag: '🇫🇷' },
]

export function useLocale() {
    const { locale, t } = useI18n()

    const currentLocale = computed(() => locale.value)

    const currentLocaleInfo = computed(
        () => availableLocales.find(l => l.code === locale.value) ?? availableLocales[0]
    )

    const changeLocale = (code) => setLocale(code)

    return { currentLocale, currentLocaleInfo, availableLocales, changeLocale, t }
}
