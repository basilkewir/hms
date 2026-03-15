<template>
    <DashboardLayout title="System Settings" :user="user">
        <!-- Settings Header -->
        <div class="bg-kotel-bg-card shadow rounded-lg p-6 mb-8 border border-kotel-border">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-kotel-text-primary">System Settings</h1>
                    <p class="text-kotel-text-secondary mt-2">Configure hotel management system settings and preferences.</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="saveSettings"
                            :disabled="isSaving"
                            class="bg-kotel-yellow text-kotel-black px-4 py-2 rounded-md hover:bg-kotel-yellow-dark disabled:opacity-50 transition-colors">
                        {{ isSaving ? 'Saving...' : 'Save Changes' }}
                    </button>
                    <button @click="resetSettings"
                            class="bg-kotel-darker text-kotel-text-secondary px-4 py-2 rounded-md hover:bg-kotel-dark hover:text-kotel-yellow transition-colors">
                        Reset to Defaults
                    </button>
                </div>
            </div>
        </div>

        <!-- Settings Tabs -->
        <div class="bg-kotel-bg-card shadow rounded-lg border border-kotel-border">
            <div class="border-b border-kotel-border">
                <nav class="-mb-px flex space-x-8 px-6">
                    <button v-for="tab in tabs" :key="tab.id"
                            @click="activeTab = tab.id"
                            class="py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                            :class="activeTab === tab.id
                                ? 'border-kotel-yellow text-kotel-yellow'
                                : 'border-transparent text-kotel-text-secondary hover:text-kotel-yellow hover:border-kotel-yellow/50'">
                        <component :is="tab.icon" class="h-5 w-5 mr-2 inline" />
                        {{ tab.name }}
                    </button>
                </nav>
            </div>

            <div class="p-6">
                <!-- Theme Settings -->
                <div v-show="activeTab === 'theme'" class="space-y-6">
                    <h3 class="text-lg font-semibold text-kotel-text-primary mb-4">Theme Customization</h3>
                    <p class="text-sm text-kotel-text-tertiary mb-6">Customize the appearance of your hotel management system with dynamic theming.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Theme Mode -->
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Theme Mode</label>
                            <select v-model="settings.theme_mode"
                                    class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                                <option value="dark">Dark</option>
                                <option value="light">Light</option>
                                <option value="auto">Auto (System)</option>
                            </select>
                            <p class="mt-1 text-xs text-kotel-text-tertiary">Choose your preferred theme mode</p>
                        </div>

                        <!-- Primary Color -->
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Primary Color</label>
                            <div class="flex space-x-3">
                                <input type="color" v-model="settings.theme_primary_color"
                                       class="w-16 h-10 border border-kotel-border rounded-md bg-kotel-black">
                                <input type="text" v-model="settings.theme_primary_color"
                                       class="flex-1 border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                            </div>
                            <p class="mt-1 text-xs text-kotel-text-tertiary">Main brand color used throughout the application</p>
                        </div>

                        <!-- Secondary Color -->
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Secondary Color</label>
                            <div class="flex space-x-3">
                                <input type="color" v-model="settings.theme_secondary_color"
                                       class="w-16 h-10 border border-kotel-border rounded-md bg-kotel-black">
                                <input type="text" v-model="settings.theme_secondary_color"
                                       class="flex-1 border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                            </div>
                            <p class="mt-1 text-xs text-kotel-text-tertiary">Secondary color for accents and highlights</p>
                        </div>

                        <!-- Success Color -->
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Success Color</label>
                            <div class="flex space-x-3">
                                <input type="color" v-model="settings.theme_success_color"
                                       class="w-16 h-10 border border-kotel-border rounded-md bg-kotel-black">
                                <input type="text" v-model="settings.theme_success_color"
                                       class="flex-1 border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                            </div>
                            <p class="mt-1 text-xs text-kotel-text-tertiary">Color used for success messages and confirmations</p>
                        </div>

                        <!-- Warning Color -->
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Warning Color</label>
                            <div class="flex space-x-3">
                                <input type="color" v-model="settings.theme_warning_color"
                                       class="w-16 h-10 border border-kotel-border rounded-md bg-kotel-black">
                                <input type="text" v-model="settings.theme_warning_color"
                                       class="flex-1 border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                            </div>
                            <p class="mt-1 text-xs text-kotel-text-tertiary">Color used for warnings and alerts</p>
                        </div>

                        <!-- Danger Color -->
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Danger Color</label>
                            <div class="flex space-x-3">
                                <input type="color" v-model="settings.theme_danger_color"
                                       class="w-16 h-10 border border-kotel-border rounded-md bg-kotel-black">
                                <input type="text" v-model="settings.theme_danger_color"
                                       class="flex-1 border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                            </div>
                            <p class="mt-1 text-xs text-kotel-text-tertiary">Color used for errors and destructive actions</p>
                        </div>

                        <!-- Background Color -->
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Background Color</label>
                            <div class="flex space-x-3">
                                <input type="color" v-model="settings.theme_background_color"
                                       class="w-16 h-10 border border-kotel-border rounded-md bg-kotel-black">
                                <input type="text" v-model="settings.theme_background_color"
                                       class="flex-1 border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                            </div>
                            <p class="mt-1 text-xs text-kotel-text-tertiary">Main background color for the application</p>
                        </div>

                        <!-- Sidebar Color -->
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Sidebar Color</label>
                            <div class="flex space-x-3">
                                <input type="color" v-model="settings.theme_sidebar_color"
                                       class="w-16 h-10 border border-kotel-border rounded-md bg-kotel-black">
                                <input type="text" v-model="settings.theme_sidebar_color"
                                       class="flex-1 border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                            </div>
                            <p class="mt-1 text-xs text-kotel-text-tertiary">Color for the sidebar and navigation areas</p>
                        </div>

                        <!-- Card Color -->
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Card Color</label>
                            <div class="flex space-x-3">
                                <input type="color" v-model="settings.theme_card_color"
                                       class="w-16 h-10 border border-kotel-border rounded-md bg-kotel-black">
                                <input type="text" v-model="settings.theme_card_color"
                                       class="flex-1 border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                            </div>
                            <p class="mt-1 text-xs text-kotel-text-tertiary">Background color for cards and panels</p>
                        </div>

                        <!-- Text Colors -->
                        <div class="md:col-span-2">
                            <h4 class="text-md font-semibold text-kotel-text-primary mb-3">Text Colors</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Primary Text</label>
                                    <div class="flex space-x-3">
                                        <input type="color" v-model="settings.theme_text_primary"
                                               class="w-16 h-10 border border-kotel-border rounded-md bg-kotel-black">
                                        <input type="text" v-model="settings.theme_text_primary"
                                               class="flex-1 border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Secondary Text</label>
                                    <div class="flex space-x-3">
                                        <input type="color" v-model="settings.theme_text_secondary"
                                               class="w-16 h-10 border border-kotel-border rounded-md bg-kotel-black">
                                        <input type="text" v-model="settings.theme_text_secondary"
                                               class="flex-1 border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Tertiary Text</label>
                                    <div class="flex space-x-3">
                                        <input type="color" v-model="settings.theme_text_tertiary"
                                               class="w-16 h-10 border border-kotel-border rounded-md bg-kotel-black">
                                        <input type="text" v-model="settings.theme_text_tertiary"
                                               class="flex-1 border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Border Color -->
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Border Color</label>
                            <div class="flex space-x-3">
                                <input type="color" v-model="settings.theme_border_color"
                                       class="w-16 h-10 border border-kotel-border rounded-md bg-kotel-black">
                                <input type="text" v-model="settings.theme_border_color"
                                       class="flex-1 border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                            </div>
                            <p class="mt-1 text-xs text-kotel-text-tertiary">Color used for borders and separators</p>
                        </div>

                        <!-- Border Radius -->
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Border Radius</label>
                            <select v-model="settings.theme_radius"
                                    class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                                <option value="0.25rem">Small (0.25rem)</option>
                                <option value="0.375rem">Medium Small (0.375rem)</option>
                                <option value="0.5rem">Medium (0.5rem)</option>
                                <option value="0.75rem">Medium Large (0.75rem)</option>
                                <option value="1rem">Large (1rem)</option>
                                <option value="9999px">Rounded</option>
                            </select>
                            <p class="mt-1 text-xs text-kotel-text-tertiary">Rounded corners for buttons, cards, and inputs</p>
                        </div>

                        <!-- Shadow -->
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Shadow Style</label>
                            <select v-model="settings.theme_shadow"
                                    class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                                <option value="0 1px 2px 0 rgba(0, 0, 0, 0.05)">None</option>
                                <option value="0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)">Subtle</option>
                                <option value="0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)">Medium</option>
                                <option value="0 25px 50px -12px rgba(0, 0, 0, 0.25)">Strong</option>
                            </select>
                            <p class="mt-1 text-xs text-kotel-text-tertiary">Shadow intensity for cards and buttons</p>
                        </div>

                        <!-- Transition -->
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Transition Speed</label>
                            <select v-model="settings.theme_transition"
                                    class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                                <option value="all 0.15s ease-in-out">Fast (150ms)</option>
                                <option value="all 0.3s ease-in-out">Normal (300ms)</option>
                                <option value="all 0.5s ease-in-out">Slow (500ms)</option>
                                <option value="all 0.75s ease-in-out">Very Slow (750ms)</option>
                                <option value="none">None</option>
                            </select>
                            <p class="mt-1 text-xs text-kotel-text-tertiary">Animation speed for hover effects and transitions</p>
                        </div>
                    </div>

                    <!-- Theme Preview -->
                    <div class="mt-8 p-6 bg-kotel-bg-card border border-kotel-border rounded-lg">
                        <h4 class="text-md font-semibold text-kotel-text-primary mb-4">Theme Preview</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Button Preview -->
                            <div>
                                <h5 class="text-sm font-medium text-kotel-text-secondary mb-2">Buttons</h5>
                                <div class="space-y-2">
                                    <button class="w-full px-4 py-2 rounded-md font-medium"
                                            :style="{ backgroundColor: settings.theme_primary_color, color: getContrastColor(settings.theme_primary_color) }">
                                        Primary
                                    </button>
                                    <button class="w-full px-4 py-2 rounded-md font-medium border"
                                            :style="{ borderColor: settings.theme_border_color, color: settings.theme_text_primary }">
                                        Secondary
                                    </button>
                                    <button class="w-full px-4 py-2 rounded-md font-medium"
                                            :style="{ backgroundColor: settings.theme_success_color, color: getContrastColor(settings.theme_success_color) }">
                                        Success
                                    </button>
                                </div>
                            </div>

                            <!-- Card Preview -->
                            <div>
                                <h5 class="text-sm font-medium text-kotel-text-secondary mb-2">Card</h5>
                                <div class="p-4 rounded-lg"
                                     :style="{ backgroundColor: settings.theme_card_color, border: `1px solid ${settings.theme_border_color}`, boxShadow: settings.theme_shadow }">
                                    <p class="text-sm" :style="{ color: settings.theme_text_primary }">Card content</p>
                                    <p class="text-xs" :style="{ color: settings.theme_text_secondary }">Secondary text</p>
                                </div>
                            </div>

                            <!-- Input Preview -->
                            <div>
                                <h5 class="text-sm font-medium text-kotel-text-secondary mb-2">Input</h5>
                                <input type="text"
                                       placeholder="Input field"
                                       class="w-full px-3 py-2 rounded-md"
                                       :style="{ backgroundColor: settings.theme_background_color, border: `1px solid ${settings.theme_border_color}`, color: settings.theme_text_primary }">
                            </div>
                        </div>
                    </div>

                    <!-- Theme Actions -->
                    <div class="flex space-x-3 pt-4">
                        <button @click="applyTheme"
                                class="bg-kotel-yellow text-kotel-black px-6 py-2 rounded-md hover:bg-kotel-yellow-dark font-medium transition-colors">
                            Apply Theme
                        </button>
                        <button @click="resetTheme"
                                class="bg-kotel-darker text-kotel-text-secondary px-6 py-2 rounded-md hover:bg-kotel-dark hover:text-kotel-yellow transition-colors">
                            Reset to Defaults
                        </button>
                        <button @click="saveTheme"
                                class="bg-kotel-sky-blue text-white px-6 py-2 rounded-md hover:bg-kotel-sky-blue-dark font-medium transition-colors">
                            Save Theme
                        </button>
                    </div>
                </div>

                <!-- General Settings -->
                <div v-show="activeTab === 'general'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Hotel Name</label>
                            <input type="text" v-model="settings.hotel_name"
                                   class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Hotel Address</label>
                            <input type="text" v-model="settings.hotel_address"
                                   class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Phone Number</label>
                            <input type="text" v-model="settings.hotel_phone"
                                   class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Email Address</label>
                            <input type="email" v-model="settings.hotel_email"
                                   class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Timezone</label>
                            <select v-model="settings.timezone"
                                    class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                                <option value="UTC">UTC</option>
                                <option value="America/New_York">Eastern Time</option>
                                <option value="America/Chicago">Central Time</option>
                                <option value="America/Denver">Mountain Time</option>
                                <option value="America/Los_Angeles">Pacific Time</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Currency</label>
                            <select v-model="settings.currency"
                                    class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                                <option value="">Select Currency...</option>
                                <option v-for="(name, code) in supportedCurrencies"
                                        :key="code"
                                        :value="code">
                                    {{ code }} - {{ name }}
                                </option>
                            </select>
                            <p v-if="Object.keys(supportedCurrencies).length === 0" class="mt-1 text-sm text-red-400">
                                No currencies available. Please check settings.
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Currency Position</label>
                            <select v-model="settings.currency_position"
                                    class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                                <option value="prefix">Prefix (e.g., $100.00)</option>
                                <option value="suffix">Suffix (e.g., 100.00 $)</option>
                            </select>
                            <p class="mt-1 text-xs text-kotel-text-tertiary">Choose whether the currency symbol appears before or after the amount</p>
                        </div>
                        <!-- Interface Language -->
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">{{ $t('settings.language_setting') }}</label>
                            <select v-model="uiLocale" @change="handleLocaleChange"
                                    class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                                <option value="en">{{ $t('languages.en') }}</option>
                                <option value="fr">{{ $t('languages.fr') }}</option>
                            </select>
                            <p class="mt-1 text-xs text-kotel-text-tertiary">{{ $t('settings.language_hint') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Default POS Tax Rate (%)</label>
                            <input
                                type="number"
                                v-model.number="settings.tax_rate"
                                min="0"
                                step="0.1"
                                class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow"
                            >
                            <p class="mt-1 text-xs text-kotel-text-tertiary">
                                This tax rate will be used as the default display rate on the POS screen. Individual products can still have their own tax rates.
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Room Tax Rate (%)</label>
                            <input
                                type="number"
                                v-model.number="settings.room_tax_rate"
                                min="0"
                                step="0.1"
                                class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow"
                            >
                            <p class="mt-1 text-xs text-kotel-text-tertiary">
                                This tax rate is applied to room charges at checkout. Set to 0 for no room tax.
                            </p>
                        </div>
                    </div>

                    <!-- Hotel Logo Section -->
                    <div class="mt-8 pt-6 border-t border-kotel-border">
                        <h3 class="text-lg font-semibold text-kotel-text-primary mb-1">Hotel Logo</h3>
                        <p class="text-sm text-kotel-text-tertiary mb-4">Upload your hotel logo. It will appear on receipts, bills, and PDF reports.</p>
                        <div class="flex flex-col sm:flex-row items-start gap-6">
                            <!-- Current logo preview -->
                            <div class="flex-shrink-0">
                                <div v-if="settings.hotel_logo" class="relative w-32 h-32 rounded-lg border border-kotel-border overflow-hidden bg-kotel-darker flex items-center justify-center">
                                    <img :src="settings.hotel_logo" alt="Hotel Logo" class="max-w-full max-h-full object-contain p-2">
                                    <button @click="handleRemoveLogo" type="button"
                                            class="absolute top-1 right-1 bg-red-600 hover:bg-red-700 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs transition-colors"
                                            title="Remove logo">✕</button>
                                </div>
                                <div v-else class="w-32 h-32 rounded-lg border-2 border-dashed border-kotel-border bg-kotel-darker flex flex-col items-center justify-center text-kotel-text-tertiary">
                                    <svg class="w-10 h-10 mb-1 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-xs">No logo</span>
                                </div>
                            </div>
                            <!-- Upload controls -->
                            <div class="flex-1 space-y-3">
                                <input type="file" ref="logoInput" @change="handleLogoUpload"
                                       accept="image/png,image/jpeg,image/jpg,image/gif,image/webp,image/svg+xml"
                                       class="hidden">
                                <div class="flex flex-wrap gap-3">
                                    <button @click="logoInput && logoInput.click()" type="button"
                                            class="px-4 py-2 bg-kotel-yellow text-kotel-black font-medium rounded-md hover:bg-yellow-400 transition-colors text-sm">
                                        {{ settings.hotel_logo ? 'Change Logo' : 'Upload Logo' }}
                                    </button>
                                    <button v-if="uploadedLogo" @click="saveLogo" type="button"
                                            :disabled="isLogoSaving"
                                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-md transition-colors text-sm disabled:opacity-50">
                                        {{ isLogoSaving ? 'Saving...' : 'Save Logo' }}
                                    </button>
                                    <button v-if="settings.hotel_logo && !uploadedLogo" @click="handleRemoveLogo" type="button"
                                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md transition-colors text-sm">
                                        Remove Logo
                                    </button>
                                </div>
                                <p class="text-xs text-kotel-text-tertiary">PNG, JPG, GIF, WEBP or SVG. Max 2 MB.</p>
                                <p v-if="logoError" class="text-xs text-red-400">{{ logoError }}</p>
                                <p v-if="uploadedLogo && !isLogoSaving" class="text-xs text-kotel-yellow">
                                    ✓ New logo selected — click "Save Logo" to apply.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Guest & Discount Settings Section -->
                    <div class="mt-8 pt-6 border-t border-kotel-border">
                        <h3 class="text-lg font-semibold text-kotel-text-primary mb-4">Guest Type & Discount Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-center">
                                <input type="checkbox" v-model="settings.auto_apply_guest_type_discount"
                                       class="h-4 w-4 text-kotel-yellow focus:ring-kotel-yellow border-kotel-border rounded bg-kotel-black">
                                <label class="ml-2 block text-sm text-kotel-text-secondary">
                                    Automatically Apply Guest Type Discount
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" v-model="settings.auto_apply_vip_discount"
                                       class="h-4 w-4 text-kotel-yellow focus:ring-kotel-yellow border-kotel-border rounded bg-kotel-black">
                                <label class="ml-2 block text-sm text-kotel-text-secondary">
                                    Automatically Apply VIP Discount
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-kotel-text-secondary mb-2">VIP Discount Percentage (%)</label>
                                <input
                                    type="number"
                                    v-model.number="settings.vip_discount_percentage"
                                    min="0"
                                    max="100"
                                    step="0.1"
                                    class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow"
                                >
                                <p class="mt-1 text-xs text-kotel-text-tertiary">
                                    Default discount percentage for VIP guests in POS and reservations.
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Discount Combination Mode</label>
                                <select v-model="settings.discount_combination_mode"
                                        class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                                    <option value="add">Add (Sum all discounts)</option>
                                    <option value="override">Override (Manual discount overrides automatic)</option>
                                </select>
                                <p class="mt-1 text-xs text-kotel-text-tertiary">
                                    How to combine automatic discounts (guest type, VIP) with manual discounts.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Print Settings -->
                <div v-show="activeTab === 'print'" class="space-y-6">
                    <h3 class="text-lg font-semibold text-kotel-text-primary mb-4">Print Settings</h3>
                    <p class="text-sm text-kotel-text-tertiary mb-6">Configure paper sizes, fonts, and layout for receipts and bills.</p>

                    <!-- POS / Thermal Printer Settings -->
                    <div class="bg-kotel-dark border border-kotel-border rounded-lg p-6 mb-6">
                        <h4 class="text-md font-semibold text-kotel-yellow mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            POS / Restaurant (Thermal Printer)
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Paper Width</label>
                                <select v-model="settings.pos_print_paper_width"
                                        class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                                    <option value="58mm">58 mm (2.25 inch)</option>
                                    <option value="80mm">80 mm (3.15 inch)</option>
                                    <option value="112mm">112 mm (4.4 inch)</option>
                                </select>
                                <p class="mt-1 text-xs text-kotel-text-tertiary">Standard thermal printer widths</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Font Size</label>
                                <select v-model="settings.pos_print_font_size"
                                        class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                                    <option value="10px">Small (10px)</option>
                                    <option value="12px">Medium (12px)</option>
                                    <option value="14px">Large (14px)</option>
                                    <option value="16px">Extra Large (16px)</option>
                                </select>
                                <p class="mt-1 text-xs text-kotel-text-tertiary">Receipt text size</p>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" v-model="settings.pos_print_show_logo"
                                       class="h-4 w-4 text-kotel-yellow focus:ring-kotel-yellow border-kotel-border rounded bg-kotel-black">
                                <label class="ml-2 block text-sm text-kotel-text-secondary">Show Hotel Logo on Receipt</label>
                            </div>
                        </div>

                        <!-- POS Receipt Preview -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Receipt Preview</label>
                            <div class="border border-kotel-border rounded-lg p-4 bg-white text-black"
                                 :style="{ width: getReceiptPreviewWidth(settings.pos_print_paper_width), fontSize: settings.pos_print_font_size }">
                                <div v-if="settings.pos_print_show_logo" class="text-center mb-2">
                                    <img v-if="settings.hotel_logo" :src="settings.hotel_logo" alt="Hotel Logo" class="h-12 max-w-full object-contain mx-auto">
                                    <div v-else class="w-12 h-12 bg-kotel-yellow mx-auto rounded-full flex items-center justify-center font-bold">LOGO</div>
                                </div>
                                <div class="text-center border-b border-gray-300 pb-2 mb-2">
                                    <p class="font-bold">{{ settings.hotel_name || 'Grand Hotel' }}</p>
                                    <p class="text-xs">{{ settings.hotel_address || '123 Hotel Street' }}</p>
                                </div>
                                <div class="text-xs space-y-1">
                                    <p>Item 1 x2 .......... $20.00</p>
                                    <p>Item 2 x1 .......... $15.00</p>
                                </div>
                                <div class="border-t border-gray-300 mt-2 pt-2 font-bold">
                                    <p>TOTAL .......... $35.00</p>
                                </div>
                                <div class="text-center text-xs mt-4">
                                    <p>Thank you!</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Front Desk / Checkout Settings -->
                    <div class="bg-kotel-dark border border-kotel-border rounded-lg p-6">
                        <h4 class="text-md font-semibold text-kotel-yellow mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Front Desk / Checkout (Full Page)
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Paper Size</label>
                                <select v-model="settings.frontdesk_print_paper_width"
                                        class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                                    <option value="A4">A4 (210 × 297 mm)</option>
                                    <option value="A5">A5 (148 × 210 mm)</option>
                                    <option value="Letter">Letter (8.5 × 11 inch)</option>
                                </select>
                                <p class="mt-1 text-xs text-kotel-text-tertiary">Used for guest bills and invoices</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Font Size</label>
                                <select v-model="settings.frontdesk_print_font_size"
                                        class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                                    <option value="10px">Small (10px)</option>
                                    <option value="12px">Medium (12px)</option>
                                    <option value="14px">Large (14px)</option>
                                    <option value="16px">Extra Large (16px)</option>
                                </select>
                                <p class="mt-1 text-xs text-kotel-text-tertiary">Bill text size</p>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" v-model="settings.frontdesk_print_show_logo"
                                       class="h-4 w-4 text-kotel-yellow focus:ring-kotel-yellow border-kotel-border rounded bg-kotel-black">
                                <label class="ml-2 block text-sm text-kotel-text-secondary">Show Hotel Logo on Bill</label>
                            </div>
                        </div>

                        <!-- Front Desk Bill Preview -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Bill Preview</label>
                            <div class="border border-kotel-border rounded-lg p-4 bg-white text-black"
                                 :style="{ width: settings.frontdesk_print_paper_width === 'A4' ? '210mm' : settings.frontdesk_print_paper_width === 'A5' ? '148mm' : '8.5in', fontSize: settings.frontdesk_print_font_size }">
                                <div class="flex justify-between items-start border-b border-gray-300 pb-4 mb-4">
                                    <div v-if="settings.frontdesk_print_show_logo">
                                        <img v-if="settings.hotel_logo" :src="settings.hotel_logo" alt="Hotel Logo" class="h-16 max-w-xs object-contain">
                                        <div v-else class="w-16 h-16 bg-kotel-yellow rounded flex items-center justify-center font-bold text-sm">LOGO</div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold">{{ settings.hotel_name || 'Grand Hotel' }}</p>
                                        <p class="text-xs">{{ settings.hotel_address || '123 Hotel Street' }}</p>
                                        <p class="text-xs">{{ settings.hotel_phone || '+1 555-123-4567' }}</p>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <p><strong>Guest:</strong> John Doe</p>
                                    <p><strong>Room:</strong> 101</p>
                                    <p><strong>Check-out:</strong> Feb 10, 2026</p>
                                </div>
                                <table class="w-full text-sm mb-4">
                                    <thead>
                                        <tr class="border-b border-gray-300">
                                            <th class="text-left py-1">Description</th>
                                            <th class="text-right py-1">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="py-1">Room Charge (3 nights)</td>
                                            <td class="text-right py-1">$300.00</td>
                                        </tr>
                                        <tr>
                                            <td class="py-1">Food & Beverage</td>
                                            <td class="text-right py-1">$45.00</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="border-t border-gray-300 font-bold">
                                            <td class="py-2">TOTAL</td>
                                            <td class="text-right py-2">$345.00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Settings -->
                <div v-show="activeTab === 'security'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Session Timeout (minutes)</label>
                            <input type="number" v-model="settings.session_timeout"
                                   class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Password Minimum Length</label>
                            <input type="number" v-model="settings.password_min_length"
                                   class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" v-model="settings.require_2fa"
                                   class="h-4 w-4 text-kotel-yellow focus:ring-kotel-yellow border-kotel-border rounded bg-kotel-black">
                            <label class="ml-2 block text-sm text-kotel-text-secondary">Require Two-Factor Authentication</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" v-model="settings.force_password_change"
                                   class="h-4 w-4 text-kotel-yellow focus:ring-kotel-yellow border-kotel-border rounded bg-kotel-black">
                            <label class="ml-2 block text-sm text-kotel-text-secondary">Force Password Change on First Login</label>
                        </div>
                    </div>
                </div>

                <!-- IPTV Settings -->
                <div v-show="activeTab === 'iptv'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">IPTV Server URL</label>
                            <input type="url" v-model="settings.iptv_server_url"
                                   class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Default Channel Package</label>
                            <select v-model="settings.default_channel_package"
                                    class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                                <option value="basic">Basic Package</option>
                                <option value="premium">Premium Package</option>
                                <option value="vip">VIP Package</option>
                            </select>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" v-model="settings.enable_vod"
                                   class="h-4 w-4 text-kotel-yellow focus:ring-kotel-yellow border-kotel-border rounded bg-kotel-black">
                            <label class="ml-2 block text-sm text-kotel-text-secondary">Enable Video on Demand</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" v-model="settings.enable_parental_controls"
                                   class="h-4 w-4 text-kotel-yellow focus:ring-kotel-yellow border-kotel-border rounded bg-kotel-black">
                            <label class="ml-2 block text-sm text-kotel-text-secondary">Enable Parental Controls</label>
                        </div>
                    </div>
                </div>

                <!-- Integrations -->
                <div v-show="activeTab === 'integrations'" class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-kotel-text-primary">Booking Website Integration</h3>
                        <p class="text-sm text-kotel-text-tertiary mt-1">
                            Use this token from your public booking site when sending reservations to this server.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Booking API Token</label>
                            <input type="text" v-model="settings['integration.booking_api_token']"
                                   class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                            <p class="mt-1 text-xs text-kotel-text-tertiary">
                                Send as `X-Booking-Token` header on booking requests.
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Booking Endpoint</label>
                            <input type="text" :value="bookingApiEndpoint" readonly
                                   class="w-full border border-kotel-border bg-kotel-black text-kotel-text-primary rounded-md px-3 py-2">
                            <p class="mt-1 text-xs text-kotel-text-tertiary">
                                POST reservations to this URL from your website.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- License Information -->
                <div v-show="activeTab === 'license'" class="space-y-6">
                    <!-- Loading state -->
                    <div v-if="licenseLoading" class="bg-kotel-dark border border-kotel-border rounded-lg p-6 text-center">
                        <svg class="animate-spin h-8 w-8 text-kotel-yellow mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-kotel-text-secondary">Loading license information...</p>
                    </div>

                    <!-- License Active -->
                    <div v-else-if="licenseInfo" class="bg-kotel-dark border border-kotel-yellow rounded-lg p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-kotel-yellow flex items-center">
                                <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                License Active
                            </h3>
                            <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium">{{ licenseInfo.status }}</span>
                        </div>

                        <!-- Basic Information -->
                        <div class="bg-kotel-black/50 rounded-lg p-4 mb-6">
                            <h4 class="text-kotel-sky-blue font-semibold mb-4">Basic Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-kotel-sky-blue mb-1">License Key</label>
                                    <p class="text-white font-mono bg-kotel-black p-2 rounded text-sm break-all">{{ licenseInfo.license_key }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-kotel-sky-blue mb-1">Hotel Name</label>
                                    <p class="text-white font-medium">{{ licenseInfo.hotel_name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-kotel-sky-blue mb-1">License Type</label>
                                    <span class="bg-kotel-yellow text-kotel-black px-2 py-1 rounded font-medium text-sm">{{ licenseInfo.license_type }}</span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-kotel-sky-blue mb-1">Expires At</label>
                                    <p class="text-white">{{ licenseInfo.expires_at }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Device Allocation -->
                        <div class="bg-kotel-black/50 rounded-lg p-4 mb-6">
                            <h4 class="text-kotel-sky-blue font-semibold mb-4">Device Allocation</h4>
                            <p class="text-kotel-sky-blue/70 text-sm mb-4">Current device usage and limits</p>
                            <div class="space-y-3">
                                <div v-for="device in licenseInfo.device_allocation" :key="device.type"
                                     class="flex items-center justify-between p-3 bg-kotel-dark rounded border border-kotel-yellow/20">
                                    <div class="flex items-center space-x-3">
                                        <div class="text-center">
                                            <div class="text-kotel-yellow font-bold text-lg">{{ device.used }}/{{ device.limit }}</div>
                                            <div class="text-kotel-sky-blue text-sm">{{ device.type }}</div>
                                        </div>
                                    </div>
                                    <div class="flex-1 mx-4">
                                        <div class="w-full bg-kotel-black rounded-full h-3">
                                            <div class="bg-kotel-yellow h-3 rounded-full transition-all"
                                                 :style="{ width: Math.min((device.used / device.limit * 100), 100) + '%' }"></div>
                                        </div>
                                        <div class="text-xs text-kotel-sky-blue/70 mt-1 text-center">
                                            {{ Math.round((device.used / device.limit * 100)) }}% used
                                        </div>
                                    </div>
                                </div>
                                <div class="border-t border-kotel-yellow/30 pt-4 mt-4">
                                    <div class="flex items-center justify-between p-3 bg-kotel-yellow/10 rounded">
                                        <div class="text-center">
                                            <div class="text-kotel-yellow font-bold text-xl">{{ licenseInfo.total_used }}/{{ licenseInfo.total_limit }}</div>
                                            <div class="text-kotel-sky-blue font-medium">Total Devices</div>
                                        </div>
                                        <div class="flex-1 mx-4">
                                            <div class="w-full bg-kotel-black rounded-full h-4">
                                                <div class="bg-kotel-yellow h-4 rounded-full transition-all"
                                                     :style="{ width: Math.min((licenseInfo.total_used / licenseInfo.total_limit * 100), 100) + '%' }"></div>
                                            </div>
                                            <div class="text-sm text-kotel-sky-blue/70 mt-1 text-center">
                                                {{ Math.round((licenseInfo.total_used / licenseInfo.total_limit * 100)) }}% of total allocation used
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- License Features -->
                        <div class="bg-kotel-black/50 rounded-lg p-4 mb-6" v-if="licenseInfo.features">
                            <h4 class="text-kotel-sky-blue font-semibold mb-3">License Features</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                <div v-for="(value, feature) in licenseInfo.features" :key="feature"
                                     class="flex items-center space-x-2 p-2 bg-kotel-dark rounded">
                                    <svg v-if="value === true || value === -1" class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <svg v-else class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-white text-sm capitalize">{{ feature.replace('_', ' ') }}</span>
                                    <span v-if="typeof value === 'number' && value !== -1" class="text-kotel-yellow text-xs">({{ value }})</span>
                                    <span v-else-if="value === -1" class="text-kotel-yellow text-xs">(Unlimited)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Inline activation form (Change License) -->
                        <div v-if="showActivateForm" class="bg-kotel-black/50 rounded-lg p-5 mb-6 border border-kotel-yellow/40">
                            <h4 class="text-kotel-yellow font-semibold mb-4">Enter New License Key</h4>
                            <div class="space-y-3">
                                <input type="text" v-model="activateForm.license_key" placeholder="XXXX-XXXX-XXXX-XXXX"
                                       class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary font-mono focus:outline-none focus:ring-2 focus:ring-kotel-yellow" />
                                <input type="text" v-model="activateForm.hotel_name" placeholder="Hotel name (optional)"
                                       class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow" />
                                <div v-if="activateError" class="text-red-400 text-sm">{{ activateError }}</div>
                                <div v-if="activateSuccess" class="text-green-400 text-sm">{{ activateSuccess }}</div>
                                <div class="flex gap-3">
                                    <button @click="activateLicense" :disabled="licenseActionLoading || !activateForm.license_key"
                                            class="bg-kotel-yellow text-kotel-black px-5 py-2 rounded-md hover:bg-yellow-400 font-medium disabled:opacity-50 transition-colors">
                                        {{ licenseActionLoading ? 'Validating…' : 'Activate' }}
                                    </button>
                                    <button @click="showActivateForm = false"
                                            class="bg-kotel-darker text-kotel-text-secondary px-5 py-2 rounded-md hover:bg-kotel-dark transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-3 flex-wrap gap-y-2">
                            <button @click="changeLicense"
                                    class="bg-kotel-yellow text-kotel-black px-6 py-2 rounded-md hover:bg-yellow-400 font-medium transition-colors">
                                Change License
                            </button>
                            <button @click="refreshToken" :disabled="licenseActionLoading"
                                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 font-medium disabled:opacity-50 transition-colors">
                                {{ licenseActionLoading ? 'Working…' : 'Refresh Token' }}
                            </button>
                            <button @click="removeLicense" :disabled="licenseActionLoading"
                                    class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 font-medium disabled:opacity-50 transition-colors">
                                Remove License
                            </button>
                        </div>
                    </div>

                    <!-- No license state -->
                    <div v-else class="bg-kotel-dark border border-red-500 rounded-lg p-6">
                        <div class="text-center">
                            <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <h3 class="text-xl font-bold text-red-500 mb-2">No Valid License</h3>
                            <p class="text-kotel-sky-blue mb-4">System requires a valid license to operate properly.</p>

                            <!-- Inline activation form for unlicensed state -->
                            <div class="max-w-md mx-auto text-left space-y-3 mb-4">
                                <input type="text" v-model="activateForm.license_key" placeholder="XXXX-XXXX-XXXX-XXXX"
                                       class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary font-mono text-center focus:outline-none focus:ring-2 focus:ring-kotel-yellow" />
                                <input type="text" v-model="activateForm.hotel_name" placeholder="Hotel name (optional)"
                                       class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow" />
                                <div v-if="activateError" class="text-red-400 text-sm text-center">{{ activateError }}</div>
                                <div v-if="activateSuccess" class="text-green-400 text-sm text-center">{{ activateSuccess }}</div>
                            </div>
                            <button @click="activateLicense" :disabled="licenseActionLoading || !activateForm.license_key"
                                    class="bg-kotel-yellow text-kotel-black px-6 py-3 rounded-md hover:bg-yellow-400 font-medium disabled:opacity-50 transition-colors">
                                {{ licenseActionLoading ? 'Validating…' : 'Activate License' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Backup Settings -->
                <div v-show="activeTab === 'backup'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Backup Frequency</label>
                            <select v-model="settings.backup_frequency"
                                    class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Backup Retention (days)</label>
                            <input type="number" v-model="settings.backup_retention_days"
                                   class="w-full border border-kotel-border rounded-md px-3 py-2 bg-kotel-black text-kotel-text-primary focus:outline-none focus:ring-2 focus:ring-kotel-yellow">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-kotel-text-secondary mb-2">Last Backup</label>
                            <p class="text-sm text-kotel-text-tertiary py-2">{{ lastBackup }}</p>
                        </div>
                        <div>
                            <button @click="runBackup" :disabled="backupRunning"
                                    class="bg-kotel-yellow text-kotel-black px-4 py-2 rounded-md hover:bg-kotel-yellow-dark font-medium disabled:opacity-50 transition-colors">
                                {{ backupRunning ? 'Creating backup…' : 'Run Backup Now' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { notify } from '@/Composables/useNotification.js'
import { useTheme } from '@/Composables/useTheme.js'
import { setLocale } from '@/i18n/index.js'
import { useI18n } from 'vue-i18n'

const { locale } = useI18n()
const uiLocale = ref(locale.value)

function handleLocaleChange() {
    setLocale(uiLocale.value)
}

// Initialize theme
const { loadTheme } = useTheme()
const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    textTertiary: `var(--kotel-text-tertiary)`,
    primary: `var(--kotel-primary)`,
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    hover: `rgba(255, 255, 255, 0.1)`
}))

// Load theme on mount
loadTheme()
import {
    CogIcon,
    ShieldCheckIcon,
    TvIcon,
    CloudArrowUpIcon,
    PrinterIcon,
    LinkIcon,
    PaintBrushIcon
} from '@heroicons/vue/24/outline'

// Icons for new tabs
const PrinterIconComponent = {
    template: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>`
}

const props = defineProps({
    user: Object,
    settings: Object,
    allSettings: Object,
})

const activeTab = ref('general')

// Logo upload functionality
const logoInput = ref(null)
const logoPreview = ref(null)
const uploadedLogo = ref(null)
const isLogoSaving = ref(false)
const logoError = ref('')

const handleLogoUpload = (event) => {
    const file = event.target.files[0]
    logoError.value = ''
    if (file) {
        // Validate file type
        if (!file.type.match('image.*')) {
            logoError.value = 'Please select an image file (PNG, JPG, GIF, WEBP, SVG).'
            return
        }

        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            logoError.value = 'File size must be less than 2 MB.'
            return
        }

        // Store file for upload
        uploadedLogo.value = file

        // Create preview
        const reader = new FileReader()
        reader.onload = (e) => {
            logoPreview.value = e.target.result
            settings.value.hotel_logo = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const saveLogo = async () => {
    if (!uploadedLogo.value) return
    isLogoSaving.value = true
    logoError.value = ''
    try {
        const formData = new FormData()
        formData.append('logo', uploadedLogo.value)
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'))

        const response = await fetch('/admin/settings/logo', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData,
        })

        const result = await response.json()
        if (response.ok && result.success) {
            settings.value.hotel_logo = result.logo_url
            logoPreview.value = result.logo_url
            uploadedLogo.value = null
            notify.success('Logo saved successfully!')
        } else {
            const msg = result.message || result.errors?.logo?.[0] || 'Failed to save logo.'
            logoError.value = msg
            notify.error(msg)
        }
    } catch (e) {
        logoError.value = 'Network error while saving logo.'
        notify.error(logoError.value)
    } finally {
        isLogoSaving.value = false
    }
}

const handleRemoveLogo = async () => {
    if (!confirm('Remove the hotel logo?')) return
    uploadedLogo.value = null
    logoPreview.value = null
    settings.value.hotel_logo = null
    if (logoInput.value) logoInput.value.value = ''

    try {
        await fetch('/admin/settings/logo', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        })
        notify.success('Logo removed.')
    } catch {
        // silently ignore network errors for remove
    }
}

const removeLogo = handleRemoveLogo

// Initialize logo preview from existing settings
onMounted(() => {
    if (props.settings?.general?.hotel_logo) {
        logoPreview.value = props.settings.general.hotel_logo
        settings.value.hotel_logo = props.settings.general.hotel_logo
    }
})

// Get supported currencies
const supportedCurrencies = computed(() => {
    try {
        // First try to get from settings
        let currencies = props.settings?.general?.supported_currencies;

        // If it's a string, try to parse it as JSON
        if (typeof currencies === 'string') {
            try {
                currencies = JSON.parse(currencies);
            } catch (e) {
                console.error('Error parsing supported_currencies:', e);
                currencies = null;
            }
        }

        // If it's an object/array, use it; otherwise use fallback
        if (currencies && typeof currencies === 'object' && !Array.isArray(currencies)) {
            return currencies;
        }

        // Fallback currencies
        return {
            'USD': 'US Dollar ($)',
            'EUR': 'Euro (€)',
            'GBP': 'British Pound (£)',
            'CAD': 'Canadian Dollar (C$)',
            'AUD': 'Australian Dollar (A$)',
            'JPY': 'Japanese Yen (¥)',
            'CHF': 'Swiss Franc (CHF)',
            'CNY': 'Chinese Yuan (¥)',
            'INR': 'Indian Rupee (₹)',
            'KRW': 'South Korean Won (₩)',
            'SGD': 'Singapore Dollar (S$)',
            'HKD': 'Hong Kong Dollar (HK$)',
            'NZD': 'New Zealand Dollar (NZ$)',
            'SEK': 'Swedish Krona (kr)',
            'NOK': 'Norwegian Krone (kr)',
            'DKK': 'Danish Krone (kr)',
            'PLN': 'Polish Złoty (zł)',
            'CZK': 'Czech Koruna (Kč)',
            'HUF': 'Hungarian Forint (Ft)',
            'RUB': 'Russian Ruble (₽)',
            'BRL': 'Brazilian Real (R$)',
            'MXN': 'Mexican Peso ($)',
            'ARS': 'Argentine Peso ($)',
            'CLP': 'Chilean Peso ($)',
            'COP': 'Colombian Peso ($)',
            'PEN': 'Peruvian Sol (S/)',
            'ZAR': 'South African Rand (R)',
            'EGP': 'Egyptian Pound (£)',
            'MAD': 'Moroccan Dirham (DH)',
            'TND': 'Tunisian Dinar (د.ت)',
            'NGN': 'Nigerian Naira (₦)',
            'GHS': 'Ghanaian Cedi (₵)',
            'KES': 'Kenyan Shilling (KSh)',
            'UGX': 'Ugandan Shilling (USh)',
            'TZS': 'Tanzanian Shilling (TSh)',
            'ETB': 'Ethiopian Birr (Br)',
            'XAF': 'Central African CFA Franc (FCFA)',
            'XOF': 'West African CFA Franc (CFA)',
            'BWP': 'Botswana Pula (P)',
            'MUR': 'Mauritian Rupee (₨)',
            'SCR': 'Seychellois Rupee (₨)',
            'AED': 'UAE Dirham (د.إ)',
            'SAR': 'Saudi Riyal (﷼)',
            'QAR': 'Qatari Riyal (﷼)',
            'KWD': 'Kuwaiti Dinar (د.ك)',
            'BHD': 'Bahraini Dinar (د.ب)',
            'OMR': 'Omani Rial (﷼)',
            'JOD': 'Jordanian Dinar (د.ا)',
            'LBP': 'Lebanese Pound (ل.ل)',
            'ILS': 'Israeli Shekel (₪)',
            'TRY': 'Turkish Lira (₺)',
            'IRR': 'Iranian Rial (﷼)',
            'AFN': 'Afghan Afghani (؋)',
            'PKR': 'Pakistani Rupee (₨)',
            'BDT': 'Bangladeshi Taka (৳)',
            'LKR': 'Sri Lankan Rupee (₨)',
            'NPR': 'Nepalese Rupee (₨)',
            'BTN': 'Bhutanese Ngultrum (Nu.)',
            'MVR': 'Maldivian Rufiyaa (Rf)',
            'THB': 'Thai Baht (฿)',
            'VND': 'Vietnamese Dong (₫)',
            'LAK': 'Lao Kip (₭)',
            'KHR': 'Cambodian Riel (៛)',
            'MMK': 'Myanmar Kyat (K)',
            'MYR': 'Malaysian Ringgit (RM)',
            'IDR': 'Indonesian Rupiah (Rp)',
            'PHP': 'Philippine Peso (₱)',
            'TWD': 'Taiwan Dollar (NT$)',
            'MOP': 'Macanese Pataca (MOP$)',
            'BND': 'Brunei Dollar (B$)',
            'FJD': 'Fijian Dollar (FJ$)',
            'TOP': 'Tongan Paʻanga (T$)',
            'WST': 'Samoan Tala (WS$)',
            'VUV': 'Vanuatu Vatu (VT)',
            'SBD': 'Solomon Islands Dollar (SI$)',
            'PGK': 'Papua New Guinean Kina (K)',
            'NCL': 'New Caledonian Franc (₣)',
            'XPF': 'CFP Franc (₣)'
        }
    } catch (e) {
        console.error('Error in supportedCurrencies computed:', e);
        return {
            'USD': 'US Dollar ($)',
            'EUR': 'Euro (€)',
            'GBP': 'British Pound (£)',
            'XAF': 'Central African CFA Franc (FCFA)'
        }
    }
})

const tabs = [
    { id: 'general', name: 'General', icon: CogIcon },
    { id: 'theme', name: 'Theme', icon: PaintBrushIcon },
    { id: 'print', name: 'Print', icon: PrinterIcon },
    { id: 'security', name: 'Security', icon: ShieldCheckIcon },
    { id: 'iptv', name: 'IPTV', icon: TvIcon },
    { id: 'integrations', name: 'Integrations', icon: LinkIcon },
    { id: 'license', name: 'License', icon: ShieldCheckIcon },
    { id: 'backup', name: 'Backup', icon: CloudArrowUpIcon },
]

// Helper function for receipt preview width
const getReceiptPreviewWidth = (width) => {
    const widths = {
        '58mm': '180px',
        '80mm': '250px',
        '112mm': '350px'
    }
    return widths[width] || '250px'
}

// Initialize settings from props with Kotel theme defaults
const settings = ref({
    // General settings
    hotel_logo: props.settings?.general?.hotel_logo || null,
    hotel_name: props.settings?.general?.hotel_name || 'Grand Hotel',
    hotel_address: props.settings?.general?.hotel_address || '123 Hotel Street, City, State 12345',
    hotel_phone: props.settings?.general?.hotel_phone || '+1 (555) 123-4567',
    hotel_email: props.settings?.general?.hotel_email || 'info@grandhotel.com',
    timezone: props.settings?.general?.timezone || 'America/New_York',
    currency: props.settings?.general?.currency || 'USD',
    currency_position: props.settings?.general?.currency_position || 'prefix',
    tax_rate: props.settings?.general?.tax_rate != null ? Number(props.settings.general.tax_rate) : 0,
    room_tax_rate: props.settings?.general?.room_tax_rate != null ? Number(props.settings.general.room_tax_rate) : 0,

    // Guest & Discount settings
    auto_apply_guest_type_discount: props.settings?.general?.auto_apply_guest_type_discount !== undefined
        ? props.settings.general.auto_apply_guest_type_discount
        : true,
    auto_apply_vip_discount: props.settings?.general?.auto_apply_vip_discount !== undefined
        ? props.settings.general.auto_apply_vip_discount
        : true,
    vip_discount_percentage: props.settings?.general?.vip_discount_percentage || 10,
    discount_combination_mode: props.settings?.general?.discount_combination_mode || 'add',

    // Receipt / print sizes
    receipt_size_front_desk: props.settings?.general?.receipt_size_front_desk || 'A4',
    receipt_size_restaurant: props.settings?.general?.receipt_size_restaurant || '80mm',

    // POS Print Settings
    pos_print_paper_width: props.settings?.pos?.pos_print_paper_width || '80mm',
    pos_print_font_size: props.settings?.pos?.pos_print_font_size || '12px',
    pos_print_show_logo: props.settings?.pos?.pos_print_show_logo !== undefined ? props.settings.pos.pos_print_show_logo : true,

    // Front Desk Print Settings
    frontdesk_print_paper_width: props.settings?.frontdesk?.frontdesk_print_paper_width || 'A4',
    frontdesk_print_font_size: props.settings?.frontdesk?.frontdesk_print_font_size || '12px',
    frontdesk_print_show_logo: props.settings?.frontdesk?.frontdesk_print_show_logo !== undefined ? props.settings.frontdesk.frontdesk_print_show_logo : true,

    // Security settings
    session_timeout: props.settings?.security?.session_timeout || 120,
    password_min_length: props.settings?.security?.password_min_length || 8,
    require_2fa: props.settings?.security?.require_2fa || false,
    force_password_change: props.settings?.security?.force_password_change || true,
    max_login_attempts: props.settings?.security?.max_login_attempts || 5,

    // IPTV settings
    iptv_server_url: props.settings?.iptv?.iptv_server_url || 'http://iptv.grandhotel.com',
    default_channel_package: props.settings?.iptv?.default_channel_package || 'premium',
    enable_vod: props.settings?.iptv?.enable_vod || true,
    enable_parental_controls: props.settings?.iptv?.enable_parental_controls || true,
    auto_provision_rooms: props.settings?.iptv?.auto_provision_rooms || true,

    // Integration settings
    'integration.booking_api_token': props.settings?.integration?.['integration.booking_api_token'] || '',

    // Backup settings
    backup_frequency: props.settings?.backup?.backup_frequency || 'daily',
    backup_retention_days: props.settings?.backup?.backup_retention_days || 30,
    backup_location: props.settings?.backup?.backup_location || 'local',
    enable_auto_backup: props.settings?.backup?.enable_auto_backup || true,

    // Theme settings with Kotel Hotel Management System defaults
    theme_mode: props.settings?.theme?.theme_mode || 'dark',
    theme_primary_color: props.settings?.theme?.theme_primary_color || '#FFD700', // Kotel Yellow
    theme_secondary_color: props.settings?.theme?.theme_secondary_color || '#87CEEB', // Sky Blue
    theme_background_color: props.settings?.theme?.theme_background_color || '#000000', // Black
    theme_sidebar_color: props.settings?.theme?.theme_sidebar_color || '#1a1a1a', // Dark Gray
    theme_card_color: props.settings?.theme?.theme_card_color || '#111827', // Dark Blue-Gray
    theme_text_primary: props.settings?.theme?.theme_text_primary || '#ffffff', // White
    theme_text_secondary: props.settings?.theme?.theme_text_secondary || '#87CEEB', // Sky Blue
    theme_text_tertiary: props.settings?.theme?.theme_text_tertiary || '#D1D5DB', // Light Gray
    theme_border_color: props.settings?.theme?.theme_border_color || '#374151', // Gray
    theme_success_color: props.settings?.theme?.theme_success_color || '#10B981', // Green
    theme_warning_color: props.settings?.theme?.theme_warning_color || '#F97316', // Orange
    theme_danger_color: props.settings?.theme?.theme_danger_color || '#EF4444', // Red
    theme_info_color: props.settings?.theme?.theme_info_color || '#8B5CF6', // Purple
    theme_font_family: props.settings?.theme?.theme_font_family || 'Figtree, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif',
    theme_radius: props.settings?.theme?.theme_radius || '0.5rem',
    theme_shadow: props.settings?.theme?.theme_shadow || '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
    theme_transition: props.settings?.theme?.theme_transition || 'all 0.3s ease-in-out',
})

const lastBackup = computed(() => {
    return new Date().toLocaleString()
})

const bookingApiEndpoint = computed(() => {
    if (typeof window !== 'undefined') {
        return `${window.location.origin}/api/public/bookings`
    }
    return '/api/public/bookings'
})

const isSaving = ref(false)
const licenseInfo = ref(null)
const licenseLoading = ref(true)
const showActivateForm = ref(false)
const licenseActionLoading = ref(false)
const activateForm = ref({ license_key: '', hotel_name: '' })
const activateError = ref('')
const activateSuccess = ref('')
const backupRunning = ref(false)

const changeLicense = () => {
    showActivateForm.value = true
    activateError.value = ''
    activateSuccess.value = ''
    activateForm.value.license_key = ''
}

const refreshLicenseInfo = async () => {
    await loadLicenseInfo()
}

const activateLicense = async () => {
    if (!activateForm.value.license_key) return
    licenseActionLoading.value = true
    activateError.value = ''
    activateSuccess.value = ''
    try {
        const res = await fetch('/admin/settings/license/activate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify(activateForm.value),
        })
        const data = await res.json()
        if (data.success) {
            activateSuccess.value = 'License activated successfully.'
            showActivateForm.value = false
            await loadLicenseInfo()
        } else {
            activateError.value = data.message || data.errors?.license_key?.[0] || 'Activation failed.'
        }
    } catch {
        activateError.value = 'Network error — could not reach license server.'
    }
    licenseActionLoading.value = false
}

const refreshToken = async () => {
    licenseActionLoading.value = true
    try {
        const res = await fetch('/admin/settings/license/refresh', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
        })
        const data = await res.json()
        if (data.success) {
            notify.success('License token refreshed.')
            await loadLicenseInfo()
        } else {
            notify.error(data.message || 'Token refresh failed.')
        }
    } catch {
        notify.error('Network error.')
    }
    licenseActionLoading.value = false
}

const runBackup = async () => {
    if (!confirm('Create a new system backup now?')) return
    backupRunning.value = true
    try {
        const res = await fetch('/admin/settings/backup', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
        })
        if (res.ok) {
            notify.success('Backup created successfully.')
        } else {
            notify.error('Backup failed.')
        }
    } catch {
        notify.error('Network error.')
    }
    backupRunning.value = false
}

// Load license information
onMounted(async () => {
    await loadLicenseInfo()

    console.log('🔄 Settings page mounted, checking theme...')

    // Apply theme from props when settings page loads
    const { applyTheme } = useTheme()

    if (props.settings?.theme) {
        console.log('📦 Loading theme from props:', props.settings.theme.theme_border_color)

        const themeSettings = {
            theme_mode: props.settings.theme.theme_mode,
            theme_primary_color: props.settings.theme.theme_primary_color,
            theme_secondary_color: props.settings.theme.theme_secondary_color,
            theme_background_color: props.settings.theme.theme_background_color,
            theme_sidebar_color: props.settings.theme.theme_sidebar_color,
            theme_card_color: props.settings.theme.theme_card_color,
            theme_text_primary: props.settings.theme.theme_text_primary,
            theme_text_secondary: props.settings.theme.theme_text_secondary,
            theme_text_tertiary: props.settings.theme.theme_text_tertiary,
            theme_border_color: props.settings.theme.theme_border_color,
            theme_success_color: props.settings.theme.theme_success_color,
            theme_warning_color: props.settings.theme.theme_warning_color,
            theme_danger_color: props.settings.theme.theme_danger_color,
            theme_info_color: props.settings.theme.theme_info_color,
            theme_font_family: props.settings.theme.theme_font_family,
            theme_radius: props.settings.theme.theme_radius,
            theme_shadow: props.settings.theme.theme_shadow,
            theme_transition: props.settings.theme.theme_transition
        }

        // Also save to localStorage for consistency
        localStorage.setItem('kotel_theme', JSON.stringify(themeSettings))
        console.log('💾 Theme from props saved to localStorage')

        applyTheme(themeSettings)

        // Verify CSS variables
        setTimeout(() => {
            const root = document.documentElement
            const borderColor = getComputedStyle(root).getPropertyValue('--kotel-border').trim()
            console.log('🎨 Border color in CSS after props load:', borderColor)
        }, 100)

    } else {
        console.log('⚠️ No theme in props, checking localStorage...')

        // Fallback to loadTheme if no theme in props
        const { loadTheme } = useTheme()
        await loadTheme()

        // Check what was loaded
        const savedTheme = localStorage.getItem('kotel_theme')
        if (savedTheme) {
            const parsed = JSON.parse(savedTheme)
            console.log('📦 Theme loaded from localStorage:', parsed.theme_border_color)
        } else {
            console.log('❌ No theme found in localStorage either')
        }
    }
})

const loadLicenseInfo = async () => {
    licenseLoading.value = true
    try {
        const response = await fetch('/admin/license/info', {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '' }
        })
        if (response.ok) {
            const data = await response.json()
            licenseInfo.value = data.licensed ? data : null
        } else {
            licenseInfo.value = null
        }
    } catch {
        licenseInfo.value = null
    } finally {
        licenseLoading.value = false
    }
}

const saveSettings = async () => {
    isSaving.value = true

    try {
        // Determine the correct route based on user role
        const userRole = props.user?.roles?.[0]?.name || 'admin'
        const settingsRoute = userRole === 'manager' ? '/manager/settings' : '/admin/settings'

        // Only save settings from the active tab to reduce payload size
        const settingsToSave = {}

        if (activeTab.value === 'general') {
            // General settings
            settingsToSave.hotel_name = settings.value.hotel_name
            settingsToSave.hotel_address = settings.value.hotel_address
            settingsToSave.hotel_phone = settings.value.hotel_phone
            settingsToSave.hotel_email = settings.value.hotel_email
            settingsToSave.timezone = settings.value.timezone
            settingsToSave.currency = settings.value.currency
            settingsToSave.currency_position = settings.value.currency_position
            settingsToSave.tax_rate = settings.value.tax_rate
            settingsToSave.room_tax_rate = settings.value.room_tax_rate
            settingsToSave.auto_apply_guest_type_discount = settings.value.auto_apply_guest_type_discount
            settingsToSave.auto_apply_vip_discount = settings.value.auto_apply_vip_discount
            settingsToSave.vip_discount_percentage = settings.value.vip_discount_percentage
            settingsToSave.discount_combination_mode = settings.value.discount_combination_mode
        } else if (activeTab.value === 'print') {
            // Print settings
            settingsToSave.pos_print_paper_width = settings.value.pos_print_paper_width
            settingsToSave.pos_print_font_size = settings.value.pos_print_font_size
            settingsToSave.pos_print_show_logo = settings.value.pos_print_show_logo
            settingsToSave.frontdesk_print_paper_width = settings.value.frontdesk_print_paper_width
            settingsToSave.frontdesk_print_font_size = settings.value.frontdesk_print_font_size
            settingsToSave.frontdesk_print_show_logo = settings.value.frontdesk_print_show_logo
        } else if (activeTab.value === 'security') {
            // Security settings
            settingsToSave.session_timeout = settings.value.session_timeout
            settingsToSave.password_min_length = settings.value.password_min_length
            settingsToSave.require_2fa = settings.value.require_2fa
            settingsToSave.force_password_change = settings.value.force_password_change
        } else if (activeTab.value === 'iptv') {
            // IPTV settings
            settingsToSave.iptv_server_url = settings.value.iptv_server_url
            settingsToSave.default_channel_package = settings.value.default_channel_package
            settingsToSave.enable_vod = settings.value.enable_vod
            settingsToSave.enable_parental_controls = settings.value.enable_parental_controls
        } else if (activeTab.value === 'integrations') {
            // Integration settings
            settingsToSave['integration.booking_api_token'] = settings.value['integration.booking_api_token']
        } else if (activeTab.value === 'backup') {
            // Backup settings
            settingsToSave.backup_frequency = settings.value.backup_frequency
            settingsToSave.backup_retention_days = settings.value.backup_retention_days
        } else if (activeTab.value === 'theme') {
            // Theme settings
            settingsToSave.theme_mode = settings.value.theme_mode
            settingsToSave.theme_primary_color = settings.value.theme_primary_color
            settingsToSave.theme_secondary_color = settings.value.theme_secondary_color
            settingsToSave.theme_background_color = settings.value.theme_background_color
            settingsToSave.theme_sidebar_color = settings.value.theme_sidebar_color
            settingsToSave.theme_card_color = settings.value.theme_card_color
            settingsToSave.theme_text_primary = settings.value.theme_text_primary
            settingsToSave.theme_text_secondary = settings.value.theme_text_secondary
            settingsToSave.theme_text_tertiary = settings.value.theme_text_tertiary
            settingsToSave.theme_border_color = settings.value.theme_border_color
            settingsToSave.theme_success_color = settings.value.theme_success_color
            settingsToSave.theme_warning_color = settings.value.theme_warning_color
            settingsToSave.theme_danger_color = settings.value.theme_danger_color
            settingsToSave.theme_radius = settings.value.theme_radius
        }

        const response = await fetch(settingsRoute, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                settings: settingsToSave
            })
        })

        if (response.ok) {
            const result = await response.json()
            console.log('📊 Server response:', result)

            if (result.debug) {
                console.log('✅ Total settings saved:', result.debug.total_settings_saved)
                console.log('✅ Theme settings saved:', result.debug.theme_settings_saved)
                console.log('📦 Theme settings data:', result.debug.theme_settings)
            }

            notify.success('Settings saved successfully!')

            // Apply theme immediately if theme settings were changed
            const themeSettings = {
                theme_mode: settings.value.theme_mode,
                theme_primary_color: settings.value.theme_primary_color,
                theme_secondary_color: settings.value.theme_secondary_color,
                theme_background_color: settings.value.theme_background_color,
                theme_sidebar_color: settings.value.theme_sidebar_color,
                theme_card_color: settings.value.theme_card_color,
                theme_text_primary: settings.value.theme_text_primary,
                theme_text_secondary: settings.value.theme_text_secondary,
                theme_text_tertiary: settings.value.theme_text_tertiary,
                theme_border_color: settings.value.theme_border_color,
                theme_success_color: settings.value.theme_success_color,
                theme_warning_color: settings.value.theme_warning_color,
                theme_danger_color: settings.value.theme_danger_color,
                theme_info_color: settings.value.theme_info_color,
                theme_font_family: settings.value.theme_font_family,
                theme_radius: settings.value.theme_radius,
                theme_shadow: settings.value.theme_shadow,
                theme_transition: settings.value.theme_transition
            }

            console.log('💾 Saving theme to localStorage:', themeSettings.theme_border_color)

            // Explicitly save to localStorage
            localStorage.setItem('kotel_theme', JSON.stringify(themeSettings))

            // Verify it was saved
            const savedTheme = localStorage.getItem('kotel_theme')
            if (savedTheme) {
                const parsed = JSON.parse(savedTheme)
                console.log('✅ Theme verified in localStorage:', parsed.theme_border_color)
            } else {
                console.error('❌ Failed to save theme to localStorage')
            }

            // Apply theme
            applyTheme(themeSettings)

            // Verify CSS variables were set
            setTimeout(() => {
                const root = document.documentElement
                const borderColor = getComputedStyle(root).getPropertyValue('--kotel-border').trim()
                console.log('🎨 Border color in CSS after apply:', borderColor)
            }, 100)

        } else {
            console.error('❌ Server response not OK:', response.status)
            notify.error('Error saving settings. Please try again.')
        }
    } catch (error) {
        console.error('Error saving settings:', error)
        notify.error('Error saving settings. Please try again.')
    }

    isSaving.value = false
}

const resetSettings = () => {
    if (confirm('Are you sure you want to reset all settings to default values?')) {
        settings.value = {
            hotel_name: 'Grand Hotel',
            hotel_address: '123 Hotel Street, City, State 12345',
            hotel_phone: '+1 (555) 123-4567',
            hotel_email: 'info@grandhotel.com',
            timezone: 'America/New_York',
            currency: 'USD',
            receipt_size_front_desk: 'A4',
            receipt_size_restaurant: '80mm',
            session_timeout: 120,
            password_min_length: 8,
            require_2fa: false,
            force_password_change: true,
            iptv_server_url: 'http://iptv.grandhotel.com',
            default_channel_package: 'premium',
            enable_vod: true,
            enable_parental_controls: true,
            'integration.booking_api_token': '',
            backup_frequency: 'daily',
            backup_retention_days: 30,
        }
        notify.success('Settings reset to default values.')
    }
}

const removeLicense = async () => {
    if (!confirm('Remove the license? The system will be locked until re-activated.')) return
    licenseActionLoading.value = true
    try {
        const res = await fetch('/admin/settings/license/deactivate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        })
        if (!res.ok && res.status !== 200) {
            notify.error('Server error (' + res.status + '). Please try again.')
            licenseActionLoading.value = false
            return
        }
        const result = await res.json()
        if (result.success) {
            licenseInfo.value = null
            notify.success('License removed. Redirecting to activation…')
            setTimeout(() => window.location.href = '/license/activate', 1500)
        } else {
            notify.error(result.message || 'Failed to remove license.')
        }
    } catch (err) {
        notify.error('Could not reach the server. Please try again.')
    }
    licenseActionLoading.value = false
}

// Theme methods
const getContrastColor = (hexColor) => {
    // Handle null/undefined or invalid color values
    if (!hexColor || typeof hexColor !== 'string') {
        return '#ffffff'
    }

    try {
        // Convert hex to RGB
        const hex = hexColor.replace('#', '')
        const r = parseInt(hex.substr(0, 2), 16)
        const g = parseInt(hex.substr(2, 2), 16)
        const b = parseInt(hex.substr(4, 2), 16)

        // Calculate brightness
        const brightness = (r * 299 + g * 587 + b * 114) / 1000

        // Return black or white based on brightness
        return brightness > 128 ? '#000000' : '#ffffff'
    } catch (error) {
        console.warn('Error calculating contrast color:', error)
        return '#ffffff'
    }
}

const applyTheme = () => {
    // Apply theme to document root
    const root = document.documentElement

    // Set CSS custom properties
    root.style.setProperty('--kotel-primary', settings.value.theme_primary_color)
    root.style.setProperty('--kotel-secondary', settings.value.theme_secondary_color)
    root.style.setProperty('--kotel-success', settings.value.theme_success_color)
    root.style.setProperty('--kotel-warning', settings.value.theme_warning_color)
    root.style.setProperty('--kotel-danger', settings.value.theme_danger_color)
    root.style.setProperty('--kotel-background', settings.value.theme_background_color)
    root.style.setProperty('--kotel-sidebar', settings.value.theme_sidebar_color)
    root.style.setProperty('--kotel-card', settings.value.theme_card_color)
    root.style.setProperty('--kotel-text-primary', settings.value.theme_text_primary)
    root.style.setProperty('--kotel-text-secondary', settings.value.theme_text_secondary)
    root.style.setProperty('--kotel-text-tertiary', settings.value.theme_text_tertiary)
    root.style.setProperty('--kotel-border', settings.value.theme_border_color)
    root.style.setProperty('--kotel-radius', settings.value.theme_radius)
    root.style.setProperty('--kotel-shadow', settings.value.theme_shadow)
    root.style.setProperty('--kotel-transition', settings.value.theme_transition)

    notify.success('Theme applied successfully!')
}

const resetTheme = () => {
    if (confirm('Are you sure you want to reset theme to default values?')) {
        settings.value.theme_mode = 'dark'
        settings.value.theme_primary_color = '#facc15'
        settings.value.theme_secondary_color = '#3b82f6'
        settings.value.theme_success_color = '#22c55e'
        settings.value.theme_warning_color = '#f59e0b'
        settings.value.theme_danger_color = '#ef4444'
        settings.value.theme_background_color = '#0b0b0b'
        settings.value.theme_sidebar_color = '#0f172a'
        settings.value.theme_card_color = '#111827'
        settings.value.theme_text_primary = '#f3f4f6'
        settings.value.theme_text_secondary = '#9ca3af'
        settings.value.theme_text_tertiary = '#6b7280'
        settings.value.theme_border_color = '#374151'
        settings.value.theme_radius = '0.5rem'
        settings.value.theme_shadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)'
        settings.value.theme_transition = 'all 0.3s ease-in-out'

        applyTheme()
        notify.success('Theme reset to default values.')
    }
}

const saveTheme = async () => {
    try {
        // Determine the correct route based on user role
        const userRole = props.user?.roles?.[0]?.name || 'admin'
        const settingsRoute = userRole === 'manager' ? '/manager/settings' : '/admin/settings'

        const response = await fetch(settingsRoute, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                settings: settings.value
            })
        })

        if (response.ok) {
            notify.success('Theme saved successfully!')
        } else {
            notify.error('Error saving theme. Please try again.')
        }
    } catch (error) {
        console.error('Error saving theme:', error)
        notify.error('Error saving theme. Please try again.')
    }
}
</script>
