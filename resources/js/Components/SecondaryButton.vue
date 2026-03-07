<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex items-center px-4 py-2 bg-kotel-bg-card border border-kotel-border rounded-md font-semibold text-xs text-kotel-text-primary uppercase tracking-widest shadow-sm hover:bg-kotel-bg-card focus:outline-none focus:ring-2 focus:ring-kotel-primary focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
        <slot />
    </button>
</template>
