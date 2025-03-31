<script setup>
import DashboardLayout from '../../components/AdminDashboardLayout.vue';
import Overlay from '../../components/Overlay.vue';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faXmark } from '@fortawesome/free-solid-svg-icons'

import { useForm } from '@inertiajs/vue3'
import { defineProps } from "vue";
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

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

const sections = ref(props.sections);
const yearLevels = ref(props.yearLevels);
const students = ref(props.students);
const isEditMode = ref(false);
const selectedStudent = ref(null);
const loading = ref(false);



watch(() => props.students, (newStudents) => {
  students.value = newStudents; // ✅ Update students if props change
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
  } else{
    isEditMode.value = false; // Adding new student
    form.reset(); // Clear form
  }

  isModalOPen.value = true; // Open the modal
};


const closeModal = () => {
  isModalOPen.value = false;
  selectedStudent.value = null;
  isEditMode.value = false;
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

const submitForm = () => {
  loading.value = true;
  if (isEditMode.value && selectedStudent.value) {
    // Update existing student
    form.put(`/student/${selectedStudent.value.id}/update`, {
      preserveScroll: true,
      onSuccess: () => {
        router.reload();
        form.reset();
        closeModal();
      },
      onError: (errors) => {
        console.log("Validation Errors:", errors);
      },
       onFinish: () => {
        loading.value = false; // Stop loading after request finishes
      }
    });
  } else {
    // Add new student
    form.post('/student/addInfo', {
      preserveScroll: true,
      onSuccess: () => {
        router.reload();
        form.reset();
        closeModal();
      },
      onError: (errors) => {
        console.log("Validation Errors:", errors);
      },
      onFinish: () => {
        loading.value = false; // Stop loading after request finishes
      }
    });
  }
};



const deleteStudent = (id) => {
  if (!confirm("Are you sure you want to delete this student?")) return; // ✅ Show confirmation dialog

  router.delete(`/student/${id}/delete`, {
    preserveScroll: true,
    onSuccess: () => {
      router.reload(); // ✅ Reload the page to update the student list
    },
    onError: (errors) => {
      console.log("Error deleting student:", errors);
    },
  });
};

</script>

<template>
  <div class="relative">
    <Overlay :show="isModalOPen" @click="closeModal" />
    <div class="flex justify-between items-center">
      <form>
        <input style="outline: none;" type="text" placeholder="Search for students..." class="bg-[#ffff] p-2 pr-[3rem] text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 border-gray-500 w-[120%]">
      </form>
      <button @click="openModal(null)" class="cursor-pointer bg-[#1a3047] text-[#ffff] font-bold rounded-md pt-2 pb-2 pl-3 pr-3 flex justify-center items-center">Add New Students</button>
    </div>

    <!-- table for students -->
    <div class="w-full">
      <table class="w-full border-separate border-spacing-y-6 text-center">
        <!-- Table Head -->
        <thead>
          <tr class="bg-[#1a3047] text-[#ffff] rounded-lg">
            <th class="p-4 rounded-l-lg">Student No.</th>
            <th class="p-4">Email Address</th>
            <th class="p-4">First Name</th>
            <th class="p-4">Middle Name</th>
            <th class="p-4">Last Name</th>
            <th class="p-4">Section</th>
            <th class="p-4">Year Level</th>
            <th class="p-4">Status</th>
            <th class="p-4 rounded-r-lg">Action</th>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody>
          <tr v-for="student in students" :key="student.id" class="bg-gray-200 shadow-md rounded-lg">
            <td class="p-4 rounded-l-lg">{{ student.student_number }}</td>
            <td class="p-4">{{ student.user?.email || "N/A" }}</td>
            <td class="p-4">{{ student.first_name }} </td>
            <td class="p-4">{{ student.middle_name ? student.middle_name : 'N/A' }} </td>
            <td class="p-4">{{ student.last_name }} </td>
            <td class="p-4">{{ student.section?.section || "N/A" }}</td>
            <td class="p-4">{{ student.year_level?.year_level || "N/A" }}</td>
            <td class="p-4">{{ student.status || "N/A" }}</td>
            <td class="p-4 rounded-r-lg">
              <ul class="flex justify-center items-center gap-x-3">
                <li><button @click="openModal(student)" class="bg-[#559de6] text-white px-3 py-1 rounded cursor-pointer">Edit</button></li>
                <li><button @click="deleteStudent(student.id)"  class="bg-red-500 text-white px-3 py-1 rounded cursor-pointer">Delete</button></li>
              </ul>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

     <!-- Modal -->
    <transition name="slide">
      <form @submit.prevent="submitForm" v-if="isModalOPen" class="card fixed z-40 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 p-6 w-[600px]">
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-xl font-semibold text-center mb-4">{{ isEditMode ? "Edit Student" : "Add Student" }}</h2>
            <button @click.prevent="closeModal" class="text-xl cursor-pointer bg-[#1a3047] text-[#ffff] hover:bg-[#559de6] font-bold rounded-md pt-2 pb-2 pl-3 pr-3 flex justify-center items-center"><font-awesome-icon icon="xmark" /></button>
          </div>
          

          <!-- Form Grid Container -->
          <div class="grid grid-cols-3 gap-4">
              <!-- 1st Row -->
              <input type="text" name="student_number" placeholder="Enter Student No." class="input-field-add-student" v-model="form.student_number">
              <input type="text" name="first_name" placeholder="Enter First Name" class="input-field-add-student" v-model="form.first_name">
              <input type="text" name="middle_name" placeholder="Enter Middle Name" class="input-field-add-student" v-model="form.middle_name">

              <!-- 2nd Row -->
              <input type="text" name="last_name" placeholder="Enter Last Name" class="input-field-add-student" v-model="form.last_name">
              <select v-model="form.section" class="input-field-add-student">
                <option value="" disabled selected>Select Section</option>
                <option v-for="section in sections" :key="section.id" :value="section.id">
                  {{ section.section }}
                </option>
              </select>

              <select v-model="form.year_level" class="input-field-add-student">
                <option value="" disabled selected>Select Year Level</option>
                <option v-for="year in yearLevels" :key="year.id" :value="year.id">
                  {{ year.year_level }}
                </option>
              </select>

              <!-- 3rd Row -->
              <input type="text" name="phone_number" placeholder="Enter Phone Number" class="input-field-add-student" v-model="form.phone_number">
              <select name="gender" class="input-field-add-student" v-model="form.gender">
                  <option value="" disabled selected>Select Gender</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                  <option value="Others">Others</option>
              </select>
              <input type="text" name="address" placeholder="Enter Address" class="input-field-add-student" v-model="form.address">

              <!-- 4th Row -->
              <div class="col-span-2">
                  <label for="enrollment_date" class="block text-sm font-medium">Enrollment Date</label>
                  <input type="date" name="enrollment_date" class="input-field-add-student" v-model="form.enrollment_date">
              </div>
              <select name="status" class="input-field-add-student" v-model="form.status">
                  <option value="" disabled selected>Select Status</option>
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                  <option value="Graduated">Graduated</option>
                  <option value="Dropped">Dropped</option>
              </select>

              <!-- 5th Row -->
              <input type="text" name="email" placeholder="Enter Email" class="input-field-add-student" v-model="form.email">
              <input type="password" name="password" placeholder="Enter Password" class="input-field-add-student" v-model="form.password">
              <div></div> <!-- Empty cell for alignment -->
          </div>

          <!-- Submit Button -->
          <button type="submit" class="w-full bg-[#1a3047] cursor-pointer hover:bg-[#559de6] text-white py-2 mt-6 rounded-md font-semibold transition">
            <span v-if="loading">Saving...</span>
            <span v-else>{{ isEditMode ? "Edit Student" : "Add Student" }}</span>
          </button>
      </form>
    </transition>

    <!-- overlay -->
  </div>
</template>
