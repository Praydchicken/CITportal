<script setup>
import TeacherDashboardLayout from '../../components/teacherDashboardLayout/TeacherDashboardLayout.vue';
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineOptions({
  layout: TeacherDashboardLayout
});

const page = usePage();
const searchQuery = ref('');
const selectedSubject = ref('all');
const selectedYearLevel = ref('all');
const selectedSection = ref('all');
const selectedSemester = ref('all');

// Debug logging
// onMounted(() => {
//   console.log('Page props:', page.props);
//   console.log('Students data:', page.props.students);
// });

// Safely access students with null check
const students = computed(() => {
  const studentsData = page.props.students;
  console.log('Computing students:', studentsData);
  return studentsData || [];
});

// Safely access auth user
const auth = computed(() => {
  return page.props.auth?.user || null;
});

// Get unique values for filters with null checks
const subjects = computed(() => {
  if (!students.value) return [];
  const uniqueSubjects = new Set(
    students.value
      .filter(student => student && student.subject)
      .map(student => student.subject)
  );
  return Array.from(uniqueSubjects).sort();
});

const yearLevels = computed(() => {
  if (!students.value) return [];
  const uniqueYearLevels = new Set(
    students.value
      .filter(student => student && student.year_level)
      .map(student => student.year_level)
  );
  return Array.from(uniqueYearLevels).sort();
});

const sections = computed(() => {
  if (!students.value) return [];
  const uniqueSections = new Set(
    students.value
      .filter(student => student && student.section)
      .map(student => student.section)
  );
  return Array.from(uniqueSections).sort();
});

// Add semester to the list of computed properties
const semesters = computed(() => {
  if (!students.value) return [];
  const uniqueSemesters = new Set(
    students.value
      .filter(student => student && student.semester)
      .map(student => student.semester)
  );
  return Array.from(uniqueSemesters).sort();
});

// Filter students based on search and filters with null checks
const filteredStudents = computed(() => {
  if (!students.value) return [];
  
  return students.value.filter(student => {
    if (!student) return false;

    const matchesSearch = searchQuery.value === '' || (
      (student.name?.toLowerCase() || '').includes(searchQuery.value.toLowerCase()) ||
      (student.student_number?.toLowerCase() || '').includes(searchQuery.value.toLowerCase())
    );
    
    const matchesSubject = selectedSubject.value === 'all' || 
      student.subject === selectedSubject.value;

    const matchesYearLevel = selectedYearLevel.value === 'all' || 
      student.year_level === selectedYearLevel.value;

    const matchesSection = selectedSection.value === 'all' || 
      student.section === selectedSection.value;
    
    const matchesSemester = selectedSemester.value === 'all' || 
      student.semester === selectedSemester.value;
    
    return matchesSearch && matchesSubject && matchesYearLevel && matchesSection && matchesSemester;
  });
});

// Group students by subject with null checks
const groupedStudents = computed(() => {
  const groups = {};
  if (!filteredStudents.value) return groups;

  filteredStudents.value.forEach(student => {
    if (!student || !student.subject) return;
    
    if (!groups[student.subject]) {
      groups[student.subject] = [];
    }
    groups[student.subject].push(student);
  });
  return groups;
});
</script>

<template>
  <div class="p-6 max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-800 mb-2">Assigned Students</h1>
      <p class="text-gray-600">
        <span class="font-medium">{{ auth?.name || 'Teacher' }}</span> - Manage and view your assigned students for each subject.
      </p>
    </div>

    <!-- Debug Info (temporary) -->
    <!-- <div class="mb-4 p-4 bg-gray-100 rounded-lg text-xs">
      <details>
        <summary class="text-gray-600 cursor-pointer">Debug Information</summary>
        <div class="mt-2 space-y-1">
          <p class="text-gray-600">Students count: {{ students?.length || 0 }}</p>
          <p class="text-gray-600">Filtered count: {{ filteredStudents?.length || 0 }}</p>
          <p class="text-gray-600">Groups count: {{ Object.keys(groupedStudents).length }}</p>
          <p class="text-gray-600">Current filters:</p>
          <ul class="pl-4">
            <li>Subject: {{ selectedSubject }}</li>
            <li>Year Level: {{ selectedYearLevel }}</li>
            <li>Section: {{ selectedSection }}</li>
            <li>Search: "{{ searchQuery }}"</li>
          </ul>
        </div>
      </details>
    </div> -->

    <!-- Filters Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
      <div class="space-y-4">
        <!-- Search Bar -->
        <div class="relative">
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search by student name or number..."
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
          <!-- Subject Filter -->
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
            <select
              v-model="selectedSubject"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="all">All Subjects</option>
              <option v-for="subject in subjects" :key="subject" :value="subject">
                {{ subject }}
              </option>
            </select>
          </div>

          <!-- Semester Filter -->
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
            <select
              v-model="selectedSemester"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="all">All Semesters</option>
              <option v-for="semester in semesters" :key="semester" :value="semester">
                {{ semester }}
              </option>
            </select>
          </div>

          <!-- Year Level Filter -->
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Year Level</label>
            <select
              v-model="selectedYearLevel"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="all">All Year Levels</option>
              <option v-for="yearLevel in yearLevels" :key="yearLevel" :value="yearLevel">
                {{ yearLevel }}
              </option>
            </select>
          </div>

          <!-- Section Filter -->
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Section</label>
            <select
              v-model="selectedSection"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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

    <!-- Students List Grouped by Subject -->
    <div class="space-y-8">
      <div v-for="(students, subject) in groupedStudents" :key="subject" class="bg-white rounded-lg shadow-sm overflow-hidden">
        <!-- Subject Header -->
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">{{ subject }}</h2>
          <p class="text-sm text-gray-600">{{ students[0].course_code }} • Section {{ students[0].section }}</p>
        </div>

        <!-- Students Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Number</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year Level</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Semester</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="student in students" :key="student.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                      <span class="text-sm font-medium text-blue-800">
                        {{ student.name.charAt(0) }}
                      </span>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ student.name }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ student.student_number }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ student.year_level }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ student.section }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ student.semester }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="Object.keys(groupedStudents).length === 0" class="text-center py-12 bg-white rounded-lg shadow-sm">
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
          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
        />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No students found</h3>
      <p class="mt-1 text-sm text-gray-500">
        Try adjusting your search or filter criteria.
      </p>
    </div>
  </div>
</template>

