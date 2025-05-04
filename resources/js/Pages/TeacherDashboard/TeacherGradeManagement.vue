<script setup>
import TeacherDashboardLayout from '../../components/teacherDashboardLayout/TeacherDashboardLayout.vue';
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { defineProps } from 'vue';
import { Head } from '@inertiajs/vue3';

defineOptions({
    layout: TeacherDashboardLayout
});

const props = defineProps({
    auth: Object,
    students: {
        type: Array,
        default: () => []
    },
    schoolYears: {
        type: Array,
        default: () => []
    },
    yearLevels: {
        type: Array,
        default: () => []
    },
    sections: {
        type: Array,
        default: () => []
    },
    activeSchoolYear: {
        type: Object,
        required: true
    }
});

// Filter states - initialize with active school year
const selectedSchoolYear = ref(props.activeSchoolYear?.id || '');
const selectedYearLevel = ref('');
const selectedSection = ref('');
const searchQuery = ref('');

// Watch for changes in activeSchoolYear prop
watch(() => props.activeSchoolYear, (newVal) => {
    selectedSchoolYear.value = newVal?.id || '';
}, { immediate: true });

// Filtered students based on selected filters and search query
const filteredStudents = computed(() => {
    // If no school year selected, default to active school year
    const schoolYearId = selectedSchoolYear.value || props.activeSchoolYear?.id;

    return props.students.filter(student => {
        const schoolYearMatch = !schoolYearId || student.school_year_id == schoolYearId;
        const yearLevelMatch = !selectedYearLevel.value || student.year_level == selectedYearLevel.value;
        const sectionMatch = !selectedSection.value || student.section == selectedSection.value;
        const searchMatch = !searchQuery.value ||
            student.student_number.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            student.first_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            student.last_name.toLowerCase().includes(searchQuery.value.toLowerCase());

        return schoolYearMatch && yearLevelMatch && sectionMatch && searchMatch;
    });
});

// Add new method to handle viewing courses
const viewCourse = (student, semester) => {
    router.get(route('teacher.grade.management.view.course.grade', {
        student: student.id,
        semester: semester
    }));
};

// Clear all filters - resets to active school year
const clearFilters = () => {
    selectedSchoolYear.value = props.activeSchoolYear?.id || '';
    selectedYearLevel.value = '';
    selectedSection.value = '';
    searchQuery.value = '';
};
</script>

<template>

    <Head :title="'Teacher Grade Management'" />
    <div class="p-6">
        <!-- Page Title -->
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Student Grades Management</h1>

        <!-- Filters Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- School Year Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
                    <select v-model="selectedSchoolYear"
                        class="w-full bg-white p-2 text-sm rounded-lg border-2 border-gray-300 focus:border-[#1a3047] focus:outline-none">
                        <option :value="props.activeSchoolYear?.id">
                            {{ props.activeSchoolYear?.school_year }} (Active)
                        </option>
                        <option v-for="year in schoolYears.filter(y => y.id !== props.activeSchoolYear?.id)"
                            :key="year.id" :value="year.id">
                            {{ year.school_year }}
                        </option>
                    </select>
                </div>

                <!-- Year Level Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Year Level</label>
                    <select v-model="selectedYearLevel"
                        class="w-full bg-white p-2 text-sm rounded-lg border-2 border-gray-300 focus:border-[#1a3047] focus:outline-none">
                        <option value="">All Year Levels</option>
                        <option v-for="level in yearLevels" :key="level.id" :value="level.year_level">
                            {{ level.year_level }}
                        </option>
                    </select>
                </div>

                <!-- Section Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                    <select v-model="selectedSection"
                        class="w-full bg-white p-2 text-sm rounded-lg border-2 border-gray-300 focus:border-[#1a3047] focus:outline-none">
                        <option value="">All Sections</option>
                        <option v-for="section in sections" :key="section.id" :value="section.section">
                            {{ section.section }}
                        </option>
                    </select>
                </div>

                <!-- Search Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search Student</label>
                    <div class="flex gap-2">
                        <input type="text" v-model="searchQuery" placeholder="Search by name or student number..."
                            class="flex-1 bg-white p-2 text-sm rounded-lg border-2 border-gray-300 focus:border-[#1a3047] focus:outline-none">
                        <button @click="clearFilters"
                            class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600 text-sm">
                            Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Students Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#1a3047]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                Student Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                First Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Last
                                Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Year
                                Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                Section</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                First Semester</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                Second Semester</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="student in filteredStudents" :key="student.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ student.student_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ student.first_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ student.last_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ student.year_level }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ student.section }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <button @click="viewCourse(student, 1)"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path fill-rule="evenodd"
                                            d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    View Course
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <button @click="viewCourse(student, 2)"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path fill-rule="evenodd"
                                            d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    View Course
                                </button>
                            </td>
                        </tr>
                        <tr v-if="filteredStudents.length === 0">
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No students found matching the selected filters.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<style scoped>
.inline-flex {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    transition: all 0.2s;
}

.inline-flex:hover {
    background-color: #f3f4f6;
    border-color: #d1d5db;
}
</style>
