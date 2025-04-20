<script setup>
import DashboardLayout from '../../Components/StudentDashboardLayout.vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
  layout: DashboardLayout
});

const page = usePage();
const classSchedule = computed(() => page.props.classSchedule || []);
const earliestTime = ref(null);
const latestTime = ref(null);

// Determine the earliest and latest times
const calculateTimeRange = () => {
  if (classSchedule.value.length > 0) {
    const startTimes = classSchedule.value.map(item => item.start_time);
    const endTimes = classSchedule.value.map(item => item.end_time);

    earliestTime.value = startTimes.reduce((min, current) => {
      return current < min ? current : min;
    });
    latestTime.value = endTimes.reduce((max, current) => {
      return current > max ? current : max;
    });
  } else {
    earliestTime.value = '07:00';
    latestTime.value = '18:00';
  }
};

calculateTimeRange();

const baseStartTimeMinutes = computed(() => {
  if (!earliestTime.value) return 7 * 60;
  const [hours, minutes] = earliestTime.value.split(':').map(Number);
  return hours * 60 + minutes;
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
      const timeA = parseTime(a.start_time);
      const timeB = parseTime(b.start_time);
      return timeA - timeB;
    });

    // Add position information to each item
    grouped[day] = grouped[day].map(item => ({
      ...item,
      heightClass: calculateHeightClass(item.start_time, item.end_time),
      topClass: calculateTopPosition(item.start_time, earliestTime.value)
    }));
  });

  return grouped;
});

function parseTime(timeString) {
  const [hours, minutes] = timeString.split(':').map(Number);
  return hours * 60 + minutes;
}

// Calculate the height class based on duration in minutes
function calculateHeightClass(startTime, endTime) {
  const start = parseTime(startTime);
  const end = parseTime(endTime);
  const durationInMinutes = end - start;
  // Adjust the multiplier (e.g., 1rem per 30 minutes, or adjust based on your desired scale)
  return `h-[${durationInMinutes / 30}rem]`;
}

// Calculate top position based on the start time relative to the earliest time
function calculateTopPosition(startTime, earliestTime) {
  const startMinutes = parseTime(startTime);
  const earliestMinutes = parseTime(earliestTime);
  const offsetMinutes = startMinutes - earliestMinutes;
  // Adjust the multiplier to match the height scale (e.g., 1rem per 30 minutes)
  return `top-[${offsetMinutes / 30}rem]`;
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
console.log('Earliest Time:', earliestTime.value);
console.log('Latest Time:', latestTime.value);
console.log('Base Start Time (minutes):', baseStartTimeMinutes.value);
console.log('Grouped Schedule:', groupedSchedule.value);
</script>

<template>
  <div class="p-6 h-[calc(90vh-theme(spacing.32))]">
    <div class="schedule-container relative bg-white rounded-lg shadow-lg h-full">
      <div class="grid grid-cols-6">
        <div class="sticky top-0 z-10 col-span-6 grid grid-cols-6 w-full bg-[#1a3047]">
          <div v-for="day in daysOfWeek" :key="day"
               class="h-14 px-4 border-r last:border-r-0 border-gray-600">
            <h2 class="text-lg font-semibold text-white h-full flex items-center justify-center whitespace-nowrap">
              {{ day }}
            </h2>
          </div>
        </div>

        <div class="col-span-6 grid grid-cols-6">
          <template v-for="day in daysOfWeek" :key="day">
            <div class="relative border-r last:border-r-0 border-gray-200"
                 style="min-height: 60rem;"> <div v-for="classItem in (groupedSchedule[day] || [])"
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
                  <div class="mt-2 text-sm text-gray-600">
                    Teacher: {{ classItem.teacher_name }}
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
  overflow-y: auto; /* Enable vertical scrolling */
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