<script setup>
import DashboardLayout from '../../components/AdminDashboardLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';


defineOptions({
  layout: DashboardLayout
});

</script>

<template>
    <Head title="Room Management" />

   <h1>This is room management</h1>
</template>
