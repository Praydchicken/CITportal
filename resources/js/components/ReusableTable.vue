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
  },
  rowKey: {
    type: String,
    default: 'id' // Default key property to use
  }
});

const emit = defineEmits(['row-click']);

const getNestedValue = (obj, path) => {
  if (!obj) return '';
  return path.split('.').reduce((value, key) => {
    return value && value[key] !== undefined ? value[key] : '';
  }, obj);
};

const handleRowClick = (item) => {
  if (props.rowClickable) {
    emit('row-click', item);
  }
};
</script>

<template>
  <div class="w-full">
    <table class="min-w-full divide-y divide-gray-200">
      <!-- Table Head -->
      <thead>
        <tr class="bg-[#1a3047] text-[#ffff]">
          <th v-for="header in headers" :key="header.key" scope="col"
            class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">
            {{ header.label }}
          </th>
          <th v-if="actions" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Action</th>
        </tr>
      </thead>

      <!-- Table Body -->
      <tbody class="bg-white divide-y divide-gray-200">
        <template v-for="(item, index) in data" :key="item[rowKey] || index">
          <!-- Main Row -->
          <tr @click="handleRowClick(item)" :class="[
            'transition-colors duration-200',
            rowClickable ? 'cursor-pointer hover:bg-gray-300' : '',
            index % 2 === 0 ? 'bg-gray-200' : 'bg-white'
          ]">
            <td v-for="header in headers" :key="`${item[rowKey] || index}-${header.key}`" :class="[
              'p-4 text-center',
              header.key === headers[0].key ? 'rounded-l-lg' : '',
              header.key === headers[headers.length - 1].key && !actions ? 'rounded-r-lg' : ''
            ]">
              {{ getNestedValue(item, header.key) }}
            </td>
            <td v-if="actions" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <ul class="flex justify-center items-center gap-x-3">
                <li v-for="(button, btnIndex) in actionButtons" :key="btnIndex">
                  <button @click.stop="button.action(item)" :class="button.class">
                    {{ button.label }}
                  </button>
                </li>
              </ul>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</template>