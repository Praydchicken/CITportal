<script setup>
import DashboardLayout from '../../components/AdminDashboardLayout.vue';
import { defineProps, reactive, watch } from "vue";
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3' // or @inertiajs/react if using React
import Notification from '../../components/Notification.vue';
import LogoutBtn from '../../components/LogoutBtn.vue';

defineOptions({
  layout: DashboardLayout // ✅ Assign the layout
});

const page = usePage()

const props = defineProps({
    studentInfo: Array
})


const studentCurriculum = props.studentInfo.curriculum;
const studentNo = props.studentInfo.student_no;

// console.log(props.studentInfo);

// In your main layout component or app.js
watch(() => page.props.flash, (newVal) => {
  if (newVal?.success) {
    showNotification(newVal.success, 'success');
  }
  if (newVal?.error) {
    showNotification(newVal.error, 'error');
  }
}, { deep: true });

const tableHeaders = [
  { label: 'Course Code', key: 'course_code' },
  { label: 'Subject', key: 'subject_name' },
  { label: 'Lecture Unit', key: 'lecture_units' },
  { label: 'Lab Unit', key: 'lab_units' },
  { label: 'Unit', key: 'units' },
  // Make it similar to the name of the attributes from the controller
  { label: 'Grades', key: 'grade' },
  { label: 'Remarks', key: 'grade_remark' },
];

// Create a reactive object for the notification state
const notification = reactive({
  show: false,
  message: '',
  type: 'success'
});

// Function to show the notification
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

const printTOR = () => {
    router.visit(`/admin/preview/print/tor/${studentNo}`)
}

const promoteStudent = (studentNo, promotionType) => {
  router.put(route('admin.student.promote', studentNo), {
    promotion_type: promotionType
  }, {
    preserveScroll: true,
    onSuccess: () => {
      if (page.props.flash?.success) {
        showNotification(page.props.flash.success, 'success');
        // Optional: refresh student data
        router.reload({ only: ['studentInfo'] });
      }
    },
    onError: (errors) => {
      if (page.props.flash?.error) {
        showNotification(page.props.flash.error, 'error');
      } else {
        showNotification('Promotion failed', 'error');
      }
    }
  });
};

</script>

<template>
    <div class="relative">
         <!-- Notification component -->
        <Teleport to="body">
        <Notification 
            :show="notification.show" 
            :message="notification.message" 
            :type="notification.type" 
        />
        </Teleport>
    

        <div class="w-[100%] h-[40vh] flex gap-x-4">
            <!-- Initial profile picture -->
            <div class="card w-[40%] h-auto flex flex-col items-center justify-center gap-y-4 p-10">
                <div class="bg-blue-200 rounded-full flex justify-center items-center w-[100px] h-[100px]">
                    <h1 class="font-bold text-6xl text-white">{{ props.studentInfo.first_name.slice(0, 1) }}</h1>
                </div>

                <div class="flex flex-col items-center justify-center">
                    <p class="font-medium">{{ props.studentInfo.student_no }}</p>
                    <p class="font-medium">BSIT - {{ props.studentInfo.year_level }}</p>
                </div>
            </div>

            <!-- Other Information -->
        <div class="card w-[60%] h-auto p-2">
                <!-- Full Name -->
                <div class="flex border-b border-gray-300 mb-3 p-4">
                    <span class="w-40 font-bold">Full Name</span>
                    <p class="flex-1">{{ props.studentInfo.first_name }} {{ props.studentInfo.middle_name }} {{ props.studentInfo.last_name }}</p>
                </div>

                <!-- Email -->
                <div class="flex border-b border-gray-300 mb-3 p-4">
                    <span class="w-40 font-bold">Email</span>
                    <p class="flex-1">{{ props.studentInfo.email }}</p>
                </div>

                <div class="flex border-b border-gray-300 mb-3 p-4">
                    <span class="w-40 font-bold">Phone</span>
                    <p class="flex-1">{{ props.studentInfo.phone_number }}</p>
                </div>

                <div class="flex border-b border-gray-300 mb-3 p-4">
                    <span class="w-40 font-bold">Address</span>
                    <p class="flex-1">{{ props.studentInfo.address }}</p>
                </div>
            </div>
        </div>

       <!-- Reusable Table Component -->
        <div class="overflow-x-auto mt-8 pb-6 pt-6">
             <button @click="printTOR" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded mb-3">
                 Print Grades
            </button>
            <!-- Display curriculum groups in tabs or accordion -->
            <div v-for="(curriculumGroup, index) in props.studentInfo.curricula" :key="index">
                <div class="flex justify-between items-center mb-4">
                    <h1 class="font-bold text-xl">
                        {{ curriculumGroup.group_name }}
                        <span v-if="curriculumGroup.is_current" class="text-sm text-green-600 ml-2">(Current Semester)</span>
                    </h1>
                    
                   <!-- Promotion Buttons Section -->
                    <div v-if="curriculumGroup.is_current" class="flex gap-x-3">
                      <!-- Next Semester Button (shows only if NOT in 2nd semester) -->
                      <button 
                          v-if="!props.studentInfo.semester.includes('2nd') && 
                              !props.studentInfo.semester.includes('Second')"
                          @click="promoteStudent(props.studentInfo.student_no, 'semester')" 
                          class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded"
                      >
                          Promote to Next Semester
                      </button>

                      <!-- Next Year Level Button (shows only in 2nd semester) -->
                      <button 
                          v-if="(props.studentInfo.semester.includes('2nd') || 
                              props.studentInfo.semester.includes('Second')) &&
                              !props.studentInfo.is_final_semester"
                          @click="promoteStudent(props.studentInfo.student_no, 'year')" 
                          class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded"
                      >
                          Promote to Next Year Level
                      </button>

                        <!-- Button for graduation students shows only for 4th yr 2nd semester-->
                        <button 
                        v-if="(props.studentInfo.semester.includes('2nd') || 
                                props.studentInfo.semester.includes('Second')) &&
                                props.studentInfo.year_level.includes('4th')"
                        @click="promoteStudent(props.studentInfo.student_no, 'year')" 
                        :class="[
                          'px-3 py-1 rounded',
                          props.studentInfo.status === 'Graduated' 
                            ? 'bg-gray-400 text-white cursor-not-allowed' 
                            : 'bg-blue-500 hover:bg-blue-600 text-white cursor-pointer'
                        ]"
                        :disabled="props.studentInfo.status === 'Graduated'"
                      >
                        Promote as Graduated
                      </button>
                    </div>
                </div>

                <table class="w-full divide-y divide-gray-200 mb-8 shadow-2xl">
                    <thead>
                        <tr class="bg-[#1a3047] text-white">
                            <th v-for="header in tableHeaders" :key="header.key" 
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                {{ header.label }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(subject, subIndex) in curriculumGroup.subjects" :key="subject.curriculum_id"
                            :class="subIndex % 2 === 0 ? 'bg-zinc-200' : 'bg-white'">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ subject.course_code }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ subject.subject_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ subject.lecture_units }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ subject.lab_units }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ Number(subject.lecture_units) + Number(subject.lab_units) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ subject.gwa_equivalent ?? "No Record" }}
                            </td>
                            <td class="px-6 py-4 text-sm" 
                                :class="subject.grade_remarks === 'PASSED' ? 'text-green-500' : 'text-red-500'">
                                {{ subject.grade_remarks ?? "No Record" }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>