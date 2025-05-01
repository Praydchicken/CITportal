<script setup>
import DashboardLayout from '../../components/AdminDashboardLayout.vue';
import Overlay from '../../components/Overlay.vue';
import ReusableTable from '../../components/ReusableTable.vue';
import ReusableModal from '../../components/ReusableModal.vue';
import Notification from '../../components/Notification.vue';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faXmark } from '@fortawesome/free-solid-svg-icons';

import { useForm } from '@inertiajs/vue3';
import { defineProps } from "vue";
import { ref, watch, computed, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { Teleport } from 'vue';

library.add(faXmark);

defineOptions({
    layout: DashboardLayout
});

const props = defineProps({
    sections: Array,
    errors: Object,
    activeSchoolYear: {
        type: Object,
        required: true
    },
    flash: {
        type: Object,
        default: () => ({})
    }
});

const sections = ref(props.sections || []);
const isEditMode = ref(false);
const selectedSection = ref(null);
const loading = ref(false);
const selectedYearLevel = ref('');
const selectedSectionFilter = ref('');
const selectedSchoolYear = ref('');
const isModalOpen = ref(false);

// Table headers
const tableHeaders = [
    { key: 'section', label: 'Section Name' },
    { key: 'year_level', label: 'Year Level' },
    { key: 'min_students', label: 'Minimum Students' },
    { key: 'max_students', label: 'Maximum Students' },
    { key: 'school_year', label: 'School Year' }
];

// Action buttons for the table
const actionButtons = [
    {
        label: 'Edit',
        class: 'bg-blue-500 text-white px-3 py-1 rounded cursor-pointer hover:bg-blue-600',
        action: (section) => editSection(section)
    },
    {
        label: 'Delete',
        class: 'bg-red-500 text-white px-3 py-1 rounded cursor-pointer hover:bg-red-600',
        action: (section) => deleteSection(section.id)
    }
];

// Year level options for the form
const yearLevelOptions = [
    { value: '1', label: '1st Year' },
    { value: '2', label: '2nd Year' },
    { value: '3', label: '3rd Year' },
    { value: '4', label: '4th Year' }
];

// Get unique year levels and sections for filters
const uniqueYearLevels = computed(() => {
    const yearLevels = new Set(sections.value.map(section => section.year_level));
    return Array.from(yearLevels);
});

const uniqueSections = computed(() => {
    const sectionNames = new Set(sections.value.map(section => section.section));
    return Array.from(sectionNames);
});

// Get unique school years for filter
const uniqueSchoolYears = computed(() => {
    const schoolYears = new Set(sections.value.map(section => section.school_year));
    return Array.from(schoolYears);
});

// Filtered sections
const filteredSections = computed(() => {
    return sections.value.filter(section => {
        const yearLevelMatch = !selectedYearLevel.value || section.year_level === selectedYearLevel.value;
        const sectionMatch = !selectedSectionFilter.value || section.section === selectedSectionFilter.value;
        const schoolYearMatch = !selectedSchoolYear.value || section.school_year === selectedSchoolYear.value;
        return yearLevelMatch && sectionMatch && schoolYearMatch;
    });
});

// Form for adding/editing sections
const form = useForm({
    section: '',
    year_level_id: '',
    school_year: props.activeSchoolYear?.school_year || '',
    school_year_status: props.activeSchoolYear?.school_year_status || '',
    minimum_number_students: '',
    maximum_number_students: ''
});

// Replace the notification ref with reactive object
const notification = reactive({
    show: false,
    message: '',
    type: 'success'
});

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

const openAddModal = () => {
    isEditMode.value = false;
    selectedSection.value = null;
    form.reset();
    // Set the school year fields to active school year when opening add modal
    form.school_year = props.activeSchoolYear?.school_year || '';
    form.school_year_status = props.activeSchoolYear?.school_year_status || '';
    isModalOpen.value = true;
};

const editSection = (section) => {
    if (!section) return;
    isEditMode.value = true;
    selectedSection.value = section;
    form.section = section.section;
    form.year_level_id = section.year_level_id;
    form.school_year = section.school_year;
    form.school_year_status = section.school_year_status;
    form.minimum_number_students = section.min_students;
    form.maximum_number_students = section.max_students;
    isModalOpen.value = true;
};

// Add validation function
const validateForm = () => {
    let isValid = true;
    form.clearErrors();

    // Validate Section Name
    if (!form.section) {
        form.setError('section', 'Section name is required');
        isValid = false;
    } else if (!/^[A-Za-z0-9\s-]+$/.test(form.section)) {
        form.setError('section', 'Section name can only contain letters, numbers, spaces and hyphens');
        isValid = false;
    }

    // Validate Year Level
    if (!form.year_level_id) {
        form.setError('year_level_id', 'Year level is required');
        isValid = false;
    }

    // Validate Minimum Students
    if (!form.minimum_number_students) {
        form.setError('minimum_number_students', 'Minimum number of students is required');
        isValid = false;
    } else if (parseInt(form.minimum_number_students) < 1) {
        form.setError('minimum_number_students', 'Minimum students must be at least 1');
        isValid = false;
    }

    // Validate Maximum Students
    if (!form.maximum_number_students) {
        form.setError('maximum_number_students', 'Maximum number of students is required');
        isValid = false;
    } else if (parseInt(form.maximum_number_students) <= parseInt(form.minimum_number_students)) {
        form.setError('maximum_number_students', 'Maximum students must be greater than minimum students');
        isValid = false;
    }

    return isValid;
};

const submitForm = () => {
    // Validate form before submission
    if (!validateForm()) {
        showNotification('Please fix the errors in the form', 'error');
        return;
    }

    loading.value = true;
    
    // Prepare form data with exact field names matching the fillable array
    const formData = {
        section: form.section.trim(),
        year_level_id: form.year_level_id,
        school_year: props.activeSchoolYear?.school_year,
        school_year_status: props.activeSchoolYear?.school_year_status,
        minimum_number_students: parseInt(form.minimum_number_students),
        maximum_number_students: parseInt(form.maximum_number_students)
    };

    if (isEditMode.value && selectedSection.value) {
        // Update existing section
        router.put(`/section/${selectedSection.value.id}`, formData, {
            preserveScroll: true,
            onSuccess: (page) => {
                if (page.props.flash?.error) {
                    showNotification(page.props.flash.error, 'error');
                    return;
                }
                sections.value = page.props.sections;
                closeModal();
                showNotification('Section updated successfully');
            },
            onError: (errors) => {
                if (typeof errors === 'object') {
                    Object.keys(errors).forEach(key => {
                        form.setError(key, errors[key]);
                    });
                }
                showNotification(errors.message || 'Failed to update section', 'error');
            },
            onFinish: () => {
                loading.value = false;
            }
        });
    } else {
        // Add new section
        router.post('/section/add', formData, {
            preserveScroll: true,
            onSuccess: (page) => {
                if (page.props.flash?.error) {
                    showNotification(page.props.flash.error, 'error');
                    loading.value = false;
                    return;
                }
                
                if (page.props.sections) {
                    sections.value = page.props.sections;
                    closeModal();
                    showNotification(page.props.flash?.success || 'Section added successfully');
                } else {
                    showNotification('Error: No sections data received', 'error');
                }
            },
            onError: (errors) => {
                if (typeof errors === 'object') {
                    Object.keys(errors).forEach(key => {
                        form.setError(key, errors[key]);
                    });
                }
                showNotification(errors.message || 'Failed to add section', 'error');
            },
            onFinish: () => {
                loading.value = false;
            }
        });
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    isEditMode.value = false;
    selectedSection.value = null;
    form.reset();
    form.clearErrors();
};

const deleteSection = (id) => {
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
            router.delete(`/section/${id}`, {
                preserveScroll: true,
                onSuccess: (page) => {
                    sections.value = page.props.sections;
                    showNotification('Section deleted successfully');
                },
                onError: () => {
                    showNotification('Failed to delete section', 'error');
                },
                onFinish: () => {
                    loading.value = false;
                }
            });
        }
    });
};

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
</script>

<template>
    <Head title="Sections" />
    <div v-if="activeSchoolYear"
        class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium">
                    Active School Year: <span class="font-bold">{{ activeSchoolYear.school_year }}</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="relative">
        <!-- Replace existing Notification with Teleport -->
        <Teleport to="body">
            <Notification :show="notification.show" :message="notification.message" :type="notification.type" />
        </Teleport>

        <Overlay :show="isModalOpen" @click="closeModal" />
        <div class="flex flex-col gap-4 mb-4">
            <!-- Filter dropdowns -->
            <div class="flex items-center gap-4">
                <select v-model="selectedYearLevel"
                    class="bg-[#ffff] p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 border-gray-500">
                    <option value="">All Year Levels</option>
                    <option v-for="yearLevel in uniqueYearLevels" :key="yearLevel" :value="yearLevel">
                        {{ yearLevel }}
                    </option>
                </select>

                <select v-model="selectedSectionFilter"
                    class="bg-[#ffff] p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 border-gray-500">
                    <option value="">All Sections</option>
                    <option v-for="section in uniqueSections" :key="section" :value="section">
                        {{ section }}
                    </option>
                </select>

                <!-- <select v-model="selectedSchoolYear"
                    class="bg-[#ffff] p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 border-gray-500">
                    <option value="">All School Years</option>
                    <option v-for="schoolYear in uniqueSchoolYears" :key="schoolYear" :value="schoolYear">
                        {{ schoolYear }}
                    </option>
                </select> -->

                <button @click="() => { selectedYearLevel = ''; selectedSectionFilter = ''; selectedSchoolYear = ''; }"
                    class="cursor-pointer bg-[#1a3047] text-[#ffff] font-bold rounded-md px-3 py-2 text-sm">
                    Clear Filters
                </button>
            </div>

            <!-- Add Section Button -->
            <div class="flex justify-end">
                <button @click="openAddModal"
                    class="cursor-pointer bg-[#1a3047] text-[#ffff] font-bold rounded-md pt-2 pb-2 pl-3 pr-3 flex justify-center items-center">
                    Add New Section
                </button>
            </div>
        </div>

        <!-- Reusable Table Component -->
        <ReusableTable :headers="tableHeaders" :data="filteredSections" :actions="true"
            :action-buttons="actionButtons" />

        <!-- Reusable Modal Component -->
        <ReusableModal :show="isModalOpen" :title="isEditMode ? 'Edit Section' : 'Add Section'" :loading="loading"
            :submit-button-text="isEditMode ? 'Update Section' : 'Add Section'" @close="closeModal"
            @submit="submitForm">
            <!-- Form Grid Container -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Section Name -->
                <div>
                    <label for="section" class="block text-sm font-medium text-gray-700 mb-1">Section Name</label>
                    <input id="section" type="text" v-model="form.section" class="input-field-add-student w-full"
                        :class="{ 'border-red-500': form.errors.section }">
                    <p v-if="form.errors.section" class="text-red-500 text-sm mt-1">
                        {{ form.errors.section }}
                    </p>
                </div>

                <!-- Year Level -->
                <div>
                    <label for="year_level" class="block text-sm font-medium text-gray-700 mb-1">Year Level</label>
                    <select id="year_level" v-model="form.year_level_id" class="input-field-add-student w-full"
                        :class="{ 'border-red-500': form.errors.year_level_id }">
                        <option value="" disabled selected>Select Year Level</option>
                        <option v-for="option in yearLevelOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                    <p v-if="form.errors.year_level_id" class="text-red-500 text-sm mt-1">
                        {{ form.errors.year_level_id }}
                    </p>
                </div>

                <!-- School Year (Disabled) -->
                <div>
                    <label for="school_year" class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
                    <input id="school_year" type="text" :value="props.activeSchoolYear?.school_year"
                        class="input-field-add-student w-full bg-gray-100 cursor-not-allowed" disabled>
                </div>

                <!-- Status (Disabled) -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <input id="status" type="text" :value="props.activeSchoolYear?.school_year_status"
                        class="input-field-add-student w-full bg-gray-100 cursor-not-allowed" disabled>
                </div>

                <!-- Minimum Students -->
                <div>
                    <label for="min_students" class="block text-sm font-medium text-gray-700 mb-1">Minimum
                        Students</label>
                    <input id="min_students" type="number" v-model="form.minimum_number_students"
                        class="input-field-add-student w-full"
                        :class="{ 'border-red-500': form.errors.minimum_number_students }">
                    <p v-if="form.errors.minimum_number_students" class="text-red-500 text-sm mt-1">
                        {{ form.errors.minimum_number_students }}
                    </p>
                </div>

                <!-- Maximum Students -->
                <div>
                    <label for="max_students" class="block text-sm font-medium text-gray-700 mb-1">Maximum
                        Students</label>
                    <input id="max_students" type="number" v-model="form.maximum_number_students"
                        class="input-field-add-student w-full"
                        :class="{ 'border-red-500': form.errors.maximum_number_students }">
                    <p v-if="form.errors.maximum_number_students" class="text-red-500 text-sm mt-1">
                        {{ form.errors.maximum_number_students }}
                    </p>
                </div>
            </div>
        </ReusableModal>
    </div>
</template>
