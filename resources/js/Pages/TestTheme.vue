<script setup>
import { onMounted } from 'vue'
import { useTheme } from '@/Composables/useTheme'

const { currentTheme, loadTheme, updateTheme } = useTheme()

onMounted(() => {
    loadTheme()
})

const testThemeSwitch = () => {
    // Switch to light theme
    updateTheme({
        theme_mode: 'light',
        theme_primary_color: '#3b82f6',
        theme_background_color: '#f3f4f6',
        theme_text_primary: '#111827'
    })
}

const testDarkTheme = () => {
    // Switch back to dark theme
    updateTheme({
        theme_mode: 'dark',
        theme_primary_color: '#facc15',
        theme_background_color: '#0b0b0b',
        theme_text_primary: '#f3f4f6'
    })
}

const testCustomTheme = () => {
    // Test custom theme
    updateTheme({
        theme_mode: 'dark',
        theme_primary_color: '#10b981',
        theme_secondary_color: '#8b5cf6',
        theme_background_color: '#0f172a',
        theme_text_primary: '#e5e7eb'
    })
}
</script>

<template>
    <div class="space-y-6">
        <div class="bg-kotel-bg-card rounded-lg p-6 shadow-kotel-card">
            <h1 class="text-2xl font-bold text-kotel-text-primary mb-4">Theme Testing Page</h1>
            <p class="text-kotel-text-secondary mb-6">
                This page tests the dynamic theme switching functionality. Use the buttons below to test different themes.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-kotel-bg-header p-4 rounded-lg border border-kotel-border">
                    <h3 class="font-semibold text-kotel-text-primary mb-2">Current Theme</h3>
                    <div class="text-sm text-kotel-text-secondary space-y-1">
                        <div>Mode: {{ currentTheme.theme_mode }}</div>
                        <div>Primary: {{ currentTheme.theme_primary_color }}</div>
                        <div>Background: {{ currentTheme.theme_background_color }}</div>
                        <div>Text: {{ currentTheme.theme_text_primary }}</div>
                    </div>
                </div>

                <div class="bg-kotel-bg-header p-4 rounded-lg border border-kotel-border">
                    <h3 class="font-semibold text-kotel-text-primary mb-2">Theme Features</h3>
                    <ul class="text-sm text-kotel-text-secondary space-y-1">
                        <li>✓ Dynamic CSS-in-JS styling</li>
                        <li>✓ Local storage persistence</li>
                        <li>✓ Server-side fallback</li>
                        <li>✓ Real-time updates</li>
                    </ul>
                </div>

                <div class="bg-kotel-bg-header p-4 rounded-lg border border-kotel-border">
                    <h3 class="font-semibold text-kotel-text-primary mb-2">Test Actions</h3>
                    <div class="text-sm text-kotel-text-secondary space-y-2">
                        <p>Click buttons below to test theme switching:</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-4">
                <button @click="testThemeSwitch" class="bg-kotel-sky-blue text-white px-4 py-2 rounded-md hover:bg-kotel-sky-blue-dark transition-colors">
                    Test Light Theme
                </button>
                <button @click="testDarkTheme" class="bg-kotel-yellow text-kotel-black px-4 py-2 rounded-md hover:bg-kotel-yellow-dark transition-colors">
                    Test Dark Theme
                </button>
                <button @click="testCustomTheme" class="bg-kotel-green text-white px-4 py-2 rounded-md hover:bg-kotel-green-dark transition-colors">
                    Test Custom Theme
                </button>
                <button @click="loadTheme" class="bg-kotel-purple text-white px-4 py-2 rounded-md hover:bg-kotel-purple-dark transition-colors">
                    Reset to Saved
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-kotel-bg-card rounded-lg p-6 shadow-kotel-card">
                <h2 class="text-xl font-semibold text-kotel-text-primary mb-4">Component Testing</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Text Input</label>
                        <input type="text" placeholder="This is a themed input" class="w-full px-4 py-2 bg-kotel-gray border border-kotel-border-light rounded-lg text-kotel-text-primary focus:ring-kotel-yellow focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Checkbox</label>
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" class="w-4 h-4 text-kotel-yellow bg-kotel-gray border-kotel-border-light rounded focus:ring-kotel-yellow">
                            <span class="text-kotel-text-secondary">This is a themed checkbox</span>
                        </div>
                    </div>
                    <div class="flex space-x-4">
                        <button class="bg-kotel-yellow text-kotel-black px-4 py-2 rounded-md hover:bg-kotel-yellow-dark transition-colors">
                            Primary Button
                        </button>
                        <button class="bg-kotel-sky-blue text-white px-4 py-2 rounded-md hover:bg-kotel-sky-blue-dark transition-colors">
                            Secondary Button
                        </button>
                        <button class="bg-kotel-green text-white px-4 py-2 rounded-md hover:bg-kotel-green-dark transition-colors">
                            Success Button
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-kotel-bg-card rounded-lg p-6 shadow-kotel-card">
                <h2 class="text-xl font-semibold text-kotel-text-primary mb-4">Visual Testing</h2>
                <div class="space-y-4">
                    <div class="bg-kotel-bg-header p-4 rounded-lg border border-kotel-border">
                        <h3 class="font-medium text-kotel-text-primary">Card Background</h3>
                        <p class="text-sm text-kotel-text-secondary mt-1">This card should match your current theme colors</p>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="bg-kotel-yellow h-12 rounded"></div>
                        <div class="bg-kotel-sky-blue h-12 rounded"></div>
                        <div class="bg-kotel-green h-12 rounded"></div>
                    </div>
                    <div class="text-sm text-kotel-text-secondary">
                        <p>These colored blocks should reflect your current theme's color scheme.</p>
                        <p class="mt-2">If you changed the theme using the buttons above, these colors should update immediately.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-kotel-bg-card rounded-lg p-6 shadow-kotel-card">
            <h2 class="text-xl font-semibold text-kotel-text-primary mb-4">Theme Persistence Test</h2>
            <p class="text-kotel-text-secondary mb-4">
                Your theme settings are automatically saved to localStorage. Try changing the theme, then refresh the page to see if your settings persist.
            </p>
            <div class="bg-kotel-bg-header p-4 rounded-lg border border-kotel-border">
                <h3 class="font-medium text-kotel-text-primary mb-2">Instructions:</h3>
                <ol class="list-decimal list-inside text-sm text-kotel-text-secondary space-y-1">
                    <li>Use the theme buttons above to change the theme</li>
                    <li>Refresh the page</li>
                    <li>Verify that your theme settings are preserved</li>
                    <li>Check that all components maintain their themed appearance</li>
                </ol>
            </div>
        </div>
    </div>
</template>
