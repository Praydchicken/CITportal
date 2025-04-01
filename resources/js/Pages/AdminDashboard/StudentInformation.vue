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
  yearLevels: Array
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
    form.section = student.section?.id || "";
    form.year_level = student.year_level?.id || "";
    form.phone_number = student.phone_number;
    form.gender = student.gender;
    form.address = student.address;
    form.enrollment_date = student.enrollment_date;
    form.status = student.status;
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
  section: "",
  year_level: "",
  phone_number: null,
  gender: "",
  address: null,
  enrollment_date: null,
  status: "",
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
      onSuccess: (response) => {
        // Find and update the edited student
        const editedStudentIndex = students.value.findIndex(s => s.id === selectedStudent.value.id);
        if (editedStudentIndex !== -1) {
          const updatedStudent = {
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
      onSuccess: (response) => {
        // Show notification first
        showNotification('Student added successfully');
        
        // Then update the UI
        if (response?.student) {
          students.value.unshift(response.student);
        }
        
        // Finally close the modal and reset form
        form.reset();
        closeModal();
      },
      onError: (errors) => {
        console.log("Validation Errors:", errors);
        showNotification('Failed to add student', 'error');
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
  { key: 'status', label: 'Status' }
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
  if (!searchQuery.value) return students.value;
  
  const query = searchQuery.value.toLowerCase();
  return students.value.filter(student => {
    return (
      (student.student_number && student.student_number.toLowerCase().includes(query)) ||
      (student.first_name && student.first_name.toLowerCase().includes(query)) ||
      (student.middle_name && student.middle_name.toLowerCase().includes(query)) ||
      (student.last_name && student.last_name.toLowerCase().includes(query)) ||
      (student.user?.email && student.user.email.toLowerCase().includes(query)) ||
      (student.section?.section && student.section.section.toLowerCase().includes(query)) ||
      (student.year_level?.year_level && student.year_level.year_level.toLowerCase().includes(query)) ||
      (student.status && student.status.toLowerCase().includes(query))
    );
  });
});

</script>

<template>
  <div class="relative">
    <!-- Notification using teleport to ensure it's always on top -->
    <Teleport to="body">
      <Notification :show="notification.show" :message="notification.message" :type="notification.type" />
    </Teleport>

    <Overlay :show="isModalOPen" @click="closeModal" />
    <div class="flex justify-between items-center">
      <form @submit.prevent>
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Search for students..." 
          class="bg-[#ffff] p-2 pr-[3rem] text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 border-gray-500 w-[120%]"
        >
      </form>
      <button @click="openModal(null)" 
        class="cursor-pointer bg-[#1a3047] text-[#ffff] font-bold rounded-md pt-2 pb-2 pl-3 pr-3 flex justify-center items-center">
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
            v-model="form.section" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.section }"
          >
            <option value="" disabled selected>Select Section</option>
            <option v-for="section in sections" :key="section.id" :value="section.id">
              {{ section.section }}
            </option>
          </select>
          <p v-if="form.errors.section" class="text-red-500 text-sm mt-1">
            {{ form.errors.section }}
          </p>
        </div>

        <div>
          <select 
            v-model="form.year_level" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.year_level }"
          >
            <option value="" disabled selected>Select Year Level</option>
            <option v-for="year in yearLevels" :key="year.id" :value="year.id">
              {{ year.year_level }}
            </option>
          </select>
          <p v-if="form.errors.year_level" class="text-red-500 text-sm mt-1">
            {{ form.errors.year_level }}
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
            name="status" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.status }"
            v-model="form.status"
          >
            <option value="" disabled selected>Select Status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
            <option value="Graduated">Graduated</option>
            <option value="Dropped">Dropped</option>
          </select>
          <p v-if="form.errors.status" class="text-red-500 text-sm mt-1">
            {{ form.errors.status }}
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
