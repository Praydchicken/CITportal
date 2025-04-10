<script setup>
import DashboardSideNav from '../DashboardSideNav.vue';
import DashboardHeader from '../DashboardHeader.vue';

import { usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed } from 'vue';

defineProps({
  title: String
});

// Get the current Inertia page
const page = usePage();

const adminLinks = [
  { name: "Dashboard", url: '#' },
  { name: "Profile Information", url: '#' },
  { name: "Faculty Load", url: '#' },
  { name: "Grade Report", url: '#' },
];

const headerContent = computed(() => [
  {
    title: page.props.title || "Admin Portal",
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
