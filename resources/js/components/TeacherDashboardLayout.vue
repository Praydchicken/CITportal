<template>
  <div class="dashboard-container relative">
    <DashboardSideNav :links="teacherLinks" />
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

<script setup>
import DashboardSideNav from './DashboardSideNav.vue';
import DashboardHeader from './DashboardHeader.vue';

import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
  title: String
});

// Get the current Inertia page
const page = usePage();

const teacherLinks = [
  { name: "Dashboard", url: route('teacher.dashboard') },
  { name: "Class Schedule", url: route('teacher.class.schedule') },
  { name: "Assigned Subjects", url: '#' },
  { name: "Assigned Students", url: '#' },
  { name: "Grade Management", url: '#' },
  { name: "Announcements", url: '#' },
];

const headerContent = computed(() => [
  {
    title: page.props.title || "Teacher Portal",
    name: `${page.props.auth?.user?.teacher?.first_name || ''} ${page.props.auth?.user?.teacher?.last_name || ''}`.trim() || "Teacher",
    userType: "Teacher"
  }
]);
</script>

<style scoped>
.dashboard-container {
  min-height: 100vh;
  background-color: #f3f4f6;
}
</style> 