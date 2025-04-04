<script setup>
import DashboardLayout from '../../components/AdminDashboardLayout.vue';
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

defineOptions({
  layout: DashboardLayout
});

const props = defineProps({
  sections: Array
});

// Create a ref for sections
const sections = ref(props.sections || []);

// Define year level options
const yearLevelOptions = [
  { value: '1', label: '1st Year' },
  { value: '2', label: '2nd Year' },
  { value: '3', label: '3rd Year' },
  { value: '4', label: '4th Year' }
];

// Create form with useForm
const form = useForm({
  title: '',
  description: '',
  deadline: '',
  year_level_id: '',
  section_id: ''
});

// Reset section when year level changes
watch(() => form.year_level_id, () => {
  form.section_id = '';
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
  return sections.value.filter(section => 
    section.year_level_id === form.year_level_id
  );
});

const submitForm = () => {
  form.post('/announcement/create', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
    }
  });
};
</script>

<template>
  <div class="p-6">
    <div class="grid grid-cols-2 gap-8">
      <!-- Left Side - Create Announcement Form -->
      <div class="rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-[#1a3047] mb-6">Create Announcement</h2>
        
        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- Title -->
          <div class="space-y-2">
            <label class="block text-sm font-semibold">Announcement Title</label>
            <input
              v-model="form.title"
              type="text"
              class="bg-[#ffff] p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 w-full"
              placeholder="Enter announcement title"
            >
            <p v-if="form.errors.title" class="text-[#FF0000] text-sm">{{ form.errors.title }}</p>
          </div>

          <!-- Description -->
          <div class="space-y-2">
            <label class="block text-sm font-semibold">Description</label>
            <textarea
              v-model="form.description"
              rows="4"
              class="bg-[#ffff] p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 w-full"
              placeholder="Enter announcement description"
            ></textarea>
            <p v-if="form.errors.description" class="text-[#FF0000] text-sm">{{ form.errors.description }}</p>
          </div>

          <!-- Deadline -->
          <div class="space-y-2">
            <label class="block text-sm font-semibold">Deadline</label>
            <input
              v-model="form.deadline"
              type="datetime-local"
              class="bg-[#ffff] p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 w-full"
            >
            <p v-if="form.errors.deadline" class="text-[#FF0000] text-sm">{{ form.errors.deadline }}</p>
          </div>

          <!-- Year Level -->
          <div class="space-y-2">
            <label class="block text-sm font-semibold">Year Level</label>
            <select
              v-model="form.year_level_id"
              class="bg-[#ffff] p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 w-full"
            >
              <option value="">Select Year Level</option>
              <option 
                v-for="option in yearLevelOptions" 
                :key="option.value" 
                :value="option.value"
              >
                {{ option.label }}
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
              <option 
                v-for="section in filteredSections" 
                :key="section.id" 
                :value="section.id"
              >
                {{ section.section }}
              </option>
            </select>
            <p v-if="form.errors.section_id" class="text-[#FF0000] text-sm">{{ form.errors.section_id }}</p>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end space-x-4">
            <button
              type="button"
              class="cursor-pointer bg-[#1a3047] text-[#ffff] font-bold rounded-md pt-2 pb-2 pl-3 pr-3 flex justify-center items-center"
              @click="form.reset()"
            >
              Clear
            </button>
            <button
              type="submit"
              class="cursor-pointer bg-[#1a3047] text-[#ffff] font-bold rounded-md pt-2 pb-2 pl-3 pr-3 flex justify-center items-center"
              :disabled="form.processing"
            >
              {{ form.processing ? 'Creating...' : 'Create Announcement' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Right Side - Preview or List -->
      <div class="rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-[#1a3047] mb-6">Preview</h2>
        <!-- Add preview content here -->
      </div>
    </div>
  </div>
</template>
