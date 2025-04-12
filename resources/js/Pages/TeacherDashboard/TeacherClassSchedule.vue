<script setup>
import TeacherDashboardLayout from '../../components/teacherDashboardLayout/TeacherDashboardLayout.vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineOptions({
  layout: TeacherDashboardLayout
});

const page = usePage();
const classSchedule = computed(() => page.props.classSchedule || []);

// Time slots from 7:00 to 17:00 (5 PM)
const timeSlots = Array.from({ length: 11 }, (_, i) => {
  const hour = i + 7;
  return `${hour.toString().padStart(2, '0')}:00`;
});

// Group schedule by day
const groupedSchedule = computed(() => {
  const grouped = {};
  classSchedule.value.forEach(item => {
    if (!grouped[item.day]) {
      grouped[item.day] = [];
    }
    grouped[item.day].push(item);
  });
  
  // Sort classes by start time within each day
  Object.keys(grouped).forEach(day => {
    grouped[day].sort((a, b) => {
      const timeA = parseInt(a.start_time.split(':')[0]);
      const timeB = parseInt(b.start_time.split(':')[0]);
      return timeA - timeB;
    });

    // Add position information to each item
    grouped[day] = grouped[day].map((item, index) => ({
      ...item,
      heightClass: calculateHeightClass(item.start_time, item.end_time),
      topClass: grouped[day].length === 1 ? 'top-0' : calculateTopPosition(item.start_time)
    }));
  });
  
  return grouped;
});

// Calculate the height class based on duration
function calculateHeightClass(startTime, endTime) {
  const start = parseInt(startTime.split(':')[0]);
  const end = parseInt(endTime.split(':')[0]);
  const duration = end - start;
  return `h-[${duration * 6}rem]`; // Increased height per hour
}

// Calculate top position based on start time
function calculateTopPosition(startTime) {
  const [hours, minutes] = startTime.split(':').map(Number);
  const baseHour = 7; // Schedule starts at 7:00
  const hourOffset = (hours - baseHour) * 6; // Increased offset per hour
  const minuteOffset = (minutes / 60) * 6; // Add minute-based offset
  return `top-[${hourOffset + minuteOffset}rem]`;
}

// Days of the week in order
const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Generate random pastel color for subjects
function getSubjectColor(subject) {
  const colors = [
    'bg-pink-100', 'bg-blue-100', 'bg-green-100', 'bg-yellow-100', 
    'bg-purple-100', 'bg-indigo-100', 'bg-orange-100'
  ];
  const index = subject.length % colors.length;
  return colors[index];
}

// Add debug logging
console.log('Page Props:', page.props);
console.log('Class Schedule:', classSchedule.value);

// Computed property for development mode
const isDevelopment = computed(() => {
  return process.env.NODE_ENV === 'development';
});
</script>

<template>
  <div class="p-6 h-[calc(90vh-theme(spacing.32))]">
    <!-- Debug Info (only visible during development) -->
    <!-- <div v-if="isDevelopment" class="mb-4 p-4 bg-yellow-100 rounded">
      <p class="font-bold">Debug Info:</p>
      <p>Has Schedule Data: {{ classSchedule.length > 0 ? 'Yes' : 'No' }}</p>
      <p>Number of Items: {{ classSchedule.length }}</p>
    </div> -->

    <div class="schedule-container relative bg-white rounded-lg shadow-lg h-full">
      <!-- Schedule Grid -->
      <div class="grid grid-cols-6">
        <!-- Fixed Headers -->
        <div class="sticky top-0 z-10 col-span-6 grid grid-cols-6 w-full bg-[#1a3047]">
          <div v-for="day in daysOfWeek" :key="day" 
               class="h-14 px-4 border-r last:border-r-0 border-gray-600">
            <h2 class="text-lg font-semibold text-white h-full flex items-center justify-center whitespace-nowrap">
              {{ day }}
            </h2>
          </div>
        </div>

        <!-- Scrollable Schedule Content -->
        <div class="col-span-6 grid grid-cols-6">
          <!-- Schedule Columns -->
          <template v-for="day in daysOfWeek" :key="day">
            <div class="relative h-[66rem] border-r last:border-r-0 border-gray-200">
              <!-- Time slot markers -->
              <div v-for="time in timeSlots" :key="time" 
                   class="h-24 border-b border-gray-200">
              </div>

              <!-- Class blocks -->
              <div v-for="classItem in (groupedSchedule[day] || [])" 
                   :key="`${day}-${classItem.start_time}`"
                   :class="[
                     'absolute w-[95%] mx-[2.5%] rounded-lg border shadow-sm transition-shadow hover:shadow-md',
                     getSubjectColor(classItem.subject),
                     classItem.heightClass,
                     classItem.topClass
                   ]">
                <div class="p-3 h-full flex flex-col">
                  <h3 class="font-semibold text-gray-800">{{ classItem.subject }}</h3>
                  <p class="text-sm text-gray-600">{{ classItem.course_code }}</p>
                  <div class="text-sm text-gray-600 mt-1">
                    {{ classItem.start_time }} - {{ classItem.end_time }}
                  </div>
                  <div class="text-sm text-gray-600 mt-auto">
                    {{ classItem.section }} - {{ classItem.year_level }}
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.schedule-container {
  overflow: auto;
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

