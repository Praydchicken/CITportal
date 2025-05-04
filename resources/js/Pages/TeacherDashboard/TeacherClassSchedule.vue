<script setup>
import TeacherDashboardLayout from '../../components/teacherDashboardLayout/TeacherDashboardLayout.vue';
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineOptions({
    layout: TeacherDashboardLayout
});

const page = usePage();
const classSchedule = computed(() => page.props.classSchedule || []);

// State for filtering and search
const searchQuery = ref('');
const selectedDay = ref('All Days');
const selectedSection = ref('All Sections');
const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
const daysOfWeekWithAll = computed(() => ['All Days', ...daysOfWeek]);

// Get unique sections for the dropdown
const uniqueSections = computed(() => {
    const sections = new Set(['All Sections']);
    processedSchedule.value.forEach(item => {
        if (item.section) {
            sections.add(item.section);
        }
    });
    return Array.from(sections);
});

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

const processedSchedule = computed(() => {
    return classSchedule.value.map(item => ({
        ...item,
        start_time: convertTo24Hour(item.start_time),
        end_time: convertTo24Hour(item.end_time)
    }));
});

// Filtered schedule based on selected day, section, and search query
const filteredSchedule = computed(() => {
    return processedSchedule.value.filter(item => {
        const dayMatch = selectedDay.value === 'All Days' || item.day === selectedDay.value;
        const sectionMatch = selectedSection.value === 'All Sections' || item.section === selectedSection.value;
        const searchMatch =
            searchQuery.value.toLowerCase() === '' ||
            item.subject.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            item.course_code.toLowerCase().includes(searchQuery.value.toLowerCase());
        return dayMatch && sectionMatch && searchMatch;
    });
});

const scheduleTimes = computed(() => {
    if (!filteredSchedule.value.length) {
        return { startTime: '07:00', endTime: '12:00' }; // Default times
    }

    let earliestTime = '23:59';
    let latestTime = '00:00';

    filteredSchedule.value.forEach(item => {
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
        startTime: `${startHour.toString().padStart(2, '0')}:00`,
        endTime: `${endHour.toString().padStart(2, '0')}:00`,
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

// Group and position schedule items (using filteredSchedule)
const groupedSchedule = computed(() => {
    const grouped = {};

    // Initialize empty arrays for each day
    daysOfWeek.forEach(day => {
        grouped[day] = [];
    });

    // Group by day and calculate positioning
    filteredSchedule.value.forEach(item => {
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

    // Sort each day's schedule by start time and detect overlaps
    daysOfWeek.forEach(day => {
        grouped[day].sort((a, b) => {
            return convertTimeToMinutes(a.start_time) - convertTimeToMinutes(b.start_time);
        });

        for (let i = 1; i < grouped[day].length; i++) {
            const prevEnd = convertTimeToMinutes(grouped[day][i - 1].end_time);
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

// Convert HH:MM to minutes
function convertTimeToMinutes(time) {
    const [hours, minutes] = time.split(':').map(Number);
    return hours * 60 + minutes;
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
  <Head title="Schedule" />
  <h1 class="text-2xl font-bold text-gray-800 mb-6">My Class Schedule</h1>

  <div class="mb-4 flex items-center space-x-4">
      <div>
          <label for="search" class="block text-sm font-medium text-slate-700">Search:</label>
          <input type="text" id="search" v-model="searchQuery"class="bg-[#ffff] p-2 pr-[3rem] text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 border-gray-500 w-[300px]" placeholder="Search subject, code, section">
      </div>
      <div>
          <label for="day-filter" class="block text-sm font-medium text-slate-700">Filter by Day:</label>
          <select id="day-filter" v-model="selectedDay" class="text-[0.875rem] leading-[1.25rem] p-2 mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:outline-none">
              <option v-for="day in daysOfWeekWithAll" :key="day" :value="day">{{ day }}</option>
          </select>
          
      </div>
      <div>
            <label for="section-filter" class="block text-sm font-medium text-slate-700">Filter by Section:</label>
            <select id="section-filter" v-model="selectedSection" class="text-[0.875rem] leading-[1.25rem] p-2 mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:outline-none">
                <option v-for="section in uniqueSections" :key="section" :value="section">{{ section }}</option>
            </select>
        </div>
  </div>

  <div v-if="classSchedule.length > 0" class="schedule-container bg-white rounded-lg shadow-lg">
      <div class="grid grid-cols-6 min-w-[1200px]">
          <div v-for="day in daysOfWeek" :key="day"
              class="sticky top-0 z-30 bg-[#1a3047] text-white p-3 border-r border-gray-600 text-center font-semibold">
              {{ day }}
          </div>

          <template v-for="day in daysOfWeek" :key="`col-${day}`">
              <div class="relative border-r border-gray-200"
                  :style="{ minHeight: `${(parseInt(scheduleTimes.endTime.split(':')[0]) - parseInt(scheduleTimes.startTime.split(':')[0]) + 1) * 4}rem` }">
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
  <div v-else class="text-gray-500 italic">No classes scheduled.</div>
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