<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-kotel-black via-kotel-dark to-kotel-black relative overflow-hidden">
        <!-- Animated background elements -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-20 w-40 h-40 bg-kotel-yellow/20 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-32 right-16 w-56 h-56 bg-kotel-sky-blue/15 rounded-full blur-3xl animate-pulse delay-1000"></div>
            <div class="absolute top-1/3 right-1/3 w-32 h-32 bg-kotel-yellow/10 rounded-full blur-2xl animate-pulse delay-500"></div>
        </div>

        <!-- Main login container -->
        <div class="relative z-10 w-full max-w-lg mx-6">
            <div class="bg-kotel-dark/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-kotel-yellow/30 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-kotel-black to-kotel-dark p-10 text-center">
                    <div class="mb-6">
                        <div class="w-20 h-20 mx-auto bg-kotel-yellow rounded-2xl flex items-center justify-center shadow-lg">
                            <svg class="w-12 h-12 text-kotel-black" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L2 7v10c0 5.55 3.84 9.74 9 11 5.16-1.26 9-5.45 9-11V7l-10-5z"/>
                                <path d="M8 11h8v2H8v-2zm0-2h8v2H8V9zm0 6h6v2H8v-2z" fill="white"/>
                            </svg>
                        </div>
                    </div>
                    <h1 class="text-3xl font-bold text-white mb-2">Kotel Management</h1>
                    <p class="text-kotel-sky-blue text-lg font-medium">System</p>
                    <div class="mt-4 h-1 w-24 bg-kotel-yellow mx-auto rounded-full"></div>
                </div>

                <!-- Login form -->
                <div class="p-10">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
