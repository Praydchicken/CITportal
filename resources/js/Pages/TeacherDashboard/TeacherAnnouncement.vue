<script setup>
import TeacherDashboardLayout from '../../components/teacherDashboardLayout/TeacherDashboardLayout.vue';
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Notification from '../../components/Notification.vue';
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

defineOptions({
  layout: TeacherDashboardLayout
});

const props = defineProps({
  yearLevels: {
    type: Array,
    required: true
  },
  sections: {
    type: Array,
    required: true
  },
  announcements: {
    type: Array,
    required: true
  },
  flash: {
    type: Object,
    default: () => ({})
  }
});

// Add state for edit mode
const isEditMode = ref(false);
const selectedAnnouncement = ref(null);

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

// Watch for flash messages
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

// Format date for display
const formatDate = (dateString) => {
  const options = { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  };
  return new Date(dateString).toLocaleDateString('en-US', options);
};

// Create a ref for sections
const sections = ref(props.sections || []);

// Create form with useForm
const form = useForm({
  title_announcement: '',
  description_announcement: '',
  deadline_announcement: '',
  year_level_id: '',
  section_id: ''
});

// Reset section when year level changes
watch(() => form.year_level_id, (newYearLevel) => {
  form.section_id = ''; // Clear section selection when year level changes
});

// Watch for props changes
watch(() => props.sections, (newSections) => {
  if (newSections) {
    sections.value = newSections;
  }
});

// Filter sections based on selected year level
const filteredSections = computed(() => {
  if (!form.year_level_id) return [];
  if (form.year_level_id === 'all') return sections.value; // Return all sections if "All Year Levels" is selected
  
  // Filter sections for the selected year level
  return sections.value.filter(section => 
    section.year_level_id === parseInt(form.year_level_id)
  );
});

// Get unique section letters for the selected year level
const uniqueSections = computed(() => {
  if (!form.year_level_id) return [];
  if (form.year_level_id === 'all') {
    // Get all unique section letters across all year levels
    const sectionLetters = new Set(sections.value.map(section => section.section));
    return Array.from(sectionLetters).sort();
  }
  
  // Get unique section letters for the selected year level
  const sectionLetters = new Set(
    filteredSections.value.map(section => section.section)
  );
  
  // Convert to array and sort
  return Array.from(sectionLetters).sort();
});

// Function to edit announcement
const editAnnouncement = (announcement) => {
  isEditMode.value = true;
  selectedAnnouncement.value = announcement;

  // Pre-fill the form with announcement data
  form.title_announcement = announcement.title;
  form.description_announcement = announcement.description;
  form.deadline_announcement = announcement.deadline;
  
  // Find the year level ID from the first year level in the announcement
  const yearLevel = props.yearLevels.find(yl => yl.year_level === announcement.year_levels[0]);
  if (yearLevel) {
    form.year_level_id = yearLevel.id;
  }
  
  // Set the section (assuming first section for now)
  if (announcement.sections.length > 0) {
    form.section_id = announcement.sections[0];
  }
};

const submitForm = () => {
  const url = isEditMode.value 
    ? `/teacher/announcement/${selectedAnnouncement.value.id}` 
    : route('teacher.announcement.store');

  const method = isEditMode.value ? 'put' : 'post';

  form[method](url, {
    preserveScroll: true,
    onSuccess: () => {
      showNotification(isEditMode.value ? 'Announcement updated successfully' : 'Announcement created successfully');
      form.reset();
      isEditMode.value = false;
      selectedAnnouncement.value = null;
    },
    onError: (errors) => {
      console.error('Form submission errors:', errors);
      showNotification(isEditMode.value ? 'Failed to update announcement' : 'Failed to create announcement', 'error');
    }
  });
};

const cancelEdit = () => {
  isEditMode.value = false;
  selectedAnnouncement.value = null;
  form.reset();
  form.clearErrors();
};

const deleteAnnouncement = (id) => {
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
            router.delete(`/teacher/announcement/${id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    showNotification('Announcement deleted successfully');
                },
                onError: () => {
                    showNotification('Failed to delete announcement', 'error');
                },
            });
        }
    });
};

// Add state for filters after the notification state
const selectedYearLevelFilter = ref('');
const selectedSectionFilter = ref('');

// Get unique year levels and sections for filters
const uniqueYearLevelsFilter = computed(() => {
  const yearLevels = new Set(props.announcements.flatMap(announcement => announcement.year_levels));
  return Array.from(yearLevels).sort();
});

const uniqueSectionsFilter = computed(() => {
  const sections = new Set(props.announcements.flatMap(announcement => announcement.sections));
  return Array.from(sections).sort();
});

// Filtered announcements
const filteredAnnouncements = computed(() => {
  return props.announcements.filter(announcement => {
    const yearLevelMatch = !selectedYearLevelFilter.value || 
      announcement.year_levels.includes(selectedYearLevelFilter.value);
    const sectionMatch = !selectedSectionFilter.value || 
      announcement.sections.includes(selectedSectionFilter.value);
    return yearLevelMatch && sectionMatch;
  });
});

// Function to clear filters
const clearFilters = () => {
  selectedYearLevelFilter.value = '';
  selectedSectionFilter.value = '';
};
</script>

<template>
  <div class="p-6">
    <!-- Notification using teleport to ensure it's always on top -->
    <Teleport to="body">
      <Notification :show="notification.show" :message="notification.message" :type="notification.type" />
    </Teleport>

    <div class="grid grid-cols-2 gap-8">
      <!-- Left Side - Create Announcement Form -->
      <div class="rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-[#1a3047] mb-6">
          {{ isEditMode ? 'Edit Announcement' : 'Create Announcement' }}
        </h2>
        
        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- Title -->
          <div class="space-y-2">
            <label class="block text-sm font-semibold">Announcement Title</label>
            <input
              v-model="form.title_announcement"
              type="text"
              class="bg-[#ffff] p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 w-full"
              placeholder="Enter announcement title"
            >
            <p v-if="form.errors.title_announcement" class="text-[#FF0000] text-sm">{{ form.errors.title_announcement }}</p>
          </div>

          <!-- Description -->
          <div class="space-y-2">
            <label class="block text-sm font-semibold">Description</label>
            <textarea
              v-model="form.description_announcement"
              rows="4"
              class="bg-[#ffff] p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 w-full"
              placeholder="Enter announcement description"
            ></textarea>
            <p v-if="form.errors.description_announcement" class="text-[#FF0000] text-sm">{{ form.errors.description_announcement }}</p>
          </div>

          <!-- Deadline -->
          <div class="space-y-2">
            <label class="block text-sm font-semibold">Deadline</label>
            <input
              v-model="form.deadline_announcement"
              type="datetime-local"
              class="bg-[#ffff] p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 w-full"
            >
            <p v-if="form.errors.deadline_announcement" class="text-[#FF0000] text-sm">{{ form.errors.deadline_announcement }}</p>
          </div>

          <!-- Year Level -->
          <div class="space-y-2">
            <label class="block text-sm font-semibold">Year Level</label>
            <select
              v-model="form.year_level_id"
              class="bg-[#ffff] p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 w-full"
            >
              <option value="">Select Year Level</option>
              <option value="all">All Year Levels</option>
              <option 
                v-for="year in yearLevels" 
                :key="year.id" 
                :value="year.id"
              >
                {{ year.year_level }}
              </option>
            </select>
            <p v-if="form.errors.year_level_id" class="text-[#FF0000] text-sm">{{ form.errors.year_level_id }}</p>
          </div>

          <!-- Section -->
          <div class="space-y-2">
            <label class="block text-sm font-semibold">Section</label>
            <select
              v-model="form.section_id"
              class="bg-[#ffff] p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 w-full"
              :disabled="!form.year_level_id"
            >
              <option value="">Select Section</option>
              <option value="all">All Sections</option>
              <option 
                v-for="section in uniqueSections" 
                :key="section" 
                :value="section"
              >
                {{ section }}
              </option>
            </select>
            <p v-if="form.errors.section_id" class="text-[#FF0000] text-sm">{{ form.errors.section_id }}</p>
          </div>

          <!-- Submit and Cancel Buttons -->
          <div class="flex justify-end space-x-4">
            <button
              type="button"
              class="cursor-pointer bg-[#1a3047] text-[#ffff] font-bold rounded-md pt-2 pb-2 pl-3 pr-3 flex justify-center items-center"
              @click="isEditMode ? cancelEdit() : form.reset()"
            >
              {{ isEditMode ? 'Cancel' : 'Clear' }}
            </button>
            <button
              type="submit"
              class="cursor-pointer bg-[#1a3047] text-[#ffff] font-bold rounded-md pt-2 pb-2 pl-3 pr-3 flex justify-center items-center"
              :disabled="form.processing"
            >
              {{ form.processing ? (isEditMode ? 'Updating...' : 'Creating...') : (isEditMode ? 'Update Announcement' : 'Create Announcement') }}
            </button>
          </div>
        </form>
      </div>

      <!-- Right Side - Preview or List -->
      <div class="rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-[#1a3047] mb-6">Announcements List</h2>
        
        <!-- Filter Controls -->
        <div class="flex items-center gap-4 mb-6">
          <select
            v-model="selectedYearLevelFilter"
            class="bg-[#ffff] p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 border-gray-500"
          >
            <option value="">All Year Levels</option>
            <option 
              v-for="yearLevel in uniqueYearLevelsFilter" 
              :key="yearLevel" 
              :value="yearLevel"
            >
              {{ yearLevel }}
            </option>
          </select>

          <select
            v-model="selectedSectionFilter"
            class="bg-[#ffff] p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 border-gray-500"
          >
            <option value="">All Sections</option>
            <option 
              v-for="section in uniqueSectionsFilter" 
              :key="section" 
              :value="section"
            >
              Section {{ section }}
            </option>
          </select>

          <button 
            @click="clearFilters"
            class="cursor-pointer bg-[#1a3047] text-[#ffff] font-bold rounded-md px-3 py-2 text-sm"
          >
            Clear Filters
          </button>
        </div>
        
        <!-- Announcements Container -->
        <div class="space-y-6 max-h-[600px] overflow-y-auto">
          <div v-if="filteredAnnouncements.length === 0" class="text-center text-gray-500">
            No announcements found
          </div>
          
          <!-- Individual Announcement Cards -->
          <div 
            v-for="announcement in filteredAnnouncements" 
            :key="announcement.id"
            class="bg-white rounded-lg shadow-sm p-4 border border-gray-200"
          >
            <!-- Title and Deadline -->
            <div class="flex justify-between items-start mb-2">
              <h3 class="text-lg font-semibold text-[#1a3047]">{{ announcement.title }}</h3>
              <div class="text-sm text-gray-600">
                Deadline: {{ formatDate(announcement.deadline) }}
              </div>
            </div>
            
            <!-- Description -->
            <p class="text-gray-700 mb-3">{{ announcement.description }}</p>
            
            <!-- Year Levels and Sections -->
            <div class="flex flex-wrap gap-2 mb-2">
              <!-- Year Levels -->
              <template v-if="announcement.year_levels.length === yearLevels.length">
                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                  All year levels
                </span>
              </template>
              <template v-else>
                <span 
                  v-for="yearLevel in announcement.year_levels" 
                  :key="yearLevel"
                  class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-sm"
                >
                  {{ yearLevel }}
                </span>
              </template>

              <!-- Sections -->
              <template v-if="announcement.sections.length === sections.length">
                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                  All sections
                </span>
              </template>
              <template v-else>
                <span 
                  v-for="section in announcement.sections" 
                  :key="section"
                  class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-sm"
                >
                  Section {{ section }}
                </span>
              </template>
            </div>
            
            <!-- Published Date and Actions -->
            <div class="flex justify-between items-center mt-2">
              <div class="text-sm text-gray-500">
                Published: {{ formatDate(announcement.published_at) }}
              </div>
              <div class="flex gap-2">
                <button
                  @click="editAnnouncement(announcement)"
                  class="bg-[#559de6] text-white px-3 py-1 rounded cursor-pointer text-sm hover:bg-blue-600 transition-colors"
                >
                  Edit
                </button>
                <button
                  @click="deleteAnnouncement(announcement.id)"
                  class="bg-red-500 text-white px-3 py-1 rounded cursor-pointer text-sm hover:bg-red-600 transition-colors"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
