<script setup>
import DashboardLayout from '../../components/AdminDashboardLayout.vue';
import ReusableTable from '../../components/ReusableTable.vue';
import Notification from '../../components/Notification.vue';
import ReusableModal from '../../components/ReusableModal.vue';
import Overlay from '../../components/Overlay.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faXmark } from '@fortawesome/free-solid-svg-icons';

import { useForm } from '@inertiajs/vue3'
import { defineProps } from "vue";
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import Swal from 'sweetalert2';

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

const students = ref(props.students || []);
const sections = ref(props.sections || []);
const curricula = ref(props.curricula || []);
const isEditMode = ref(false);
const selectedGrade = ref(null);
const loading = ref(false);
const searchQuery = ref('');
const selectedSchoolYear = ref('');
const selectedSubject = ref('');
const isModalOpen = ref(false);
const selectedSection = ref('');

// Notification system
const notification = reactive({
  show: false,
  message: '',
  type: 'success'
});

const showNotification = (message, type = 'success') => {
  notification.show = false;
  setTimeout(() => {
    notification.message = message;
    notification.type = type;
    notification.show = true;
  }, 50);

  setTimeout(() => {
    notification.show = false;
  }, 2500);
};

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

const openModal = (grade = null) => {
  if (grade) {
    isEditMode.value = true;
    selectedGrade.value = grade;
    form.student_id = grade.student_id;
    form.curriculum_id = grade.curriculum_id;
    form.grade = grade.grade;
    form.grade_remarks = grade.grade_remarks;
    form.year_level_id = grade.year_level_id;
    form.section_id = grade.section_id;
    form.semester_id = grade.semester_id;
  } else {
    isEditMode.value = false;
    selectedGrade.value = null;
    form.reset();
  }
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  loading.value = false;
  selectedGrade.value = null;
  isEditMode.value = false;
  form.reset();
  form.clearErrors();
};

const firstSemesterHeaders = [
  { key: 'student.student_number', label: 'Student No.' },
  { key: 'student.first_name', label: 'First Name' },
  { key: 'student.last_name', label: 'Last Name' },
  { key: 'student.section.section', label: 'Section' },
  { key: 'curriculum.course_code', label: 'Subject Code' },
  { key: 'curriculum.subject_name', label: 'Subject Name' },
  { key: 'first_semester', label: 'First Semester' },
  { key: 'remarks', label: 'Remarks' }
];

const secondSemesterHeaders = [
  { key: 'student.student_number', label: 'Student No.' },
  { key: 'student.first_name', label: 'First Name' },
  { key: 'student.last_name', label: 'Last Name' },
  { key: 'student.section.section', label: 'Section' },
  { key: 'curriculum.course_code', label: 'Subject Code' },
  { key: 'curriculum.subject_name', label: 'Subject Name' },
  { key: 'second_semester', label: 'Second Semester' },
  { key: 'remarks', label: 'Remarks' }
];

const actionButtons = [
  {
    label: 'Edit',
    class: 'bg-[#559de6] text-white px-3 py-1 rounded cursor-pointer',
    action: (grade) => openModal(grade)
  },
  {
    label: 'Delete',
    class: 'bg-red-500 text-white px-3 py-1 rounded cursor-pointer',
    action: (grade) => deleteGrade(grade.id)
  }
];

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

const calculateFinalGrade = () => {
  const quarters = [
    parseFloat(form.first_quarter) || 0,
    parseFloat(form.second_quarter) || 0,
    parseFloat(form.third_quarter) || 0,
    parseFloat(form.fourth_quarter) || 0
  ];
  
  const validQuarters = quarters.filter(q => q > 0);
  if (validQuarters.length === 0) return '';
  
  const average = validQuarters.reduce((a, b) => a + b) / validQuarters.length;
  form.final_grade = Math.round(average);
  
  // Set remarks based on final grade
  form.remarks = form.final_grade >= 75 ? 'Passed' : 'Failed';
};

// Watch for grade changes to auto-calculate final grade
watch(() => [form.first_quarter, form.second_quarter, form.third_quarter, form.fourth_quarter], () => {
  calculateFinalGrade();
}, { deep: true });

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
  if (!form.year_level_id) {
    return [];
  }
  
  // Get sections for the selected year level
  const yearLevelSections = sections.value.filter(section => 
    section.year_level_id === parseInt(form.year_level_id)
  );

  // Create a Set of unique section names
  const uniqueSectionNames = new Set(['A', 'B', 'C', 'D']);

  // Return array of unique sections
  return Array.from(uniqueSectionNames).map(sectionName => {
    const existingSection = yearLevelSections.find(s => s.section === sectionName);
    return existingSection || {
      id: sectionName,
      section: sectionName,
      year_level_id: parseInt(form.year_level_id)
    };
  });
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

// Add the submitForm function
const submitForm = () => {
  loading.value = true;
  
  // Format the grade to always have 2 decimal places before submitting
  if (form.grade !== '') {
    form.grade = parseFloat(form.grade).toFixed(2);
  }

  if (isEditMode.value && selectedGrade.value) {
    // Update existing grade
    form.put(`/grade/${selectedGrade.value.id}/update`, {
      preserveScroll: true,
      onSuccess: () => {
        // Find and update the grade in the grades array
        const index = props.grades.findIndex(g => g.id === selectedGrade.value.id);
        if (index !== -1) {
          props.grades[index] = {
            ...props.grades[index],
            grade: form.grade,
            grade_remarks: form.grade_remarks,
            semester_id: form.semester_id,
            curriculum_id: form.curriculum_id,
            student_id: form.student_id,
            section_id: form.section_id,
            year_level_id: form.year_level_id
          };
        }

        showNotification('Grade updated successfully', 'success');
        closeModal();
        form.reset();
      },
      onError: (errors) => {
        console.error("Validation Errors:", errors);
        const errorMessage = errors.message || Object.values(errors)[0] || 'Failed to update grade';
        showNotification(errorMessage, 'error');
      },
      onFinish: () => {
        loading.value = false;
      }
    });
  } else {
    // Add new grade
    form.post('/admin/add/student/grade', {
      preserveScroll: true,
      onSuccess: (page) => {
        if (page?.props?.grade) {
          props.grades.unshift(page.props.grade);
        }
        showNotification('Grade added successfully', 'success');
        closeModal();
        form.reset();
      },
      onError: (errors) => {
        console.error("Validation Errors:", errors);
        const errorMessage = errors.message || Object.values(errors)[0] || 'Failed to add grade';
        showNotification(errorMessage, 'error');
      },
      onFinish: () => {
        loading.value = false;
      }
    });
  }
};

// Add the delete function
const deleteGrade = (id) => {
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#1a3047',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(`/grade/${id}/delete`, {
        preserveScroll: true,
        onSuccess: () => {
          // Remove grade from both semester lists
          const firstSemIndex = props.grades.findIndex(g => g.id === id && g.semester_id === 1);
          const secondSemIndex = props.grades.findIndex(g => g.id === id && g.semester_id === 2);
          
          if (firstSemIndex !== -1) {
            props.grades.splice(firstSemIndex, 1);
          }
          if (secondSemIndex !== -1) {
            props.grades.splice(secondSemIndex, 1);
          }
          
          showNotification('Grade deleted successfully', 'success');
        },
        onError: () => {
          showNotification('Failed to delete grade', 'error');
        }
      });
    }
  });
};

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
            class="bg-[#ffff] p-2 pr-[3rem] text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 border-gray-500 w-[300px]"
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
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="C">C</option>
            <option value="D">D</option>
          </select>

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
            class="border-2 border-gray-500 rounded-[0.5rem] text-[0.875rem] p-2 focus:outline-none focus:border-blue-500"
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
      </div>

      <!-- Add Grade Button -->
      <button @click="openModal(null)" 
        class="cursor-pointer bg-[#1a3047] text-[#ffff] font-bold rounded-md pt-2 pb-2 pl-3 pr-3 flex justify-center items-center ml-4">
        Add Grade
      </button>
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
              <th class="px-6 py-3 text-left text-sm font-semibold">First Semester</th>
              <th class="px-6 py-3 text-left text-sm font-semibold">Remarks</th>
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
              <td class="px-6 py-4 text-sm">{{ parseFloat(grade.grade).toFixed(2) }}</td>
              <td class="px-6 py-4 text-sm">{{ grade.grade_remarks }}</td>
              <td class="px-6 py-4 text-sm">
                <div class="flex gap-2 justify-center">
                  <button 
                    @click="openModal(grade)"
                    class="bg-[#559de6] text-white px-3 py-1 rounded cursor-pointer hover:bg-blue-600 transition-colors"
                  >
                    Edit
                  </button>
                  <button 
                    @click="deleteGrade(grade.id)"
                    class="bg-red-500 text-white px-3 py-1 rounded cursor-pointer hover:bg-red-600 transition-colors"
                  >
                    Delete
                  </button>
                </div>
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
                <div class="flex gap-2 justify-center">
                  <button 
                    @click="openModal(grade)"
                    class="bg-[#559de6] text-white px-3 py-1 rounded cursor-pointer hover:bg-blue-600 transition-colors"
                  >
                    Edit
                  </button>
                  <button 
                    @click="deleteGrade(grade.id)"
                    class="bg-red-500 text-white px-3 py-1 rounded cursor-pointer hover:bg-red-600 transition-colors"
                  >
                    Delete
                  </button>
                </div>
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

    <!-- Grade Modal -->
    <ReusableModal 
      :show="isModalOpen" 
      :title="isEditMode ? 'Edit Grade' : 'Add Grade'" 
      :loading="loading"
      :submit-button-text="isEditMode ? 'Update Grade' : 'Add Grade'"
      @close="closeModal"
      @submit="submitForm"
      class="z-50"
    >
      <div class="grid grid-cols-3 gap-4">
        <!-- Year Level -->
        <div>
          <select 
            v-model="form.year_level_id" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.year_level_id }"
          >
            <option value="">Select Year Level</option>
            <option value="1">1st Year</option>
            <option value="2">2nd Year</option>
            <option value="3">3rd Year</option>
            <option value="4">4th Year</option>
          </select>
          <p v-if="form.errors.year_level_id" class="text-red-500 text-sm mt-1">
            {{ form.errors.year_level_id }}
          </p>
        </div>

        <!-- Section -->
        <div>
          <select 
            v-model="form.section_id" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.section_id }"
          >
            <option value="">Select Section</option>
            <option v-for="section in filteredSections" :key="section.id" :value="section.id">
              {{ section.section }}
            </option>
          </select>
          <p v-if="form.errors.section_id" class="text-red-500 text-sm mt-1">
            {{ form.errors.section_id }}
          </p>
        </div>

        <!-- Student -->
        <div>
          <select 
            v-model="form.student_id" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.student_id }"
          >
            <option value="">Select Student</option>
            <option v-for="student in filteredStudents" :key="student.id" :value="student.id">
              {{ student.student_number }} - {{ student.first_name }} {{ student.last_name }}
            </option>
          </select>
          <p v-if="form.errors.student_id" class="text-red-500 text-sm mt-1">
            {{ form.errors.student_id }}
          </p>
        </div>

        <!-- Subject -->
        <div>
          <select 
            v-model="form.curriculum_id" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.curriculum_id }"
          >
            <option value="">Select Subject</option>
            <option v-for="curriculum in curricula" :key="curriculum.id" :value="curriculum.id">
              {{ curriculum.course_code }} - {{ curriculum.subject_name }}
            </option>
          </select>
          <p v-if="form.errors.curriculum_id" class="text-red-500 text-sm mt-1">
            {{ form.errors.curriculum_id }}
          </p>
        </div>

        <!-- Semester -->
        <div>
          <select 
            v-model="form.semester_id" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.semester_id }"
          >
            <option value="">Select Semester</option>
            <option value="1">First Semester</option>
            <option value="2">Second Semester</option>
          </select>
          <p v-if="form.errors.semester_id" class="text-red-500 text-sm mt-1">
            {{ form.errors.semester_id }}
          </p>
        </div>

        <!-- Grade -->
        <div>
          <input 
            type="number" 
            v-model="form.grade" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.grade }"
            placeholder="Enter grade"
            min="0"
            max="100"
            step="0.01"
          >
          <p v-if="form.errors.grade" class="text-red-500 text-sm mt-1">
            {{ form.errors.grade }}
          </p>
        </div>

        <!-- Remarks -->
        <div>
          <input 
            type="text" 
            v-model="form.grade_remarks" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.grade_remarks }"
            placeholder="Enter remarks"
          >
          <p v-if="form.errors.grade_remarks" class="text-red-500 text-sm mt-1">
            {{ form.errors.grade_remarks }}
          </p>
        </div>
      </div>
    </ReusableModal>
  </div>
</template>
