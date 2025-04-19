<script setup>
import DashboardLayout from '../../components/AdminDashboardLayout.vue';
import { defineProps } from "vue";
import { router } from '@inertiajs/vue3';

defineOptions({
  layout: DashboardLayout
});

const props = defineProps({
  title: String,
  studentGrade: Object,
  teacher: Object,
  curriculum: Object
});

const approve = async (studentGradeId) => {
    try {
        await router.put(route('admin.student.approve.grade', { studentGradeId }));

        // Optional: Add a toast or alert for user feedback
        alert('Grade approved successfully!');
        
        // Optional: refresh data or update local state here if needed
        // e.g., fetchGrades();

    } catch (error) {
        console.error('Error approving grade:', error);
        alert('Something went wrong while approving the grade.');
    }
};

const reject = async (studentGradeId) => {
    try {
        await router.put(route('admin.student.reject.grade', { studentGradeId }));

        // Optional: Add a toast or alert for user feedback
        alert('Grade rejected');
        
        // Optional: refresh data or update local state here if needed
        // e.g., fetchGrades();

    } catch (error) {
        console.error('Error approving grade:', error);
        alert('Something went wrong while rejecting the grade.');
    }
};
</script>

<template>
        <!-- show table where it has already grade -->
    <template v-if="props.studentGrade !== null">
        <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Greade Results</h3>
            <h5 class="font-medium mb-8">Professor: {{ props.teacher.first_name }} {{ props.teacher.last_name }}</h5>
        </div>
        <div class="overflow-x-auto bg-white rounded-lg">

            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">Course Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">PRELIM</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">MIDTERM</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">FINAL</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">RAW GRADE</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">RAW GRADE EQUIVALENT</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">REMARKS</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider bg-[#1a3047] text-[#ffff]">STATUS</th>
                    </tr>
                </thead>

                <!-- ✅ Move v-if to tbody instead of using a div -->
                <tbody :class="['divide-y divide-gray-200 bg-white']">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ props.curriculum.subject_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ props.curriculum.course_code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{Math.floor(props.studentGrade.prelim_grade)}}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{Math.floor(props.studentGrade.midterm_grade)}}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{Math.floor(props.studentGrade.final_grade)}}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{Math.floor(props.studentGrade.raw_grade)}}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{props.studentGrade.gwa_equivalent}}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{props.studentGrade.grade_remarks}}</td>
                        <td :class="{
                            'text-green-600': props.studentGrade.grade_status === 'APPROVED',
                            'text-red-600': props.studentGrade.grade_status === 'REJECTED',
                            'text-yellow-600': props.studentGrade.grade_status === 'PENDING',
                            'px-6 py-4 whitespace-nowrap text-sm': true
                            }">
                            {{ props.studentGrade.grade_status }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

         <ul class="flex items-center gap-x-3 mt-10">
            <li>
                <button @click="approve(props.studentGrade.id)" class='bg-green-500 text-white px-3 py-1 rounded cursor-pointer'>
                    APPROVE
                </button>
            </li>

             <li>
                <button @click="reject(props.studentGrade.id)" class='bg-red-500 text-white px-3 py-1 rounded cursor-pointer'>
                    REJECT
                </button>
            </li>
        </ul>
    </template>

    <template v-else>
        <div class="text-center py-12 bg-white rounded-lg shadow-sm w-full">
            <h3 class="mt-2 text-sm font-medium text-gray-900">No Grade Results Found</h3>
        </div>
    </template>
</template>
