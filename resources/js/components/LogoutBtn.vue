<script setup>
import { router } from '@inertiajs/vue3';
// import route from 'ziggy-js';

const logout = () => {
  router.post('/logout'); // ✅ Sends POST request to logout route
};
</script>

<template>
    <button @click="logout" class="cursor-pointer bg-[#1a3047] text-[#ffff] font-bold rounded-md pt-2 pb-2 pl-3 pr-3 flex justify-center items-center">Logout</button>
</template>