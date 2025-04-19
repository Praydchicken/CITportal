<script setup>
import DashboardLayout from '../../components/AdminDashboardLayout.vue';
import Overlay from '../../components/Overlay.vue';
import ReusableTable from '../../components/ReusableTable.vue';
import ReusableModal from '../../components/ReusableModal.vue';
import Notification from '../../components/Notification.vue';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faXmark } from '@fortawesome/free-solid-svg-icons'

import { useForm } from '@inertiajs/vue3'
import { defineProps } from "vue";
import { ref, watch, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import Swal from 'sweetalert2';

library.add(faXmark);

defineOptions({
  layout: DashboardLayout
});

const props = defineProps({
  title: String,
  subjects: Array,
  yearLevels: Array,
  semesters: Array,
  flash: {
    type: Object,
    default: () => ({})
  },
  curriculums: {
    type: Object,
    required: true
  }
});

const subjects = ref(props.subjects);
const yearLevels = ref(props.yearLevels);
const semesters = ref(props.semesters);
const isEditMode = ref(false);
const selectedSubject = ref(null);
const loading = ref(false);
const searchQuery = ref('');

// Add filter state variables
const selectedYearLevelFilter = ref('');
const selectedSemesterFilter = ref('');

// Function to clear filters
const clearFilters = () => {
  selectedYearLevelFilter.value = '';
  selectedSemesterFilter.value = '';
};

// Watch for props.subjects changes
watch(() => props.subjects, (newSubjects) => {
  if (newSubjects) {
    subjects.value = newSubjects;
    console.log('Subjects data:', newSubjects);
  }
});

// Watch for flash messages from the controller
watch(() => props.flash?.success, (message) => {
  if (message) {
    showNotification(message, 'success');
  }
}, { immediate: true });

watch(() => props.flash?.error, (message) => {
  if (message) {
    showNotification(message, 'error');
  }
}, { immediate: true });

const isModalOpen = ref(false);

const form = useForm({
  year_level_id: "",
  semester_id: "",
  course_code: "",
  subject_name: "",
  description: "",
  lecture_units: "",
  lab_units: "",
  total_units: "",
});

// Create a reactive object for the notification state
const notification = reactive({
  show: false,
  message: '',
  type: 'success'
});

// Function to show the notification
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

const openModal = (subject) => {
  if (subject) {
    editSubject(subject);
  } else {
    isEditMode.value = false;
    form.reset();
    isModalOpen.value = true;
  }
};

const editSubject = (subject) => {
  isEditMode.value = true;
  selectedSubject.value = subject;

  // Pre-fill the form with subject data
  form.year_level_id = subject.year_level_id;
  form.semester_id = subject.semester_id;
  form.course_code = subject.course_code;
  form.subject_name = subject.subject_name;
  form.description = subject.description;
  form.lecture_units = subject.lecture_units;
  form.lab_units = subject.lab_units;
  form.total_units = subject.total_units;

  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  selectedSubject.value = null;
  isEditMode.value = false;
  form.reset();
  form.clearErrors();
};

// Add a computed property to automatically calculate the total units
const computedTotalUnits = computed(() => {
  const lectureUnits = parseFloat(form.lecture_units) || 0;
  const labUnits = parseFloat(form.lab_units) || 0;
  return lectureUnits + labUnits;
});

// Watch for changes to lecture_units and lab_units to update total_units
watch([() => form.lecture_units, () => form.lab_units], () => {
  // Update form.total_units automatically
  form.total_units = computedTotalUnits.value;
});

// Update the submitForm function with the correct routes
const submitForm = () => {
  loading.value = true;
  
  // Clear previous errors
  form.clearErrors();

  // Validate required fields
  let hasErrors = false;

  // Validate Year Level
  if (!form.year_level_id) {
    form.setError('year_level_id', 'Year Level is required');
    hasErrors = true;
  }

  // Validate Semester
  if (!form.semester_id) {
    form.setError('semester_id', 'Semester is required');
    hasErrors = true;
  }

  // Validate Course Code
  if (!form.course_code || form.course_code.trim() === '') {
    form.setError('course_code', 'Course Code is required');
    hasErrors = true;
  }

  // Validate Subject Name
  if (!form.subject_name || form.subject_name.trim() === '') {
    form.setError('subject_name', 'Subject Name is required');
    hasErrors = true;
  }

  // Description is optional - removed validation

  // Validate Lecture Units
  const lectureUnits = parseFloat(form.lecture_units);
  if (isNaN(lectureUnits) || lectureUnits < 0) {
    form.setError('lecture_units', 'Lecture units must be a valid number');
    hasErrors = true;
  }

  // Validate Laboratory Units
  const labUnits = parseFloat(form.lab_units);
  if (isNaN(labUnits) || labUnits < 0) {
    form.setError('lab_units', 'Laboratory units must be a valid number');
    hasErrors = true;
  }

  // Set the total_units field automatically
  form.total_units = lectureUnits + labUnits;

  if (hasErrors) {
    loading.value = false;
    showNotification('Please fix the errors in the form', 'error');
    return;
  }

  if (isEditMode.value && selectedSubject.value) {
    // Update existing subject using the correct route
    router.put(`/curriculum/subject/${selectedSubject.value.id}/update`, form.data(), {
      preserveScroll: true,
      onSuccess: () => {
        handleSearch(); // Refresh the data after update
        showNotification('Subject updated successfully');
        closeModal();
      },
      onError: (errors) => {
        Object.keys(errors).forEach(key => {
          form.setError(key, errors[key]);
        });
        showNotification('Failed to update subject', 'error');
      },
      onFinish: () => {
        loading.value = false;
      }
    });
  } else {
    // Add new subject
    router.post('/curriculum/subject/add', form.data(), {
      preserveScroll: true,
      onSuccess: () => {
        handleSearch(); // Refresh the data after adding
        showNotification('Subject added successfully');
        closeModal();
      },
      onError: (errors) => {
        Object.keys(errors).forEach(key => {
          form.setError(key, errors[key]);
        });
        showNotification('Failed to add subject', 'error');
      },
      onFinish: () => {
        loading.value = false;
      }
    });
  }
};

// Add computed property to check if form is valid
const isFormValid = computed(() => {
  return !Object.keys(form.errors).length;
});

const deleteSubject = (id) => {
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
      loading.value = true;
      router.delete(`/curriculum/subject/${id}/delete`, {
        preserveScroll: true,
        onSuccess: (page) => {
          subjects.value = page.props.subjects;
          if (page.props.flash.success) {
            showNotification(page.props.flash.success);
          }
        },
        onError: () => {
          showNotification('Failed to delete subject', 'error');
        },
        onFinish: () => {
          loading.value = false;
        }
      });
    }
  });
};

const tableHeaders = [
  { key: 'year_level.year_level', label: 'Year Level' },
  { key: 'semester.semester_name', label: 'Semester' },
  { key: 'course_code', label: 'Course Code' },
  { key: 'subject_name', label: 'Subject Name' },
  { key: 'description', label: 'Description' },
  { key: 'lecture_units', label: 'Lecture Units' },
  { key: 'lab_units', label: 'Laboratory Units' },
  { key: 'total_units', label: 'Total Units' }
];

const actionButtons = [
  {
    label: 'Edit',
    class: 'bg-[#559de6] text-white px-3 py-1 rounded cursor-pointer',
    action: (subject) => editSubject(subject)
  },
  {
    label: 'Delete',
    class: 'bg-red-500 text-white px-3 py-1 rounded cursor-pointer',
    action: (subject) => deleteSubject(subject.id)
  }
];

// Computed property for filtered subjects
const filteredSubjects = computed(() => {
  let filtered = subjects.value;

  // Apply year level filter
  if (selectedYearLevelFilter.value) {
    filtered = filtered.filter(subject => 
      subject.year_level_id === selectedYearLevelFilter.value
    );
  }

  // Apply semester filter
  if (selectedSemesterFilter.value) {
    filtered = filtered.filter(subject => 
      subject.semester_id === selectedSemesterFilter.value
    );
  }

  // Apply search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(subject => {
      return (
        (subject.course_code && subject.course_code.toLowerCase().includes(query)) ||
        (subject.subject_name && subject.subject_name.toLowerCase().includes(query)) ||
        (subject.description && subject.description.toLowerCase().includes(query)) ||
        (subject.lecture_units && subject.lecture_units.toString().includes(query)) ||
        (subject.lab_units && subject.lab_units.toString().includes(query)) ||
        (subject.total_units && subject.total_units.toString().includes(query)) ||
        (subject.year_level?.year_level && subject.year_level.year_level.toLowerCase().includes(query)) ||
        (subject.semester?.semester_name && subject.semester.semester_name.toLowerCase().includes(query))
      );
    });
  }

  return filtered;
});

const filteredCurriculums = computed(() => {
  // Check if curriculums and curriculums.data exist to avoid null reference errors
  if (!props.curriculums || !props.curriculums.data) {
    return [];
  }
  
  // We're returning the data array directly because filtering is handled server-side
  // via the handleSearch function that makes a new request with the filter params
  return props.curriculums.data;
});

const yearLevelOptions = computed(() => {
  return props.yearLevels.map(level => ({
    value: level.id,
    label: level.year_level
  }));
});

const semesterOptions = computed(() => {
  return props.semesters.map(semester => ({
    value: semester.id,
    label: semester.semester_name
  }));
});

// Add a debounce function for search
let searchTimeout;
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    handleSearch();
  }, 500); // 500ms debounce
};

// Update handleSearch to use admin.curriculum.config route
const handleSearch = () => {
  router.get(route('admin.curriculum.config'), {
    search: searchQuery.value,
    year_level: selectedYearLevelFilter.value,
    semester: selectedSemesterFilter.value
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['curriculums']
  });
};

const handlePageChange = (url) => {
  if (!url) return;
  
  router.visit(url, {
    preserveState: true,
    preserveScroll: true,
    only: ['curriculums']
  });
};

// Add a function to debug and log the data
const logData = () => {
  console.log('Curriculums prop:', props.curriculums);
  console.log('Filtered curriculums:', filteredCurriculums.value);
};

// Call the debug function when component mounts
onMounted(() => {
  logData();
});

// Fix the editCurriculum function to correctly populate the form
const editCurriculum = (curriculum) => {
  console.log("Editing curriculum:", curriculum); // Add debugging
  
  isEditMode.value = true;
  selectedSubject.value = curriculum;

  // Pre-fill the form with curriculum data, ensuring field names match
  form.year_level_id = curriculum.year_level_id;
  form.semester_id = curriculum.semester_id;
  form.course_code = curriculum.course_code;
  form.subject_name = curriculum.subject_name;
  form.description = curriculum.description || '';
  form.lecture_units = curriculum.lecture_units;
  form.lab_units = curriculum.lab_units;
  
  // The total_units field will be calculated automatically via the watcher
  
  // Open the modal
  isModalOpen.value = true;
};

const confirmDelete = (curriculum) => {
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
      loading.value = true;
      router.delete(`/curriculum/subject/${curriculum.id}/delete`, {
        preserveScroll: true,
        onSuccess: () => {
          handleSearch(); // Refresh the data after deletion
          showNotification('Subject deleted successfully');
        },
        onError: () => {
          showNotification('Failed to delete subject', 'error');
        },
        onFinish: () => {
          loading.value = false;
        }
      });
    }
  });
};

// Add other missing state variables if not already defined
const selectedCurriculum = ref(null);
const isDeleteModalOpen = ref(false);

// Add a function to clear filters and trigger search
const clearFiltersAndSearch = () => {
  selectedYearLevelFilter.value = '';
  selectedSemesterFilter.value = '';
  searchQuery.value = '';
  handleSearch();
};

// Add a computed property or method to calculate units if needed
const getTotalUnits = (curriculum) => {
  if (!curriculum) return 0;
  
  const lectureUnits = parseFloat(curriculum.lecture_units) || 0;
  const labUnits = parseFloat(curriculum.lab_units) || 0;
  
  return lectureUnits + labUnits;
};
</script>

<template>
  <div class="relative">
    <!-- Notification using teleport to ensure it's always on top -->
    <Teleport to="body">
      <Notification :show="notification.show" :message="notification.message" :type="notification.type" />
    </Teleport>

    <Overlay :show="isModalOpen" @click="closeModal" />
    
    <!-- Search and Filter Section -->
    <div class="mb-6">
      <!-- Filters -->
      <div class="flex gap-4 mb-4">
        <!-- Year Level Filter -->
        <select
          v-model="selectedYearLevelFilter"
          class="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 hover:border-gray-400 focus:outline-none focus:border-blue-500"
          @change="handleSearch"
        >
          <option value="">All Year Levels</option>
          <option
            v-for="year in yearLevels"
            :key="year.id"
            :value="year.id"
          >
            {{ year.year_level }}
          </option>
        </select>

        <!-- Semester Filter -->
        <select
          v-model="selectedSemesterFilter"
          class="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 hover:border-gray-400 focus:outline-none focus:border-blue-500"
          @change="handleSearch"
        >
          <option value="">All Semesters</option>
          <option
            v-for="semester in semesters"
            :key="semester.id"
            :value="semester.id"
          >
            {{ semester.semester_name }}
          </option>
        </select>

        <!-- Clear Filters Button -->
        <button
          @click="clearFiltersAndSearch"
          class="px-4 py-2 bg-[#1a3047] text-white rounded-md hover:bg-[#2a4057] transition-colors duration-200"
        >
          Clear Filters
        </button>
      </div>

      <!-- Search and Add Button Row -->
      <div class="flex justify-between items-center">
        <form @submit.prevent>
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Search for subjects..." 
            class="bg-[#ffff] p-2 pr-[3rem] text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 border-gray-500 w-[120%]"
            @input="debounceSearch"
          >
        </form>
        <button @click="openModal(null)" 
          class="cursor-pointer bg-[#1a3047] text-[#ffff] font-bold rounded-md pt-2 pb-2 pl-3 pr-3 flex justify-center items-center">
          Add New Subject
        </button>
      </div>
    </div>

    <!-- Reusable Table Component -->
    <div class="overflow-x-auto mt-6">
      <table class="min-w-full divide-y divide-gray-200 ">
        <thead>
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">Year Level</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">Semester</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">Course Code</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">Subject Name</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">Lecture Units</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">Lab Units</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">Total Units</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="curriculum in filteredCurriculums" :key="curriculum.id">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ curriculum.year_level?.year_level }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ curriculum.semester?.semester_name }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ curriculum.course_code }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ curriculum.subject_name }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ curriculum.lecture_units }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ curriculum.lab_units }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ getTotalUnits(curriculum) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <div class="flex space-x-2">
                <button @click="editCurriculum(curriculum)" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">Edit</button>
                <button @click="confirmDelete(curriculum)" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">Delete</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- No data message -->
      <div v-if="!filteredCurriculums.length" class="text-center py-10 bg-gray-100 rounded-lg mt-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
        </svg>
        <p class="text-gray-500 font-medium mt-2">No curriculum data found</p>
        <p class="text-gray-400 text-sm mt-1">Try adjusting your filters or add new subjects</p>
        <button 
          @click="openModal(null)" 
          class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors duration-200"
        >
          Add New Subject
        </button>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="curriculums && curriculums.links && curriculums.links.length > 3" class="mt-6 flex justify-center">
      <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
        <template v-for="(link, index) in curriculums.links" :key="index">
          <a
            v-if="link.url"
            :href="link.url"
            @click.prevent="handlePageChange(link.url)"
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
            :class="{
              'z-10 bg-blue-500 text-white border-blue-500': link.active
            }"
            v-html="link.label"
          ></a>
          <span
            v-else
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500"
            v-html="link.label"
          ></span>
        </template>
      </nav>
    </div>

    <!-- Reusable Modal Component -->
    <ReusableModal :show="isModalOpen" :title="isEditMode ? 'Edit Subject' : 'Add Subject'" :loading="loading"
      :submit-button-text="isEditMode ? 'Edit Subject' : 'Add Subject'" @close="closeModal" @submit="submitForm">
      <!-- Form Grid Container -->
      <div class="grid grid-cols-2 gap-4">
        <!-- Year Level -->
        <div>
          <label for="year_level" class="block text-sm font-medium text-gray-700 mb-1">Year Level</label>
          <select 
            id="year_level"
            v-model="form.year_level_id" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.year_level_id }"
          >
            <option value="" disabled selected>Select Year Level</option>
            <option v-for="year in yearLevels" :key="year.id" :value="year.id">
              {{ year.year_level }}
            </option>
          </select>
          <p v-if="form.errors.year_level_id" class="text-[#FF0000] text-sm mt-1">
            {{ form.errors.year_level_id }}
          </p>
        </div>

        <!-- Semester -->
        <div>
          <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
          <select 
            id="semester"
            v-model="form.semester_id" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.semester_id }"
          >
            <option value="" disabled selected>Select Semester</option>
            <option v-for="semester in semesters" :key="semester.id" :value="semester.id">
              {{ semester.semester_name }}
            </option>
          </select>
          <p v-if="form.errors.semester_id" class="text-[#FF0000] text-sm mt-1">
            {{ form.errors.semester_id }}
          </p>
        </div>

        <!-- Course Code -->
        <div>
          <label for="course_code" class="block text-sm font-medium text-gray-700 mb-1">Course Code</label>
          <input 
            type="text" 
            id="course_code"
            name="course_code" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.course_code }"
            v-model="form.course_code"
          >
          <p v-if="form.errors.course_code" class="text-[#FF0000] text-sm mt-1">
            {{ form.errors.course_code }}
          </p>
        </div>

        <!-- Subject Name -->
        <div>
          <label for="subject_name" class="block text-sm font-medium text-gray-700 mb-1">Subject Name</label>
          <input 
            type="text" 
            id="subject_name"
            name="subject_name" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.subject_name }"
            v-model="form.subject_name"
          >
          <p v-if="form.errors.subject_name" class="text-[#FF0000] text-sm mt-1">
            {{ form.errors.subject_name }}
          </p>
        </div>

        <!-- Description -->
        <div class="col-span-2">
          <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea 
            id="description"
            name="description" 
            class="input-field-add-student min-h-[100px]"
            :class="{ 'border-red-500': form.errors.description }"
            v-model="form.description"
          ></textarea>
          <p v-if="form.errors.description" class="text-[#FF0000] text-sm mt-1">
            {{ form.errors.description }}
          </p>
        </div>

        <!-- Lecture Units -->
        <div>
          <label for="lecture_units" class="block text-sm font-medium text-gray-700 mb-1">Lecture Units</label>
          <input 
            type="text" 
            id="lecture_units"
            name="lecture_units" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.lecture_units }"
            v-model="form.lecture_units"
          >
          <p v-if="form.errors.lecture_units" class="text-[#FF0000] text-sm mt-1">
            {{ form.errors.lecture_units }}
          </p>
        </div>

        <!-- Laboratory Units -->
        <div>
          <label for="lab_units" class="block text-sm font-medium text-gray-700 mb-1">Laboratory Units</label>
          <input 
            type="text" 
            id="lab_units"
            name="lab_units" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.lab_units }"
            v-model="form.lab_units"
          >
          <p v-if="form.errors.lab_units" class="text-[#FF0000] text-sm mt-1">
            {{ form.errors.lab_units }}
          </p>
        </div>

        <!-- Total Units -->
        <div>
          <label for="total_units" class="block text-sm font-medium text-gray-700 mb-1">Total Units</label>
          <input 
            type="text" 
            id="total_units"
            name="total_units" 
            class="input-field-add-student bg-gray-100"
            :class="{ 'border-red-500': form.errors.total_units }"
            v-model="computedTotalUnits"
            readonly
          >
          <p class="text-xs text-gray-500 mt-1">Automatically calculated from lecture and laboratory units</p>
          <p v-if="form.errors.total_units" class="text-[#FF0000] text-sm mt-1">
            {{ form.errors.total_units }}
          </p>
        </div>
      </div>
    </ReusableModal>
  </div>
</template>
