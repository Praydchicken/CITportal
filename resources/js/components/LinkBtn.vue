<script setup>
defineProps({
	routeName: String,
	label: String,
});
</script>
<template>

<Link :href="route(routeName)" class="inline-block bg-yellow-600 text-white px-3 py-1 rounded hover:bg-yellow-800">
    {{ label }}
  </Link>
</template>