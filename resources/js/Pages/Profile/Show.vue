<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import {
    UserIcon,
    KeyIcon,
    ShieldCheckIcon,
    ComputerDesktopIcon,
    TrashIcon
} from '@heroicons/vue/24/outline'

defineProps({
    user: Object,
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});
</script>

<template>
    <DashboardLayout title="Profile" :user="user">
        <!-- Header Section -->
        <div class="bg-kotel-bg-card shadow rounded-lg p-6 mb-8 border border-kotel-border">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2 text-kotel-text-primary">Profile Management</h1>
                    <p class="text-sm text-kotel-text-secondary">Manage your account settings, security, and preferences.</p>
                </div>
                <div class="flex items-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mr-4 bg-kotel-yellow-light">
                        <UserIcon class="h-8 w-8 text-kotel-yellow" />
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-kotel-text-primary">{{ user?.name || 'User' }}</p>
                        <p class="text-sm text-kotel-text-secondary">{{ user?.email || '' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Sections Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Profile Information -->
            <div v-if="$page.props.jetstream.canUpdateProfileInformation" 
                 class="bg-kotel-bg-card rounded-lg overflow-hidden shadow border border-kotel-border">
                <div class="px-6 py-4 border-b border-kotel-border">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3 bg-kotel-yellow-light">
                            <UserIcon class="h-5 w-5 text-kotel-yellow" />
                        </div>
                        <h3 class="text-lg font-semibold text-kotel-text-primary">Profile Information</h3>
                    </div>
                </div>
                <div class="p-6">
                    <UpdateProfileInformationForm :user="$page.props.auth.user" />
                </div>
            </div>

            <!-- Password Update -->
            <div v-if="$page.props.jetstream.canUpdatePassword" 
                 class="bg-kotel-bg-card rounded-lg overflow-hidden shadow border border-kotel-border">
                <div class="px-6 py-4 border-b border-kotel-border">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3 bg-kotel-green-light">
                            <KeyIcon class="h-5 w-5 text-kotel-green" />
                        </div>
                        <h3 class="text-lg font-semibold text-kotel-text-primary">Password</h3>
                    </div>
                </div>
                <div class="p-6">
                    <UpdatePasswordForm />
                </div>
            </div>

            <!-- Two Factor Authentication -->
            <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication" 
                 class="bg-kotel-bg-card rounded-lg overflow-hidden shadow border border-kotel-border">
                <div class="px-6 py-4 border-b border-kotel-border">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3 bg-kotel-orange-light">
                            <ShieldCheckIcon class="h-5 w-5 text-kotel-orange" />
                        </div>
                        <h3 class="text-lg font-semibold text-kotel-text-primary">Two Factor Authentication</h3>
                    </div>
                </div>
                <div class="p-6">
                    <TwoFactorAuthenticationForm
                        :requires-confirmation="confirmsTwoFactorAuthentication"
                    />
                </div>
            </div>

            <!-- Browser Sessions -->
            <div class="bg-kotel-bg-card rounded-lg overflow-hidden shadow border border-kotel-border">
                <div class="px-6 py-4 border-b border-kotel-border">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3 bg-kotel-purple-light">
                            <ComputerDesktopIcon class="h-5 w-5 text-kotel-purple" />
                        </div>
                        <h3 class="text-lg font-semibold text-kotel-text-primary">Browser Sessions</h3>
                    </div>
                </div>
                <div class="p-6">
                    <LogoutOtherBrowserSessionsForm :sessions="sessions" />
                </div>
            </div>

            <!-- Delete Account -->
            <div v-if="$page.props.jetstream.hasAccountDeletionFeatures" 
                 class="bg-kotel-bg-card rounded-lg overflow-hidden shadow lg:col-span-2 border border-kotel-border">
                <div class="px-6 py-4 border-b border-kotel-border">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3 bg-kotel-red-light">
                            <TrashIcon class="h-5 w-5 text-kotel-red" />
                        </div>
                        <h3 class="text-lg font-semibold text-kotel-text-primary">Delete Account</h3>
                    </div>
                </div>
                <div class="p-6">
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
