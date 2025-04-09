<script setup>
defineProps({
	routeName: String,
	label: String,
});
</script>
<template>

	<Link :href="route(routeName)" class="underline hover:text-blue-500 hover:no-underline">
		{{ label }}
	</Link>
</template>