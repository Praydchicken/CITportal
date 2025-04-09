<script setup>
import AuthLayout from '../../Layouts/AuthLayout.vue';
import InputField from '../../Components/InputField.vue';
import PrimaryBtn from '../../Components/PrimaryBtn.vue';
import { useForm } from '@inertiajs/vue3';
import ErrorMessages from '../../Components/ErrorMessages.vue';

const props = defineProps({
    token: String,
    email: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: null,
    password_confirmation: null,
});

const submit = () => {
    form.post(route("password.update"), {
        onFinish: () => form.reset('password, password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout title="Reset Password">
        <form @submit.prevent="submit" class="w-sm">
            <div class="flex justify-center mb-6">
                <img src="../../../../public/assets/img/cit_logo.png" alt="CIT Logo"
                    class="h-50 w-50 object-contain pointer-events-none user-drag:none" />
            </div>
            <h1 class="text-4xl font-bold mb-6">Create new password</h1>
            <p class="text-slate-500 py-3">Your new password must be different from previous used passowrd.</p>
            <div class="flex flex-wrap -mx-3 mb-6">
                <div class="w-full px-3 mb-10">
                    <InputField label="Password" type="password" v-model="form.password"
                        :error="form.errors.password" />
                </div>
                <div class="w-full px-3 mb-10">
                    <InputField label="Confirm Password" type="password" v-model="form.password_confirmation"
                        :error="form.errors.password_confirmation" />
                </div>
            </div>

            <div class="w-full px-3 mb-3 justify-center flex">
                <PrimaryBtn :disabled="form.processing"
                    class="w-full py-3 disabled:opacity-50 disabled:pointer-events-none">
                    Reset Password
                </PrimaryBtn>
            </div>
        </form>
    </AuthLayout>
</template>
