<script setup>
import TeacherDashboardLayout from '../../components/teacherDashboardLayout/TeacherDashboardLayout.vue';
import { defineProps, ref, onMounted, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const page = usePage();
const props = defineProps({
    auth: Object,
    student: {
        type: Object,
        required: true
    },
    courses: {
        type: Array,
        required: true
    },
    semester: {
        type: Number,
        required: true
    },
    schoolYears: {
        type: Array,
        required: true
    },
    activeSchoolYear: {
        type: Object,
        required: true
    }
});

// console.log(props.courses)

// Get teacher's assigned subjects from auth data - more defensive approach

// Filter courses to only show subjects assigned to the teacher
const assignedCurriculumIds = computed(() => {
    const ids = page.props?.assignedCurriculumIds;
    console.log('Raw assignedCurriculumIds:', ids); // Debug log

    // Handle various cases where the data might not be an array
    if (!ids) return [];
    if (Array.isArray(ids)) return ids;
    if (typeof ids === 'object' && ids !== null) return Object.values(ids);

    // If it's a single ID (number or string), wrap it in an array
    return [ids].filter(Boolean);
});

const assignedCourses = computed(() => {
    const ids = assignedCurriculumIds.value;
    console.log('Processed assignedCurriculumIds:', ids); // Debug log

    // Ensure we're working with an array
    const idArray = Array.isArray(ids) ? ids : [ids].filter(Boolean);

    return props.courses.filter(course =>
        idArray.includes(course.id) ||
        idArray.some(id => id == course.id) // Loose comparison for type safety
    );
});

// Alternative approach if you're sure about the structure but want to be safe
const user = computed(() => {
    return page.props?.auth?.user || page.props?.user || {};
});

const teacherAssignedSubjects = computed(() => {
    // Check multiple possible paths to the assigned subjects
    return (
        page.props?.auth?.user?.assigned_subjects ||
        page.props?.user?.assigned_subjects ||
        []
    );
});

// const teacherAssignedSubjects = computed(() => {
//     return user.value?.assigned_subjects || [];
// });

// Create a reactive copy of courses with initialized grades
const courseGrades = ref([]);
const computedResults = ref([]);
const showResults = ref(false);
const isEditMode = ref(false);
const selectedSchoolYear = ref(props.activeSchoolYear?.id || null);

// Create form with additional required fields
const form = useForm({
    results: [],
    editGrades: [],
    school_year_id: props.activeSchoolYear?.id || null,
    semester_id: props.semester || 1
});

const isReadOnly = ref(false);

// Filter courses to only show grades for selected school year
const filteredCourses = computed(() => {
    return props.courses.map(course => {
        const gradeForYear = course.grades[selectedSchoolYear.value];
        return {
            ...course,
            grade: gradeForYear || null
        };
    });
});

console.log(filteredCourses);


// Check if any grades exist for the selected school year
const hasGrades = computed(() => {
    return filteredCourses.value.some(course => course.grade !== null);
});

onMounted(() => {
    // Initialize with existing grades if available
    courseGrades.value = props.courses.map(course => {
        const gradeForYear = course.grades[selectedSchoolYear.value];
        return {
            ...course,
            grade: gradeForYear ? {
                id: gradeForYear.id, // Include grade ID for editing
                prelim_grade: Number(gradeForYear.prelim_grade) || null,
                midterm_grade: Number(gradeForYear.midterm_grade) || null,
                final_grade: Number(gradeForYear.final_grade) || null,
                raw_grade: Number(gradeForYear.raw_grade) || null,
                gwa_equivalent: Number(gradeForYear.gwa_equivalent) || null,
                grade_remarks: gradeForYear.grade_remarks || null,
                grade_status: gradeForYear.grade_status || null
            } : {
                prelim_grade: null,
                midterm_grade: null,
                final_grade: null,
                raw_grade: null,
                gwa_equivalent: null,
                grade_remarks: null,
                grade_status: null
            }
        };
    });

    isReadOnly.value = hasGrades.value;
});

const getRemarks = (convertedGrade) => {
    return convertedGrade < 5.0 ? 'PASSED' : 'FAILED';
};


const computeResult = () => {
    computedResults.value = courseGrades.value.map(course => {
        const prelim = course.grade.prelim_grade;
        const midterm = course.grade.midterm_grade;
        const final = course.grade.final_grade;

        if (
            isNaN(prelim) || isNaN(midterm) || isNaN(final) ||
            prelim < 50 || prelim > 100 ||
            midterm < 50 || midterm > 100 ||
            final < 50 || final > 100
        ) {
            Swal.fire({
                title: 'Invalid grade input',
                text: 'Please enter all grades for all subjects and it must minimum of 50 and maximum of 100',
                icon: 'error',
                confirmButtonColor: '#1a3047'
            });
            return null;
        }

        // In your computeResult method
        const raw = calculateRawGrade(
            Number(course.grade.prelim_grade),
            Number(course.grade.midterm_grade),
            Number(course.grade.final_grade)
        );
        const converted = calculateConvertedGrade(raw);
        const remarks = getRemarks(converted);

        return {
            subject_name: course.subject_name,
            course_code: course.course_code,
            units: course.units,
            prelim_grade: prelim,
            midterm_grade: midterm,
            final_grade: final,
            raw_grade: raw,
            converted_grade: converted,
            remarks: remarks
        };
    });

    // Push computed values back into courseGrades
    computedResults.value.forEach((result, index) => {
        if (courseGrades.value[index]) {
            courseGrades.value[index].grade.raw_grade = result.raw_grade;
            courseGrades.value[index].grade.gwa_equivalent = result.converted_grade;
            courseGrades.value[index].grade.total_gwa = result.converted_grade;
            courseGrades.value[index].grade.grade_remarks = result.remarks;
        }
    });

    showResults.value = true;
};

const hasMissingGrades = computed(() => {
    return assignedCourses.value.some(course => {
        const gradeExists = filteredCourses.value.some(fc =>
            fc.id === course.id && fc.grade !== null
        );
        return !gradeExists;
    });
});

const calculateRawGrade = (prelim, midterm, final) => {
    const prelimGrade = prelim;
    const midtermGrade = midterm;
    const finalGrade = final;

    const result = Math.floor((prelimGrade + midtermGrade + finalGrade) / 3);
    return result;
}

const calculateConvertedGrade = (rawGrade) => {
    const grade = parseFloat(rawGrade);

    if (grade >= 97) return 1.00;
    if (grade >= 94) return 1.25;
    if (grade >= 91) return 1.50;
    if (grade >= 88) return 1.75;
    if (grade >= 85) return 2.00;
    if (grade >= 82) return 2.25;
    if (grade >= 79) return 2.50;
    if (grade >= 76) return 2.75;
    if (grade >= 75) return 3.00;
    return 5.00;
};

const validateGrades = () => {
    for (const course of courseGrades.value) {
        if (
            course.grade.prelim_grade === null ||
            course.grade.midterm_grade === null ||
            course.grade.final_grade === null
        ) {
            Swal.fire({
                title: 'Missing Grades',
                text: 'Please enter all grades for all subjects',
                icon: 'error',
                confirmButtonColor: '#1a3047'
            });
            return false;
        }
    }
    return true;
};


const submitGrade = () => {
    // Prepare the form data with all required fields
    if (!validateGrades()) return;
    form.results = courseGrades.value.map(course => ({
        curriculum_id: course.id,
        subject_name: course.subject_name,
        course_code: course.course_code, // 👈 required by validation too
        prelim_grade: Number(course.grade.prelim_grade),
        midterm_grade: Number(course.grade.midterm_grade),
        final_grade: Number(course.grade.final_grade),
        raw_grade: Number(course.grade.raw_grade),
        converted_grade: Number(course.grade.gwa_equivalent), // 👈 ADD THIS
        remarks: course.grade.grade_remarks, // 👈 Match validation key: 'remarks'
        grade_status: 'Pending'
    }));


    form.school_year_id = selectedSchoolYear.value;
    form.semester_id = props.semester;
    form.student_id = props.student.id;

    form.post(`/teacher/grade/management/add/${props.student.id}/course/grade`, {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                title: 'Success!',
                text: 'Grades submitted successfully!',
                icon: 'success',
                confirmButtonColor: '#1a3047'
            });

            // Refresh the page to get updated data
            router.reload();
        },
        onError: (errors) => {
            console.error('Submit Grade Errors:', errors); // 👈 this will help

            let message = 'Failed to submit grades';

            if (typeof errors === 'object') {
                // Show first error if it exists
                const firstError = Object.values(errors)[0];
                message = Array.isArray(firstError) ? firstError[0] : firstError;
            }

            Swal.fire({
                title: 'Error!',
                text: message,
                icon: 'error',
                confirmButtonColor: '#1a3047'
            });
        }
    });
};

const submitEdit = () => {
    // Prepare the form data with all required fields
    form.editGrades = courseGrades.value.map(course => ({
        curriculum_id: course.id, // The curriculum ID
        id: course.grade?.id || null, // The grade ID if editing existing grade
        prelim_grade: Number(course.grade.prelim_grade),
        midterm_grade: Number(course.grade.midterm_grade),
        final_grade: Number(course.grade.final_grade),
        raw_grade: Number(course.grade.raw_grade),
        gwa_equivalent: Number(course.grade.gwa_equivalent),
        grade_remarks: course.grade.grade_remarks,
        grade_status: course.grade.grade_status || 'PENDING'
    }));

    // Set all required top-level fields
    form.school_year_id = selectedSchoolYear.value;
    form.semester_id = props.semester;
    //   form.student_id = props.student.id; // Make sure this is set

    // The endpoint should match your controller route
    form.put(`/teacher/grade/management/edit/${props.student.id}/course/grade`, {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                title: 'Success!',
                text: 'Grades updated successfully!',
                icon: 'success',
                confirmButtonColor: '#1a3047'
            });
            router.reload();
        },
        onError: (errors) => {
            console.error('Update Grade Errors:', errors);

            let message = 'Failed to update grades';
            if (errors?.message) {
                message = errors.message;
            } else if (typeof errors === 'object') {
                const firstError = Object.values(errors)[0];
                message = Array.isArray(firstError) ? firstError[0] : firstError;
            }

            Swal.fire({
                title: 'Error!',
                text: message,
                icon: 'error',
                confirmButtonColor: '#1a3047'
            });
        }
    });
};

const editGrades = () => {
    isReadOnly.value = false;
    isEditMode.value = true;

    // Initialize the form with existing grades for the selected school year
    courseGrades.value = filteredCourses.value.map(course => ({
        ...course,
        grade: course.grade ? {
            prelim_grade: Number(course.grade.prelim_grade) || null,
            midterm_grade: Number(course.grade.midterm_grade) || null,
            final_grade: Number(course.grade.final_grade) || null,
            raw_grade: Number(course.grade.raw_grade) || null,
            gwa_equivalent: Number(course.grade.gwa_equivalent) || null,
            grade_remarks: course.grade.grade_remarks || null,
            grade_status: course.grade.grade_status || null
        } : {
            prelim_grade: null,
            midterm_grade: null,
            final_grade: null,
            raw_grade: null,
            gwa_equivalent: null,
            grade_remarks: null,
            grade_status: null
        }
    }));

    showResults.value = false;
};

const deleteGrade = (gradeId) => {
    console.log('Attempting to delete grade with ID:', gradeId); // Add this line

    if (!gradeId) {
        Swal.fire({
            title: 'Error!',
            text: 'Grade ID is missing',
            icon: 'error',
            confirmButtonColor: '#1a3047'
        });
        return;
    }

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
            router.delete(`/teacher/grade/management/delete/${gradeId}/course/grade`, {
                preserveScroll: true,
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Grade has been deleted.',
                        icon: 'success',
                        confirmButtonColor: '#1a3047'
                    });
                    // Reset these states to show the input form
                    isReadOnly.value = false;
                    isEditMode.value = false;
                    showResults.value = false;
                    router.reload();
                },
                onError: (error) => {
                    console.error('Delete error:', error); // Log the error
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to delete grade.',
                        icon: 'error',
                        confirmButtonColor: '#1a3047'
                    });
                },
            });
        }
    });
};

const changeSchoolYear = (yearId) => {
    selectedSchoolYear.value = yearId;
    isReadOnly.value = hasGrades.value;
    isEditMode.value = false;
    showResults.value = false;
    computedResults.value = [];
};

</script>

<template>
    <TeacherDashboardLayout>
        <div class="p-6">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">
                        {{ student.first_name }} {{ student.last_name }}'s
                        {{ student.year_level }} [{{ semester == 1 ? 'First' : 'Second' }} Sem] - Grades
                    </h2>

                    <!-- School Year Selector -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">School Year:</label>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="year in schoolYears" :key="year.id" @click="changeSchoolYear(year.id)"
                                :class="[
                                    'px-4 py-2 rounded-md text-sm font-medium',
                                    selectedSchoolYear === year.id
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                ]">
                                {{ year.school_year }}
                                <span v-if="year.id === activeSchoolYear?.id" class="ml-1">(Current)</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Show message if no assigned subjects -->
                    <div v-if="assignedCourses.length === 0" class="text-center py-12 bg-white rounded-lg shadow-sm">
                        <h3 class="text-lg font-medium text-gray-900">No Assigned Subjects</h3>
                        <p class="mt-2 text-sm text-gray-600">You don't have any subjects assigned to you for this
                            student.</p>
                        <p class="mt-1 text-sm text-gray-500">Please contact the administrator if you believe this is
                            incorrect.</p>
                    </div>
                    <!-- Input Table (only show if no grades exist for selected school year or in edit mode) -->
                    <template v-else>
                        <div v-if="!hasGrades || hasMissingGrades || isEditMode"
                            class="overflow-x-auto bg-white rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                            #</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                            Subject</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                            Code</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                            Prelim</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                            Midterm</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                            Final</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(course, index) in courseGrades" :key="course.id"
                                        :class="index % 2 === 0 ? 'bg-neutral-300' : 'bg-gray-200'">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                                            course.subject_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                                            course.course_code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" v-model="course.grade.prelim_grade"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                min="50" max="100" step="0.01" placeholder="Enter score">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" v-model="course.grade.midterm_grade"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                min="50" max="100" step="0.01" placeholder="Enter score">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" v-model="course.grade.final_grade"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                min="50" max="100" step="0.01" placeholder="Enter score">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>


                        <!-- Compute Button (only show if no grades exist for selected school year or in edit mode) -->
                        <div v-if="(!hasGrades || hasMissingGrades || isEditMode) && !showResults" class="mt-6">
                            <button @click="computeResult"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Compute Result
                            </button>
                        </div>

                        <!-- Results Table (shown after computation) -->
                        <div v-if="showResults" class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Computed Results</h3>
                            <div class="overflow-x-auto bg-white rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                                Subject</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                                Course Code</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                                Raw Grade</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                                Converted Grade</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                                Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(result, index) in computedResults" :key="index">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                                                result.subject_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                                                result.course_code }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                                                result.raw_grade }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                                                result.converted_grade.toFixed(2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm" :class="{
                                                'text-green-600 font-medium': result.remarks === 'PASSED',
                                                'text-red-600 font-medium': result.remarks === 'FAILED'
                                            }">{{ result.remarks }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-6">
                                <button v-if="isEditMode" @click="submitEdit"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Update Grades
                                </button>

                                <button v-else @click="submitGrade"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Save Grades
                                </button>
                            </div>
                        </div>

                        <!-- Grades Table (shown when grades exist for selected school year) -->
                        <div v-if="hasGrades && !isEditMode" class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Grade Results for
                                {{schoolYears.find(y => y.id === selectedSchoolYear)?.school_year}}</h3>
                            <div class="overflow-x-auto bg-white rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                                Subject</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                                Course Code</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                                PRELIM</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                                MIDTERM</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                                FINAL</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                                RAW GRADE</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                                GWA EQUIVALENT</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                                REMARKS</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                                STATUS</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">
                                                ACTION</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200"
                                        :class="filteredCourses[0]?.grade?.grade_status?.toLowerCase() === 'pending' ? 'bg-gray-100' : 'bg-white'">
                                        <tr v-for="(course, index) in filteredCourses" :key="index">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                                                course.subject_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                                                course.course_code }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                                                course.grade?.prelim_grade ? Math.floor(course.grade.prelim_grade) :
                                                    'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                                                course.grade?.midterm_grade ? Math.floor(course.grade.midterm_grade) :
                                                    'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                                                course.grade?.final_grade ? Math.floor(course.grade.final_grade) : 'N/A'
                                                }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                                                course.grade?.raw_grade ? Math.floor(course.grade.raw_grade) : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                                                Number(course.grade?.gwa_equivalent).toFixed(2) || 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm" :class="{
                                                'text-green-600 font-medium': course.grade?.grade_remarks === 'PASSED',
                                                'text-red-600 font-medium': course.grade?.grade_remarks === 'FAILED'
                                            }">
                                                {{ course.grade?.grade_remarks || 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <span :class="{
                                                    'text-yellow-600': course.grade?.grade_status === 'Pending',
                                                    'text-green-600': course.grade?.grade_status === 'Approved',
                                                    'text-red-600': course.grade?.grade_status === 'Rejected'
                                                }">
                                                    {{ course.grade?.grade_status || 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <ul class="flex justify-center items-center gap-x-3">
                                                    <li>
                                                        <button @click="editGrades"
                                                            class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600"
                                                            :disabled="course.grade?.grade_status === 'Approved'"
                                                            :class="{ 'opacity-50 cursor-not-allowed': course.grade?.grade_status === 'Approved' }">
                                                            Edit
                                                        </button>
                                                    </li>
                                                    <!-- <li>
                                                        <button @click="deleteGrade(course.grade?.id)"
                                                            class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600"
                                                            :disabled="course.grade?.grade_status === 'Approved'"
                                                            :class="{ 'opacity-50 cursor-not-allowed': course.grade?.grade_status === 'Approved' }">
                                                            Delete
                                                        </button>
                                                    </li> -->
                                                </ul>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Empty state when no grades exist for selected school year -->
                        <div v-if="!hasGrades && !hasMissingGrades && !isEditMode"
                            class="mt-8 text-center py-12 bg-white rounded-lg shadow-sm w-full">
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No Grades Found for
                                "{{ student.first_name }} {{ student.last_name }}"</h3>
                            <p class="mt-1 text-sm text-gray-500">You can add grades for students above.</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </TeacherDashboardLayout>
</template>