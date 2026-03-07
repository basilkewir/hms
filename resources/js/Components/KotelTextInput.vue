<script setup>
defineProps({
    modelValue: String,
    type: {
        type: String,
        default: 'text',
    },
    placeholder: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <input
        :type="type"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :placeholder="placeholder"
        :required="required"
        class="w-full px-4 py-3 bg-kotel-gray border border-kotel-border-light rounded-lg text-kotel-text-primary placeholder-kotel-text-tertiary focus:outline-none focus:ring-2 focus:ring-kotel-yellow focus:border-transparent transition-all duration-300"
    />
</template>
