<script setup>
import DashboardSideNav from '../components/DashboardSideNav.vue';
import DashboardHeader from '../components/DashboardHeader.vue';

import { usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

defineProps({
  title: String
});

// Get the current Inertia page
const page = usePage();

const adminLinks = [
  { name: "Dashboard", url: route('admin.dashboard') },
  { name: "Student Information", url: route('student.info') },
  { name: "Faculty Load", url: "#" },
  { name: "Section Management", url: route('admin.section.management') },
  { name: "Schedule  Management", url: route('admin.schedule.management') },
  { name: "Curriculum Configuration", url: route('admin.curriculum.config') },
  { name: "Admin Announcement", url: route('admin.announcement') },
];

const headerContent = [
  {
    title: page.props.title || "Admin Portal",
    name: "Im the admin",
    userType: "Admin"
  }
];
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
