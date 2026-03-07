<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'button',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    color: {
        type: String,
        default: 'primary', // primary, secondary, success, danger, warning, purple
    },
});

const colorClass = computed(() => {
    const colorMap = {
        primary: 'bg-kotel-yellow text-kotel-black hover:bg-kotel-yellow-dark focus:ring-kotel-yellow',
        secondary: 'bg-kotel-sky-blue text-white hover:bg-kotel-sky-blue-dark focus:ring-kotel-sky-blue',
        success: 'bg-kotel-green text-white hover:bg-kotel-green-dark focus:ring-kotel-green',
        danger: 'bg-kotel-red text-white hover:bg-kotel-red-dark focus:ring-kotel-red',
        warning: 'bg-kotel-orange text-white hover:bg-kotel-orange-dark focus:ring-kotel-orange',
        purple: 'bg-kotel-purple text-white hover:bg-kotel-purple-dark focus:ring-kotel-purple',
    }
    return colorMap[props.color] || colorMap.primary
})
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:ring-offset-2 transition ease-in-out duration-150 shadow-kotel-button"
        :class="colorClass"
    >
        <slot />
    </button>
</template>
