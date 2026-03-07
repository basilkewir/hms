<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
	<button
		:type="type"
		class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest disabled:opacity-50 transition ease-in-out duration-150 bg-kotel-yellow text-black hover:bg-kotel-yellow-dark focus:bg-kotel-yellow-dark active:bg-kotel-yellow-dark focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2"
	>
		<slot />
	</button>
</template>
