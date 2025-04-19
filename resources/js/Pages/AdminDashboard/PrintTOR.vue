<script setup>
const props = defineProps({
  studentInfo: Object
});

const printTOR = () => {
  window.print();
};

// Format current date
const currentDate = new Date().toLocaleDateString('en-US', {
  year: 'numeric',
  month: 'long',
  day: 'numeric'
});

// Calculate semester averages and overall average
const calculateAverages = () => {
  const results = {
    semesters: [],
    overall: null
  };

  let totalWeightedSum = 0;
  let totalUnits = 0;

  // Convert to array if it's a collection
  const curricula = Array.isArray(props.studentInfo.curricula) ? 
    props.studentInfo.curricula : 
    Object.values(props.studentInfo.curricula);

  curricula.forEach(curriculum => {
    let semesterWeightedSum = 0;
    let semesterUnits = 0;
    let validGradesCount = 0;

    // Convert subjects to array if it's a collection
    const subjects = Array.isArray(curriculum.subjects) ? 
      curriculum.subjects : 
      Object.values(curriculum.subjects);

    subjects.forEach(subject => {
      const gwa = parseFloat(subject.gwa_equivalent);
      if (!isNaN(gwa)) {
        const units = Number(subject.lecture_units) + Number(subject.lab_units);
        semesterWeightedSum += gwa * units;
        semesterUnits += units;
        validGradesCount++;
      }
    });

    const semesterAverage = validGradesCount > 0 ? (semesterWeightedSum / semesterUnits) : null;
    
    results.semesters.push({
      group_name: curriculum.group_name,
      average: semesterAverage,
      units: semesterUnits
    });

    if (semesterAverage !== null) {
      totalWeightedSum += semesterWeightedSum;
      totalUnits += semesterUnits;
    }
  });

  results.overall = totalUnits > 0 ? (totalWeightedSum / totalUnits) : null;

  return results;
};

const averages = calculateAverages();
</script>

<template>
  <div class="max-w-4xl mx-auto bg-white p-10 shadow-lg print:p-0 print:shadow-none print:bg-white print:text-black">
    <!-- School Header -->
    <div class="text-center mb-8">
      <img src="../../../../public/assets/img/cit_logo.png" alt="School Logo" class="h-20 mx-auto mb-4" />
      <h1 class="text-2xl font-bold uppercase">Systems Plus Computer College</h1>
      <p class="text-sm">141 - 143 10th Avenue Caloocan City</p>
      <h2 class="text-lg font-semibold mt-4 uppercase">Transcript of Records</h2>
    </div>

    <!-- Student Info -->
    <div class="mb-6 grid grid-cols-2 gap-4 text-sm">
      <div><strong>Student No:</strong> {{ studentInfo.student_no }}</div>
      <div><strong>Email:</strong> {{ studentInfo.email }}</div>
      <div><strong>Name:</strong> {{ studentInfo.last_name }}, {{ studentInfo.first_name }} {{ studentInfo.middle_name }}</div>
      <div><strong>Contact:</strong> {{ studentInfo.phone_number }}</div>
      <div><strong>Address:</strong> {{ studentInfo.address }}</div>
      <div><strong>Section:</strong> {{ studentInfo.section }}</div>
      <div><strong>Year Level:</strong> {{ studentInfo.year_level }}</div>
    </div>

    <!-- Grades Tables by Semester -->
    <div v-for="(curriculum, index) in studentInfo.curricula" :key="index" class="mb-8">
      <h3 class="font-bold text-lg mb-2">{{ curriculum.group_name }}</h3>
      
      <table class="w-full text-sm border border-black border-collapse">
        <thead class="bg-gray-100 border border-black">
          <tr>
            <th class="border border-black px-2 py-1">Course Code</th>
            <th class="border border-black px-2 py-1">Subject Name</th>
            <th class="border border-black px-2 py-1">Units</th>
            <th class="border border-black px-2 py-1">Grade</th>
            <th class="border border-black px-2 py-1">GWA</th>
            <th class="border border-black px-2 py-1">Remarks</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(subject, subIndex) in curriculum.subjects" :key="subIndex">
            <td class="border border-black px-2 py-1">{{ subject.course_code }}</td>
            <td class="border border-black px-2 py-1">{{ subject.subject_name }}</td>
            <td class="border border-black px-2 py-1 text-center">
              {{ Number(subject.lecture_units) + Number(subject.lab_units) }}
            </td>
            <td class="border border-black px-2 py-1 text-center">
              {{ subject.raw_grade || '-' }}
            </td>
            <td class="border border-black px-2 py-1 text-center">
              {{ subject.gwa_equivalent || '-' }}
            </td>
            <td class="border border-black px-2 py-1 text-center" 
                :class="{'text-green-600': subject.grade_remarks === 'PASSED', 
                        'text-red-600': subject.grade_remarks && subject.grade_remarks !== 'PASSED'}">
              {{ subject.grade_remarks || 'NOT TAKEN' }}
            </td>
          </tr>
          <!-- Semester Average Row -->
          <tr class="bg-gray-50 font-semibold">
            <td colspan="2" class="border border-black px-2 py-1 text-right">Semester Average:</td>
            <td class="border border-black px-2 py-1 text-center">{{ averages.semesters[index].units }}</td>
            <td colspan="2" class="border border-black px-2 py-1 text-center">
              {{ averages.semesters[index].average ? averages.semesters[index].average.toFixed(2) : 'N/A' }}
            </td>
            <td class="border border-black px-2 py-1 text-center">
              {{ averages.semesters[index].average ? 'COMPLETED' : 'INCOMPLETE' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Overall Average -->
    <!-- <div class="mt-6 p-4 border border-black bg-gray-100">
      <div class="flex justify-between items-center">
        <span class="font-bold">Cumulative Weighted Average (GWA):</span>
        <span class="font-bold text-lg">{{ averages.overall ? averages.overall.toFixed(2) : 'N/A' }}</span>
      </div>
    </div> -->

    <!-- Footer -->
    <div class="mt-12 text-sm">
      <p><strong>Date Issued:</strong> {{ currentDate }}</p>
      <p class="mt-4">Certified by:</p>
      <p class="mt-10 uppercase font-bold underline">Prof. Juan Dela Cruz</p>
      <p>Registrar</p>
    </div>

    <!-- Print Button -->
    <div class="mt-6 text-center no-print">
      <button @click="printTOR" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow">
        🖨️ Print Transcript
      </button>
    </div>
  </div>
</template>

<style>
@media print {
  .no-print {
    display: none !important;
  }
  body {
    margin: 0;
    font-size: 12pt;
  }
  table {
    page-break-inside: avoid;
  }
  h3 {
    page-break-after: avoid;
  }
}
</style>