<script setup>
import TeacherDashboardLayout from '../../components/teacherDashboardLayout/TeacherDashboardLayout.vue';

import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

defineOptions({
  layout: TeacherDashboardLayout
});

const page = usePage();
const subjects = computed(() => page.props.subjects || []);
const searchQuery = ref('');
const selectedSemester = ref('all');
const selectedYearLevel = ref('all');
const selectedSection = ref('all');

// Filter subjects based on search and filters
const filteredSubjects = computed(() => {
  return subjects.value.filter(subject => {
    const matchesSearch = 
      subject.subject_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      subject.course_code.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      subject.section.toLowerCase().includes(searchQuery.value.toLowerCase());
    
    const matchesSemester = selectedSemester.value === 'all' || 
      subject.semester === selectedSemester.value;

    const matchesYearLevel = selectedYearLevel.value === 'all' || 
      subject.year_level === selectedYearLevel.value;

    const matchesSection = selectedSection.value === 'all' || 
      subject.section === selectedSection.value;
    
    return matchesSearch && matchesSemester && matchesYearLevel && matchesSection;
  });
});

// Get unique values for filters
const semesters = computed(() => {
  const uniqueSemesters = new Set(subjects.value.map(subject => subject.semester));
  return Array.from(uniqueSemesters);
});

const yearLevels = computed(() => {
  const uniqueYearLevels = new Set(subjects.value.map(subject => subject.year_level));
  return Array.from(uniqueYearLevels).sort();
});

const sections = computed(() => {
  const uniqueSections = new Set(subjects.value.map(subject => subject.section));
  return Array.from(uniqueSections).sort();
});

const viewSchedule = () => {
  router.visit(route('teacher.class.schedule'));
};
</script>

<template>
  <div class="p-6">
    <!-- Header Section -->
    <div class="mb-8 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Assigned Subjects</h1>
        <p class="text-gray-600">Manage and view your assigned subjects for the current academic year.</p>
      </div>
      <button 
        @click="viewSchedule"
        class="bg-[#1a3047] text-white px-4 py-2 rounded-lg hover:bg-[#2a4057] transition-colors flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        View Schedule
      </button>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
      <div class="flex flex-col gap-4">
        <!-- Search Bar -->
        <div class="relative flex-1">
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search subjects, course codes, or sections..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
          <svg
            class="absolute left-3 top-2.5 h-5 w-5 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>
        </div>

        <!-- Filter Controls -->
        <div class="flex flex-wrap gap-4">
          <!-- Semester Filter -->
          <div class="flex items-center gap-2">
            <label class="text-gray-700 font-medium">Semester:</label>
            <select
              v-model="selectedSemester"
              class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="all">All Semesters</option>
              <option v-for="semester in semesters" :key="semester" :value="semester">
                {{ semester }}
              </option>
            </select>
          </div>

          <!-- Year Level Filter -->
          <div class="flex items-center gap-2">
            <label class="text-gray-700 font-medium">Year Level:</label>
            <select
              v-model="selectedYearLevel"
              class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="all">All Year Levels</option>
              <option v-for="yearLevel in yearLevels" :key="yearLevel" :value="yearLevel">
                {{ yearLevel }}
              </option>
            </select>
          </div>

          <!-- Section Filter -->
          <div class="flex items-center gap-2">
            <label class="text-gray-700 font-medium">Section:</label>
            <select
              v-model="selectedSection"
              class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="all">All Sections</option>
              <option v-for="section in sections" :key="section" :value="section">
                {{ section }}
              </option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Subjects Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="subject in filteredSubjects"
        :key="subject.id"
        class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow p-6"
      >
        <!-- Subject Header -->
        <div class="flex items-start justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-800">{{ subject.subject_name }}</h3>
            <p class="text-sm text-gray-600">{{ subject.course_code }}</p>
          </div>
          <span 
            class="px-3 py-1 text-sm rounded-full bg-blue-100 text-blue-800"
          >
            {{ subject.semester }}
          </span>
        </div>

        <!-- Subject Details -->
        <div class="space-y-3">
          <div class="flex items-center text-gray-600">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span>Section: {{ subject.section }} - {{ subject.year_level }} Year</span>
          </div>
          <div class="flex items-center text-gray-600">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Schedule: 
              <button 
                v-if="!subject.schedule"
                @click="viewSchedule"
                class="text-blue-600 hover:text-blue-800 hover:underline focus:outline-none"
              >
                View in schedule
              </button>
              <span v-else>{{ subject.schedule }}</span>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredSubjects.length === 0" class="text-center py-12">
      <svg
        class="mx-auto h-12 w-12 text-gray-400"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
        />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No subjects found</h3>
      <p class="mt-1 text-sm text-gray-500">
        Try adjusting your search or filter to find what you're looking for.
      </p>
    </div>
  </div>
</template>

<style scoped>
/* Add any custom styles here */
</style>
