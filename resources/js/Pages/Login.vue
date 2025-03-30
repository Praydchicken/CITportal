<script setup>
import LoginNavbar from '../components/LoginNavbar.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faEnvelope, faEye } from '@fortawesome/free-solid-svg-icons'

import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

library.add(faEnvelope, faEye)

defineProps({ errors: Object })

// Vue will track changes to the form data.
// When the user types in the form, Vue automatically updates this object.
const form = reactive({
	email: null,
	password: null,
})

function submit() {
	router.post('/login', form)
}
</script>

/** TODO: MAKE A LOGIN FUNCTION */
<template>
	<LoginNavbar>
		<main class="h-[87vh] overflow-hidden flex justify-center items-center">    
			<form @submit.prevent="submit" class="card w-[25%] h-fit flex justify-center items-center gap-y-2 flex-col p-4">
				<!-- Title -->
				<div>
					<h1 class="text-center text-3xl font-bold text-[#1a3047]">Welcome to CIT Portal</h1>
				</div>

				<!-- input fields -->
				<div class="mb-4 w-full">
					<div>
						<div class="input-container relative">
							<input type="email" name="email" placeholder="Enter email" v-model="form.email">
							<!-- icon -->
							<span class="text-[#1a3047]">
								<font-awesome-icon icon="envelope" />
							</span>
						</div>
						<!-- error message -->
						<p v-if="errors.email" class="text-red-500 text-sm">{{ errors.email }}</p>
					</div>
					
					<div>
						<div class="input-container">
							<div class="relative">
								<input type="password" name="password" placeholder="Enter password" v-model="form.password">
								<!-- icon -->
								<span class="text-[#1a3047]">
									<font-awesome-icon icon="eye" />
								</span>
							</div>
							<!-- error message -->
							<p v-if="errors.password" class="text-red-500 text-sm">{{ errors.password }}</p>
							
							<!-- reset password -->
							<div class="flex justify-between items-center mt-2">
								<p class="text-sm">Forgot Password?</p>
								<button class="underlineBtn-custom text-sm cursor-pointer underline underline-offset-1">Reset here</button>
							</div>
						</div>
					</div>
					
					
				</div>

				<!-- Buttons -->
				<div class="flex flex-col items-center gap-y-4 w-full">
					<button type="submit" class="loginBtn-custom liquid cursor-pointer w-full p-2">Login</button>
					<button class="underlineBtn-custom cursor-pointer text-sm underline underline-offset-1">Activate SPCC Account</button>
				</div>
			</form>
		</main>
	</LoginNavbar>
</template>