<script setup>
import DashboardLayout from '../../components/AdminDashboardLayout.vue';
import ReusableTable from '../../components/ReusableTable.vue';
import Notification from '../../components/Notification.vue';
import ReusableModal from '../../components/ReusableModal.vue';
import { ref, reactive, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Overlay from '../../components/Overlay.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faXmark } from '@fortawesome/free-solid-svg-icons';
import axios from 'axios';
import Swal from 'sweetalert2';

library.add(faXmark);

defineOptions({
  layout: DashboardLayout
});

const props = defineProps({
  title: String,
  teachers: {
    type: Array,
    default: () => []
  },
  sections: Array,
  yearLevels: Array,
  curricula: Array,
  schoolYears: {
    type: Array,
    default: () => []
  },
  semesters: Array,
  auth: {
    type: Object,
    required: true
  }
});

console.log(props.schoolYears);


const headerContent = computed(() => [
  {
    title: props.title || "Admin Portal",
    name: props.auth?.user?.admin?.first_name + " " + props.auth?.user?.admin?.last_name || "Admin",
    userType: "Admin"
  }
]);

const searchQuery = ref('');
const filterSchoolYear = ref('');

// Add computed property for filtered admins
const filteredAdmins = computed(() => {
  let filtered = props.teachers;
  
  // Apply school year filter
  if (filterSchoolYear.value) {
    filtered = filtered.filter(admin => admin.school_year_id == filterSchoolYear.value);
  }
  
  // Apply search query filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(admin => {
      return (
        admin.first_name?.toLowerCase().includes(query) ||
        admin.last_name?.toLowerCase().includes(query) ||
        admin.user?.email?.toLowerCase().includes(query) ||
        admin.phone_number?.toLowerCase().includes(query)
      );
    });
  }
  
  return filtered;
});

const selectedFaculty = ref(null);

const showFacultyPreview = (admin) => {
  selectedFaculty.value = admin;
};

// Table headers
const tableHeaders = [
  { key: 'first_name', label: 'First Name' },
  { key: 'last_name', label: 'Last Name' },
  { key: 'user.email', label: 'Email Address' },
  { key: 'phone_number', label: 'Phone Number' }
];

// Action buttons for the table
const actionButtons = [
  {
    label: 'View',
    class: 'bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded cursor-pointer mr-2',
    action: (faculty) => showFacultyPreview(faculty)
  },
  {
    label: 'Edit',
    class: 'bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded cursor-pointer mr-2',
    action: (faculty) => {
      selectedFaculty.value = faculty;
      openModal(faculty);
    }
  },
  {
    label: 'Delete',
    class: 'bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded cursor-pointer',
    action: (faculty) => deleteFaculty(faculty)
  }
];

const isModalOpen = ref(false);
const isEditMode = ref(false);
const loading = ref(false);

const form = useForm({
  first_name: '',
  middle_name: '',
  last_name: '',
  phone_number: '',
  address: '',
  email: '',
  school_year_id: ''
});

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

const activeSchoolYear = computed(() => {
  return props.schoolYears?.find(status => status.school_year_status?.toLowerCase() === 'active');
});
const activeSchoolYearId = computed(() => activeSchoolYear.value?.id);

const openModal = (faculty = null) => {
  if (faculty) {
    isEditMode.value = true;
    form.first_name = faculty.first_name;
    form.middle_name = faculty.middle_name;
    form.last_name = faculty.last_name;
    form.phone_number = faculty.phone_number;
    form.address = faculty.address;
    form.email = faculty.user?.email || '';
    form.school_year_id = activeSchoolYearId.value;
  } else {
    isEditMode.value = false;
    form.reset();
    form.school_year_id = activeSchoolYearId.value;
  }
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  isEditMode.value = false;
  form.reset();
  form.clearErrors();
};

const submitForm = () => {
  loading.value = true;
  if (isEditMode.value) {
    // Update existing faculty
    form.put(route('admin.update.teacher', selectedFaculty.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        showNotification('Faculty updated successfully');
        closeModal();
      },
      onError: (errors) => {
        console.error("Validation Errors:", errors);
        showNotification('Failed to update faculty', 'error');
      },
      onFinish: () => {
        loading.value = false;
      }
    });
  } else {
    // Add new faculty
    form.post(route('admin.add.teacher'), {
      preserveScroll: true,
      onSuccess: () => {
        showNotification('Faculty added successfully');
        closeModal();
      },
      onError: (errors) => {
        console.error("Validation Errors:", errors);
        showNotification('Failed to add faculty', 'error');
      },
      onFinish: () => {
        loading.value = false;
      }
    });
  }
};

const deleteFaculty = (faculty) => {
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
      form.delete(route('admin.destroy.teacher', faculty.id), {
        preserveScroll: true,
        onSuccess: () => {
          if (selectedFaculty.value?.id === faculty.id) {
            selectedFaculty.value = null; // Clear the preview if the deleted faculty was selected
          }
          showNotification('Faculty deleted successfully');
        },
        onError: () => {
          showNotification('Failed to delete faculty', 'error');
        }
      });
    }
  });
};

// Add these to the script setup section after the existing imports
const isScheduleModalOpen = ref(false);
const scheduleForm = useForm({
  teacher_id: '',
  curriculum_id: '',
  section_id: '',
  year_level_id: '',
  semester_id: '',
  day: '',
  start_time: '',
  end_time: ''
});

const openScheduleModal = (faculty) => {
  scheduleForm.teacher_id = faculty.id;
  isScheduleModalOpen.value = true;
};

const closeScheduleModal = () => {
  isScheduleModalOpen.value = false;
  scheduleForm.reset();
  scheduleForm.clearErrors();
};

const submitScheduleForm = () => {
  loading.value = true;
  
  // Submit the faculty load directly with all data
  scheduleForm.post('/admin/faculty/load', {
    preserveScroll: true,
    onSuccess: (response) => {
      const updatedFaculty = response?.props?.teachers?.find(
        teacher => teacher.id === selectedFaculty.value.id
      );
      
      if (updatedFaculty) {
        selectedFaculty.value = JSON.parse(JSON.stringify(updatedFaculty));
      }
      
      showNotification('Schedule assigned successfully');
      closeScheduleModal();
    },
    onError: (errors) => {
      console.error("Validation Errors:", errors);
      if (errors.message) {
        // Display server-side schedule conflict error
        showNotification(errors.message, 'error');
      } else {
        showNotification('Failed to assign schedule', 'error');
      }
    },
    onFinish: () => {
      loading.value = false;
    }
  });
};

// Add a watch for selectedFaculty to debug updates
watch(selectedFaculty, (newValue) => {
  console.log('Selected Faculty Updated:', newValue);
}, { deep: true });

// Computed property for filtered sections
const filteredSections = computed(() => {
  if (!scheduleForm.year_level_id) return [];
  if (scheduleForm.year_level_id === 'all') return props.sections;
  
  return props.sections.filter(section => 
    section.year_level_id === parseInt(scheduleForm.year_level_id)
  );
});

// Reset section when year level changes
watch(() => scheduleForm.year_level_id, (newYearLevel) => {
  scheduleForm.section_id = ''; // Clear section selection when year level changes
});

// Computed property for filtered curricula
const filteredCurricula = computed(() => {
  if (!scheduleForm.year_level_id || !scheduleForm.semester_id) {
    console.log('No year level or semester selected');
    return [];
  }
  
  const yearLevelId = parseInt(scheduleForm.year_level_id);
  const semesterId = parseInt(scheduleForm.semester_id);
  
  console.log('Filtering curricula:', {
    yearLevelId,
    semesterId,
    totalCurricula: props.curricula.length
  });

  const filtered = props.curricula.filter(curriculum => {
    const currYearLevelId = parseInt(curriculum.year_level_id);
    const currSemesterId = parseInt(curriculum.semester_id);
    
    console.log('Checking curriculum:', {
      id: curriculum.id,
      name: curriculum.subject_name,
      yearLevel: { curr: currYearLevelId, form: yearLevelId, match: currYearLevelId === yearLevelId },
      semester: { curr: currSemesterId, form: semesterId, match: currSemesterId === semesterId }
    });
    
    return currYearLevelId === yearLevelId && currSemesterId === semesterId;
  });

  console.log('Filtered curricula:', {
    count: filtered.length,
    items: filtered.map(c => ({ id: c.id, name: c.subject_name }))
  });

  return filtered;
});

// Reset curriculum when year level or semester changes
watch([() => scheduleForm.year_level_id, () => scheduleForm.semester_id], ([newYearLevel, newSemester]) => {
  console.log('Year Level:', newYearLevel);
  console.log('Semester:', newSemester);
  console.log('Available Curricula:', props.curricula);
  console.log('Filtered Curricula:', filteredCurricula.value);
  scheduleForm.curriculum_id = ''; // Clear curriculum selection when year level or semester changes
});

const formatTime = (time) => {
  if (!time) return '';
  try {
    const [hours, minutes] = time.split(':');
    const date = new Date();
    date.setHours(parseInt(hours));
    date.setMinutes(parseInt(minutes));
    return date.toLocaleTimeString('en-US', { 
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    });
  } catch (e) {
    return time;
  }
};

const getSemesterName = (semesterId) => {
  const semester = props.semesters?.find(s => s.id === semesterId);
  return semester ? semester.semester_name : 'N/A';
};

const isEditLoadModalOpen = ref(false);
const selectedLoad = ref(null);

const openEditLoadModal = (load) => {
  selectedLoad.value = load;
  scheduleForm.teacher_id = load.teacher_id;
  scheduleForm.curriculum_id = load.curriculum_id;
  scheduleForm.section_id = load.section_id;
  scheduleForm.year_level_id = load.year_level_id;
  scheduleForm.semester_id = load.semester_id;
  scheduleForm.day = load.schedule?.day || '';
  scheduleForm.start_time = load.schedule?.start_time || '';
  scheduleForm.end_time = load.schedule?.end_time || '';
  isEditLoadModalOpen.value = true;
};

const closeEditLoadModal = () => {
  isEditLoadModalOpen.value = false;
  selectedLoad.value = null;
  scheduleForm.reset();
  scheduleForm.clearErrors();
};

const submitEditLoadForm = () => {
  if (!selectedLoad.value) return;
  
  loading.value = true;
  scheduleForm.put(`/admin/faculty/load/${selectedLoad.value.id}`, {
    preserveScroll: true,
    onSuccess: (response) => {
      const updatedFaculty = response?.props?.teachers?.find(
        teacher => teacher.id === selectedFaculty.value.id
      );
      
      if (updatedFaculty) {
        selectedFaculty.value = JSON.parse(JSON.stringify(updatedFaculty));
      }
      
      showNotification('Schedule updated successfully');
      closeEditLoadModal();
    },
    onError: (errors) => {
      console.error("Validation Errors:", errors);
      if (errors.message) {
        // Display server-side schedule conflict error
        showNotification(errors.message, 'error');
      } else {
        showNotification('Failed to update schedule', 'error');
      }
    },
    onFinish: () => {
      loading.value = false;
    }
  });
};

const deleteSchedule = (load) => {
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
      router.delete(`/admin/faculty/load/${load.id}`, {
        preserveScroll: true,
        onSuccess: (response) => {
          const updatedFaculty = response?.props?.teachers?.find(
            teacher => teacher.id === selectedFaculty.value.id
          );
          
          if (updatedFaculty) {
            selectedFaculty.value = JSON.parse(JSON.stringify(updatedFaculty));
          }
          
          showNotification('Schedule deleted successfully');
        },
        onError: () => {
          showNotification('Failed to delete schedule', 'error');
        }
      });
    }
  });
};

const editFaculty = (faculty) => {
  showEditModal.value = true;
  editedFaculty.value = { ...faculty };
};

const handleEditSubmit = () => {
  router.put(`/admin/faculty/${editedFaculty.value.id}`, editedFaculty.value, {
    onSuccess: (response) => {
      // Update the faculty in the admins array
      const index = props.teachers.findIndex(admin => admin.id === editedFaculty.value.id);
      if (index !== -1) {
        props.teachers[index] = response.props.admin;
        // If this is the currently selected faculty, update the preview
        if (selectedFaculty.value && selectedFaculty.value.id === editedFaculty.value.id) {
          selectedFaculty.value = response.props.admin;
        }
      }
      showEditModal.value = false;
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'Faculty information updated successfully',
        timer: 1500,
        showConfirmButton: false
      });
    },
    onError: (errors) => {
      console.error(errors);
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: 'Failed to update faculty information',
      });
    }
  });
};

// Update the viewFaculty function to use the latest data
const viewFaculty = (faculty) => {
  selectedFaculty.value = props.teachers.find(admin => admin.id === faculty.id) || faculty;
};

// Filter State
const filterSemester = ref('');
const filterSection = ref('');
const filterYearLevel = ref('');

// Computed Property for Filtered Faculty Loads
const filteredFacultyLoads = computed(() => {
  if (!selectedFaculty.value?.faculty_loads) {
    return [];
  }

  return selectedFaculty.value.faculty_loads.filter(load => {
    const semesterMatch = !filterSemester.value || load.semester_id == filterSemester.value; // Loose compare
    const yearLevelMatch = !filterYearLevel.value || load.year_level_id == filterYearLevel.value; // Loose compare
    const sectionMatch = !filterSection.value || load.section_id == filterSection.value; // Loose compare
    
    return semesterMatch && yearLevelMatch && sectionMatch;
  });
});
</script>

<template>
  <Head title="Faculty" />
  <div class="flex flex-col gap-6 p-6">
    <Teleport to="body">
      <Notification 
        :show="notification.show" 
        :message="notification.message" 
        :type="notification.type" 
      />
    </Teleport>

    <Overlay :show="isModalOpen" @click="closeModal" />

    <!-- Top Section - Faculty Table -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-[#1a3047]">Faculty List</h2>
        <button 
          @click="openModal()"
          class="bg-[#1a3047] text-white px-4 py-2 rounded-md hover:bg-[#2a4057] transition-colors"
        >
          Add Faculty
        </button>
      </div>

      <!-- Search and Filter Section -->
      <div class="mb-6 flex justify-start gap-4">
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Search faculty..." 
          class="w-1/3 p-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#1a3047]"
        >
        <select 
          v-model="filterSchoolYear"
          class="w-1/3 p-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#1a3047]"
        >
          <option value="">All School Years</option>
          <option v-for="year in schoolYears" :key="year.id" :value="year.id">
            {{ year.school_year }} {{ year.school_year_status === 'Active' ? '(Active)' : '' }}
          </option>
        </select>
      </div>

      <!-- Faculty Table -->
      <ReusableTable 
        :headers="tableHeaders"
        :data="filteredAdmins"
        :actions="true"
        :action-buttons="actionButtons"
      />
    </div>

    <!-- Bottom Section - Faculty Preview -->
    <div v-if="selectedFaculty" class="bg-white rounded-lg shadow p-6 mb-6">
      <h2 class="text-2xl font-bold text-[#1a3047] mb-6">Faculty Preview</h2>
      
      <div class="grid grid-cols-2 gap-8 mb-6">
        <div class="flex items-center gap-6">
          <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center">
            <svg class="w-12 h-12 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" />
            </svg>
          </div>
          <div class="space-y-4">
            <div>
              <p class="text-gray-600">Full Name</p>
              <p class="font-medium">{{ selectedFaculty.first_name }} {{ selectedFaculty.middle_name }} {{ selectedFaculty.last_name }}</p>
            </div>
            <div>
              <p class="text-gray-600">Phone Number</p>
              <p class="font-medium">{{ selectedFaculty.phone_number }}</p>
            </div>
          </div>
          <div class="space-y-4">
            <div>
              <p class="text-gray-600">Email Address</p>
              <p class="font-medium">{{ selectedFaculty.user.email }}</p>
            </div>
            <div>
              <p class="text-gray-600">Gender</p>
              <p class="font-medium">{{ selectedFaculty.gender }}</p>
            </div>
          </div>
        </div>
        <div class="flex justify-end">
          <button @click="openScheduleModal(selectedFaculty)" class="bg-[#1a3047] text-white px-3 py-1.5 text-sm rounded hover:bg-[#2a4057] transition-colors">
            Assign Schedule
          </button>
        </div>
      </div>

      <!-- Teaching Load Section -->
      <div class="mt-8">
        <h3 class="text-lg font-semibold text-[#1a3047] mb-4">Teaching Load</h3>

        <!-- Filter Dropdowns -->
        <div class="flex gap-4 mb-4 flex-wrap">
          <!-- Semester Filter -->
          <div class="flex-1 min-w-[150px]">
            <label for="filterSemester" class="block text-sm font-medium text-gray-700 mb-1">Filter by Semester</label>
            <select 
              id="filterSemester"
              v-model="filterSemester"
              class="w-full bg-white p-2 text-sm rounded border border-gray-300 focus:outline-none focus:border-blue-500"
            >
              <option value="">All Semesters</option>
              <option v-for="semester in semesters" :key="semester.id" :value="semester.id">
                {{ semester.semester_name }}
              </option>
            </select>
          </div>

          <!-- Year Level Filter -->
          <div class="flex-1 min-w-[150px]">
            <label for="filterYearLevel" class="block text-sm font-medium text-gray-700 mb-1">Filter by Year Level</label>
            <select 
              id="filterYearLevel"
              v-model="filterYearLevel"
              class="w-full bg-white p-2 text-sm rounded border border-gray-300 focus:outline-none focus:border-blue-500"
            >
              <option value="">All Year Levels</option>
              <option v-for="year in yearLevels" :key="year.id" :value="year.id">
                {{ year.year_level }}
              </option>
            </select>
          </div>

          <!-- Section Filter -->
          <div class="flex-1 min-w-[150px]">
            <label for="filterSection" class="block text-sm font-medium text-gray-700 mb-1">Filter by Section</label>
            <select 
              id="filterSection"
              v-model="filterSection"
              class="w-full bg-white p-2 text-sm rounded border border-gray-300 focus:outline-none focus:border-blue-500"
            >
              <option value="">All Sections</option>
              <option v-for="section in sections" :key="section.id" :value="section.id">
                {{ section.section }} ({{ section.year_level }})
              </option>
            </select>
          </div>
        </div>

        <!-- Teaching Load Display (Using filtered list) -->
        <div v-if="filteredFacultyLoads.length > 0" 
             class="grid grid-cols-1 gap-4">
          <div v-for="load in filteredFacultyLoads" 
               :key="load.id" 
               class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <!-- Existing Load Details Display Structure -->
            <div class="flex justify-between mb-4">
              <h4 class="font-medium text-gray-900">Teaching Load Details</h4>
              <div class="flex gap-2">
                <button 
                  @click="openEditLoadModal(load)"
                  class="bg-[#559de6] text-white px-3 py-1 text-sm rounded hover:bg-[#4589d1] transition-colors"
                >
                  Edit Schedule
                </button>
                <button 
                  @click="deleteSchedule(load)"
                  class="bg-red-500 text-white px-3 py-1 text-sm rounded hover:bg-red-600 transition-colors"
                >
                  Delete
                </button>
              </div>
            </div>
            <div class="grid grid-cols-3 gap-6">
              <!-- Subject Info -->
              <div>
                <h4 class="font-medium text-gray-900 mb-3">Subject Details</h4>
                <div class="space-y-3">
                  <div>
                    <p class="text-sm text-gray-500">Subject Name</p>
                    <p class="font-medium">{{ load.curriculum?.subject_name }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Course Code</p>
                    <p class="font-medium">{{ load.curriculum?.course_code }}</p>
                  </div>
                </div>
              </div>
              <!-- Class Info -->
              <div>
                <h4 class="font-medium text-gray-900 mb-3">Class Details</h4>
                <div class="space-y-3">
                  <div>
                    <p class="text-sm text-gray-500">Section</p>
                    <p class="font-medium">{{ load.section?.section }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Year Level</p>
                    <p class="font-medium">{{ load.year_level?.year_level }}</p>
                  </div>
                </div>
              </div>
              <!-- Schedule Info -->
              <div>
                <h4 class="font-medium text-gray-900 mb-3">Schedule Details</h4>
                <div class="space-y-3">
                  <div>
                    <p class="text-sm text-gray-500">Day</p>
                    <p class="font-medium">{{ load.schedule?.day }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Time</p>
                    <p class="font-medium" v-if="load.schedule?.start_time && load.schedule?.end_time">
                      {{ formatTime(load.schedule.start_time) }} - {{ formatTime(load.schedule.end_time) }}
                    </p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Semester</p>
                    <p class="font-medium">{{ getSemesterName(load.semester_id) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="flex items-center justify-center h-32 text-gray-500 bg-gray-50 rounded-lg">
          <p>No teaching load assigned yet or matches current filters.</p>
        </div>
      </div>
    </div>

    <!-- Add/Edit Faculty Modal -->
    <ReusableModal 
      :show="isModalOpen"
      :title="isEditMode ? 'Edit Faculty' : 'Add Faculty'"
      :loading="loading"
      :submit-button-text="isEditMode ? 'Update Faculty' : 'Add Faculty'"
      @close="closeModal"
      @submit="submitForm"
    >
      <template #header>
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ isEditMode ? 'Edit Faculty' : 'Add Faculty' }}
          </h3>
          <button
            @click="closeModal"
            class="text-gray-400 hover:text-gray-500 focus:outline-none"
          >
            <font-awesome-icon :icon="['fas', 'xmark']" class="text-xl" />
          </button>
        </div>
      </template>

      <!-- Form Grid Container -->
      <div class="mb-4 bg-blue-50 p-3 rounded-lg text-sm text-blue-700">
        <p>Note: A password will be automatically generated using the teacher's first and last name. For example, for "John Doe", the password will be "johndoe".</p>
      </div>
      
      <div class="grid grid-cols-3 gap-4">
        <!-- First Row -->
        <div>
          <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
          <input 
            id="first_name"
            type="text" 
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': form.errors.first_name }"
            v-model="form.first_name"
          >
          <p v-if="form.errors.first_name" class="text-red-500 text-sm mt-1">
            {{ form.errors.first_name }}
          </p>
        </div>

        <div>
          <label for="middle_name" class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
          <input 
            id="middle_name"
            type="text" 
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': form.errors.middle_name }"
            v-model="form.middle_name"
          >
          <p v-if="form.errors.middle_name" class="text-red-500 text-sm mt-1">
            {{ form.errors.middle_name }}
          </p>
        </div>

        <div>
          <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
          <input 
            id="last_name"
            type="text" 
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': form.errors.last_name }"
            v-model="form.last_name"
          >
          <p v-if="form.errors.last_name" class="text-red-500 text-sm mt-1">
            {{ form.errors.last_name }}
          </p>
        </div>

        <!-- Second Row -->
        <div>
          <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
          <input 
            id="phone_number"
            type="tel" 
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': form.errors.phone_number }"
            v-model="form.phone_number"
          >
          <p v-if="form.errors.phone_number" class="text-red-500 text-sm mt-1">
            {{ form.errors.phone_number }}
          </p>
        </div>

        <!-- School Year -->
        <div v-if="isEditMode">
          <label for="school_year" class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
          <select 
            id="school_year"
            v-model="form.school_year_id"
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': form.errors.school_year_id }"
          >
            <option value="" disabled selected>Select School Year</option>
            <option v-for="year in schoolYears" :key="year.id" :value="year.id" 
                   :class="{'bg-blue-100 font-semibold': year.school_year_status === 'Active'}">
              {{ year.school_year }} {{ year.school_year_status === 'Active' ? '(Active)' : '' }}
            </option>
          </select>
          <p v-if="form.errors.school_year_id" class="text-red-500 text-sm mt-1">
            {{ form.errors.school_year_id }}
          </p>
        </div>

         <div v-else>
          <label for="school_year_status" class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
          <input 
            type="text" 
            id="school_year_status" 
            :value="activeSchoolYear?.school_year || 'N/A'" 
            class="input-field-add-student bg-gray-200 cursor-not-allowed" 
            readonly 
          />
          <!-- Hidden input to actually submit the ID -->
          <!-- <input type="hidden" name="student_status_id" v-model="form.student_status_id">  -->
        </div>

        <div>
          <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
          <input 
            id="address"
            type="text" 
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': form.errors.address }"
            v-model="form.address"
          >
          <p v-if="form.errors.address" class="text-red-500 text-sm mt-1">
            {{ form.errors.address }}
          </p>
        </div>

        <!-- Third Row -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input 
            id="email"
            type="email" 
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': form.errors.email }"
            v-model="form.email"
          >
          <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">
            {{ form.errors.email }}
          </p>
        </div>
      </div>
    </ReusableModal>

    <!-- Assign Schedule Modal -->
    <ReusableModal 
      :show="isScheduleModalOpen"
      title="Assign Schedule"
      :loading="loading"
      submit-button-text="Assign Schedule"
      @close="closeScheduleModal"
      @submit="submitScheduleForm"
    >
      <template #header>
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-900">Assign Schedule</h3>
          <button
            @click="closeScheduleModal"
            class="text-gray-400 hover:text-gray-500 focus:outline-none"
          >
            <font-awesome-icon :icon="['fas', 'xmark']" class="text-xl" />
          </button>
        </div>
      </template>

      <!-- Schedule Form -->
      <div class="grid grid-cols-2 gap-4">
        <!-- Year Level -->
        <div>
          <label for="year_level" class="block text-sm font-medium text-gray-700 mb-1">Year Level</label>
          <select 
            id="year_level"
            v-model="scheduleForm.year_level_id"
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': scheduleForm.errors.year_level_id }"
          >
            <option value="" disabled selected>Select Year Level</option>
            <option v-for="yearLevel in yearLevels" :key="yearLevel.id" :value="yearLevel.id">
              {{ yearLevel.year_level }}
            </option>
          </select>
          <p v-if="scheduleForm.errors.year_level_id" class="text-red-500 text-sm mt-1">
            {{ scheduleForm.errors.year_level_id }}
          </p>
        </div>

        <!-- Section -->
        <div>
          <label for="section" class="block text-sm font-medium text-gray-700 mb-1">Section</label>
          <select 
            id="section"
            v-model="scheduleForm.section_id"
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': scheduleForm.errors.section_id }"
          >
            <option value="" disabled selected>Select Section</option>
            <option v-for="section in filteredSections" :key="section.id" :value="section.id">
              {{ section.section }}
            </option>
          </select>
          <p v-if="scheduleForm.errors.section_id" class="text-red-500 text-sm mt-1">
            {{ scheduleForm.errors.section_id }}
          </p>
        </div>

        <!-- Semester -->
        <div>
          <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
          <select 
            id="semester"
            v-model="scheduleForm.semester_id"
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': scheduleForm.errors.semester_id }"
          >
            <option value="" disabled selected>Select Semester</option>
            <option value="1">First Semester</option>
            <option value="2">Second Semester</option>
          </select>
          <p v-if="scheduleForm.errors.semester_id" class="text-red-500 text-sm mt-1">
            {{ scheduleForm.errors.semester_id }}
          </p>
        </div>

        <!-- Subject/Curriculum -->
        <div>
          <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
          <select 
            id="subject"
            v-model="scheduleForm.curriculum_id"
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': scheduleForm.errors.curriculum_id }"
          >
            <option value="" disabled selected>Select Subject</option>
            <option v-for="curriculum in filteredCurricula" :key="curriculum.id" :value="curriculum.id">
              {{ curriculum.course_code }} - {{ curriculum.subject_name }}
            </option>
          </select>
          <p v-if="scheduleForm.errors.curriculum_id" class="text-red-500 text-sm mt-1">
            {{ scheduleForm.errors.curriculum_id }}
          </p>
        </div>

        <!-- Rest of the form fields -->
        <div>
          <label for="day" class="block text-sm font-medium text-gray-700 mb-1">Day</label>
          <select 
            id="day"
            v-model="scheduleForm.day"
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': scheduleForm.errors.day }"
          >
            <option value="" disabled selected>Select Day</option>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
          </select>
          <p v-if="scheduleForm.errors.day" class="text-red-500 text-sm mt-1">
            {{ scheduleForm.errors.day }}
          </p>
        </div>

        <div>
          <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
          <input 
            id="start_time"
            type="time"
            v-model="scheduleForm.start_time"
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': scheduleForm.errors.start_time }"
          >
          <p v-if="scheduleForm.errors.start_time" class="text-red-500 text-sm mt-1">
            {{ scheduleForm.errors.start_time }}
          </p>
        </div>

        <div>
          <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
          <input 
            id="end_time"
            type="time"
            v-model="scheduleForm.end_time"
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': scheduleForm.errors.end_time }"
          >
          <p v-if="scheduleForm.errors.end_time" class="text-red-500 text-sm mt-1">
            {{ scheduleForm.errors.end_time }}
          </p>
        </div>
      </div>
    </ReusableModal>

    <!-- Add the Edit Load Modal -->
    <ReusableModal 
      :show="isEditLoadModalOpen"
      title="Edit Schedule"
      :loading="loading"
      submit-button-text="Update Schedule"
      @close="closeEditLoadModal"
      @submit="submitEditLoadForm"
    >
      <template #header>
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-900">Edit Schedule</h3>
          <button
            @click="closeEditLoadModal"
            class="text-gray-400 hover:text-gray-500 focus:outline-none"
          >
            <font-awesome-icon :icon="['fas', 'xmark']" class="text-xl" />
          </button>
        </div>
      </template>

      <!-- Schedule Form -->
      <div class="grid grid-cols-2 gap-4">
        <!-- Year Level -->
        <div>
          <label for="edit_year_level" class="block text-sm font-medium text-gray-700 mb-1">Year Level</label>
          <select 
            id="edit_year_level"
            v-model="scheduleForm.year_level_id"
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': scheduleForm.errors.year_level_id }"
          >
            <option value="" disabled selected>Select Year Level</option>
            <option v-for="yearLevel in yearLevels" :key="yearLevel.id" :value="yearLevel.id">
              {{ yearLevel.year_level }}
            </option>
          </select>
          <p v-if="scheduleForm.errors.year_level_id" class="text-red-500 text-sm mt-1">
            {{ scheduleForm.errors.year_level_id }}
          </p>
        </div>

        <!-- Section -->
        <div>
          <label for="edit_section" class="block text-sm font-medium text-gray-700 mb-1">Section</label>
          <select 
            id="edit_section"
            v-model="scheduleForm.section_id"
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': scheduleForm.errors.section_id }"
          >
            <option value="" disabled selected>Select Section</option>
            <option v-for="section in filteredSections" :key="section.id" :value="section.id">
              {{ section.section }}
            </option>
          </select>
          <p v-if="scheduleForm.errors.section_id" class="text-red-500 text-sm mt-1">
            {{ scheduleForm.errors.section_id }}
          </p>
        </div>

        <!-- Semester -->
        <div>
          <label for="edit_semester" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
          <select 
            id="edit_semester"
            v-model="scheduleForm.semester_id"
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': scheduleForm.errors.semester_id }"
          >
            <option value="" disabled selected>Select Semester</option>
            <option value="1">First Semester</option>
            <option value="2">Second Semester</option>
          </select>
          <p v-if="scheduleForm.errors.semester_id" class="text-red-500 text-sm mt-1">
            {{ scheduleForm.errors.semester_id }}
          </p>
        </div>

        <!-- Subject/Curriculum -->
        <div>
          <label for="edit_subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
          <select 
            id="edit_subject"
            v-model="scheduleForm.curriculum_id"
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': scheduleForm.errors.curriculum_id }"
          >
            <option value="" disabled selected>Select Subject</option>
            <option v-for="curriculum in filteredCurricula" :key="curriculum.id" :value="curriculum.id">
              {{ curriculum.course_code }} - {{ curriculum.subject_name }}
            </option>
          </select>
          <p v-if="scheduleForm.errors.curriculum_id" class="text-red-500 text-sm mt-1">
            {{ scheduleForm.errors.curriculum_id }}
          </p>
        </div>

        <!-- Day -->
        <div>
          <label for="edit_day" class="block text-sm font-medium text-gray-700 mb-1">Day</label>
          <select 
            id="edit_day"
            v-model="scheduleForm.day"
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': scheduleForm.errors.day }"
          >
            <option value="" disabled selected>Select Day</option>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
          </select>
          <p v-if="scheduleForm.errors.day" class="text-red-500 text-sm mt-1">
            {{ scheduleForm.errors.day }}
          </p>
        </div>

        <!-- Start Time -->
        <div>
          <label for="edit_start_time" class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
          <input 
            id="edit_start_time"
            type="time"
            v-model="scheduleForm.start_time"
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': scheduleForm.errors.start_time }"
          >
          <p v-if="scheduleForm.errors.start_time" class="text-red-500 text-sm mt-1">
            {{ scheduleForm.errors.start_time }}
          </p>
        </div>

        <!-- End Time -->
        <div>
          <label for="edit_end_time" class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
          <input 
            id="edit_end_time"
            type="time"
            v-model="scheduleForm.end_time"
            class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': scheduleForm.errors.end_time }"
          >
          <p v-if="scheduleForm.errors.end_time" class="text-red-500 text-sm mt-1">
            {{ scheduleForm.errors.end_time }}
          </p>
        </div>
      </div>
    </ReusableModal>
  </div>
</template>
