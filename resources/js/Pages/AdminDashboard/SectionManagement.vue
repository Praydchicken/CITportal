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
import { defineProps, reactive } from "vue";
import { ref, watch, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

library.add(faXmark);

defineOptions({
    layout: DashboardLayout
});

const props = defineProps({
    sections: Object,
    yearLevels: Array,
    activeSchoolYear: Object,
    schoolYears: Array,
    semesters: Array,
    filters: Object,
});

const isEditMode = ref(false);
const selectedSection = ref(null);
const loading = ref(false);

// Filters
const search = ref(props.filters.search || '');
const yearLevelFilter = ref(props.filters.year_level || '');
const semesterFilter = ref(props.filters.semester || '');
const selectedSchoolYear = ref(props.filters.school_year || '');

onMounted(() => {
    if (!selectedSchoolYear.value) {
        selectedSchoolYear.value = props.activeSchoolYear?.id ?? null;
    }
});

// const filteredSections = computed(() => {
//     let filtered = props.sections.data;

//     if (yearLevelFilter.value) {
//         filtered = filtered.filter(section => section.year_level_id == yearLevelFilter.value);
//     }

//     const schoolYearId = selectedSchoolYear.value || props.activeSchoolYear?.id;
//     if (schoolYearId) {
//         filtered = filtered.filter(section => section.school_year_id == schoolYearId);
//     }

//     if (semesterFilter.value) {
//         filtered = filtered.filter(section => section.semester_id == semesterFilter.value);
//     }

//     if (search.value) {
//         filtered = filtered.filter(section =>
//             section.section.toLowerCase().includes(search.value.toLowerCase())
//         );
//     }

//     return filtered;
// });

// console.log(filteredSections);


// Watch filters and update URL
watch(search, async (newSearch) => {
    router.get(route('admin.section.management'), {
        search: newSearch,
        year_level: yearLevelFilter.value,
        semester: semesterFilter.value,
        school_year: selectedSchoolYear.value || props.activeSchoolYear?.id
        // Don't preserve page here - let server decide
    }, {
        preserveState: true,
        preserveScroll: true
    });
});

// Apply similar changes to all other filter watchers

watch(yearLevelFilter, async (newYearLevel) => {
    router.get(route('admin.section.management'), {
        search: search.value,
        year_level: newYearLevel,
        semester: semesterFilter.value,
        school_year: selectedSchoolYear.value || props.activeSchoolYear?.id,
        // page: props.sections.current_page
    }, {
        preserveScroll: true,
        preserveState: true,
        // replace: true,
    });
});

watch(semesterFilter, async (newSemester) => {
    router.get(route('admin.section.management'), {
        search: search.value,
        year_level: yearLevelFilter.value,
        semester: newSemester,
        school_year: selectedSchoolYear.value || props.activeSchoolYear?.id,
        // page: props.sections.current_page
    }, {
        preserveState: true,
        preserveScroll: true,
        // replace: true,
    });
});

watch(selectedSchoolYear, async (newSchoolYear) => {
    router.get(route('admin.section.management'), {
        search: search.value,
        year_level: yearLevelFilter.value,
        semester: semesterFilter.value,
        school_year: newSchoolYear || props.activeSchoolYear?.id,
        page: 1 // Always reset to page 1 when changing school years
    }, {
        preserveState: true,
        preserveScroll: true
    });
});

const resetFilters = () => {
    search.value = '';
    yearLevelFilter.value = '';
    semesterFilter.value = '';
    selectedSchoolYear.value = props.activeSchoolYear?.id;

    router.get(route('admin.section.management'), {
        school_year: props.activeSchoolYear?.id,
        page: 1
    }, {
        preserveScroll: true
    });
};

const isModalOpen = ref(false);

const openModal = (section) => {
    if (section) {
        isEditMode.value = true;
        selectedSection.value = section;
        form.section = section.section;
        form.year_level_id = section.year_level_id;
        form.school_year_id = section.school_year_id;
        form.semester_id = section.semester_id;
        form.minimum_number_students = section.minimum_number_students;
        form.maximum_number_students = section.maximum_number_students;
    } else {
        isEditMode.value = false;
        form.reset();
        form.school_year_id = props.activeSchoolYear?.id;
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    if (loading.value) return;
    selectedSection.value = null;
    isEditMode.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = false;
};

const form = useForm({
    section: '',
    year_level_id: '',
    school_year_id: '',
    semester_id: '',
    minimum_number_students: '',
    maximum_number_students: ''
});

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

const submitForm = () => {
    loading.value = true;
    if (isEditMode.value && selectedSection.value) {
        form.put(`/admin/section/${selectedSection.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showNotification('Section updated successfully');
                isModalOpen.value = false;
                closeModal();
            },
            onError: (errors) => {
                showNotification('Failed to update section', 'error');
            },
            onFinish: () => {
                loading.value = false;
            }
        });

    } else {
        form.post('/admin/section/add', {
            preserveScroll: true,
            onSuccess: () => {
                showNotification('Section added successfully');
                isModalOpen.value = false;
                closeModal();
            },
            onError: (errors) => {
                showNotification('Failed to add section', 'error');
            },
            onFinish: () => {
                loading.value = false;
            }
        });
    }
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
            router.delete(`/admin/section/${id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    showNotification('Section deleted successfully');
                },
                onError: () => {
                    showNotification('Failed to delete section', 'error');
                }
            });
        }
    });
};

const tableHeaders = [
    { key: 'section', label: 'Section Name' },
    { key: 'year_level.year_level', label: 'Year Level' },
    { key: 'semester.semester_name', label: 'Semester' },
    { key: 'minimum_number_students', label: 'Min Students' },
    { key: 'maximum_number_students', label: 'Max Students' }
];

const processNestedValue = (item, key) => {
    return key.split('.').reduce((obj, k) => obj?.[k], item) || 'N/A';
};
</script>

<template>

    <Head title="Section Management" />
    <div class="relative">
        <Teleport to="body">
            <Notification :show="notification.show" :message="notification.message" :type="notification.type" />
        </Teleport>

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

        <Overlay :show="isModalOpen" @click="closeModal" />

        <div class="flex flex-col mb-4">
            <div class="flex justify-between items-center">
                <form @submit.prevent>
                    <input v-model="search" type="text" placeholder="Search sections..."
                        class="bg-[#ffff] p-2 pr-[3rem] text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border-2 border-gray-500 w-[300px]">
                </form>

                <button @click="openModal(null)"
                    class="cursor-pointer bg-[#1a3047] text-[#ffff] font-bold rounded-md pt-2 pb-2 pl-3 pr-3 flex justify-center items-center">
                    Add New Section
                </button>
            </div>

            <div class="flex gap-4 items-center mt-3">
                <div>
                    <select v-model="selectedSchoolYear"
                        class="w-full bg-white p-2 text-[0.875rem] leading-[1.25rem] rounded-[0.5rem] border border-gray-300 appearance-none cursor-pointer focus:outline-none focus:border-blue-500">
                        <option v-for="year in schoolYears" :key="year.id" :value="year.id">
                            {{ year.school_year }}
                            <span v-if="year.id === activeSchoolYear?.id">(Active)</span>
                        </option>
                    </select>
                </div>

                <div>
                    <select v-model="yearLevelFilter"
                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">All Year Levels</option>
                        <option v-for="year in yearLevels" :key="year.id" :value="year.id">
                            {{ year.year_level }}
                        </option>
                    </select>
                </div>

                <div>
                    <select v-model="semesterFilter"
                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">All Semesters</option>
                        <option v-for="semester in semesters" :key="semester.id" :value="semester.id">
                            {{ semester.semester_name }}
                        </option>
                    </select>
                </div>

                <button @click="resetFilters"
                    class="cursor-pointer bg-[#1a3047] text-[#ffff] font-bold rounded-md pt-2 pb-2 pl-3 pr-3 flex justify-center items-center">
                    Clear Filter
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-[#1a3047] text-white">
                        <th v-for="header in tableHeaders" :key="header.key"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                            {{ header.label }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(section, index) in sections.data" :key="section.id"
                        :class="index % 2 === 0 ? 'bg-neutral-300' : 'bg-gray-200'">
                        <td v-for="header in tableHeaders" :key="header.key" class="px-6 py-4 text-sm text-gray-900">
                            {{ processNestedValue(section, header.key) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex space-x-2">
                                <button @click="openModal(section)"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">Edit</button>
                                <button @click="deleteSection(section.id)"
                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex items-center justify-between p-6">
            <div class="text-sm text-gray-700">
                Showing <span class="font-semibold">{{ sections.from }}</span> to <span class="font-semibold">{{
                    sections.to }}</span> of <span class="font-semibold">{{ sections.total }}</span> sections
            </div>
            <nav class="relative rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <Link v-if="sections.currentPage > 1" :href="sections.links.prev" preserve-scroll rel="prev"
                    aria-label="Previous"
                    class="bg-white border border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 rounded-l-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z"
                        clip-rule="evenodd" />
                </svg>
                </Link>

                <template v-for="(link, key) in sections.links" :key="key">
                    <Link v-if="link.url" :href="link.url" preserve-scroll :class="[
                        'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border focus:outline-none focus:ring-2 focus:ring-indigo-500',
                        { ' bg-indigo-50 border-indigo-500 text-indigo-600': link.active },
                        key === 0 && sections.currentPage === 1 ? 'rounded-l-md' : '',
                        key === sections.links.length - 1 && sections.currentPage === sections.lastPage ? 'rounded-r-md' : '',
                    ]" :aria-current="link.active ? 'page' : null" v-html="link.label">
                    </Link>
                </template>

                <Link v-if="sections.currentPage < sections.lastPage" :href="sections.links.next" preserve-scroll
                    rel="next" aria-label="Next"
                    class="bg-white border border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 rounded-r-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </Link>
            </nav>
        </div>

        <ReusableModal :show="isModalOpen" :title="isEditMode ? 'Edit Section' : 'Add Section'" :loading="loading"
            :submit-button-text="isEditMode ? 'Update Section' : 'Add Section'" @close="closeModal"
            @submit="submitForm">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Year Level</label>
                    <select v-model="form.year_level_id" class="input-field-add-student w-full"
                        :class="{ 'border-red-500': form.errors.year_level_id }">
                        <option value="" disabled selected>Select Year Level</option>
                        <option v-for="year in yearLevels" :key="year.id" :value="year.id">
                            {{ year.year_level }}
                        </option>
                    </select>
                    <p v-if="form.errors.year_level_id" class="text-red-500 text-sm mt-1">{{ form.errors.year_level_id
                        }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                    <select v-model="form.semester_id" class="input-field-add-student w-full"
                        :class="{ 'border-red-500': form.errors.semester_id }">
                        <option value="" disabled selected>Select Semester</option>
                        <option v-for="semester in semesters" :key="semester.id" :value="semester.id">
                            {{ semester.semester_name }}
                        </option>
                    </select>
                    <p v-if="form.errors.semester_id" class="text-red-500 text-sm mt-1">{{ form.errors.semester_id }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Section Name</label>
                    <input type="text" v-model="form.section" class="input-field-add-student w-full"
                        :class="{ 'border-red-500': form.errors.section }">
                    <p v-if="form.errors.section" class="text-red-500 text-sm mt-1">{{ form.errors.section }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
                    <input type="text" :value="activeSchoolYear?.school_year"
                        class="input-field-add-student w-full bg-gray-100 cursor-not-allowed" disabled>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Students</label>
                    <input type="number" v-model="form.minimum_number_students" class="input-field-add-student w-full"
                        :class="{ 'border-red-500': form.errors.minimum_number_students }">
                    <p v-if="form.errors.minimum_number_students" class="text-red-500 text-sm mt-1">
                        {{ form.errors.minimum_number_students }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Maximum Students</label>
                    <input type="number" v-model="form.maximum_number_students" class="input-field-add-student w-full"
                        :class="{ 'border-red-500': form.errors.maximum_number_students }">
                    <p v-if="form.errors.maximum_number_students" class="text-red-500 text-sm mt-1">
                        {{ form.errors.maximum_number_students }}
                    </p>
                </div>
            </div>
        </ReusableModal>
    </div>
</template>