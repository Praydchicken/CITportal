<template>
    <div class="bg-white shadow overflow-hidden rounded-md">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">
                        Semester
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">
                        Year Level
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">
                        Course Code
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">
                        GWA Equivalent
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-[#1a3047]">
                        Grade Remarks
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(grade, index) in grades" :key="grade.id"
                    :class="index % 2 === 0 ? 'bg-neutral-300' : 'bg-gray-200'">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ grade.semester_id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ grade.year_level }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ grade.course_code }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ grade.gwa_equivalent }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span
                            :class="{ 'text-green-500': grade.grade_remarks === 'PASSED', 'text-red-500': grade.grade_remarks !== 'PASSED' }">
                            {{ grade.grade_remarks }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { defineProps } from 'vue';

defineProps({
    grades: {
        type: Array,
        required: true,
    },
});
</script>