<script setup>
import TeacherDashboardLayout from '../../components/teacherDashboardLayout/TeacherDashboardLayout.vue';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import LogoutBtn from '../../components/LogoutBtn.vue';

defineOptions({
  layout: TeacherDashboardLayout
});

const page = usePage();
const searchQuery = ref('');
const selectedSubject = ref('all');
const selectedYearLevel = ref('all');
const selectedSection = ref('all');
const selectedSemester = ref('all');

// Safely access students with null check and initial empty array
const students = computed(() => {
  return page.props.students || [];
});

// Check if there are any assigned students at all
const hasAssignedStudents = computed(() => {
  return students.value.length > 0;
});

// Safely access auth user
const auth = computed(() => {
  return page.props.auth?.user || null;
});

// Get unique subjects with their metadata (course code, etc.)
const subjectOptions = computed(() => {
  if (!hasAssignedStudents.value) return [];

  const subjectsMap = new Map();

  students.value.forEach(student => {
    if (!student || !student.subject) return;

    if (!subjectsMap.has(student.subject)) {
      subjectsMap.set(student.subject, {
        course_code: student.course_code,
        sections: new Set(),
        year_levels: new Set(),
        semesters: new Set()
      });
    }

    const subjectData = subjectsMap.get(student.subject);
    if (student.section) subjectData.sections.add(student.section);
    if (student.year_level) subjectData.year_levels.add(student.year_level);
    if (student.semester) subjectData.semesters.add(student.semester);
  });

  return Array.from(subjectsMap.entries()).map(([subject, data]) => ({
    subject,
    course_code: data.course_code,
    sections: Array.from(data.sections).sort(),
    year_levels: Array.from(data.year_levels).sort(),
    semesters: Array.from(data.semesters).sort()
  }));
});

// Get unique values for filters based on selected subject
const yearLevels = computed(() => {
  if (!hasAssignedStudents.value) return [];

  if (selectedSubject.value === 'all') {
    const allYearLevels = new Set(
      students.value
        .filter(student => student?.year_level)
        .map(student => student.year_level)
    );
    return Array.from(allYearLevels).sort();
  }

  const subject = subjectOptions.value.find(s => s.subject === selectedSubject.value);
  return subject?.year_levels || [];
});

const sections = computed(() => {
  if (!hasAssignedStudents.value) return [];

  if (selectedSubject.value === 'all') {
    const allSections = new Set(
      students.value
        .filter(student => student?.section)
        .map(student => student.section)
    );
    return Array.from(allSections).sort();
  }

  const subject = subjectOptions.value.find(s => s.subject === selectedSubject.value);
  return subject?.sections || [];
});

const semesters = computed(() => {
  if (!hasAssignedStudents.value) return [];

  if (selectedSubject.value === 'all') {
    const allSemesters = new Set(
      students.value
        .filter(student => student?.semester)
        .map(student => student.semester)
    );
    return Array.from(allSemesters).sort();
  }

  const subject = subjectOptions.value.find(s => s.subject === selectedSubject.value);
  return subject?.semesters || [];
});

// Filter students based on search and filters with null checks
const filteredStudents = computed(() => {
  if (!hasAssignedStudents.value) return [];

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

// Group students by subject with their current academic details
const groupedStudents = computed(() => {
  const groups = {};

  if (!hasAssignedStudents.value) return groups;

  filteredStudents.value.forEach(student => {
    if (!student || !student.subject) return;

    const groupKey = `${student.subject}-${student.course_code}`;

    if (!groups[groupKey]) {
      groups[groupKey] = {
        subject: student.subject,
        course_code: student.course_code,
        students: []
      };
    }

    groups[groupKey].students.push(student);
  });

  return groups;
});

console.log(groupedStudents);

</script>

<template>
  <div class="p-6 max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-800 mb-2">Assigned Students</h1>
      <p class="text-gray-600">
        <span class="font-medium">{{ auth?.name || 'Teacher' }}</span> - Manage and view your assigned students for each
        subject.
      </p>
    </div>

    <!-- No Assigned Students State -->
    <div v-if="!hasAssignedStudents" class="bg-white rounded-lg shadow-sm p-12 text-center">
      <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
      </svg>
      <h3 class="mt-4 text-lg font-medium text-gray-900">No assigned students</h3>
      <p class="mt-2 text-sm text-gray-600">
        You currently don't have any students assigned to your subjects.
      </p>
      <p class="mt-1 text-sm text-gray-500">
        Please contact the administrator if you believe this is incorrect.
      </p>
    </div>

    <!-- Content when there are assigned students -->
    <template v-else>
      <!-- Filters Section -->
      <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <div class="space-y-4">
          <!-- Search Bar -->
          <div class="relative">
            <input type="text" v-model="searchQuery" placeholder="Search by student name or number..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>

          <!-- Filter Controls -->
          <div class="flex flex-wrap gap-4">
            <!-- Subject Filter -->
            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
              <select v-model="selectedSubject"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                @change="selectedYearLevel = 'all'; selectedSection = 'all'; selectedSemester = 'all'">
                <option value="all">All Subjects</option>
                <option v-for="subject in subjectOptions" :key="subject.subject" :value="subject.subject">
                  {{ subject.subject }} ({{ subject.course_code }})
                </option>
              </select>
            </div>

            <!-- Semester Filter -->
            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
              <select v-model="selectedSemester"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="all">All Semesters</option>
                <option v-for="semester in semesters" :key="semester" :value="semester">
                  {{ semester }}
                </option>
              </select>
            </div>

            <!-- Year Level Filter -->
            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700 mb-1">Year Level</label>
              <select v-model="selectedYearLevel"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="all">All Year Levels</option>
                <option v-for="yearLevel in yearLevels" :key="yearLevel" :value="yearLevel">
                  {{ yearLevel }}
                </option>
              </select>
            </div>

            <!-- Section Filter -->
            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700 mb-1">Section</label>
              <select v-model="selectedSection"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
        <div v-for="(group, key) in groupedStudents" :key="key" class="bg-white rounded-lg shadow-sm overflow-hidden">
          <!-- Subject Header -->
          <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">{{ group.subject }}</h2>
            <p class="text-sm text-gray-600">{{ group.course_code }}</p>
          </div>

          <!-- Students Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student
                    Number</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year Level
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Semester
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(student, index) in group.students" :key="student.id" class="hover:bg-gray-50"
                  :class="index % 2 === 0 ? 'bg-neutral-300' : 'bg-gray-200'">
                  <template v-if="student.id === null">
                    <td :colspan="5" class="text-center py-6 rounded-lg shadow-sm">
                      <svg class="mx-auto h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                      </svg>
                      <h3 class="mt-2 text-sm font-medium text-gray-400">No students assigned yet</h3>
                    </td>
                  </template>

                  <template v-else>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                          <span class="text-sm font-medium text-blue-800">
                            {{ student.name ? student.name.charAt(0) : 'N/A' }}
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
                  </template>
                </tr>

              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Empty Filter Results State -->
      <div v-if="hasAssignedStudents && Object.keys(groupedStudents).length === 0"
        class="text-center py-12 bg-white rounded-lg shadow-sm">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No students match your criteria</h3>
        <p class="mt-1 text-sm text-gray-500">
          Try adjusting your search or filter settings.
        </p>
      </div>
    </template>
  </div>
</template>