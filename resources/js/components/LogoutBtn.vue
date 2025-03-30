<script setup>
import { router } from '@inertiajs/vue3';
// import route from 'ziggy-js';

const logout = () => {
  router.post('/logout'); // ✅ Sends POST request to logout route
};
</script>

<template>
    <button @click="logout" class="cursor-pointer bg-amber-100">Logout</button>
</template>