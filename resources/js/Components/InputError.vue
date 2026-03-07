<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message">
        <p class="text-sm text-kotel-red">
            {{ message }}
        </p>
    </div>
</template>
