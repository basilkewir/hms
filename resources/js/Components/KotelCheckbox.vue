<script setup>
import { computed } from 'vue'

const props = defineProps({
    checked: [Boolean, Array],
    value: [String, Number, Boolean],
    id: String,
});

const emit = defineEmits(['update:checked']);

const isChecked = computed(() => {
    if (Array.isArray(props.checked)) {
        // For array values, check if the value prop is in the array
        return props.checked.includes(props.value);
    }
    // For boolean values, return the checked prop directly
    return props.checked;
});

const handleChange = (event) => {
    if (Array.isArray(props.checked)) {
        // Handle array case: add or remove the value
        const newArray = [...props.checked];
        if (event.target.checked) {
            if (!newArray.includes(props.value)) {
                newArray.push(props.value);
            }
        } else {
            const index = newArray.indexOf(props.value);
            if (index > -1) {
                newArray.splice(index, 1);
            }
        }
        emit('update:checked', newArray);
    } else {
        // Handle boolean case
        emit('update:checked', event.target.checked);
    }
};
</script>

<template>
    <input
        :id="id"
        type="checkbox"
        :checked="isChecked"
        :value="value"
        @change="handleChange"
        class="w-4 h-4 text-kotel-yellow bg-kotel-gray border-kotel-border-light rounded focus:ring-kotel-yellow focus:ring-2"
    />
</template>
