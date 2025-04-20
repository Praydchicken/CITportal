<script setup>
import StudentDashboardLayout from '../../components/StudentDashboardLayout.vue';
import { defineProps, ref, computed } from "vue";

defineOptions({
    layout: StudentDashboardLayout,
});


const props = defineProps({
    studentInfo: Object,
    studentFinancial: Array,
    auth: Object
})

console.log(props.studentFinancial);


const expandedSet = ref(new Set())

console.log(props.studentFinancial);

const toggleAccordion = (key) => {
    if (expandedSet.value.has(key)) {
        console.log(expandedSet.value.has(key));
        expandedSet.value.delete(key)
    } else {
        expandedSet.value.add(key)
        console.log(expandedSet.value.add(key));

    }
}

const expandAll = () => {
    props.studentFinancial.forEach(record => {
        const key = record.school_year + 'SEM' + record.semester
        expandedSet.value.add(key)
    })
}

const collapseAll = () => {
    expandedSet.value.clear()
}

const totalBalance = computed(() =>
    props.studentFinancial.reduce((total, record) => total + parseFloat(record.balance || 0), 0))

</script>
<template>

    <Head title="Profile Info" />
    <!-- student details -->
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
                <p class="flex-1">{{ props.studentInfo.first_name }} {{ props.studentInfo.middle_name }} {{
                    props.studentInfo.last_name }}</p>
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

    <!-- student balance -->
    <h1 class="text-2xl font-bold text-[#1a3047] m-3 p-3">My Balance</h1>

    <div v-if="props.studentFinancial.length" class="flex justify-end gap-3 my-5">
        <button @click="expandAll"
            class="bg-gray-200 hover:bg-gray-300 text-sm text-gray-800 px-4 py-2 rounded-lg transition duration-200">
            Expand All
        </button>
        <button @click="collapseAll"
            class="bg-gray-200 hover:bg-gray-300 text-sm text-gray-800 px-4 py-2 rounded-lg transition duration-200">
            Collapse All
        </button>
    </div>

    <!-- Loading Spinner -->
    <div v-if="loading" class="text-center text-lg font-medium text-gray-500">Loading...</div>

    <!-- Records -->
    <div v-if="props.studentFinancial.length">
        <div v-for="(record, index) in props.studentFinancial" :key="index" class="space-y-4">
            <div class="cursor-pointer bg-slate-300 hover:bg-gray-500 shadow-lg rounded-lg p-4 transition-all duration-300 ease-in-out flex justify-between items-center border border-gray-200"
                @click="toggleAccordion(record.school_year + 'SEM' + record.semester)">
                <span class="font-semibold text-gray-800">
                    {{ record.school_year }} SEM {{ record.semester }}
                </span>
            </div>

            <Transition name="accordion">
                <div v-show="expandedSet.has(record.school_year + 'SEM' + record.semester)"
                    class="bg-zinc-300 p-6 border-t-2 border-gray-500 rounded-lg shadow-md space-y-4 mb-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-gray-700">
                        <div class="font-semibold border-b border-gray-300 pb-2">Tuition Fee</div>
                        <div class="border-b border-gray-300 pb-2">
                            {{ record.tuition_fee ? '₱' + Number(record.tuition_fee).toLocaleString(undefined, {
                                minimumFractionDigits: 2
                            }) : '₱0.00' }}
                        </div>

                        <div class="font-semibold border-b border-gray-300 pb-2">Discount</div>
                        <div class="border-b border-gray-300 pb-2 text-gray-600">
                            -{{ record.discount ? '₱' + Number(record.discount).toLocaleString(undefined, {
                                minimumFractionDigits: 2
                            }) : '₱0.00' }}
                        </div>

                        <div class="font-semibold border-b border-gray-300 pb-2">Adjustment</div>
                        <div class="border-b border-gray-300 pb-2 text-gray-600">
                            -{{ record.adjustment ? '₱' + Number(record.adjustment).toLocaleString(undefined, {
                                minimumFractionDigits: 2
                            }) : '₱0.00' }}
                        </div>

                        <div class="font-semibold border-b border-gray-300 pb-2">Paid</div>
                        <div class="border-b border-gray-300 pb-2 text-gray-600">
                            -{{ record.amount_paid ? '₱' + Number(record.amount_paid).toLocaleString(undefined, {
                                minimumFractionDigits: 2
                            }) : '₱0.00' }}
                        </div>

                        <div class="col-span-2 border-t-2 border-black pt-4 grid grid-cols-2 gap-4">
                            <div class="font-semibold">Balance</div>
                            <div
                                :class="[parseFloat(record.balance || 0) !== 0 ? 'text-red-600 font-semibold' : 'text-gray-800']">
                                {{ '₱' + Number(record.balance || 0).toLocaleString(undefined, {
                                    minimumFractionDigits:
                                        2
                                }) }}
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- Total Balance -->
        <!-- <div class="mt-8 text-right text-lg font-semibold text-gray-800">
				Total Balance: <span class="text-red-600">₱{{ totalBalance.toLocaleString(undefined, {
					minimumFractionDigits: 2
				}) }}</span>
			</div> -->
        <div class="mt-8 text-right text-lg font-semibold text-gray-800">
            Total Balance: <span class="text-red-600">₱{{ totalBalance.toLocaleString(undefined, {
                minimumFractionDigits: 2
            }) }}</span>
        </div>
    </div>

    <!-- No Records Found -->
    <p v-else-if="!loading && studentId" class="mt-4 text-gray-500 text-center">
        No records found for student ID {{ studentId }}.
    </p>
</template>

<style scoped>
.accordion-enter-from,
.accordion-leave-to {
    max-height: 0;
    opacity: 0;
    overflow: hidden;
}

.accordion-enter-active,
.accordion-leave-active {
    transition: max-height 0.4s ease, opacity 0.4s ease;
}

.accordion-enter-to,
.accordion-leave-from {
    max-height: 500px;
    opacity: 1;
}

.fas {
    transition: transform 0.3s ease-in-out;
}

button:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}
</style>