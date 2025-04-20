<script setup>
import StudentDashboardLayout from '../../components/StudentDashboardLayout.vue';
import { ref } from 'vue';

defineOptions({
    layout: StudentDashboardLayout
});

const dummyAnnouncements = ref([
    {
        id: 1,
        title: 'Important Update for Monday',
        announcement: 'Please remember that our class will be held in Lab A on Monday due to maintenance in the regular classroom.',
        description: 'This is a temporary change. We will return to our usual room on Tuesday. Make sure to bring your lab notebooks.',
        deadline: new Date(2025, 4, 26, 17, 0, 0).toISOString(), // May 26, 2025 5:00 PM
        published_at: new Date(2025, 4, 20, 10, 30, 0).toISOString(), // April 20, 2025 10:30 AM
    },
    {
        id: 2,
        title: 'Upcoming Quiz on Chapter 3',
        announcement: 'A quiz covering the material from Chapter 3 will be held next Wednesday. Make sure to review your notes and readings.',
        description: 'The quiz will consist of multiple-choice and short answer questions. It will cover all topics discussed in Chapter 3.',
        deadline: new Date(2025, 4, 28, 0, 0, 0).toISOString(), // May 28, 2025 Midnight
        published_at: new Date(2025, 4, 19, 14, 0, 0).toISOString(), // April 19, 2025 2:00 PM
    },
    {
        id: 3,
        title: 'Reminder: Project Proposal Due Friday',
        announcement: 'Don\'t forget that your project proposals are due this Friday by the end of the day.',
        description: 'The proposal should include your project topic, objectives, and a brief outline of your methodology.',
        deadline: new Date(2025, 4, 23, 23, 59, 59).toISOString(), // May 23, 2025 11:59 PM
        published_at: new Date(2025, 4, 18, 9, 0, 0).toISOString(), // April 18, 2025 9:00 AM
    },
]);

const formatDate = (dateString) => {
    if (!dateString) {
        return 'N/A';
    }
    try {
        const date = new Date(dateString);
        return format(date, 'MMM dd, yyyy hh:mm a'); // Adjusted format to include year
    } catch (error) {
        // console.error('Error formatting date:', error);
        return 'Invalid Date';
    }
};
</script>

<template>
    <div>
        <h2 class="text-xl font-semibold mb-4">Teacher Announcements</h2>
        <div v-if="dummyAnnouncements.length > 0">
            <div v-for="announcement in dummyAnnouncements" :key="announcement.id"
                class="bg-white shadow rounded-md p-6 mb-4">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ announcement.title }}</h3>
                <p class="text-gray-600 mb-4">{{ announcement.announcement }}</p>

                <div class="border-t border-gray-200 pt-4">
                    <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                        <div>
                            <span class="font-medium">Deadline:</span>
                            {{ formatDate(announcement.deadline) }}
                        </div>
                        <div>
                            <span class="font-medium">Published At:</span>
                            {{ formatDate(announcement.published_at) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-gray-500">
            No announcements yet.
        </div>
    </div>
</template>
