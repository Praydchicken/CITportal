<script setup>
defineProps({
  show: Boolean, // Controls whether the overlay is visible
});
</script>

<template>
    <transition>
        <div v-if="show"  @click="$emit('close')" class="overlay fixed top-0 left-0 right-0 bottom-0 "></div>
    </transition>
</template>

<style scoped>
/* Fade animation */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>