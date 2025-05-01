<script setup>
import DashboardLayout from '../../components/AdminDashboardLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

defineOptions({
  layout: DashboardLayout
});

defineProps({
    schoolYears: Array
});

const form = ref({
    school_year: '',
    is_active: false
});

const submitForm = () => {
    router.post(route('admin.school.year.store'), {
        school_year: form.value.school_year,
        school_year_status: form.value.is_active ? 'Active' : 'Upcoming'
    }, {
        onSuccess: () => {
            form.value.school_year = '';
            form.value.is_active = false;
        }
    });
};

const setActive = (id) => {
    if (confirm("Are you sure you want to set this school year as active?")) {
        router.post(route('admin.school.year.set-active', id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // You can optionally show a toast or message
            }
        });
    }
};

const deleteSchoolYear = (id) => {
    if (confirm("Are you sure you want to delete this school year?")) {
        router.delete(route('admin.school.year.delete', id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Optionally show a success message or toast
                alert('School year deleted successfully.');
            },
            onError: (error) => {
                // Optionally handle the error, maybe show a toast or message
                alert('Failed to delete school year.');
            }
        });
    }
};
</script>

<template>
    <Head title="School Year" />

    <AdminDashboardLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-[#172554]">School Year Management</h2>
                </div>

                <!-- Add School Year Form -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <form @submit.prevent="submitForm" class="flex gap-4 items-end">
                        <div class="flex-1">
                            <label for="school_year" class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
                            <input type="text" 
                                   id="school_year" 
                                   v-model="form.school_year" 
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="e.g. 2023-2024">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" 
                                   id="is_active" 
                                   v-model="form.is_active" 
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="is_active" class="block text-sm text-gray-700">Set as Active</label>
                        </div>
                        <button type="submit"
                                class="px-4 py-2 bg-[#172554] text-white rounded-md text-sm font-medium hover:bg-blue-700">
                            Add School Year
                        </button>
                    </form>
                </div>

                <!-- School Years Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-[#172554]">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                        School Year
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="year in schoolYears" :key="year.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ year.school_year }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="[
                                            'px-2 py-1 text-xs font-medium rounded-full',
                                            year.school_year_status === 'Active' ? 'bg-green-100 text-green-800'
                                                : year.school_year_status === 'Upcoming' ? 'bg-yellow-100 text-yellow-800'
                                                : 'bg-gray-100 text-gray-800'
                                            ]">
                                            {{ year.school_year_status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <button 
                                            v-if="year.school_year_status !== 'Active'"
                                            class="text-green-600 hover:text-green-800 mr-3"
                                            @click="setActive(year.id)">
                                            Set Active
                                        </button>
                                        <button 
                                            class="text-red-600 hover:text-red-800"
                                            @click="deleteSchoolYear(year.id)">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminDashboardLayout>
</template>
