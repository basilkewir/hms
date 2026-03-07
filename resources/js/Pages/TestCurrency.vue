<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6">Currency Test Page</h1>

        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Currency Settings</h2>
            <p><strong>Current Currency:</strong> {{ currentCurrency }}</p>
            <p><strong>Currency Symbol:</strong> {{ currencySymbol }}</p>
        </div>

        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Test Amounts</h2>
            <div class="space-y-3">
                <div>
                    <span class="font-medium">Amount 100:</span>
                    <span class="ml-2">{{ formatCurrency(100) }}</span>
                </div>
                <div>
                    <span class="font-medium">Amount 125.50:</span>
                    <span class="ml-2">{{ formatCurrency(125.50) }}</span>
                </div>
                <div>
                    <span class="font-medium">Amount 99.99:</span>
                    <span class="ml-2">{{ formatCurrency(99.99) }}</span>
                </div>
                <div>
                    <span class="font-medium">Amount 1000.25:</span>
                    <span class="ml-2">{{ formatCurrency(1000.25) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Currency Override Test</h2>
            <div class="space-y-3">
                <div>
                    <span class="font-medium">100 in EUR:</span>
                    <span class="ml-2">{{ formatCurrency(100, 'EUR') }}</span>
                </div>
                <div>
                    <span class="font-medium">100 in GBP:</span>
                    <span class="ml-2">{{ formatCurrency(100, 'GBP') }}</span>
                </div>
                <div>
                    <span class="font-medium">100 in JPY:</span>
                    <span class="ml-2">{{ formatCurrency(100, 'JPY') }}</span>
                </div>
                <div>
                    <span class="font-medium">100 in XAF:</span>
                    <span class="ml-2">{{ formatCurrency(100, 'XAF') }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Currency source</h2>
            <p class="text-gray-600">Currency is read from Admin Settings (hotelSettings) on each page. Use Admin → Settings to change currency.</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import {
    formatCurrency,
    getCurrencySymbol,
    getCurrentCurrency,
    initializeCurrencySettings
} from '@/Utils/currency.js'

const currentCurrency = computed(() => getCurrentCurrency())
const currencySymbol = computed(() => getCurrencySymbol(getCurrentCurrency()))

onMounted(() => {
    initializeCurrencySettings()
})
</script>
