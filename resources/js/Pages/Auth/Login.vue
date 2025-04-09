<script setup>
import AuthLayout from '../../Layouts/AuthLayout.vue';
import InputField from '../../Components/InputField.vue';
import PrimaryBtn from '../../Components/PrimaryBtn.vue';
import SessionMessage from '../../Components/SessionMessage.vue';
import TextLink from '../../Components/TextLink.vue';

import { useForm } from '@inertiajs/vue3';

defineProps({
    status: String,
});

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
    <AuthLayout title="Sign in">
        <form @submit.prevent="submit" class="w-sm">
            <div class="flex justify-center mb-6">
                <img src="../../../../public/assets/img/cit_logo.png" alt="CIT Logo"
                    class="h-50 w-50 object-contain pointer-events-none user-drag:none" />
            </div>
            <h1 class="text-4xl font-bold mb-6">Sign In</h1>
            <SessionMessage :status="status"></SessionMessage>

            <div class="flex flex-wrap -mx-3 mb-6">
                <div class="w-full px-3 mb-10">
                    <InputField label="Email" type="email" placeholder="Email" v-model="form.email"
                        :error="form.errors.email" />
                </div>
                <div class="w-full px-3 mb-10">
                    <InputField label="Password" type="password" v-model="form.password"
                        :error="form.errors.password" />
                    <div class="flex w-full justify-between mt-10">
                        <p routeName="register">Remember Me</p>
                        <TextLink routeName="password.request" label="Forgot Password?"></TextLink>
                    </div>
                </div>
            </div>

            <div class="w-full px-3 mb-3 justify-center flex">
                <PrimaryBtn :disabled="form.processing"
                    class="w-full py-3 disabled:opacity-50 disabled:pointer-events-none">
                    Sign In
                </PrimaryBtn>
            </div>

            <div
                class="py-3 flex items-center text-sm text-gray-800 before:flex-1 before:border-t before:border-gray-200 before:me-6 after:flex-1 after:border-t after:border-gray-200 after:ms-6">
                OR
            </div>

            <div class="w-full px-3 mb-3 justify-center flex">
                <button type="submit" class="w-full p-3 bg-blue-600 rounded text-white font-bold">
                    Activate Account
                </button>
            </div>
        </form>
    </AuthLayout>
</template>
