<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Reservation Summary</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'kotel-yellow': '#FFD700',
                        'kotel-black': '#000000',
                        'kotel-dark': '#1a1a1a',
                        'kotel-gray': '#6b7280',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 text-gray-900">
    <div class="max-w-4xl mx-auto p-4">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-kotel-black to-kotel-dark px-6 py-8 text-center border-b-4 border-kotel-yellow">
                <h1 class="text-3xl font-bold text-white mb-2">Daily Reservation Summary</h1>
                <p class="text-kotel-yellow text-lg">{{ $summary['date_formatted'] }}</p>
            </div>

            <!-- Summary Cards -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Arrivals Card -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium uppercase tracking-wider opacity-90">Today's Arrivals</h3>
                            <svg class="w-6 h-6 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="text-3xl font-bold mb-1">{{ $summary['arrivals'] + $summary['arrivals_checked_in'] }}</div>
                        <div class="text-sm opacity-90">{{ $summary['arrivals_checked_in'] }} checked in</div>
                    </div>
                    
                    <!-- Departures Card -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium uppercase tracking-wider opacity-90">Today's Departures</h3>
                            <svg class="w-6 h-6 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </div>
                        <div class="text-3xl font-bold mb-1">{{ $summary['departures'] + $summary['departures_checked_out'] }}</div>
                        <div class="text-sm opacity-90">{{ $summary['departures_checked_out'] }} checked out</div>
                    </div>
                    
                    <!-- Issues Card -->
                    <div class="bg-gradient-to-br from-red-500 to-orange-500 rounded-xl p-6 text-white shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium uppercase tracking-wider opacity-90">Issues</h3>
                            <svg class="w-6 h-6 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-3xl font-bold mb-1">{{ $summary['no_shows'] + $summary['cancellations'] }}</div>
                        <div class="text-sm opacity-90">{{ $summary['no_shows'] }} no-shows, {{ $summary['cancellations'] }} cancellations</div>
                    </div>
                    
                    <!-- Revenue Card -->
                    <div class="bg-gradient-to-br from-kotel-yellow to-yellow-500 rounded-xl p-6 text-black shadow-lg">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium uppercase tracking-wider opacity-90">Today's Revenue</h3>
                            <svg class="w-6 h-6 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-3xl font-bold mb-1">{{ number_format($summary['total_revenue'], 0) }}</div>
                        <div class="text-sm opacity-90">FCFA</div>
                    </div>
                </div>

                <!-- Alert for Issues -->
                @if($summary['no_shows'] > 0 || $summary['cancellations'] > 0)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium text-yellow-800">⚠️ Attention Required</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    @if($summary['no_shows'] > 0)
                                        <p><strong>{{ $summary['no_shows'] }}</strong> reservations marked as no-show today. Rooms have been released automatically.</p>
                                    @endif
                                    @if($summary['cancellations'] > 0)
                                        <p><strong>{{ $summary['cancellations'] }}</strong> reservations cancelled today.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Current Status Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Current Status</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-medium text-gray-600">Current Occupancy</h3>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                            </div>
                            <div class="text-2xl font-bold text-gray-900">{{ $summary['current_occupancy'] }}</div>
                            <div class="text-sm text-gray-600">Guests checked in</div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-medium text-gray-600">Pending</h3>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="text-2xl font-bold text-gray-900">{{ $summary['pending_reservations'] }}</div>
                            <div class="text-sm text-gray-600">Awaiting confirmation</div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-medium text-gray-600">Modified</h3>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </div>
                            <div class="text-2xl font-bold text-gray-900">{{ $summary['modified_reservations'] }}</div>
                            <div class="text-sm text-gray-600">Pending review</div>
                        </div>
                    </div>
                </div>

                <!-- Automation Summary Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Automation Summary</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-blue-50 rounded-lg p-6 border-l-4 border-blue-400">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-medium text-blue-800">Auto Check-outs</h3>
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                            <div class="text-2xl font-bold text-blue-900">{{ $summary['automation_summary']['auto_checkouts'] }}</div>
                            <div class="text-sm text-blue-700">Processed today</div>
                        </div>
                        
                        <div class="bg-orange-50 rounded-lg p-6 border-l-4 border-orange-400">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-medium text-orange-800">Auto No-shows</h3>
                                <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="text-2xl font-bold text-orange-900">{{ $summary['automation_summary']['auto_no_shows'] }}</div>
                            <div class="text-sm text-orange-700">Processed today</div>
                        </div>
                        
                        <div class="bg-red-50 rounded-lg p-6 border-l-4 border-red-400">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-medium text-red-800">Auto Cancellations</h3>
                                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="text-2xl font-bold text-red-900">{{ $summary['automation_summary']['auto_cancellations'] }}</div>
                            <div class="text-sm text-red-700">Processed today</div>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Financial Summary</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gradient-to-br from-kotel-yellow to-yellow-400 rounded-lg p-6 text-black">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-medium opacity-80">Room Revenue</h3>
                                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                            </div>
                            <div class="text-2xl font-bold mb-1">{{ number_format($summary['total_room_revenue'], 0) }}</div>
                            <div class="text-sm opacity-80">FCFA</div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-kotel-yellow to-yellow-400 rounded-lg p-6 text-black">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-medium opacity-80">Total Revenue</h3>
                                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="text-2xl font-bold mb-1">{{ number_format($summary['total_revenue'], 0) }}</div>
                            <div class="text-sm opacity-80">FCFA</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 text-center border-t border-gray-200">
                <p class="text-xs text-gray-500 mb-2">This is an automated daily summary report.</p>
                <p class="text-xs text-gray-500 mb-2">© {{ date('Y') }} Hotel Management System. All rights reserved.</p>
                <p class="text-xs text-gray-500">Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
