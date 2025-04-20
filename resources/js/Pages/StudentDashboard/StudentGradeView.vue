<script setup>
import StudentDashboardLayout from '../../components/StudentDashboardLayout.vue';
import GradeReportTable from '../../components/GradeReportTable.vue';
import { defineProps } from 'vue';

defineOptions({
    layout: StudentDashboardLayout
});

const props = defineProps({
    title: Array,
    gradesByLevel: Array,
})

console.log(props.studentGrades);


</script>

<template>
    <div>
        <!-- <h1 class="text-2xl font-bold mb-4">{{ title }}</h1> -->

        <div v-for="levelData in gradesByLevel" :key="levelData.level" class="mb-8">
            <h2 class="text-xl font-semibold mb-2">Year Level: {{ levelData.level }}</h2>
            <GradeReportTable :grades="levelData.grades" />
        </div>
    </div>
</template>