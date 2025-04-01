<script setup>
import { defineProps, defineEmits } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faXmark } from '@fortawesome/free-solid-svg-icons'

defineProps({
  show: {
    type: Boolean,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  submitButtonText: {
    type: String,
    default: 'Submit'
  }
});

const emit = defineEmits(['close', 'submit']);
</script>

<template>
  <transition name="slide">
    <form @submit.prevent="$emit('submit')" v-if="show"
      class="card fixed z-40 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 p-6 w-[600px]">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-xl font-semibold text-center mb-4">{{ title }}</h2>
        <button @click.prevent="$emit('close')"
          class="text-xl cursor-pointer bg-[#1a3047] text-[#ffff] hover:bg-[#559de6] font-bold rounded-md pt-2 pb-2 pl-3 pr-3 flex justify-center items-center">
          <font-awesome-icon icon="xmark" />
        </button>
      </div>

      <!-- Slot for form content -->
      <slot></slot>

      <!-- Submit Button -->
      <button type="submit"
        class="w-full bg-[#1a3047] cursor-pointer hover:bg-[#559de6] text-white py-2 mt-6 rounded-md font-semibold transition">
        <span v-if="loading">Saving...</span>
        <span v-else>{{ submitButtonText }}</span>
      </button>
    </form>
  </transition>
</template>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
  transform: translate(-50%, -60%);
  opacity: 0;
}
</style>