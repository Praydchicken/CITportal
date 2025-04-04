<script setup>
import DashboardLayout from '../../components/AdminDashboardLayout.vue';

defineOptions({
  layout: DashboardLayout
});

const props = defineProps({
  stats: {
    type: Object,
    required: true,
    default: () => ({
      enrolled: 0,
      dropped: 0,
      graduated: 0
    })
  }
});
</script>

<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold text-[#1a3047] mb-6">Dashboard Overview</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Enrolled Students Card -->
      <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-[#1a3047] hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm font-medium">Enrolled Students</p>
            <p class="text-4xl font-bold text-[#1a3047] mt-2">{{ props.stats.enrolled }}</p>
          </div>
          <div class="bg-[#1a3047] bg-opacity-10 p-3 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#1a3047]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Dropped Students Card -->
      <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm font-medium">Dropped Students</p>
            <p class="text-4xl font-bold text-red-500 mt-2">{{ props.stats.dropped }}</p>
          </div>
          <div class="bg-red-500 bg-opacity-10 p-3 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Graduated Students Card -->
      <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm font-medium">Graduated Students</p>
            <p class="text-4xl font-bold text-green-500 mt-2">{{ props.stats.graduated }}</p>
          </div>
          <div class="bg-green-500 bg-opacity-10 p-3 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
            </svg>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
