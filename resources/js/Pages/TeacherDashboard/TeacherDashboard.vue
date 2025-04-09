<script setup>
import StudentDashboardLayout from '../../components/studentDashboardLayout/StudentDashboardLayout.vue';
import { usePage } from '@inertiajs/vue3';

defineOptions({
  layout: StudentDashboardLayout
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
