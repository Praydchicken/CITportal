<script setup>
	defineProps({
		status_errors: String,
	});
</script>

<template>
	<div class="mb-4">
		<p class="text-sm text-red-500"> {{ status_errors }}</p>
	</div>
</template>