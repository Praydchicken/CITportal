<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
defineOptions({

});

const page = usePage();
const props = defineProps({
  welcomeMessage: String,
  announcements: Array,
  classSchedule: Array
});

console.log(props);

</script>

<template>
  <h1>Teacher Dashboard</h1>
</template>
