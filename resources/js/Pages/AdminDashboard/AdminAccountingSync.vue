<script setup>
import { ref, watch, nextTick, computed } from 'vue'
import axios from 'axios'
import DashboardLayout from '../../Components/AdminDashboardLayout.vue'

defineOptions({
	layout: DashboardLayout,
})

const studentId = ref('')
const records = ref([])
const loading = ref(false)
const syncLoading = ref(false)
const inputField = ref(null)
const syncLogs = ref([])
const lastSynced = ref(null)
const recordsUpdatedCount = ref(0)

const expandedSet = ref(new Set())

const fetchStudentRecords = async () => {
	if (!studentId.value) return

	loading.value = true
	try {
		const response = await axios.get(`http://127.0.0.1:8001/api/student/${studentId.value}/financial-records`)
		records.value = response.data
		expandedSet.value.clear() // collapse all by default
	} catch (error) {
		console.error('Error fetching records:', error)
		records.value = []
	} finally {
		loading.value = false
	}
}

const syncAllRecords = async () => {
	syncLoading.value = true
	try {
		const response = await axios.post('http://127.0.0.1:8000/admin/sync-financial-records')

		const { logs, last_synced, updated_count } = response.data

		syncLogs.value = logs || ['No logs returned.']
		lastSynced.value = last_synced || new Date().toISOString()
		recordsUpdatedCount.value = updated_count || 0

		console.log('Sync response:', response.data)
	} catch (error) {
		console.error('Error syncing all records:', error)
		syncLogs.value = ['An error occurred while syncing records.']
	} finally {
		syncLoading.value = false
	}
}

watch(studentId, async (newValue) => {
	if (newValue.length === 9) {
		await fetchStudentRecords()
	} else if (newValue.trim() === '') {
		records.value = []
		expandedSet.value.clear()
	}

	nextTick(() => {
		if (inputField.value) inputField.value.focus()
	})
})

const handleInputChange = () => {
	studentId.value = studentId.value.replace(/\D/g, '').slice(0, 9)
}

const toggleAccordion = (key) => {
	if (expandedSet.value.has(key)) {
		expandedSet.value.delete(key)
	} else {
		expandedSet.value.add(key)
	}
}

const expandAll = () => {
	records.value.forEach(record => {
		const key = record.school_year + 'SEM' + record.semester
		expandedSet.value.add(key)
	})
}

const collapseAll = () => {
	expandedSet.value.clear()
}

const totalBalance = computed(() =>
	records.value.reduce((sum, r) => sum + parseFloat(r.balance || 0), 0)
)
</script>


<template>
	<div class="w-full bg-gray-100 rounded-lg p-6 mb-8 border border-gray-300 space-y-4">
		<div class="flex justify-between items-center">
			<h2 class="text-lg font-semibold text-gray-800">Sync Logs</h2>
			<span class="text-sm text-gray-600">
				Last Synced: {{ lastSynced ? new Date(lastSynced).toLocaleString() : 'N/A' }}
			</span>
		</div>

		<!-- Scrollable Log List -->
		<div class="max-h-25 overflow-y-auto pr-2 bg-slate-200 rounded-lg p-3 border border-gray-300">
			<ul class="text-sm text-gray-700 list-disc pl-6 space-y-1">
				<li v-for="(log, index) in syncLogs" :key="index">{{ log }}</li>
			</ul>
		</div>
	</div>

	<button @click="syncAllRecords" :disabled="syncLoading"
		class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg shadow-md focus:ring-2 focus:ring-green-500 disabled:bg-gray-400 transition duration-200">
		Sync All Records
	</button>
	<div class="p-8 max-w-7xl mx-auto space-y-8 bg-white">
		<h1 class="text-4xl font-semibold text-gray-800 tracking-wide">Student Financial Records</h1>

		<!-- Input Section -->
		<div class="flex items-center gap-4 mb-6">
			<input v-model="studentId" @input="handleInputChange" ref="inputField"
				class="border-2 border-gray-300 p-4 rounded-lg w-72 text-gray-800 placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:outline-none transition duration-200"
				placeholder="Enter Student ID" :disabled="loading || syncLoading" maxlength="9" />
		</div>

		<!-- Expand/Collapse All -->
		<div v-if="records.length" class="flex justify-end gap-3">
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
		<div v-if="records.length">
			<div v-for="(record, index) in records" :key="index" class="space-y-4">
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
							<!-- Tuition Fee -->
							<div class="font-semibold border-b border-gray-300 pb-2">Tuition Fee</div>
							<div class="border-b border-gray-300 pb-2">
								{{ record.tuition_fee ? '₱' + Number(record.tuition_fee).toLocaleString(undefined, {
									minimumFractionDigits: 2
								}) : '₱0.00' }}
							</div>

							<!-- Discount -->
							<div class="font-semibold border-b border-gray-300 pb-2">Discount</div>
							<div class="border-b border-gray-300 pb-2 text-gray-600">
								-{{ record.discount ? '₱' + Number(record.discount).toLocaleString(undefined, {
									minimumFractionDigits: 2
								}) : '₱0.00' }}
							</div>

							<!-- Adjustment -->
							<div class="font-semibold border-b border-gray-300 pb-2">Adjustment</div>
							<div class="border-b border-gray-300 pb-2 text-gray-600">
								-{{ record.adjustment ? '₱' + Number(record.adjustment).toLocaleString(undefined, {
									minimumFractionDigits: 2
								}) : '₱0.00' }}
							</div>

							<!-- Paid -->
							<div class="font-semibold border-b border-gray-300 pb-2">Paid</div>
							<div class="border-b border-gray-300 pb-2 text-gray-600">
								-{{ record.amount_paid ? '₱' + Number(record.amount_paid).toLocaleString(undefined, {
									minimumFractionDigits: 2
								}) : '₱0.00' }}
							</div>

							<!-- Balance -->
							<div class="col-span-2 border-t-2 border-black pt-4 grid grid-cols-2 gap-4">
								<div class="font-semibold">Balance</div>
								<div
									:class="[parseFloat(record.balance || 0) !== 0 ? 'text-red-600 font-semibold' : 'text-gray-800']">
									{{ '₱' + Number(record.balance || 0).toLocaleString(undefined, {
										minimumFractionDigits: 2
									}) }}
								</div>
							</div>
						</div>
					</div>
				</Transition>
			</div>

			<!-- Total Balance -->
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
	</div>
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
