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

// Days of the week in order
const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Group and position schedule items
const groupedSchedule = computed(() => {
  const grouped = {};
  
  // Initialize empty arrays for each day
  daysOfWeek.forEach(day => {
    grouped[day] = [];
  });

  // Group by day and calculate positioning
  classSchedule.value.forEach(item => {
    const daySchedule = grouped[item.day];
    
    // Convert time to minutes since 7:00
    const startMinutes = convertTimeToMinutes(item.start_time);
    const endMinutes = convertTimeToMinutes(item.end_time);
    const duration = endMinutes - startMinutes;
    
    // Calculate position and height
    const position = {
      ...item,
      top: `${(startMinutes / 60) * 4}rem`, // 4rem per hour
      height: `${(duration / 60) * 4}rem`,  // 4rem per hour
      zIndex: 10
    };
    
    daySchedule.push(position);
  });

  // Sort each day's schedule by start time
  daysOfWeek.forEach(day => {
    grouped[day].sort((a, b) => {
      return convertTimeToMinutes(a.start_time) - convertTimeToMinutes(b.start_time);
    });
    
    // Detect and resolve overlaps
    for (let i = 1; i < grouped[day].length; i++) {
      const prevEnd = convertTimeToMinutes(grouped[day][i-1].end_time);
      const currStart = convertTimeToMinutes(grouped[day][i].start_time);
      
      if (currStart < prevEnd) {
        // If overlapping, offset the current item
        grouped[day][i].zIndex = 20;
        grouped[day][i].left = '50%';
        grouped[day][i].width = '48%';
      } else {
        grouped[day][i].left = '2%';
        grouped[day][i].width = '96%';
      }
    }
  });

  return grouped;
});

// Convert HH:MM to minutes since 7:00
function convertTimeToMinutes(time) {
  const [hours, minutes] = time.split(':').map(Number);
  return (hours - 7) * 60 + minutes;
}

// Generate pastel color for subjects
function getSubjectColor(subject) {
  const colors = [
    'bg-pink-100 border-pink-300', 
    'bg-blue-100 border-blue-300',
    'bg-green-100 border-green-300', 
    'bg-yellow-100 border-yellow-300',
    'bg-purple-100 border-purple-300',
    'bg-indigo-100 border-indigo-300',
    'bg-orange-100 border-orange-300'
  ];
  const index = subject.length % colors.length;
  return colors[index];
}
</script>

<template>
  <div class="p-6">
    <div class="schedule-container bg-white rounded-lg shadow-lg">
      <!-- Schedule Grid -->
      <div class="grid grid-cols-6 min-w-[1200px]">
        <!-- Day Headers -->
        <div 
          v-for="day in daysOfWeek" 
          :key="day"
          class="sticky top-0 z-30 bg-[#1a3047] text-white p-3 border-r border-gray-600 text-center font-semibold"
        >
          {{ day }}
        </div>

        <!-- Time Slots and Classes -->
        <template v-for="day in daysOfWeek" :key="`col-${day}`">
          <div class="relative border-r border-gray-200 min-h-[40rem]">
            <!-- Time markers -->
            <div 
              v-for="time in timeSlots" 
              :key="`${day}-${time}`"
              class="h-16 border-b border-gray-200 text-xs text-gray-500 pl-1 pt-1"
            >
              {{ time }}
            </div>

            <!-- Class blocks -->
            <div
              v-for="(classItem, index) in groupedSchedule[day]"
              :key="`${day}-${classItem.start_time}-${index}`"
              :class="[
                'absolute rounded-lg border shadow-sm p-2 overflow-y-scroll',
                getSubjectColor(classItem.subject),
                'hover:shadow-md transition-all'
              ]"
              :style="{
                top: classItem.top,
                height: classItem.height,
                left: classItem.left || '2%',
                width: classItem.width || '96%',
                'z-index': classItem.zIndex || 10
              }"
            >
              <h3 class="font-semibold text-gray-800 text-sm truncate">
                {{ classItem.subject }}
              </h3>
              <p class="text-xs text-gray-600">{{ classItem.course_code }}</p>
              <p class="text-xs text-gray-600 mt-1">
                {{ classItem.start_time }} - {{ classItem.end_time }}
              </p>
              <p class="text-xs text-gray-600">
                {{ classItem.section }} - {{ classItem.year_level }}
              </p>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<style scoped>
.schedule-container {
  overflow-x: auto;
  overflow-y: hidden;
}

/* Custom scrollbar */
.schedule-container::-webkit-scrollbar {
  height: 8px;
  width: 8px;
}

.schedule-container::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.schedule-container::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.schedule-container::-webkit-scrollbar-thumb:hover {
  background: #666;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
  .schedule-container {
    width: 100vw;
    margin-left: -1rem;
    margin-right: -1rem;
  }
}
</style>