<script setup>
import StudentDashboardLayout from '../../components/StudentDashboardLayout.vue';
import GradeReportTable from '../../components/GradeReportTable.vue';
import { ref } from 'vue';

defineOptions({
    layout: StudentDashboardLayout
});

const props = defineProps({
    title: Array,
    gradesByLevel: Array,
});

console.log(props.gradesByLevel)

const getSemesterName = (semesterId) => {
    return semesterId === 1 ? 'First Semester' : 'Second Semester';
};

const getYearLevelName = (levelId) => {
    const levels = {
        1: 'First Year',
        2: 'Second Year',
        3: 'Third Year',
        4: 'Fourth Year'
    };
    return levels[levelId] || `Year ${levelId}`;
};

const expandedSemesters = ref({});

const toggleSemester = (yearLevel, semesterId) => {
    const key = `${yearLevel}-${semesterId}`;
    expandedSemesters.value[key] = !expandedSemesters.value[key];
};

// Simple function to group grades by semester (no computed needed)
const groupBySemester = (grades) => {
    const semesters = {};
    grades.forEach(grade => {
        if (!semesters[grade.semester_id]) {
            semesters[grade.semester_id] = {
                id: grade.semester_id,
                name: getSemesterName(grade.semester_id),
                grades: [],
                totalUnits: 0
            };
        }
        semesters[grade.semester_id].grades.push(grade);
        semesters[grade.semester_id].totalUnits += grade.units || 0;
    });
    return Object.values(semesters);
};
</script>

<template>
    <Head title="Grade Report" />
    <div>
        <div v-if="props.gradesByLevel.length === 0" class="text-center py-12 bg-white rounded-lg shadow-sm">
            <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="M18 10.5V6l-2.11 1.06A4 4 0 0 1 12 12a4 4 0 0 1-3.89-4.94L5 5.5L12 2l7 3.5v5zM12 9l-2-1c0 1.1.9 2 2 2s2-.9 2-2zm2.75-3.58L12.16 4.1L9.47 5.47l2.6 1.32zM12 13c2.67 0 8 1.33 8 4v3H4v-3c0-2.67 5.33-4 8-4m0 1.9c-3 0-6.1 1.46-6.1 2.1v1.1h12.2V17c0-.64-3.13-2.1-6.1-2.1" />
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">No Grades Available</h3>
            <p class="mt-1 text-sm text-gray-500">Your approved grades will appear here once available.</p>
        </div>

        <div v-else class="space-y-6">
            <div v-for="levelData in props.gradesByLevel" :key="levelData.level"
                class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                <!-- Year Level Header -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-800">
                            {{ getYearLevelName(levelData.level) }}
                        </h2>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{new Set(levelData.grades.map(g => g.semester_id)).size}} Semester(s)
                        </span>
                    </div>
                </div>

                <!-- Semester Cards -->
                <div class="divide-y divide-gray-200">
                    <div v-for="semester in groupBySemester(levelData.grades)" :key="semester.id"
                        class="p-4 hover:bg-gray-50 transition-colors">
                        <button @click="toggleSemester(levelData.level, semester.id)"
                            class="w-full flex justify-between items-center text-left"
                            :aria-expanded="expandedSemesters[`${levelData.level}-${semester.id}`]">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-blue-600 font-medium">{{ semester.id }}</span>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-800">{{ semester.name }}</h3>
                                    <p class="text-sm text-gray-500">
                                        {{ semester.grades.length }} subject(s)
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                                    :class="{ 'rotate-90': expandedSemesters[`${levelData.level}-${semester.id}`] }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </button>

                        <!-- Semester Details -->
                        <div v-show="expandedSemesters[`${levelData.level}-${semester.id}`]" class="mt-4 pl-14">
                            <GradeReportTable :grades="semester.grades" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
[aria-expanded="true"] svg {
    transform: rotate(90deg);
}
</style>