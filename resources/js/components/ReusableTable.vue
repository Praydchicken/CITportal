<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  headers: {
    type: Array,
    required: true
  },
  data: {
    type: Array,
    required: true
  },
  actions: {
    type: Boolean,
    default: false
  },
  actionButtons: {
    type: Array,
    default: () => []
  },
  rowClickable: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['row-click']);

const getValue = (item, key) => {
  return key.split('.').reduce((obj, k) => obj?.[k], item) ?? 'N/A';
};

const handleRowClick = (item) => {
  if (props.rowClickable) {
    emit('row-click', item);
  }
};
</script>

<template>
  <div class="w-full">
    <table class="w-full border-separate border-spacing-y-6 text-center">
      <!-- Table Head -->
      <thead>
        <tr class="bg-[#1a3047] text-[#ffff] rounded-lg">
          <th v-for="header in headers" :key="header.key" 
              :class="[
                'p-4',
                header.key === headers[0].key ? 'rounded-l-lg' : '',
                header.key === headers[headers.length - 1].key && !actions ? 'rounded-r-lg' : ''
              ]">
            {{ header.label }}
          </th>
          <th v-if="actions" class="p-4 rounded-r-lg">Action</th>
        </tr>
      </thead>

      <!-- Table Body -->
      <tbody>
        <template v-for="(item, index) in data" :key="index">
          <tr
            @click="handleRowClick(item)"
            :class="[
              'bg-gray-200 shadow-md rounded-lg transition-colors duration-200',
              rowClickable ? 'cursor-pointer hover:bg-gray-300' : ''
            ]"
          >
            <td v-for="header in headers" :key="header.key"
                :class="[
                  'p-4',
                  header.key === headers[0].key ? 'rounded-l-lg' : '',
                  header.key === headers[headers.length - 1].key && !actions ? 'rounded-r-lg' : ''
                ]">
              {{ getValue(item, header.key) }}
            </td>
            <td v-if="actions" class="p-4 rounded-r-lg">
              <ul class="flex justify-center items-center gap-x-3">
                <li v-for="(button, btnIndex) in actionButtons" :key="btnIndex">
                  <button @click.stop="button.action(item)"
                          :class="button.class">
                    {{ button.label }}
                  </button>
                </li>
              </ul>
            </td>
          </tr>
          <!-- Transition for the details row -->
          <transition name="expand">
            <tr v-if="item.isExpanded" class="expand-row">
              <td :colspan="headers.length + (actions ? 1 : 0)" class="p-0">
                 <!-- The slot content now lives inside the transition -->
                <slot name="row-details" :item="item"></slot>
              </td>
            </tr>
          </transition>
        </template>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
/* Transition classes for expand/collapse */
.expand-enter-active,
.expand-leave-active {
  transition: max-height 0.3s ease-in-out, opacity 0.3s ease-in-out;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  max-height: 0;
  opacity: 0;
}

.expand-enter-to,
.expand-leave-from {
  /* Adjust max-height if your content might be taller */
  max-height: 500px; /* Start with a generous max-height */
  opacity: 1;
}

/* Ensure the table layout doesn't break during transition */
.expand-row td {
  padding: 0; /* Remove padding from the transition TD */
}

/* Add specific padding to the inner content div if needed */
.expand-row > td > div {
  /* Example: Restore padding if the slot content relies on it */
  /* padding: 1.5rem; */ /* Adjust as necessary based on StudentInformation.vue's slot content padding */
}
</style>