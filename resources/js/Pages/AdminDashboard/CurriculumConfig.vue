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
  }
});

const subjects = ref(props.subjects);
const yearLevels = ref(props.yearLevels);
const semesters = ref(props.semesters);
const isEditMode = ref(false);
const selectedSubject = ref(null);
const loading = ref(false);
const searchQuery = ref('');

// Watch for props.subjects changes
watch(() => props.subjects, (newSubjects) => {
  if (newSubjects) {
    subjects.value = newSubjects;
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

const submitForm = () => {
  loading.value = true;
  if (isEditMode.value && selectedSubject.value) {
    // Update existing subject
    router.put(`/curriculum/subject/${selectedSubject.value.id}/update`, form.data(), {
      preserveScroll: true,
      onSuccess: (page) => {
        // Find the updated subject in the server response
        const updatedSubject = page.props.subjects.find(s => s.id === selectedSubject.value.id);
        if (updatedSubject) {
          // Remove the subject from its current position
          const editedSubjectIndex = subjects.value.findIndex(s => s.id === selectedSubject.value.id);
          if (editedSubjectIndex !== -1) {
            subjects.value.splice(editedSubjectIndex, 1);
          }
          // Add the updated subject to the top
          subjects.value.unshift(updatedSubject);
          showNotification('Subject updated successfully');
        }
        closeModal();
      },
      onError: () => {
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
      onSuccess: (page) => {
        // Update the entire subjects list with the new data from the server
        subjects.value = page.props.subjects;
        showNotification('Subject added successfully');
        closeModal();
      },
      onError: () => {
        showNotification('Failed to add subject', 'error');
      },
      onFinish: () => {
        loading.value = false;
      }
    });
  }
};

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
  if (!searchQuery.value) return subjects.value;
  
  const query = searchQuery.value.toLowerCase();
  return subjects.value.filter(subject => {
    return (
      (subject.course_code && subject.course_code.toLowerCase().includes(query)) ||
      (subject.subject_name && subject.subject_name.toLowerCase().includes(query)) ||
      (subject.description && subject.description.toLowerCase().includes(query)) ||
      (subject.lecture_units && subject.lecture_units.toString().includes(query)) ||
      (subject.lab_units && subject.lab_units.toString().includes(query)) ||
      (subject.total_units && subject.total_units.toString().includes(query)) ||
      (subject.yearLevel?.year_level && subject.yearLevel.year_level.toLowerCase().includes(query)) ||
      (subject.semester?.semester_name && subject.semester.semester_name.toLowerCase().includes(query))
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

    <Overlay :show="isModalOpen" @click="closeModal" />
    <div class="flex justify-between items-center">
      <form @submit.prevent>
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Search for subjects..." 
          class="bg-[#ffff] p-2 pr-[3rem] text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 border-gray-500 w-[120%]"
        >
      </form>
      <button @click="openModal(null)" 
        class="cursor-pointer bg-[#1a3047] text-[#ffff] font-bold rounded-md pt-2 pb-2 pl-3 pr-3 flex justify-center items-center">
        Add New Subject
      </button>
    </div>

    <!-- Reusable Table Component -->
    <ReusableTable 
      :headers="tableHeaders" 
      :data="filteredSubjects" 
      :actions="true" 
      :action-buttons="actionButtons" 
    />

    <!-- Reusable Modal Component -->
    <ReusableModal :show="isModalOpen" :title="isEditMode ? 'Edit Subject' : 'Add Subject'" :loading="loading"
      :submit-button-text="isEditMode ? 'Edit Subject' : 'Add Subject'" @close="closeModal" @submit="submitForm">
      <!-- Form Grid Container -->
      <div class="grid grid-cols-2 gap-4">
        <!-- Year Level -->
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
          <p v-if="form.errors.year_level_id" class="text-[#FF0000] text-sm mt-1">
            {{ form.errors.year_level_id }}
          </p>
        </div>

        <!-- Semester -->
        <div>
          <select 
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
          <input 
            type="text" 
            name="course_code" 
            placeholder="Enter Course Code (e.g., CS301)" 
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
          <input 
            type="text" 
            name="subject_name" 
            placeholder="Enter Subject Name" 
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
          <textarea 
            name="description" 
            placeholder="Enter Description" 
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
          <input 
            type="text" 
            name="lecture_units" 
            placeholder="Enter Lecture Units" 
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
          <input 
            type="text" 
            name="lab_units" 
            placeholder="Enter Laboratory Units" 
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
          <input 
            type="number" 
            name="total_units" 
            placeholder="Enter Total Units" 
            class="input-field-add-student"
            :class="{ 'border-red-500': form.errors.total_units }"
            v-model="form.total_units"
          >
          <p v-if="form.errors.total_units" class="text-[#FF0000] text-sm mt-1">
            {{ form.errors.total_units }}
          </p>
        </div>
      </div>
    </ReusableModal>
  </div>
</template>
