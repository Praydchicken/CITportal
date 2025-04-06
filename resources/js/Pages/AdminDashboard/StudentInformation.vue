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
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import Swal from 'sweetalert2';

library.add(faXmark);

defineOptions({
  layout: DashboardLayout // ✅ Assign the layout
});

const props = defineProps({
  title: String,
  students: Array,
  sections: Array,
  yearLevels: Array,
  studentStatuses: Array,
  activeSchoolYear: {
    type: Object,
    required: true
  },
  schoolYears: {
    type: Array,
    required: true
  }
});

const sections = ref([
  { id: 1, section: 'A' },
  { id: 2, section: 'B' },
  { id: 3, section: 'C' },
  { id: 4, section: 'D' }
]);
const yearLevels = ref(props.yearLevels);
const students = ref(props.students);
const isEditMode = ref(false);
const selectedStudent = ref(null);
const loading = ref(false);
const searchQuery = ref('');
const selectedSchoolYear = ref('');
const selectedSection = ref('');

// Watch for props.students changes and maintain the order
watch(() => props.students, (newStudents) => {
  if (newStudents) {
    students.value = newStudents;
  }
});

const isModalOPen = ref(false);

const openModal = (student) => {
  if (student) {
    isEditMode.value = true; // We're editing
    selectedStudent.value = student;

    // Pre-fill the form with student data
    form.student_number = student.student_number;
    form.first_name = student.first_name;
    form.middle_name = student.middle_name;
    form.last_name = student.last_name;
    form.section_id = student.section?.id || "";
    form.year_level_id = student.year_level?.id || "";
    form.phone_number = student.phone_number;
    form.gender = student.gender;
    form.address = student.address;
    form.enrollment_date = student.enrollment_date;
    form.student_status_id = student.status?.id || "";
    form.email = student.user?.email || "";
    form.password = ""; // Keep password empty for security
  } else {
    isEditMode.value = false; // Adding new student
    form.reset(); // Clear form
  }

  isModalOPen.value = true; // Open the modal
};


const closeModal = () => {
  isModalOPen.value = false;
  selectedStudent.value = null;
  isEditMode.value = false;
  form.reset();  // Reset form data
  form.clearErrors();  // Clear form errors
};


const form = useForm({
  student_number: null,
  first_name: null,
  middle_name: null,
  last_name: null,
  section_id: "",
  year_level_id: "",
  phone_number: null,
  gender: "",
  address: null,
  enrollment_date: null,
  student_status_id: "",
  email: null,
  password: null,
  remember: false,
})

// Create a reactive object for the notification state
const notification = reactive({
  show: false,
  message: '',
  type: 'success'
});

// Function to show the notification
const showNotification = (message, type = 'success') => {
  notification.show = false; // Reset first
  setTimeout(() => {
    notification.message = message;
    notification.type = type;
    notification.show = true;
  }, 50);

  // Auto-hide notification after 2.5s
  setTimeout(() => {
    notification.show = false;
  }, 2500);
};

const submitForm = () => {
  loading.value = true;
  if (isEditMode.value && selectedStudent.value) {
    // Update existing student
    form.put(`/student/${selectedStudent.value.id}/update`, {
      preserveScroll: true,
      onSuccess: (page) => {
        // Find and update the edited student
        const editedStudentIndex = students.value.findIndex(s => s.id === selectedStudent.value.id);
        if (editedStudentIndex !== -1) {
          const updatedStudent = page.props.student || {
            ...students.value[editedStudentIndex],
            ...form._data
          };
          // Remove the student from current position and add to top
          students.value.splice(editedStudentIndex, 1);
          students.value.unshift(updatedStudent);
          showNotification('Student updated successfully');
        }
        form.reset();
        closeModal();
      },
      onError: (errors) => {
        console.log("Validation Errors:", errors);
        showNotification('Failed to update student', 'error');
      },
      onFinish: () => {
        loading.value = false;
      }
    });
  } else {
    // Add new student
    form.post('/student/addInfo', {
      preserveScroll: true,
      onSuccess: (page) => {
        // Show success notification
        showNotification('Student added successfully');
        
        // Add the new student to the beginning of the list
        if (page?.props?.student) {
          // Remove any existing entry with the same ID if it exists
          const existingIndex = students.value.findIndex(s => s.id === page.props.student.id);
          if (existingIndex !== -1) {
            students.value.splice(existingIndex, 1);
          }
          // Add the new student at the beginning
          students.value.unshift(page.props.student);
        }
        
        // Close modal and reset form
        form.reset();
        closeModal();
      },
      onError: (errors) => {
        console.log("Validation Errors:", errors);
        const errorMessage = errors.message || Object.values(errors)[0] || 'Failed to add student';
        showNotification(errorMessage, 'error');
      },
      onFinish: () => {
        loading.value = false;
      }
    });
  }
};

const deleteStudent = (id) => {
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
            router.delete(`/student/${id}/delete`, {
                preserveScroll: true,
                onSuccess: () => {
                    // Remove student from the list
                    const index = students.value.findIndex(s => s.id === id);
                    if (index !== -1) {
                        students.value.splice(index, 1);
                    }
                    showNotification('Student deleted successfully');
                },
                onError: (errors) => {
                    console.log("Error deleting student:", errors);
                    showNotification('Failed to delete student', 'error');
                },
            });
        }
    });
};

const tableHeaders = [
  { key: 'student_number', label: 'Student No.' },
  { key: 'user.email', label: 'Email Address' },
  { key: 'first_name', label: 'First Name' },
  { key: 'middle_name', label: 'Middle Name' },
  { key: 'last_name', label: 'Last Name' },
  { key: 'section.section', label: 'Section' },
  { key: 'year_level.year_level', label: 'Year Level' },
  { key: 'status.status_name', label: 'Status' }
];

const actionButtons = [
  {
    label: 'Edit',
    class: 'bg-[#559de6] text-white px-3 py-1 rounded cursor-pointer',
    action: (student) => openModal(student)
  },
  {
    label: 'Delete',
    class: 'bg-red-500 text-white px-3 py-1 rounded cursor-pointer',
    action: (student) => deleteStudent(student.id)
  }
];

// Modify the ReusableTable component to handle nested properties
const processNestedValue = (item, key) => {
  return key.split('.').reduce((obj, k) => obj?.[k], item) || 'N/A';
};

// Computed property for filtered students
const filteredStudents = computed(() => {
  let filtered = students.value;
  
  // Filter by school year if selected
  if (selectedSchoolYear.value) {
    filtered = filtered.filter(student => 
      student.school_year?.id === selectedSchoolYear.value
    );
  }

  // Filter by section if selected
  if (selectedSection.value) {
    filtered = filtered.filter(student => 
      student.section?.section === selectedSection.value
    );
  }
  
  // Then apply search query filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(student => {
      return (
        (student.student_number && student.student_number.toLowerCase().includes(query)) ||
        (student.first_name && student.first_name.toLowerCase().includes(query)) ||
        (student.middle_name && student.middle_name.toLowerCase().includes(query)) ||
        (student.last_name && student.last_name.toLowerCase().includes(query)) ||
        (student.user?.email && student.user.email.toLowerCase().includes(query)) ||
        (student.section?.section && student.section.section.toLowerCase().includes(query)) ||
        (student.year_level?.year_level && student.year_level.year_level.toLowerCase().includes(query)) ||
        (student.status?.status_name && student.status.status_name.toLowerCase().includes(query))
      );
    });
  }
  
  return filtered;
});

</script>

<template>
  <div class="relative">
    <!-- Notification component -->
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

    <!-- Warning when no active school year -->
    <div v-else class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-sm">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm font-medium">
            No active school year set. Please set an active school year before adding students.
          </p>
        </div>
      </div>
    </div>

    <Overlay :show="isModalOPen" @click="closeModal" />
    
    <!-- Search and Filter Section -->
    <div class="flex justify-between items-center mb-4">
      <div class="flex items-center gap-6">
        <!-- Search Input -->
        <form @submit.prevent>
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Search for students..." 
            class="bg-[#ffff] p-2 pr-[3rem] text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 border-gray-500 w-[400px]"
          >
        </form>
        
        <!-- School Year Filter -->
        <div class="w-64 relative">
          <!-- Main dropdown -->
          <select 
            v-model="selectedSchoolYear"
            class="w-full bg-white p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border border-gray-300 appearance-none cursor-pointer focus:outline-none focus:border-blue-500"
          >
            <option value="" class="hidden">All School Years</option>
            <option v-for="year in schoolYears" :key="year.id" :value="year.id">
              {{ year.school_year }}
            </option>
          </select>
          
          <!-- Quick action button - only show when a school year is selected -->
          <div 
            v-if="selectedSchoolYear"
            @click="selectedSchoolYear = ''"
            class="w-full bg-[#0d6efd] text-white p-2 text-[0.875rem] leading-[1.25rem] rounded-b-[0.5rem] text-center cursor-pointer hover:bg-[#0b5ed7] transition-colors"
          >
            All School Years
          </div>

          <!-- Custom dropdown arrow -->
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

          <!-- Custom dropdown arrow -->
          <div class="absolute right-3 top-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Add New Students Button -->
      <button @click="openModal(null)" 
        class="cursor-pointer bg-[#1a3047] text-[#ffff] font-bold rounded-md pt-2 pb-2 pl-3 pr-3 flex justify-center items-center ml-4">
        Add New Students
      </button>
    </div>

    <!-- Reusable Table Component -->
    <ReusableTable 
      :headers="tableHeaders" 
      :data="filteredStudents" 
      :actions="true" 
      :action-buttons="actionButtons" 
    />

    <!-- Reusable Modal Component -->
    <ReusableModal :show="isModalOPen" :title="isEditMode ? 'Edit Student' : 'Add Student'" :loading="loading"
      :submit-button-text="isEditMode ? 'Edit Student' : 'Add Student'" @close="closeModal" @submit="submitForm">
      <!-- Form Grid Container -->
      <div class="grid grid-cols-3 gap-4">
        <!-- 1st Row -->
        <div>
          <input 
            type="text" 
            name="student_number" 
            placeholder="Enter Student No." 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.student_number }"
            v-model="form.student_number"
          >
          <p v-if="form.errors.student_number" class="text-red-500 text-sm mt-1">
            {{ form.errors.student_number }}
          </p>
        </div>

        <div>
          <input 
            type="text" 
            name="first_name" 
            placeholder="Enter First Name" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.first_name }"
            v-model="form.first_name"
          >
          <p v-if="form.errors.first_name" class="text-red-500 text-sm mt-1">
            {{ form.errors.first_name }}
          </p>
        </div>

        <div>
          <input 
            type="text" 
            name="middle_name" 
            placeholder="Enter Middle Name" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.middle_name }"
            v-model="form.middle_name"
          >
          <p v-if="form.errors.middle_name" class="text-red-500 text-sm mt-1">
            {{ form.errors.middle_name }}
          </p>
        </div>

        <!-- 2nd Row -->
        <div>
          <input 
            type="text" 
            name="last_name" 
            placeholder="Enter Last Name" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.last_name }"
            v-model="form.last_name"
          >
          <p v-if="form.errors.last_name" class="text-red-500 text-sm mt-1">
            {{ form.errors.last_name }}
          </p>
        </div>

        <div>
          <select 
            v-model="form.section_id" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.section_id }"
          >
            <option value="" disabled selected>Select Section</option>
            <option v-for="section in sections" :key="section.id" :value="section.id">
              {{ section.section }}
            </option>
          </select>
          <p v-if="form.errors.section_id" class="text-red-500 text-sm mt-1">
            {{ form.errors.section_id }}
          </p>
        </div>

        <div>
          <select 
            v-model="form.year_level_id" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.year_level_id }"
          >
            <option value="" disabled selected>Select Year Level</option>
            <option v-for="year in yearLevels" :key="year.id" :value="year.id">
              {{ year.year_level }}
            </option>
          </select>
          <p v-if="form.errors.year_level_id" class="text-red-500 text-sm mt-1">
            {{ form.errors.year_level_id }}
          </p>
        </div>

        <!-- 3rd Row -->
        <div>
          <input 
            type="text" 
            name="phone_number" 
            placeholder="Enter Phone Number" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.phone_number }"
            v-model="form.phone_number"
          >
          <p v-if="form.errors.phone_number" class="text-red-500 text-sm mt-1">
            {{ form.errors.phone_number }}
          </p>
        </div>

        <div>
          <select 
            name="gender" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.gender }"
            v-model="form.gender"
          >
            <option value="" disabled selected>Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Others">Others</option>
          </select>
          <p v-if="form.errors.gender" class="text-red-500 text-sm mt-1">
            {{ form.errors.gender }}
          </p>
        </div>

        <div>
          <input 
            type="text" 
            name="address" 
            placeholder="Enter Address" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.address }"
            v-model="form.address"
          >
          <p v-if="form.errors.address" class="text-red-500 text-sm mt-1">
            {{ form.errors.address }}
          </p>
        </div>

        <!-- 4th Row -->
        <div class="col-span-2">
          <label for="enrollment_date" class="block text-sm font-medium">Enrollment Date</label>
          <input 
            type="date" 
            name="enrollment_date" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.enrollment_date }"
            v-model="form.enrollment_date"
          >
          <p v-if="form.errors.enrollment_date" class="text-red-500 text-sm mt-1">
            {{ form.errors.enrollment_date }}
          </p>
        </div>

        <div>
          <select 
            name="student_status_id" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.student_status_id }"
            v-model="form.student_status_id"
          >
            <option value="" disabled selected>Select Status</option>
            <option 
              v-for="status in studentStatuses" 
              :key="status.id" 
              :value="status.id"
            >
              {{ status.status_name }}
            </option>
          </select>
          <p v-if="form.errors.student_status_id" class="text-red-500 text-sm mt-1">
            {{ form.errors.student_status_id }}
          </p>
        </div>

        <!-- 5th Row -->
        <div>
          <input 
            type="text" 
            name="email" 
            placeholder="Enter Email" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.email }"
            v-model="form.email"
          >
          <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">
            {{ form.errors.email }}
          </p>
        </div>

        <div>
          <input 
            type="password" 
            name="password" 
            placeholder="Enter Password" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.password }"
            v-model="form.password"
          >
          <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">
            {{ form.errors.password }}
          </p>
        </div>
        <div></div> <!-- Empty cell for alignment -->
      </div>
    </ReusableModal>
  </div>
</template>
