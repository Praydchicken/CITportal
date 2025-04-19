<script setup>
import DashboardLayout from '../../components/AdminDashboardLayout.vue';
import Notification from '../../components/Notification.vue';
import ReusableModal from '../../components/ReusableModal.vue';
import Overlay from '../../components/Overlay.vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faXmark } from '@fortawesome/free-solid-svg-icons';

import { useForm } from '@inertiajs/vue3'
import { defineProps } from "vue";
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';

library.add(faXmark);

defineOptions({
  layout: DashboardLayout,
  components: {
    ReusableModal
  }
});

const props = defineProps({
  title: String,
  students: Array,
  curricula: Array,
  sections: Array,
  grades: Array,
  activeSchoolYear: Object,
  schoolYears: Array
});

console.log(props.grades);


const students = ref(props.students || []);
const sections = ref(props.sections || []);
const curricula = ref(props.curricula || []);
const searchQuery = ref('');
const selectedSchoolYear = ref('');
const selectedSubject = ref('');
const isModalOpen = ref(false);
const selectedSection = ref('');
const selectedYearLevel = ref('');
const selectedGradeStatus = ref("");


// Notification system
const notification = reactive({
  show: false,
  message: '',
  type: 'success'
});

const form = useForm({
  student_id: '',
  curriculum_id: '',
  section_id: '',
  year_level_id: '',
  grade: '',
  grade_remarks: '',
  semester_id: '',
  school_year_id: props.activeSchoolYear?.id || ''
});

// Update the filtering logic for grades
const filterGrades = (grades) => {
  let filtered = grades;

  // Filter by school year
  if (selectedSchoolYear.value) {
    filtered = filtered.filter(grade => 
      grade.school_year_id === parseInt(selectedSchoolYear.value)
    );
  }

  // Filter by section
  if (selectedSection.value) {
    filtered = filtered.filter(grade => 
      grade.student?.section?.section === selectedSection.value
    );
  }

  // Filter by subject
  if (selectedSubject.value) {
    filtered = filtered.filter(grade => 
      grade.curriculum_id === parseInt(selectedSubject.value)
    );
  }

  // filter by grade status
  if(selectedGradeStatus.value) {
    filtered = filtered.filter(grade => grade.grade_status === selectedGradeStatus.value);
  }

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(grade => {
      return (
        grade.student?.student_number?.toLowerCase().includes(query) ||
        grade.student?.first_name?.toLowerCase().includes(query) ||
        grade.student?.last_name?.toLowerCase().includes(query) ||
        grade.curriculum?.subject_name?.toLowerCase().includes(query) ||
        grade.curriculum?.course_code?.toLowerCase().includes(query)
      );
    });
  }

  return filtered;
};


// Update the semester grade computed properties
const firstSemesterGrades = computed(() => {
  const semesterGrades = props.grades?.filter(grade => grade.semester_id === 1) || [];
  return filterGrades(semesterGrades);
});

const secondSemesterGrades = computed(() => {
  const semesterGrades = props.grades?.filter(grade => grade.semester_id === 2) || [];
  return filterGrades(semesterGrades);
});

// Update the filteredStudents computed property
const filteredStudents = computed(() => {
  if (!form.year_level_id || !form.section_id) {
    return [];
  }
  
  return students.value.filter(student => {
    const yearLevelMatch = student.year_level?.id === parseInt(form.year_level_id);
    const sectionMatch = student.section?.section === filteredSections.value.find(s => s.id === form.section_id)?.section;
    
    return yearLevelMatch && sectionMatch;
  });
});

// Update the filteredSections computed property
const filteredSections = computed(() => {
  if (!selectedYearLevel.value) {
    // When no year level is selected, show unique sections
    const uniqueSections = new Set(sections.value.map(section => section.section));
    return Array.from(uniqueSections).map(sectionName => ({
      id: sections.value.find(s => s.section === sectionName).id,
      section: sectionName
    }));
  }
  
  // When year level is selected, filter sections by year level
  const yearLevelSections = sections.value.filter(section => 
    section.year_level_id === parseInt(selectedYearLevel.value)
  );
  
  // Return unique sections for the selected year level
  const uniqueSections = new Set(yearLevelSections.map(section => section.section));
  return Array.from(uniqueSections).map(sectionName => ({
    id: yearLevelSections.find(s => s.section === sectionName).id,
    section: sectionName
  }));
});

// Update the watch for year_level_id
watch(() => form.year_level_id, (newValue) => {
  if (newValue === '') {
    form.section_id = '';
    form.student_id = '';
  } else {
    // Don't reset section_id here to maintain selection
    form.student_id = ''; // Only reset student when year level changes
  }
}, { immediate: true });

// Update the watch for section_id
watch(() => form.section_id, (newValue) => {
  if (newValue === '') {
    form.student_id = '';
  }
  // Add a console log to help debug
  if (newValue && form.year_level_id) {
    console.log('Filtered Students:', filteredStudents.value);
  }
}, { immediate: true });

const viewCourse = (studentGradeId) => { 
  router.get(route('admin.student.view.grade', {
    studentGradeId
  }));
  
}
</script>

<template>
  <div class="relative">
    <Overlay :show="isModalOpen" @click="closeModal" />

    <Teleport to="body">
      <Notification 
        :show="notification.show" 
        :message="notification.message" 
        :type="notification.type" 
      />
    </Teleport>

    <!-- Active School Year Banner -->
    <div v-if="activeSchoolYear" class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-sm">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm font-medium">
            Active School Year: <span class="font-bold">{{ activeSchoolYear.school_year }}</span>
          </p>
        </div>
      </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="flex justify-between items-center mb-4">
      <div class="flex items-center gap-4">
        <!-- Search Input -->
        <form @submit.prevent>
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Search for students..." 
            class="bg-[#ffff] p-2 pr-[3rem] text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 border-gray-500 w-[200px]"
          >
        </form>
        
        <!-- School Year Filter -->
        <div class="w-48 relative">
          <select 
            v-model="selectedSchoolYear"
            class="w-full bg-white p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border border-gray-300 appearance-none cursor-pointer focus:outline-none focus:border-blue-500"
          >
            <option value="" class="hidden">All School Years</option>
            <option v-for="year in schoolYears" :key="year.id" :value="year.id">
              {{ year.school_year }}
            </option>
          </select>

          <div class="absolute right-3 top-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <!-- Section Filter -->
       <div class="w-48 relative">
          <select 
            v-model="selectedSection"
            class="w-full bg-white p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border border-gray-300 appearance-none cursor-pointer focus:outline-none focus:border-blue-500"
          >
            <option value="">All Sections</option>
            <option v-for="section in filteredSections" :key="section.id" :value="section.section">
              {{ section.section }}
            </option>
          </select>

          <!-- Custom dropdown arrow -->
          <div class="absolute right-3 top-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <!-- Subject Filter -->
        <div class="w-48 relative">
          <select
            v-model="selectedSubject"
             class="w-full bg-white p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border border-gray-300 appearance-none cursor-pointer focus:outline-none focus:border-blue-500"
          >
            <option value="">All Subjects</option>
            <option v-for="curriculum in curricula" :key="curriculum.id" :value="curriculum.id">
              {{ curriculum.course_code }} - {{ curriculum.subject_name }}
            </option>
          </select>

          <div class="absolute right-3 top-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <!-- Grade Status Filter -->
        <div class="w-48 relative">
          <select 
            v-model="selectedGradeStatus"
            class="w-full bg-white p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border border-gray-300 appearance-none cursor-pointer focus:outline-none focus:border-blue-500"
          >
            <option value="">All Statuses</option>
            <option value="APPROVED">Approved</option>
            <option value="PENDING">Pending</option>
            <option value="REJECTED">Rejected</option>
          </select>

          <div class="absolute right-3 top-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Add Grade Button -->
      <!-- <button @click="openModal(null)" 
        class="cursor-pointer bg-[#1a3047] text-[#ffff] font-bold rounded-md pt-2 pb-2 pl-3 pr-3 flex justify-center items-center ml-4">
        Add Grade
      </button> -->
    </div>

    <!-- First Semester Grades Table -->
    <div class="mb-8">
      <h2 class="text-xl font-bold mb-4">First Semester Grades</h2>
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
          <thead class="bg-[#1a3047] text-white">
            <tr>
              <th class="px-6 py-3 text-left text-sm font-semibold">Student No.</th>
              <th class="px-6 py-3 text-left text-sm font-semibold">First Name</th>
              <th class="px-6 py-3 text-left text-sm font-semibold">Last Name</th>
              <th class="px-6 py-3 text-left text-sm font-semibold">Section</th>
              <th class="px-6 py-3 text-left text-sm font-semibold">Subject Code</th>
              <th class="px-6 py-3 text-left text-sm font-semibold">Subject Name</th>
              <th class="px-6 py-3 text-left text-sm font-semibold">Grade</th>
              <th class="px-6 py-3 text-left text-sm font-semibold">Remarks</th>
              <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
              <th class="px-6 py-3 text-left text-sm font-semibold">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="grade in firstSemesterGrades" :key="grade.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm">{{ grade.student?.student_number }}</td>
              <td class="px-6 py-4 text-sm">{{ grade.student?.first_name }}</td>
              <td class="px-6 py-4 text-sm">{{ grade.student?.last_name }}</td>
              <td class="px-6 py-4 text-sm">{{ grade.student?.section?.section }}</td>
              <td class="px-6 py-4 text-sm">{{ grade.curriculum?.course_code }}</td>
              <td class="px-6 py-4 text-sm">{{ grade.curriculum?.subject_name }}</td>
              <td class="px-6 py-4 text-sm">{{ grade.gwa_equivalent }}</td>
              <td class="px-6 py-4 text-sm">{{ grade.grade_remarks }}</td>
              <td class="px-6 py-4 text-sm">
                <span :class="{
                  'text-green-600': grade.grade_status === 'APPROVED',
                  'text-yellow-600': grade.grade_status === 'PENDING',
                  'text-red-600': grade.grade_status === 'REJECTED'
                }">
                  {{ grade.grade_status }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm">
                 <button
                    @click="viewCourse(grade.id)"
                    class="inline-flex items-center text-gray-600 hover:text-gray-900"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                    </svg>
                    View Course
                </button>
              </td>
            </tr>
            <tr v-if="firstSemesterGrades.length === 0">
              <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                No grades found for first semester
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Second Semester Grades Table -->
    <div>
      <h2 class="text-xl font-bold mb-4">Second Semester Grades</h2>
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
          <thead class="bg-[#1a3047] text-white">
            <tr>
              <th class="px-6 py-3 text-left text-sm font-semibold">Student No.</th>
              <th class="px-6 py-3 text-left text-sm font-semibold">First Name</th>
              <th class="px-6 py-3 text-left text-sm font-semibold">Last Name</th>
              <th class="px-6 py-3 text-left text-sm font-semibold">Section</th>
              <th class="px-6 py-3 text-left text-sm font-semibold">Subject Code</th>
              <th class="px-6 py-3 text-left text-sm font-semibold">Subject Name</th>
              <th class="px-6 py-3 text-left text-sm font-semibold">Second Semester</th>
              <th class="px-6 py-3 text-left text-sm font-semibold">Remarks</th>
              <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
              <th class="px-6 py-3 text-left text-sm font-semibold">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="grade in secondSemesterGrades" :key="grade.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm">{{ grade.student?.student_number }}</td>
              <td class="px-6 py-4 text-sm">{{ grade.student?.first_name }}</td>
              <td class="px-6 py-4 text-sm">{{ grade.student?.last_name }}</td>
              <td class="px-6 py-4 text-sm">{{ grade.student?.section?.section }}</td>
              <td class="px-6 py-4 text-sm">{{ grade.curriculum?.course_code }}</td>
              <td class="px-6 py-4 text-sm">{{ grade.curriculum?.subject_name }}</td>
              <td class="px-6 py-4 text-sm">{{ parseFloat(grade.grade).toFixed(2) }}</td>
              <td class="px-6 py-4 text-sm">{{ grade.grade_remarks }}</td>
               <td class="px-6 py-4 text-sm">
                <span :class="{
                  'text-green-600': grade.grade_status === 'APPROVED',
                  'text-yellow-600': grade.grade_status === 'PENDING',
                  'text-red-600': grade.grade_status === 'REJECTED'
                }">
                  {{ grade.grade_status }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm">
               <button
                    @click="viewCourse(grade.id)"
                    class="inline-flex items-center text-gray-600 hover:text-gray-900"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                    </svg>
                    View Course
                </button>
              </td>
            </tr>
            <tr v-if="secondSemesterGrades.length === 0">
              <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                No grades found for second semester
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
