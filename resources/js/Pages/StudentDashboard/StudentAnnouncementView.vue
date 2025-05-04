<script setup>
import StudentDashboardLayout from '../../components/StudentDashboardLayout.vue';
import { format } from 'date-fns'; // Import the format function

defineOptions({
    layout: StudentDashboardLayout
});

const props = defineProps({
    announcements: {
        type: Array,
        default: () => []
    }
});

console.log(props.announcements)

const formatDate = (dateString) => {
    if (!dateString) {
        return 'N/A';
    }
    try {
        const date = new Date(dateString);
        return format(date, 'MMM dd, yyyy hh:mm a'); // Keep the adjusted format
    } catch (error) {
        // console.error('Error formatting date:', error);
        return 'Invalid Date';
    }
};
</script>

<template>
    <Head title="Announcements" />
    <div>
        <h2 class="text-xl font-semibold mb-4">Teacher Announcements</h2>
        <div v-if="props.announcements.length > 0">
            <div v-for="announcement in props.announcements" :key="announcement.id"
                class="bg-white shadow rounded-md p-6 mb-4">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ announcement.title_announcement }}</h3>
                <p class="text-gray-600 mb-4">{{ announcement.description_announcement }}</p>

                <div class="border-t border-gray-200 pt-4">
                    <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                        <div>
                            <span class="font-medium">Deadline:</span>
                            {{ formatDate(announcement.deadline_announcement) }}
                        </div>
                        <div>
                            <span class="font-medium">Published At:</span>
                            {{ formatDate(announcement.published_at) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-center py-12 bg-white rounded-lg shadow-sm">
            <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="M12 8H4a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h1v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-4h3l5 4V4zm3 7.6L13 14H4v-4h9l2-1.6zm6.5-3.6c0 1.71-.96 3.26-2.5 4V8c1.53.75 2.5 2.3 2.5 4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No Announcements</h3>
        </div>
    </div>
</template>