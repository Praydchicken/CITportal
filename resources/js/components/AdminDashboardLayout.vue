<script setup>
import DashboardSideNav from '../components/DashboardSideNav.vue';
import DashboardHeader from '../components/DashboardHeader.vue';

import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
  title: String
});

// Get the current Inertia page
const page = usePage();

const adminLinks = [
  { name: "Dashboard", url: route('admin.dashboard') },
  { name: "Manage Students", url: route('student.info') },
  { name: "Faculty Load", url: route('admin.faculty.load') },
  { name: "Grade Report", url: route('admin.student.grade') },
  { name: "Section Management", url: route('admin.section.management') },
  { name: "Curriculum Configuration", url: route('admin.curriculum.config') },
  // { name: "Manage Rooms", url: route('admin.class.room') },
  { name: "School Year Settings", url: route('admin.school.year') },
  { name: "Accounting API", url: route('admin.accountingSync') },
];

const headerContent = computed(() => [
  {
    title: page.props.title || "Admin Portal",
    name: `${page.props.auth?.user?.admin?.first_name || ''} ${page.props.auth?.user?.admin?.last_name || ''}`.trim() || "Admin",
    userType: "Admin"
  }
]);
</script>

<template>
  <div class="dashboard-container relative">
    <DashboardSideNav :links="adminLinks" :componentName="page.props.component"/>
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
