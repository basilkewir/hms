<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String,
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        ref="input"
        class="border-kotel-border focus:border-kotel-primary focus:ring-kotel-primary rounded-md shadow-sm bg-kotel-bg-card text-kotel-text-primary"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>
