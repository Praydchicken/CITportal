<script setup>
import AuthLayout from '../../Layouts/AuthLayout.vue';
import InputField from '../../Components/InputField.vue';
import PrimaryBtn from '../../Components/PrimaryBtn.vue';
import TextLink from '../../Components/TextLink.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import ErrorMessages from '../../Components/ErrorMessages.vue';
import SessionMessage from '../../Components/SessionMessage.vue';

defineProps({
	status: String,
	status_error: String,
});

const form = useForm({
	email: null,
});

const countdown = ref(0);
const countdownDuration = 30; // seconds

const startCountdown = () => {
  countdown.value = countdownDuration;
  const interval = setInterval(() => {
    if (countdown.value > 0) {
      countdown.value--;
    } else {
      clearInterval(interval);
    }
  }, 1000);
};

const submit = () => {
	  form.post(route('password.email'), {
	onSuccess: () => {
	  startCountdown();
	},
  });
	
};
</script>

<template>
	<AuthLayout title="Sign in">
		<form @submit.prevent="submit" class="w-sm">
			<div class="flex justify-center mb-6">
				<img src="../../../../public/assets/img/cit_logo.png" alt="CIT Logo"
					class="h-50 w-50 object-contain pointer-events-none user-drag:none" />
			</div>
			<TextLink routeName="login" label="Back to login"></TextLink>
			<h1 class="text-4xl font-bold my-3">Forgot Password?</h1>
			<p class="text-slate-500 py-3">Enter the email address you used and we’ll send you instructions to reset
				your password.</p>


			<ErrorMessages :status_errors="status_error" ></ErrorMessages>
			<SessionMessage :status="status"></SessionMessage>
			<div class="flex flex-wrap -mx-3 mb-6">
				<div class="w-full px-3 mb-10">
					<InputField label="Email" type="email" placeholder="Email" v-model="form.email"
						:error="form.errors.email"   :class="{ 'border-red-500': form.errors.email }" />
				</div>
			</div>

			<div class="w-full px-3 mb-3 justify-center flex">
				<PrimaryBtn :disabled="form.processing || countdown > 0" :countdown="countdown">
          Send Email
        </PrimaryBtn>
			</div>
		</form>
	</AuthLayout>
</template>
