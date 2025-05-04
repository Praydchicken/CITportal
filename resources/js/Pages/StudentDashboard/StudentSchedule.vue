<script setup>
import DashboardLayout from '../../Components/StudentDashboardLayout.vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineOptions({
  layout: DashboardLayout
});

const page = usePage();
const classSchedule = computed(() => page.props.classSchedule || []);

// Convert AM/PM time to 24-hour format
function convertTo24Hour(time) {
  const [timePart, period] = time.split(' ');
  let [hours, minutes] = timePart.split(':').map(Number);

  if (period === 'PM' && hours !== 12) {
    hours += 12;
  } else if (period === 'AM' && hours === 12) {
    hours = 0;
  }

  return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
}

// Convert 24-hour format back to AM/PM for display
function formatTimeDisplay(time24) {
  let [hours, minutes] = time24.split(':').map(Number);
  const period = hours >= 12 ? 'PM' : 'AM';
  hours = hours % 12;
  hours = hours ? hours : 12; // Convert 0 to 12 for 12 AM
  return `${hours}:${minutes.toString().padStart(2, '0')} ${period}`;
}

// Process schedule data to ensure consistent time format
const processedSchedule = computed(() => {
  return classSchedule.value.map(item => ({
    ...item,
    start_time: convertTo24Hour(item.start_time),
    end_time: convertTo24Hour(item.end_time)
  }));
});

// Determine the earliest and latest times from the schedule
const scheduleTimes = computed(() => {
  if (!processedSchedule.value.length) {
    return { startTime: '07:00', endTime: '20:00' }; // Default times
  }

  let earliestTime = '23:59';
  let latestTime = '00:00';

  processedSchedule.value.forEach(item => {
    if (item.start_time < earliestTime) {
      earliestTime = item.start_time;
    }
    if (item.end_time > latestTime) {
      latestTime = item.end_time;
    }
  });

  const startHour = parseInt(earliestTime.split(':')[0]);
  const endHour = parseInt(latestTime.split(':')[0]) + (parseInt(latestTime.split(':')[1]) > 0 ? 1 : 0);

  return {
    startTime: `${startHour.toString().padStart(2, '0')}:00`, // Removed Math.max
    endTime: `${endHour.toString().padStart(2, '0')}:00`,   // Removed Math.min (and adjusted logic)
  };
});

// Time slots based on the earliest and latest times
const timeSlots = computed(() => {
  const startTimeParts = scheduleTimes.value.startTime.split(':');
  const endTimeParts = scheduleTimes.value.endTime.split(':');
  let startHour = parseInt(startTimeParts[0]);
  const endHour = parseInt(endTimeParts[0]);
  const slots = [];

  for (let i = startHour; i <= endHour; i++) {
    const hour = i % 12 === 0 ? 12 : i % 12;
    const period = i < 12 || i === 24 ? 'AM' : 'PM';
    slots.push(`${hour.toString().padStart(2, '0')}:00 ${period}`);
  }
  return slots;
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
  processedSchedule.value.forEach(item => {
    if (!daysOfWeek.includes(item.day)) return;

    const daySchedule = grouped[item.day];
    const scheduleStartHour = parseInt(scheduleTimes.value.startTime.split(':')[0]);

    // Convert time to minutes since the adjusted start time
    const startMinutes = (parseInt(item.start_time.split(':')[0]) - scheduleStartHour) * 60 + parseInt(item.start_time.split(':')[1]);
    const endMinutes = (parseInt(item.end_time.split(':')[0]) - scheduleStartHour) * 60 + parseInt(item.end_time.split(':')[1]);
    const duration = endMinutes - startMinutes;

    // Calculate position and height (adjusting the base for top calculation)
    const minutesPerHour = 60;
    const remPerHour = 4;
    const topOffsetRem = (startMinutes / minutesPerHour) * remPerHour;
    const heightRem = (duration / minutesPerHour) * remPerHour;

    const position = {
      ...item,
      top: `${topOffsetRem}rem`,
      height: `${heightRem}rem`,
      zIndex: 10
    };

    daySchedule.push(position);
  });

  // Sort each day's schedule by start time and resolve overlaps
  daysOfWeek.forEach(day => {
    grouped[day].sort((a, b) => {
      return convertTimeToMinutesAdjusted(a.start_time, scheduleTimes.value.startTime) - convertTimeToMinutesAdjusted(b.start_time, scheduleTimes.value.startTime);
    });

    for (let i = 1; i < grouped[day].length; i++) {
      const prevEndMinutes = convertTimeToMinutesAdjusted(grouped[day][i - 1].end_time, scheduleTimes.value.startTime);
      const currStartMinutes = convertTimeToMinutesAdjusted(grouped[day][i].start_time, scheduleTimes.value.startTime);

      if (currStartMinutes < prevEndMinutes) {
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

// Convert HH:MM to minutes since the adjusted start time
function convertTimeToMinutesAdjusted(time, startTime) {
  const [hours, minutes] = time.split(':').map(Number);
  const [startHours] = startTime.split(':').map(Number);
  return (hours - startHours) * 60 + minutes;
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
  <Head title="Schedule"/>
  <div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">My Class Schedule</h1>

    <div v-if="classSchedule.length > 0" class="schedule-container bg-white rounded-lg shadow-lg">
      <div class="grid grid-cols-6 min-w-[1200px]">
        <div v-for="day in daysOfWeek" :key="day"
          class="sticky top-0 z-30 bg-[#1a3047] text-white p-3 border-r border-gray-600 text-center font-semibold">
          {{ day }}
        </div>

        <template v-for="day in daysOfWeek" :key="`col-${day}`">
          <div class="relative border-r border-gray-200" :style="{ minHeight: `${(parseInt(scheduleTimes.endTime.split(':')[0]) - parseInt(scheduleTimes.startTime.split(':')[0]) + 1) * 4}rem` }">
            <div v-for="time in timeSlots" :key="`${day}-${time}`"
              class="h-16 border-b border-gray-200 text-xs text-gray-500 pl-1 pt-1"
              :style="{ top: `${(parseInt(time.split(':')[0]) - (parseInt(scheduleTimes.startTime.split(':')[0]))) * 4}rem` }">
              {{ time }}
            </div>

            <div v-for="(classItem, index) in groupedSchedule[day]" :key="`${day}-${classItem.start_time}-${index}`"
              :class="[
                'absolute rounded-lg border shadow-sm p-2 overflow-y-auto',
                getSubjectColor(classItem.subject),
                'hover:shadow-md transition-all'
              ]" :style="{
                top: classItem.top,
                height: classItem.height,
                left: classItem.left || '2%',
                width: classItem.width || '96%',
                'z-index': classItem.zIndex || 10
              }">
              <h3 class="font-semibold text-gray-800 text-sm">
                {{ classItem.subject }}
              </h3>
              <p class="text-xs text-gray-600">{{ classItem.course_code }}</p>
              <p class="text-xs text-gray-600 mt-1">
                {{ formatTimeDisplay(classItem.start_time) }} - {{ formatTimeDisplay(classItem.end_time) }}
              </p>
              <p class="text-xs text-gray-600 mt-1">
                <span class="font-medium">Teacher:</span> {{ classItem.teacher_name }}
              </p>
              <p v-if="classItem.section" class="text-xs text-gray-600">
                <span class="font-medium">Section:</span> {{ classItem.section }}
              </p>
              <p class="text-xs text-gray-600 mt-1">
                <span class="font-medium">Semester:</span> {{ classItem.semester }}
              </p>
            </div>
          </div>
        </template>
      </div>
    </div>

    <div v-else class="bg-white rounded-lg shadow-lg p-8 text-center">
      <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
        viewBox="0 0 24 24">
        <path fill="currentColor"
          d="M15 16.69V13h1.5v2.82l2.44 1.41l-.75 1.3zm-4.42 3.73L9 22l-1.5-1.5L6 22l-1.5-1.5L3 22V2l1.5 1.5L6 2l1.5 1.5L9 2l1.5 1.5L12 2l1.5 1.5L15 2l1.5 1.5L18 2l1.5 1.5L21 2v9.1a7.001 7.001 0 0 1-9.95 9.85a5 5 0 0 1-.47-.53m-.86-1.33c-.32-.66-.54-1.36-.65-2.09H6v-2h3.07c.1-.71.31-1.38.61-2H6v-2h5.1c1.27-1.24 3-2 4.9-2H6V7h12v2h-2c1.05 0 2.07.24 3 .68V4.91H5v14.18zM20.85 16c0-2.68-2.18-4.85-4.85-4.85c-1.29 0-2.5.51-3.43 1.42c-.91.93-1.42 2.14-1.42 3.43c0 2.68 2.17 4.85 4.85 4.85c.64 0 1.27-.12 1.86-.35c.58-.26 1.14-.62 1.57-1.07c.45-.43.81-.99 1.07-1.57c.23-.59.35-1.22.35-1.86" />
      </svg>
      <p class="text-gray-600">No classes scheduled yet.</p>
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