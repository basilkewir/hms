<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import KotelAuthenticationCard from '@/Components/KotelAuthenticationCard.vue';
import KotelTextInput from '@/Components/KotelTextInput.vue';
import KotelButton from '@/Components/KotelButton.vue';
import KotelCheckbox from '@/Components/KotelCheckbox.vue';
import InputError from '@/Components/InputError.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Kotel Management System - Login" />

    <KotelAuthenticationCard>
        <div v-if="status" class="mb-4 font-medium text-sm text-green-400 text-center">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-8">
            <div class="text-center mb-8">
                <h2 class="text-xl font-semibold text-white mb-2">Welcome Back</h2>
                <p class="text-kotel-sky-blue">Sign in to your account</p>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-kotel-sky-blue mb-1">Email Address</label>
                <KotelTextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    placeholder="Enter your email"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2 text-red-400" :message="form.errors.email" />
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-kotel-sky-blue mb-1">Password</label>
                <KotelTextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-2 text-red-400" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <KotelCheckbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-gray-300">Remember me</span>
                </label>

                <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-kotel-sky-blue hover:text-kotel-yellow transition-colors duration-200">
                    Forgot password?
                </Link>
            </div>

            <div>
                <KotelButton type="submit" :disabled="form.processing" class="mt-2">
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-kotel-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ form.processing ? 'Signing in...' : 'Sign In' }}
                </KotelButton>
            </div>
        </form>
    </KotelAuthenticationCard>
</template>
