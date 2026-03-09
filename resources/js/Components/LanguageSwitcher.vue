<template>
    <div class="relative language-switcher" ref="containerRef">
        <!-- Trigger button -->
        <button
            type="button"
            @click="isOpen = !isOpen"
            class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-sm font-medium transition-all duration-200
                   border border-transparent hover:border-white/20 hover:bg-white/10"
            :title="$t('settings.language_setting')"
            :aria-expanded="isOpen"
            aria-haspopup="listbox"
        >
            <span class="text-base leading-none">{{ currentLocaleInfo.flag }}</span>
            <span class="hidden sm:inline opacity-90">{{ currentLocaleInfo.label }}</span>
            <svg class="w-3.5 h-3.5 opacity-60 transition-transform duration-200" :class="{ 'rotate-180': isOpen }"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown -->
        <transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 scale-95 -translate-y-1"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 -translate-y-1"
        >
            <ul
                v-if="isOpen"
                role="listbox"
                class="absolute right-0 mt-1.5 w-40 rounded-xl shadow-xl border border-white/10 overflow-hidden z-[200]"
                style="background: var(--kotel-sidebar-bg, #1e2530);"
            >
                <li
                    v-for="loc in availableLocales"
                    :key="loc.code"
                    role="option"
                    :aria-selected="currentLocale === loc.code"
                    @click="select(loc.code)"
                    class="flex items-center gap-2.5 px-3.5 py-2.5 cursor-pointer text-sm transition-colors duration-150
                           hover:bg-white/10"
                    :class="currentLocale === loc.code ? 'text-yellow-400 font-semibold' : 'text-white/80'"
                >
                    <span class="text-base">{{ loc.flag }}</span>
                    <span>{{ loc.label }}</span>
                    <svg v-if="currentLocale === loc.code" class="w-3.5 h-3.5 ml-auto text-yellow-400"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </li>
            </ul>
        </transition>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useLocale } from '../Composables/useLocale.js'

const { currentLocale, currentLocaleInfo, availableLocales, changeLocale } = useLocale()

const isOpen = ref(false)
const containerRef = ref(null)

function handleOutsideClick(e) {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        isOpen.value = false
    }
}

onMounted(() => document.addEventListener('click', handleOutsideClick, true))
onUnmounted(() => document.removeEventListener('click', handleOutsideClick, true))

function select(code) {
    changeLocale(code)
    isOpen.value = false
}
</script>
