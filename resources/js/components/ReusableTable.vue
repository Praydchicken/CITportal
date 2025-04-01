<script setup>
import { defineProps } from 'vue';

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
  }
});

const getValue = (item, key) => {
  return key.split('.').reduce((obj, k) => obj?.[k], item) ?? 'N/A';
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
        <tr v-for="(item, index) in data" :key="index" class="bg-gray-200 shadow-md rounded-lg">
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
                <button @click="button.action(item)" 
                        :class="button.class">
                  {{ button.label }}
                </button>
              </li>
            </ul>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template> 