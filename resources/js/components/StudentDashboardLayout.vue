<script setup>
import DashboardSideNav from '../Components/DashboardSideNav.vue';
import DashboardHeader from '../Components/DashboardHeader.vue';

import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';

defineProps({
  title: String
});

// Get the current Inertia page
const page = usePage();

const adminLinks = [
  // { name: "Dashboard", url: route('student.dashboard')},
  { name: "Profile Information", url: route('student.profile') },
  { name: "Schedule", url: route('student.schedule') },
  // { name:"View Balance", url: route('student.balances') }
  { name: "Grade Report", url: route('student.grade.view') },
  { name: "View Announcement", url: route('student.announcement.view') }
];

const headerContent = computed(() => [
  {
    title: page.props.title || "Student portal",
    name: `${page.props.auth?.user?.student?.first_name || ''} ${page.props.auth?.user?.student?.last_name || ''}`.trim() || "Student",
    userType: "Student"
  }
]);
</script>

<template>
  <div class="dashboard-container relative">
    <DashboardSideNav :links="adminLinks" />
    <main class="w-screen h-screen pl-[20%]">
      <div class="h-screen p-10">
        <DashboardHeader :content="headerContent" />
        <div class="mt-24">
          <slot /> <!-- Renders page content here -->
        </div>
      </div>
    </main>
  </div>
</template>
