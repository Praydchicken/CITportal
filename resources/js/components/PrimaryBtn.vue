<script setup>
import { defineProps, ref, watchEffect } from 'vue';

const props = defineProps({
  disabled: {
    type: Boolean,
    default: false,
  },
  countdown: {
    type: Number,
    default: 0,
  },
});

const countdownText = ref(props.countdown);

// Update countdownText when the countdown prop changes
watchEffect(() => {
  countdownText.value = props.countdown;
});
</script>

<template>
  <button
    type="submit"
    :class="['loginBtn-custom', 'liquid', 'cursor-pointer', 'w-full', 'p-2', disabled || countdownText > 0 ? 'disabled:opacity-50 disabled:pointer-events-none' : '']"
    :disabled="disabled || countdownText > 0"
  >
    <span v-if="countdownText > 0">Wait {{ countdownText }}s</span>
    <span v-else><slot /></span>
  </button>
</template>
