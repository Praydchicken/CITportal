<script setup>
import TeacherDashboardLayout from '../../components/teacherDashboardLayout/TeacherDashboardLayout.vue';
import { Head } from '@inertiajs/vue3';

defineOptions({
  layout: TeacherDashboardLayout
});

const props = defineProps({
  auth: Object,
  welcomeMessage: String,
  classSchedule: Array,
  statistics: Object
});
</script>

<template>

  <Head :title="'Teacher Dashboard'" />

  <div class="p-6">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-[#1a3047] mb-6">Dashboard Overview</h2>
      <h1 class="text-2xl font-bold">{{ welcomeMessage }}</h1>
    </div>

    <!-- Dashboard Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Assigned Students Card -->
      <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-[#1a3047] hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm font-medium">Assigned Students</p>
            <p class="text-4xl font-bold text-[#1a3047] mt-2">{{ statistics.totalAssignedStudents }}</p>
          </div>
          <div class="bg-[#1a3047] bg-opacity-10 p-3 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#1a3047]" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Assigned Subjects Card -->
      <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm font-medium">Assigned Subjects</p>
            <p class="text-4xl font-bold text-red-500 mt-2">{{ statistics.totalAssignedSubjects }}</p>
          </div>
          <div class="bg-red-500 bg-opacity-10 p-3 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Approved Grades Card -->
      <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm font-medium">Approved Grades</p>
            <p class="text-4xl font-bold text-green-500 mt-2">{{ statistics.totalApprovedGrades }}</p>
          </div>
          <div class="bg-green-500 bg-opacity-10 p-3 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Pending Grades Card -->
      <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm font-medium">Pending Grades</p>
            <p class="text-4xl font-bold text-text-500 mt-2">{{ statistics.totalPendingGrades }}</p>
          </div>
          <div class="bg-yellow-500 bg-opacity-10 p-3 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-500" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Rejected Grades Card -->
      <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-pink-500 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm font-medium">Rejected Grades</p>
            <p class="text-4xl font-bold text-pink-500 mt-2">{{ statistics.totalRejectedGrades }}</p>
          </div>
          <div class="bg-pink-500 bg-opacity-10 p-3 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-pink-500" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Class Schedule Section -->
    <div class="mt-10">
      <h2 class="mb-4 text-md font-medium">Class Schedule</h2>
      <div class="overflow-x-auto mt-6">
        <table class="min-w-full divide-y divide-gray-200 ">
          <thead>
            <tr>
              <th scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">Subject
              </th>
              <th scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">Course
                Code</th>
              <th scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">Section
              </th>
              <th scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">Year
                Level</th>
              <th scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">Day
              </th>
              <th scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">Time
              </th>
              <th scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">
                Semester</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(classItem, index) in classSchedule" :key="index"
              :class="index % 2 === 0 ? 'bg-neutral-300' : 'bg-gray-200'">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ classItem.subject }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ classItem.course_code }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ classItem.section }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ classItem.year_level }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ classItem.day }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ classItem.start_time }} - {{
                classItem.end_time }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ classItem.semester }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
