<script setup>
defineProps({
	routeName: String,
	label: String,
	icon: String,

});
</script>
<template>

	<Link :href="route(routeName)" class="font-medium underline hover:text-blue-500 hover:no-underline">
		<span v-if="icon" class="mr-1">
			<i :class="`fa-solid fa-${icon}`"></i>
		</span>{{ label }}
	</Link>
</template>