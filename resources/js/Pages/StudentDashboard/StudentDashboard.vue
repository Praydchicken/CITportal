<script setup>
import StudentDashboardLayout from '../../components/StudentDashboardLayout.vue';
import { usePage } from '@inertiajs/vue3';

defineOptions({
  layout: StudentDashboardLayout
});

const page = usePage();
const props = defineProps({
  welcomeMessage: String,
  announcements: Array,
  classSchedule: Array
});

console.log(props);

</script>

<template>
  <div class="p-6">
    <!-- Welcome Message -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[#1a3047] mb-2">{{ welcomeMessage }}</h1>
    </div>

    <!-- Announcements Section -->
    <div class="mb-8">
      <h2 class="text-2xl font-semibold text-[#1a3047] mb-4">📣 Announcements</h2>
      <div class="grid gap-4">
        <div v-if="announcements.length === 0" class="bg-gray-50 p-4 rounded-lg">
          <p class="text-gray-600">No announcements at this time.</p>
        </div>
        <div v-for="announcement in announcements" :key="announcement.id"
          class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
          <h3 class="text-xl font-semibold text-[#1a3047] mb-2">{{ announcement.title_announcement }}</h3>
          <p class="text-gray-600 mb-4">{{ announcement.description_announcement }}</p>
          <div class="text-sm text-gray-500">
            Posted on {{ new Date(announcement.created_at).toLocaleDateString() }}
          </div>
        </div>
      </div>
    </div>

    <!-- Class Schedule Section -->
    <div>
      <h2 class="text-2xl font-semibold text-[#1a3047] mb-4">🗓️ Class Schedule</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg overflow-hidden">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faculty</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr>
              <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                No class schedule available.
              </td>
            </tr>
            <tr v-for="(schedule, index) in classSchedule" :key="index" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">{{ schedule.subject }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ schedule.faculty }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ schedule.day }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                {{ schedule.start_time }} - {{ schedule.end_time }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">{{ schedule.room }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
.schedule-container {
  overflow-y: auto;
  /* Enable vertical scrolling */
  max-height: 100%;
}

.schedule-container::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.schedule-container::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.schedule-container::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.schedule-container::-webkit-scrollbar-thumb:hover {
  background: #666;
}

@media (max-width: 768px) {
  .schedule-container {
    width: 100%;
  }
}
</style>