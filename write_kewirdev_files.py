#!/usr/bin/env python3
"""Helper script to write the improved kewirdev-website Vue files."""
import os

BASE = r"C:\Users\FT_Basil\Documents\kewirdev-website"

DASHBOARD_CONTROLLER_PHP = (
    "<?php\n"
    "\n"
    "namespace App\\Http\\Controllers\\Admin;\n"
    "\n"
    "use App\\Http\\Controllers\\Controller;\n"
    "use App\\Models\\License;\n"
    "use App\\Models\\LicenseDevice;\n"
    "use Illuminate\\Http\\Request;\n"
    "\n"
    "class DashboardController extends Controller\n"
    "{\n"
    "    public function index()\n"
    "    {\n"
    "        $posts = \\App\\Models\\Post::count();\n"
    "        $projects = \\App\\Models\\Project::count();\n"
    "        $services = \\App\\Models\\Service::count();\n"
    "        $teamMembers = \\App\\Models\\TeamMember::count();\n"
    "\n"
    "        $totalLicenses = License::count();\n"
    "        $activeLicenses = License::where('status', 'active')->count();\n"
    "        $expiredLicenses = License::where('status', 'expired')->count();\n"
    "        $revokedLicenses = License::where('status', 'revoked')->count();\n"
    "        $totalDevices = LicenseDevice::where('status', 'active')->count();\n"
    "        $perpetualLicenses = License::where('license_type', 'perpetual')->count();\n"
    "        $expiringLicenses = License::where('status', 'active')\n"
    "            ->whereNotNull('expires_at')\n"
    "            ->where('expires_at', '>', now())\n"
    "            ->where('expires_at', '<', now()->addDays(30))\n"
    "            ->count();\n"
    "\n"
    "        $recentLicenses = License::latest()\n"
    "            ->limit(5)\n"
    "            ->get(['id', 'hotel_name', 'license_key', 'license_type', 'status', 'expires_at', 'created_at']);\n"
    "\n"
    "        return inertia('Admin/Dashboard', [\n"
    "            'stats' => [\n"
    "                'posts'       => $posts,\n"
    "                'projects'    => $projects,\n"
    "                'services'    => $services,\n"
    "                'teamMembers' => $teamMembers,\n"
    "            ],\n"
    "            'licenseStats' => [\n"
    "                'total'     => $totalLicenses,\n"
    "                'active'    => $activeLicenses,\n"
    "                'expired'   => $expiredLicenses,\n"
    "                'revoked'   => $revokedLicenses,\n"
    "                'perpetual' => $perpetualLicenses,\n"
    "                'devices'   => $totalDevices,\n"
    "                'expiring'  => $expiringLicenses,\n"
    "            ],\n"
    "            'recentLicenses' => $recentLicenses,\n"
    "        ]);\n"
    "    }\n"
    "}\n"
)

DASHBOARD_VUE = r'''<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';

const props = defineProps({
    stats: Object,
    licenseStats: Object,
    recentLicenses: Array,
});

const page = usePage();

const formatDate = (d) => {
    if (!d) return 'Never Expires';
    return new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const getStatusClass = (status) => {
    const map = {
        active:    'bg-green-100 text-green-800',
        expired:   'bg-red-100 text-red-800',
        suspended: 'bg-yellow-100 text-yellow-800',
        revoked:   'bg-gray-200 text-gray-700',
    };
    return map[status] || 'bg-gray-100 text-gray-800';
};

const getTypeClass = (type) => {
    const map = {
        trial:      'bg-gray-100 text-gray-700',
        basic:      'bg-blue-100 text-blue-800',
        premium:    'bg-green-100 text-green-800',
        enterprise: 'bg-purple-100 text-purple-800',
        perpetual:  'bg-yellow-100 text-yellow-800 border border-yellow-300',
    };
    return map[type] || 'bg-gray-100 text-gray-700';
};

onMounted(() => {
    gsap.from('.stat-card', {
        opacity: 0,
        y: 20,
        duration: 0.4,
        stagger: 0.07,
        ease: 'power2.out',
    });
});
</script>

<template>
    <AdminLayout title="KewirDev Control Center">
        <Head title="Admin Dashboard" />

        <!-- Welcome Banner -->
        <div class="mb-6 rounded-xl bg-gradient-to-r from-blue-600 to-blue-800 p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Welcome back, {{ page.props.auth.user.name }}</h1>
                    <p class="mt-1 text-blue-200">KewirDev Admin Control Center &mdash; manage licenses, content &amp; more.</p>
                </div>
                <div class="hidden md:flex items-center space-x-3">
                    <Link :href="route('admin.licenses.index')"
                        class="inline-flex items-center rounded-lg bg-white/20 hover:bg-white/30 px-4 py-2 text-sm font-medium transition">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Manage Licenses
                    </Link>
                </div>
            </div>
        </div>

        <!-- Expiry Warning -->
        <div v-if="licenseStats && licenseStats.expiring > 0"
            class="mb-6 flex items-center rounded-lg border border-yellow-300 bg-yellow-50 p-4 dark:bg-yellow-900/20 dark:border-yellow-700">
            <svg class="h-5 w-5 text-yellow-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                <strong>{{ licenseStats.expiring }}</strong> license{{ licenseStats.expiring > 1 ? 's' : '' }} expiring within 30 days.
                <Link :href="route('admin.licenses.index')" class="ml-2 font-semibold underline">Review now &rarr;</Link>
            </p>
        </div>

        <!-- License Stats -->
        <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-3">License Overview</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-4 mb-8">
            <div class="stat-card flex flex-col items-center justify-center rounded-xl bg-white dark:bg-gray-800 shadow p-4">
                <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ licenseStats?.total ?? 0 }}</span>
                <span class="mt-1 text-xs text-gray-500 dark:text-gray-400">Total</span>
            </div>
            <div class="stat-card flex flex-col items-center justify-center rounded-xl bg-green-50 dark:bg-green-900/20 shadow p-4">
                <span class="text-3xl font-bold text-green-700 dark:text-green-400">{{ licenseStats?.active ?? 0 }}</span>
                <span class="mt-1 text-xs text-green-600 dark:text-green-400">Active</span>
            </div>
            <div class="stat-card flex flex-col items-center justify-center rounded-xl bg-red-50 dark:bg-red-900/20 shadow p-4">
                <span class="text-3xl font-bold text-red-700 dark:text-red-400">{{ licenseStats?.expired ?? 0 }}</span>
                <span class="mt-1 text-xs text-red-600 dark:text-red-400">Expired</span>
            </div>
            <div class="stat-card flex flex-col items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 shadow p-4">
                <span class="text-3xl font-bold text-gray-700 dark:text-gray-300">{{ licenseStats?.revoked ?? 0 }}</span>
                <span class="mt-1 text-xs text-gray-500 dark:text-gray-400">Revoked</span>
            </div>
            <div class="stat-card flex flex-col items-center justify-center rounded-xl bg-yellow-50 dark:bg-yellow-900/20 shadow p-4">
                <span class="text-3xl font-bold text-yellow-700 dark:text-yellow-400">{{ licenseStats?.perpetual ?? 0 }}</span>
                <span class="mt-1 text-xs text-yellow-600 dark:text-yellow-400">Perpetual</span>
            </div>
            <div class="stat-card flex flex-col items-center justify-center rounded-xl bg-purple-50 dark:bg-purple-900/20 shadow p-4">
                <span class="text-3xl font-bold text-purple-700 dark:text-purple-400">{{ licenseStats?.devices ?? 0 }}</span>
                <span class="mt-1 text-xs text-purple-600 dark:text-purple-400">Devices</span>
            </div>
            <div class="stat-card flex flex-col items-center justify-center rounded-xl bg-orange-50 dark:bg-orange-900/20 shadow p-4">
                <span class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ licenseStats?.expiring ?? 0 }}</span>
                <span class="mt-1 text-xs text-orange-500 dark:text-orange-400">Expiring Soon</span>
            </div>
        </div>

        <!-- Content Stats -->
        <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-3">Website Content</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <Link :href="route('admin.posts.index')"
                class="stat-card group flex items-center rounded-xl bg-white dark:bg-gray-800 shadow hover:shadow-md p-5 transition">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 group-hover:bg-indigo-200 transition">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.posts }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Posts</p>
                </div>
            </Link>
            <Link :href="route('admin.projects.index')"
                class="stat-card group flex items-center rounded-xl bg-white dark:bg-gray-800 shadow hover:shadow-md p-5 transition">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 group-hover:bg-blue-200 transition">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.projects }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Projects</p>
                </div>
            </Link>
            <Link :href="route('admin.services.index')"
                class="stat-card group flex items-center rounded-xl bg-white dark:bg-gray-800 shadow hover:shadow-md p-5 transition">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-300 group-hover:bg-emerald-200 transition">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.services }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Services</p>
                </div>
            </Link>
            <Link :href="route('admin.team-members.index')"
                class="stat-card group flex items-center rounded-xl bg-white dark:bg-gray-800 shadow hover:shadow-md p-5 transition">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-pink-100 dark:bg-pink-900 text-pink-600 dark:text-pink-300 group-hover:bg-pink-200 transition">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.teamMembers }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Team Members</p>
                </div>
            </Link>
        </div>

        <!-- Recent Licenses Table -->
        <div class="stat-card rounded-xl bg-white dark:bg-gray-800 shadow overflow-hidden mb-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Recent Licenses</h3>
                <Link :href="route('admin.licenses.index')"
                    class="text-sm text-blue-600 dark:text-blue-400 hover:underline font-medium">
                    View All &rarr;
                </Link>
            </div>
            <div v-if="!recentLicenses || recentLicenses.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
                No licenses yet.
                <Link :href="route('admin.licenses.index')" class="ml-2 text-blue-600 hover:underline">Create one &rarr;</Link>
            </div>
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Hotel</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Key</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Expires</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="lic in recentLicenses" :key="lic.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-6 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ lic.hotel_name }}</td>
                            <td class="px-6 py-3 text-xs font-mono text-gray-500 dark:text-gray-400">{{ lic.license_key }}</td>
                            <td class="px-6 py-3">
                                <span :class="getTypeClass(lic.license_type)" class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full">{{ lic.license_type.toUpperCase() }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <span :class="getStatusClass(lic.status)" class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full">{{ lic.status.toUpperCase() }}</span>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ formatDate(lic.expires_at) }}</td>
                            <td class="px-6 py-3 text-right">
                                <Link :href="route('admin.licenses.show', lic.id)" class="text-blue-600 dark:text-blue-400 text-sm hover:underline font-medium">View</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-3">Quick Actions</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <Link :href="route('admin.licenses.index')"
                class="stat-card flex flex-col items-center rounded-xl bg-blue-600 hover:bg-blue-700 text-white p-4 shadow transition text-center">
                <svg class="h-6 w-6 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span class="text-xs font-medium">Licenses</span>
            </Link>
            <Link :href="route('admin.posts.create')"
                class="stat-card flex flex-col items-center rounded-xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 p-4 shadow transition text-center border border-gray-200 dark:border-gray-700">
                <svg class="h-6 w-6 mb-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="text-xs font-medium">New Post</span>
            </Link>
            <Link :href="route('admin.projects.create')"
                class="stat-card flex flex-col items-center rounded-xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 p-4 shadow transition text-center border border-gray-200 dark:border-gray-700">
                <svg class="h-6 w-6 mb-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="text-xs font-medium">New Project</span>
            </Link>
            <Link :href="route('admin.services.create')"
                class="stat-card flex flex-col items-center rounded-xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 p-4 shadow transition text-center border border-gray-200 dark:border-gray-700">
                <svg class="h-6 w-6 mb-2 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="text-xs font-medium">New Service</span>
            </Link>
            <Link :href="route('admin.team-members.create')"
                class="stat-card flex flex-col items-center rounded-xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 p-4 shadow transition text-center border border-gray-200 dark:border-gray-700">
                <svg class="h-6 w-6 mb-2 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="text-xs font-medium">New Member</span>
            </Link>
            <a :href="route('home')" target="_blank"
                class="stat-card flex flex-col items-center rounded-xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 p-4 shadow transition text-center border border-gray-200 dark:border-gray-700">
                <svg class="h-6 w-6 mb-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span class="text-xs font-medium">View Site</span>
            </a>
        </div>
    </AdminLayout>
</template>
'''

LICENSE_MGMT_VUE = r'''<template>
    <AdminLayout title="License Management">
        <Head title="License Management" />

        <!-- Header -->
        <div class="mb-6 rounded-xl bg-gradient-to-r from-blue-700 to-blue-900 p-6 text-white shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">License Management</h1>
                    <p class="mt-1 text-blue-200">Issue, modify, extend, and revoke Hotel IPTV licenses.</p>
                </div>
                <button @click="showCreateModal = true"
                    class="inline-flex items-center rounded-lg bg-white text-blue-700 hover:bg-blue-50 px-4 py-2 text-sm font-semibold shadow transition">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create License
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="rounded-xl bg-white dark:bg-gray-800 shadow p-4 flex items-center space-x-3">
                <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_licenses }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                </div>
            </div>
            <div class="rounded-xl bg-white dark:bg-gray-800 shadow p-4 flex items-center space-x-3">
                <div class="h-10 w-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                    <svg class="h-5 w-5 text-green-600 dark:text-green-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.active_licenses }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Active</p>
                </div>
            </div>
            <div class="rounded-xl bg-white dark:bg-gray-800 shadow p-4 flex items-center space-x-3">
                <div class="h-10 w-10 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center">
                    <svg class="h-5 w-5 text-red-600 dark:text-red-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.expired_licenses }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Expired</p>
                </div>
            </div>
            <div class="rounded-xl bg-white dark:bg-gray-800 shadow p-4 flex items-center space-x-3">
                <div class="h-10 w-10 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                    <svg class="h-5 w-5 text-purple-600 dark:text-purple-300" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.active_devices }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Active Devices</p>
                </div>
            </div>
        </div>

        <!-- Search / Filter Bar -->
        <div class="mb-4 flex flex-col sm:flex-row gap-3">
            <input v-model="search" type="text" placeholder="Search hotel name or license key..."
                class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"/>
            <select v-model="filterStatus"
                class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="expired">Expired</option>
                <option value="suspended">Suspended</option>
                <option value="revoked">Revoked</option>
            </select>
            <select v-model="filterType"
                class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Types</option>
                <option v-for="(label, value) in licenseTypes" :key="value" :value="value">{{ label }}</option>
            </select>
        </div>

        <!-- Licenses Table -->
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Hotel</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">License Key</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Devices</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Expires</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-if="filteredLicenses.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No licenses found.</td>
                        </tr>
                        <tr v-for="license in filteredLicenses" :key="license.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ license.hotel_name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ license.hotel_id }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs font-mono text-gray-700 dark:text-gray-300">{{ license.license_key }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getLicenseTypeClass(license.license_type)"
                                    class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full">
                                    {{ license.license_type.toUpperCase() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getStatusClass(license.status)"
                                    class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full">
                                    {{ license.status.toUpperCase() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div v-if="license.device_allocation" class="flex flex-wrap gap-1">
                                    <span class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-0.5 rounded">TV: {{ getDeviceCount(license, 'android_tv') }}</span>
                                    <span class="text-xs bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-2 py-0.5 rounded">Smart: {{ getDeviceCount(license, 'smart_tv') }}</span>
                                    <span class="text-xs bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-2 py-0.5 rounded">API: {{ getDeviceCount(license, 'backend') }}</span>
                                </div>
                                <span v-else class="text-xs text-gray-500">{{ license.active_devices || 0 }}/{{ license.max_devices || 0 }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span :class="isExpiringSoon(license) ? 'text-orange-600 dark:text-orange-400 font-semibold' : 'text-gray-700 dark:text-gray-300'">
                                    {{ formatDate(license.expires_at) }}
                                </span>
                                <span v-if="isExpiringSoon(license)" class="ml-1 text-xs text-orange-500">(soon)</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-1">
                                    <button @click="viewLicense(license)"
                                        class="rounded px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900 transition">
                                        View
                                    </button>
                                    <button @click="openEditModal(license)"
                                        class="rounded px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/30 hover:bg-green-100 dark:hover:bg-green-900 transition">
                                        Edit
                                    </button>
                                    <button @click="openExtendModal(license)"
                                        class="rounded px-2 py-1 text-xs font-medium text-purple-700 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/30 hover:bg-purple-100 dark:hover:bg-purple-900 transition">
                                        Extend
                                    </button>
                                    <button v-if="license.status === 'active'" @click="revokeLicense(license)"
                                        class="rounded px-2 py-1 text-xs font-medium text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900 transition">
                                        Revoke
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-500 dark:text-gray-400">
                Showing {{ filteredLicenses.length }} of {{ recentLicenses.length }} licenses
            </div>
        </div>

        <!-- CREATE MODAL -->
        <Teleport to="body">
            <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="w-full max-w-lg bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Create New License</h3>
                        <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="createLicense" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hotel Name *</label>
                            <input v-model="newLicense.hotel_name" type="text" required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">License Type *</label>
                            <select v-model="newLicense.license_type" required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option v-for="(label, value) in licenseTypes" :key="value" :value="value">{{ label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Device Allocation</label>
                            <div class="grid grid-cols-2 gap-3">
                                <div v-for="dtype in ['android_tv','smart_tv','backend','admin_panel']" :key="dtype">
                                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1 capitalize">{{ dtype.replace('_',' ') }}</label>
                                    <input v-model.number="newLicense.device_allocation[dtype]" type="number" min="0"
                                        :max="newLicense.license_type === 'perpetual' ? 9999 : 100"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Total: {{ totalDevices }}</p>
                        </div>
                        <div v-if="newLicense.license_type !== 'perpetual'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expires At</label>
                            <input v-model="newLicense.expires_at" type="datetime-local"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                        </div>
                        <div v-else class="rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 p-3 text-sm text-yellow-800 dark:text-yellow-200">
                            <strong>Perpetual License:</strong> Never expires. Includes lifetime updates &amp; priority support.
                        </div>
                        <div class="flex justify-end space-x-3 pt-2">
                            <button type="button" @click="showCreateModal = false"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancel</button>
                            <button type="submit"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition">Create License</button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- EDIT MODAL -->
        <Teleport to="body">
            <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="w-full max-w-lg bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Edit License</h3>
                        <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="updateLicense" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hotel Name *</label>
                            <input v-model="editLicenseData.hotel_name" type="text" required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">License Type *</label>
                            <select v-model="editLicenseData.license_type" required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option v-for="(label, value) in licenseTypes" :key="value" :value="value">{{ label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
                            <select v-model="editLicenseData.status" required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                                <option value="expired">Expired</option>
                                <option value="revoked">Revoked</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Device Allocation</label>
                            <div class="grid grid-cols-2 gap-3">
                                <div v-for="dtype in ['android_tv','smart_tv','backend','admin_panel']" :key="dtype">
                                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1 capitalize">{{ dtype.replace('_',' ') }}</label>
                                    <input v-model.number="editLicenseData.device_allocation[dtype]" type="number" min="0"
                                        :max="editLicenseData.license_type === 'perpetual' ? 9999 : 100"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Total: {{ editTotalDevices }}</p>
                        </div>
                        <div v-if="editLicenseData.license_type !== 'perpetual'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expires At</label>
                            <input v-model="editLicenseData.expires_at" type="datetime-local"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                        </div>
                        <div class="flex justify-end space-x-3 pt-2">
                            <button type="button" @click="showEditModal = false"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancel</button>
                            <button type="submit"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- EXTEND MODAL -->
        <Teleport to="body">
            <div v-if="showExtendModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Extend License</h3>
                        <button @click="showExtendModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="extendLicense" class="p-6 space-y-4">
                        <div v-if="extendTarget" class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 p-3">
                            <p class="text-sm font-medium text-blue-800 dark:text-blue-200">{{ extendTarget.hotel_name }}</p>
                            <p class="text-xs text-blue-600 dark:text-blue-300 mt-1">Current expiry: {{ formatDate(extendTarget.expires_at) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Extend by (days) *</label>
                            <input v-model.number="extendDays" type="number" min="1" max="3650" required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"/>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">New expiry: <strong class="text-purple-600 dark:text-purple-400">{{ newExpiryDate }}</strong></p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" v-for="days in [30, 90, 180, 365]" :key="days"
                                @click="extendDays = days"
                                :class="extendDays === days ? 'bg-purple-600 text-white border-purple-600' : 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-700'"
                                class="rounded px-3 py-1 text-xs font-medium border hover:bg-purple-600 hover:text-white hover:border-purple-600 transition">
                                +{{ days }}d
                            </button>
                        </div>
                        <div class="flex justify-end space-x-3 pt-2">
                            <button type="button" @click="showExtendModal = false"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancel</button>
                            <button type="submit"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 transition">Extend License</button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>

<script>
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {
    components: { Head, AdminLayout },
    props: {
        recentLicenses: Array,
        stats: Object,
        licenseTypes: Object,
        deviceTypes: Object,
    },
    data() {
        return {
            search: '',
            filterStatus: '',
            filterType: '',
            showCreateModal: false,
            showEditModal: false,
            showExtendModal: false,
            extendTarget: null,
            extendDays: 30,
            editingLicense: null,
            newLicense: {
                hotel_name: '',
                license_type: 'trial',
                device_allocation: { android_tv: 0, smart_tv: 0, backend: 0, admin_panel: 0 },
                expires_at: '',
            },
            editLicenseData: {
                id: null,
                hotel_name: '',
                license_type: 'trial',
                status: 'active',
                device_allocation: { android_tv: 0, smart_tv: 0, backend: 0, admin_panel: 0 },
                expires_at: '',
            },
        };
    },
    computed: {
        filteredLicenses() {
            return (this.recentLicenses || []).filter(l => {
                const q = this.search.toLowerCase();
                const matchSearch = !q || l.hotel_name.toLowerCase().includes(q) || l.license_key.toLowerCase().includes(q);
                const matchStatus = !this.filterStatus || l.status === this.filterStatus;
                const matchType = !this.filterType || l.license_type === this.filterType;
                return matchSearch && matchStatus && matchType;
            });
        },
        totalDevices() {
            const a = this.newLicense.device_allocation;
            return a.android_tv + a.smart_tv + a.backend + a.admin_panel;
        },
        editTotalDevices() {
            const a = this.editLicenseData.device_allocation;
            return a.android_tv + a.smart_tv + a.backend + a.admin_panel;
        },
        newExpiryDate() {
            if (!this.extendTarget || !this.extendDays) return '---';
            const base = this.extendTarget.expires_at ? new Date(this.extendTarget.expires_at) : new Date();
            base.setDate(base.getDate() + parseInt(this.extendDays));
            return base.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        },
    },
    methods: {
        getLicenseTypeClass(type) {
            const map = { trial: 'bg-gray-100 text-gray-800', basic: 'bg-blue-100 text-blue-800', premium: 'bg-green-100 text-green-800', enterprise: 'bg-purple-100 text-purple-800', perpetual: 'bg-yellow-100 text-yellow-800 border border-yellow-300' };
            return map[type] || 'bg-gray-100 text-gray-800';
        },
        getStatusClass(status) {
            const map = { active: 'bg-green-100 text-green-800', expired: 'bg-red-100 text-red-800', suspended: 'bg-yellow-100 text-yellow-800', revoked: 'bg-gray-200 text-gray-700' };
            return map[status] || 'bg-gray-100 text-gray-800';
        },
        formatDate(date) {
            if (!date) return 'Never Expires';
            return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        },
        isExpiringSoon(license) {
            if (!license.expires_at) return false;
            const diff = new Date(license.expires_at) - new Date();
            return diff > 0 && diff < 30 * 86400000;
        },
        getDeviceCount(license, deviceType) {
            if (!license.device_allocation || !license.device_allocation[deviceType]) return '0/0';
            const cur = license.device_allocation[deviceType].current || 0;
            const max = license.device_allocation[deviceType].max || 0;
            return cur + '/' + max;
        },
        viewLicense(license) { router.get(route('admin.licenses.show', license.id)); },
        openEditModal(license) {
            this.editingLicense = license;
            this.editLicenseData = {
                id: license.id,
                hotel_name: license.hotel_name,
                license_type: license.license_type,
                status: license.status,
                device_allocation: {
                    android_tv: license.device_allocation?.android_tv?.max || 0,
                    smart_tv: license.device_allocation?.smart_tv?.max || 0,
                    backend: license.device_allocation?.backend?.max || 0,
                    admin_panel: license.device_allocation?.admin_panel?.max || 0,
                },
                expires_at: license.expires_at ? new Date(license.expires_at).toISOString().slice(0, 16) : '',
            };
            this.showEditModal = true;
        },
        openExtendModal(license) {
            this.extendTarget = license;
            this.extendDays = 30;
            this.showExtendModal = true;
        },
        createLicense() {
            if (this.totalDevices === 0) { alert('Please allocate at least one device.'); return; }
            router.post(route('admin.licenses.store'), this.newLicense, {
                onSuccess: () => {
                    this.showCreateModal = false;
                    this.newLicense = { hotel_name: '', license_type: 'trial', device_allocation: { android_tv: 0, smart_tv: 0, backend: 0, admin_panel: 0 }, expires_at: '' };
                },
            });
        },
        updateLicense() {
            if (this.editTotalDevices === 0) { alert('Please allocate at least one device.'); return; }
            router.put(route('admin.licenses.update', this.editLicenseData.id), this.editLicenseData, {
                onSuccess: () => { this.showEditModal = false; this.editingLicense = null; },
            });
        },
        extendLicense() {
            if (!this.extendDays || this.extendDays < 1) { alert('Please enter a valid number of days.'); return; }
            router.post(route('admin.licenses.extend', this.extendTarget.id), { extend_by_days: this.extendDays }, {
                onSuccess: () => { this.showExtendModal = false; this.extendTarget = null; },
            });
        },
        revokeLicense(license) {
            if (!confirm('Revoke license for "' + license.hotel_name + '"? This cannot be undone easily.')) return;
            router.post(route('admin.licenses.revoke', license.id));
        },
    },
};
</script>
'''

LICENSE_SHOW_VUE = r'''<template>
    <AdminLayout :title="`License: ${license.hotel_name}`">
        <Head :title="`License - ${license.hotel_name}`" />

        <!-- Header -->
        <div class="mb-6 rounded-xl bg-gradient-to-r from-blue-700 to-blue-900 p-6 text-white shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <span :class="getStatusBadge(license.status)" class="inline-flex px-2.5 py-0.5 text-xs font-bold rounded-full">{{ license.status.toUpperCase() }}</span>
                        <span :class="getTypeBadge(license.license_type)" class="inline-flex px-2.5 py-0.5 text-xs font-bold rounded-full">{{ license.license_type.toUpperCase() }}</span>
                    </div>
                    <h1 class="text-2xl font-bold">{{ license.hotel_name }}</h1>
                    <p class="mt-1 text-blue-200 font-mono text-sm">{{ license.license_key }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button @click="$inertia.get(route('admin.licenses.index'))"
                        class="rounded-lg bg-white/20 hover:bg-white/30 px-4 py-2 text-sm font-medium transition">&larr; All Licenses</button>
                    <button @click="openEditModal"
                        class="rounded-lg bg-white text-blue-700 hover:bg-blue-50 px-4 py-2 text-sm font-semibold shadow transition">Edit</button>
                    <button @click="openExtendModal"
                        class="rounded-lg bg-purple-500 hover:bg-purple-600 px-4 py-2 text-sm font-semibold shadow transition">Extend</button>
                    <button v-if="license.status === 'active'" @click="revokeLicense"
                        class="rounded-lg bg-red-500 hover:bg-red-600 px-4 py-2 text-sm font-semibold shadow transition">Revoke</button>
                </div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Basic Info -->
            <div class="rounded-xl bg-white dark:bg-gray-800 shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">License Details</h3>
                </div>
                <dl class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div class="flex px-6 py-3">
                        <dt class="w-36 flex-shrink-0 text-sm font-medium text-gray-500 dark:text-gray-400">License Key</dt>
                        <dd class="flex-1 text-sm font-mono text-gray-900 dark:text-white break-all">{{ license.license_key }}</dd>
                    </div>
                    <div class="flex px-6 py-3">
                        <dt class="w-36 flex-shrink-0 text-sm font-medium text-gray-500 dark:text-gray-400">Hotel Name</dt>
                        <dd class="flex-1 text-sm text-gray-900 dark:text-white">{{ license.hotel_name }}</dd>
                    </div>
                    <div class="flex px-6 py-3">
                        <dt class="w-36 flex-shrink-0 text-sm font-medium text-gray-500 dark:text-gray-400">Hotel ID</dt>
                        <dd class="flex-1 text-sm font-mono text-gray-700 dark:text-gray-300">{{ license.hotel_id }}</dd>
                    </div>
                    <div class="flex px-6 py-3">
                        <dt class="w-36 flex-shrink-0 text-sm font-medium text-gray-500 dark:text-gray-400">Type</dt>
                        <dd class="flex-1">
                            <span :class="getLicenseTypeClass(license.license_type)" class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full">{{ license.license_type.toUpperCase() }}</span>
                        </dd>
                    </div>
                    <div class="flex px-6 py-3">
                        <dt class="w-36 flex-shrink-0 text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="flex-1">
                            <span :class="getStatusClass(license.status)" class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full">{{ license.status.toUpperCase() }}</span>
                        </dd>
                    </div>
                    <div class="flex px-6 py-3">
                        <dt class="w-36 flex-shrink-0 text-sm font-medium text-gray-500 dark:text-gray-400">Expires At</dt>
                        <dd class="flex-1 text-sm">
                            <span :class="isExpiringSoon ? 'text-orange-600 dark:text-orange-400 font-semibold' : 'text-gray-900 dark:text-white'">{{ formatDate(license.expires_at) }}</span>
                            <span v-if="license.is_perpetual" class="ml-2 text-xs font-bold text-yellow-600 dark:text-yellow-400">(PERPETUAL)</span>
                            <span v-if="isExpiringSoon" class="ml-2 text-xs text-orange-500">Expiring Soon!</span>
                        </dd>
                    </div>
                    <div class="flex px-6 py-3">
                        <dt class="w-36 flex-shrink-0 text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                        <dd class="flex-1 text-sm text-gray-700 dark:text-gray-300">{{ formatDate(license.created_at) }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Device Allocation -->
            <div class="rounded-xl bg-white dark:bg-gray-800 shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Device Allocation</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Current usage / maximum allowed</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4 text-center">
                            <p class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ license.device_allocation.android_tv.current }}/{{ license.device_allocation.android_tv.max }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Android TV</p>
                        </div>
                        <div class="rounded-lg bg-green-50 dark:bg-green-900/20 p-4 text-center">
                            <p class="text-2xl font-bold text-green-700 dark:text-green-400">{{ license.device_allocation.smart_tv.current }}/{{ license.device_allocation.smart_tv.max }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Smart TV</p>
                        </div>
                        <div class="rounded-lg bg-purple-50 dark:bg-purple-900/20 p-4 text-center">
                            <p class="text-2xl font-bold text-purple-700 dark:text-purple-400">{{ license.device_allocation.backend.current }}/{{ license.device_allocation.backend.max }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Backend</p>
                        </div>
                        <div class="rounded-lg bg-orange-50 dark:bg-orange-900/20 p-4 text-center">
                            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ license.device_allocation.admin_panel.current }}/{{ license.device_allocation.admin_panel.max }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Admin Panel</p>
                        </div>
                    </div>
                    <div class="mt-4 rounded-lg bg-gray-50 dark:bg-gray-700 p-3 text-center">
                        <p class="text-lg font-bold text-gray-900 dark:text-white">Total: {{ license.current_devices }} / {{ license.max_devices }}</p>
                        <div class="mt-2 h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500"
                                :class="deviceUsagePercent >= 90 ? 'bg-red-500' : deviceUsagePercent >= 70 ? 'bg-yellow-500' : 'bg-green-500'"
                                :style="{ width: deviceUsagePercent + '%' }"></div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ deviceUsagePercent }}% capacity used</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Perpetual badge -->
        <div v-if="license.is_perpetual"
            class="mb-6 rounded-xl bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 p-5">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 text-yellow-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200">Perpetual License &mdash; Lifetime Benefits</h4>
                    <ul class="mt-2 text-sm text-yellow-700 dark:text-yellow-300 space-y-1 list-disc list-inside">
                        <li>Never expires &mdash; lifetime access</li>
                        <li>Unlimited channels and users</li>
                        <li>Advanced analytics and reporting</li>
                        <li>Custom branding &amp; white-label options</li>
                        <li>Full API access</li>
                        <li>Priority support &amp; lifetime updates</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Devices Table -->
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow overflow-hidden mb-6">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Connected Devices</h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ devices.length }} device(s)</span>
            </div>
            <div v-if="devices.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">No devices connected to this license yet.</div>
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Device</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Seen</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">IP</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="device in devices" :key="device.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-6 py-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ device.device_name || device.device_id }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ device.device_model }} &mdash; {{ device.device_os }}</p>
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">{{ device.device_type.toUpperCase() }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <span :class="getDeviceStatusClass(device.status)" class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full">{{ device.status.toUpperCase() }}</span>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ formatDate(device.last_seen_at) }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ device.ip_address || 'N/A' }}</td>
                            <td class="px-6 py-3">
                                <button @click="toggleDeviceStatus(device)"
                                    :class="device.status === 'active' ? 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900' : 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900'"
                                    class="rounded px-3 py-1 text-xs font-medium transition">
                                    {{ device.status === 'active' ? 'Block' : 'Activate' }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- EDIT MODAL -->
        <Teleport to="body">
            <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="w-full max-w-lg bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Edit License</h3>
                        <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="updateLicense" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hotel Name *</label>
                            <input v-model="editData.hotel_name" type="text" required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
                            <select v-model="editData.status" required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                                <option value="expired">Expired</option>
                                <option value="revoked">Revoked</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Device Allocation</label>
                            <div class="grid grid-cols-2 gap-3">
                                <div v-for="dtype in ['android_tv','smart_tv','backend','admin_panel']" :key="dtype">
                                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1 capitalize">{{ dtype.replace('_',' ') }}</label>
                                    <input v-model.number="editData.device_allocation[dtype]" type="number" min="0"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                                </div>
                            </div>
                        </div>
                        <div v-if="license.license_type !== 'perpetual'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expires At</label>
                            <input v-model="editData.expires_at" type="datetime-local"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                        </div>
                        <div class="flex justify-end space-x-3 pt-2">
                            <button type="button" @click="showEditModal = false"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancel</button>
                            <button type="submit"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- EXTEND MODAL -->
        <Teleport to="body">
            <div v-if="showExtendModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Extend License</h3>
                        <button @click="showExtendModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="extendLicenseAction" class="p-6 space-y-4">
                        <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 p-3">
                            <p class="text-xs text-blue-600 dark:text-blue-300">Current expiry</p>
                            <p class="text-sm font-semibold text-blue-800 dark:text-blue-200">{{ formatDate(license.expires_at) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Extend by (days) *</label>
                            <input v-model.number="extendDays" type="number" min="1" max="3650" required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"/>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">New expiry: <strong class="text-purple-600 dark:text-purple-400">{{ newExpiryDate }}</strong></p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" v-for="d in [30, 90, 180, 365]" :key="d" @click="extendDays = d"
                                :class="extendDays === d ? 'bg-purple-600 text-white border-purple-600' : 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-700'"
                                class="rounded px-3 py-1 text-xs font-medium border hover:bg-purple-600 hover:text-white hover:border-purple-600 transition">
                                +{{ d }}d
                            </button>
                        </div>
                        <div class="flex justify-end space-x-3 pt-2">
                            <button type="button" @click="showExtendModal = false"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancel</button>
                            <button type="submit"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 transition">Extend License</button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>

<script>
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

export default {
    components: { Head, AdminLayout },
    props: {
        license: Object,
        devices: Array,
        stats: Object,
        security_report: Object,
    },
    data() {
        return {
            showEditModal: false,
            showExtendModal: false,
            extendDays: 30,
            editData: {
                hotel_name: '',
                status: '',
                device_allocation: { android_tv: 0, smart_tv: 0, backend: 0, admin_panel: 0 },
                expires_at: '',
            },
        };
    },
    computed: {
        isExpiringSoon() {
            if (!this.license.expires_at) return false;
            const diff = new Date(this.license.expires_at) - new Date();
            return diff > 0 && diff < 30 * 86400000;
        },
        deviceUsagePercent() {
            if (!this.license.max_devices) return 0;
            return Math.round((this.license.current_devices / this.license.max_devices) * 100);
        },
        newExpiryDate() {
            if (!this.extendDays) return '---';
            const base = this.license.expires_at ? new Date(this.license.expires_at) : new Date();
            base.setDate(base.getDate() + parseInt(this.extendDays));
            return base.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        },
    },
    methods: {
        getLicenseTypeClass(type) {
            const map = { trial: 'bg-gray-100 text-gray-800', basic: 'bg-blue-100 text-blue-800', premium: 'bg-green-100 text-green-800', enterprise: 'bg-purple-100 text-purple-800', perpetual: 'bg-yellow-100 text-yellow-800 border border-yellow-300' };
            return map[type] || 'bg-gray-100 text-gray-800';
        },
        getStatusClass(status) {
            const map = { active: 'bg-green-100 text-green-800', expired: 'bg-red-100 text-red-800', suspended: 'bg-yellow-100 text-yellow-800', revoked: 'bg-gray-200 text-gray-700' };
            return map[status] || 'bg-gray-100 text-gray-800';
        },
        getStatusBadge(status) {
            const map = { active: 'bg-green-200 text-green-900', expired: 'bg-red-200 text-red-900', suspended: 'bg-yellow-200 text-yellow-900', revoked: 'bg-gray-300 text-gray-800' };
            return map[status] || 'bg-white/20 text-white';
        },
        getTypeBadge(type) {
            const map = { trial: 'bg-white/20 text-white', basic: 'bg-blue-200 text-blue-900', premium: 'bg-green-200 text-green-900', enterprise: 'bg-purple-200 text-purple-900', perpetual: 'bg-yellow-200 text-yellow-900' };
            return map[type] || 'bg-white/20 text-white';
        },
        getDeviceStatusClass(status) {
            const map = { active: 'bg-green-100 text-green-800', inactive: 'bg-gray-100 text-gray-700', blocked: 'bg-red-100 text-red-800' };
            return map[status] || 'bg-gray-100 text-gray-700';
        },
        formatDate(date) {
            if (!date) return 'Never Expires';
            return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        },
        openEditModal() {
            this.editData = {
                hotel_name: this.license.hotel_name,
                status: this.license.status,
                device_allocation: {
                    android_tv: this.license.device_allocation?.android_tv?.max || 0,
                    smart_tv: this.license.device_allocation?.smart_tv?.max || 0,
                    backend: this.license.device_allocation?.backend?.max || 0,
                    admin_panel: this.license.device_allocation?.admin_panel?.max || 0,
                },
                expires_at: this.license.expires_at ? new Date(this.license.expires_at).toISOString().slice(0, 16) : '',
            };
            this.showEditModal = true;
        },
        openExtendModal() {
            this.extendDays = 30;
            this.showExtendModal = true;
        },
        updateLicense() {
            router.put(route('admin.licenses.update', this.license.id), this.editData, {
                onSuccess: () => { this.showEditModal = false; },
            });
        },
        extendLicenseAction() {
            if (!this.extendDays || this.extendDays < 1) { alert('Please enter a valid number of days.'); return; }
            router.post(route('admin.licenses.extend', this.license.id), { extend_by_days: this.extendDays }, {
                onSuccess: () => { this.showExtendModal = false; },
            });
        },
        revokeLicense() {
            if (!confirm('Revoke license for "' + this.license.hotel_name + '"? The client will lose access immediately.')) return;
            router.post(route('admin.licenses.revoke', this.license.id));
        },
        toggleDeviceStatus(device) {
            router.post(route('admin.licenses.devices.toggle-status', device.id), {
                action: device.status === 'active' ? 'block' : 'activate',
            }, { preserveState: true, preserveScroll: true });
        },
    },
};
</script>
'''

files = {
    os.path.join(BASE, 'app', 'Http', 'Controllers', 'Admin', 'DashboardController.php'): DASHBOARD_CONTROLLER_PHP,
    os.path.join(BASE, 'resources', 'js', 'Pages', 'Admin', 'Dashboard.vue'): DASHBOARD_VUE,
    os.path.join(BASE, 'resources', 'js', 'Pages', 'Admin', 'LicenseManagement.vue'): LICENSE_MGMT_VUE,
    os.path.join(BASE, 'resources', 'js', 'Pages', 'Admin', 'LicenseShow.vue'): LICENSE_SHOW_VUE,
}

for path, content in files.items():
    with open(path, 'w', encoding='utf-8', newline='\n') as f:
        f.write(content)
    # Verify no BOM
    with open(path, 'rb') as f:
        first3 = list(f.read(3))
    bom_present = first3[:3] == [0xEF, 0xBB, 0xBF]
    print(f"Written: {path} | BOM: {bom_present} | First bytes: {first3[:4]}")

print("\nAll files written successfully!")
