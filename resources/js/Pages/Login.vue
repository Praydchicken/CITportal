<script setup>
// import TextLink from '../../Components/TextLink.vue';
import InputField from '../Components/InputField.vue';
import PrimaryBtn from '../Components/PrimaryBtn.vue';
// import CheckBox from '../../Components/CheckBox.vue';
import { useForm } from '@inertiajs/vue3';
const form = useForm({
	email: null,
	password: null,
});

const submit = () => {
	form.post(route("login"), {
		onSubmit: () => form.reset('password'),
		onError: () => form.reset('password')
	});

};
</script>

<template>

	<Head title=" | Sign In"></Head>
	<div class="flex flex-col md:flex-row min-h-screen overflow-y-auto">
		<div class="hidden md:flex w-1/2 bg-blue-900 text-white items-center justify-center p-8">
			<div class="text-center">
				<h1 class="text-4xl font-bold mb-2">WELCOME TO CIT PORTAL</h1>
				<p class="text-slate-400">Commitment to Excellence to Serve God and the Community</p>
				
			</div>
		</div>
		
		<div class="md:w-1/2 w-full flex items-center justify-center min-h-screen p-8">
			<form @submit.prevent="submit" class="w-full max-w-lg">
				<div class="flex justify-center mb-6">
                    <img src="../../../public/assets/img/cit_logo.png" alt="CIT Logo" class="h-50 w-50 object-contain pointer-events-none user-drag:none" />
                </div>
				<h1 class="text-4xl font-bold mb-6">Sign In</h1>

				<div class="flex flex-wrap -mx-3 mb-6">
					<div class="w-full px-3 mb-10">
						<InputField label="Email" type="Email" placeholder="Email" icon="at" v-model="form.email"
							:error="form.errors.email" />
					</div>
					<div class="w-full px-3 mb-10">
						<InputField label="Password" type="password" placeholder="Password (8+ characters)" icon="key"
							v-model="form.password" :error="form.errors.password" />

						<div class="flex w-full justify-between mt-10">
							<p routeName="register" label="Forgot Password?">Remember Me</p>
							<p routeName="register" label="Forgot Password?">Forgot Password</p>
						</div>
					</div>
				</div>
				<div class="w-full px-3 mb-3 justify-center flex">
					<PrimaryBtn :disabled="form.processing" class="w-full py-3 disabled:opacity-50 disabled:pointer-events-none">
						Sign In <i class="fa-solid fa-chevron-right "></i></PrimaryBtn>
					
				</div>
				<div class="py-3 flex items-center text-sm text-gray-800 before:flex-1 before:border-t before:border-gray-200 before:me-6 after:flex-1 after:border-t after:border-gray-200 after:ms-6">OR</div>
			</form>
		</div>
	</div>
</template>