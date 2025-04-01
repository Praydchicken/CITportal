<script setup>
defineProps({
  show: {
    type: Boolean,
    required: true
  },
  message: {
    type: String,
    required: true
  },
  type: {
    type: String,
    default: 'success'
  }
});
</script>

<template>
  <div v-if="show" class="fixed top-4 right-4 z-50 animate-notification">
    <div :class="[
      'px-4 py-3 rounded-lg shadow-lg flex items-center space-x-2',
      type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    ]">
      <!-- Success Icon -->
      <svg v-if="type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
      <!-- Error Icon -->
      <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
      <span class="font-medium">{{ message }}</span>
    </div>
  </div>
</template>


<style scoped>
.animate-notification {
  animation: slideIn 0.5s ease-out forwards, fadeOut 0.5s ease-in 3s forwards;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }

  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes fadeOut {
  from {
    opacity: 1;
    transform: translateX(0);
  }

  to {
    opacity: 0;
    transform: translateX(100%);
  }
}
</style>