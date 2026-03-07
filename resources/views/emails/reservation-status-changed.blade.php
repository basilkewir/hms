<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Status Update</title>
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
    <div class="max-w-2xl mx-auto p-4">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-kotel-black to-kotel-dark px-6 py-8 text-center border-b-4 border-kotel-yellow">
                <h1 class="text-2xl font-bold text-white mb-2">Reservation Status Update</h1>
                <p class="text-kotel-yellow font-medium">Reservation #{{ $reservation->reservation_number }}</p>
            </div>

            <!-- Status Badge -->
            <div class="text-center py-6">
                @switch($newStatus)
                    @case('confirmed')
                        <span class="inline-flex items-center px-6 py-3 rounded-full text-sm font-semibold bg-green-100 text-green-800 border border-green-200">
                            ✓ Confirmed
                        </span>
                        @break
                    @case('checked_in')
                        <span class="inline-flex items-center px-6 py-3 rounded-full text-sm font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                            ✓ Checked In
                        </span>
                        @break
                    @case('checked_out')
                        <span class="inline-flex items-center px-6 py-3 rounded-full text-sm font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                            ✓ Checked Out
                        </span>
                        @break
                    @case('cancelled')
                        <span class="inline-flex items-center px-6 py-3 rounded-full text-sm font-semibold bg-red-100 text-red-800 border border-red-200">
                            ✗ Cancelled
                        </span>
                        @break
                    @case('no_show')
                        <span class="inline-flex items-center px-6 py-3 rounded-full text-sm font-semibold bg-orange-100 text-orange-800 border border-orange-200">
                            ⚠ No-Show
                        </span>
                        @break
                    @case('pending')
                        <span class="inline-flex items-center px-6 py-3 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800 border border-yellow-200">
                            ⏳ Pending
                        </span>
                        @break
                    @case('modified')
                        <span class="inline-flex items-center px-6 py-3 rounded-full text-sm font-semibold bg-cyan-100 text-cyan-800 border border-cyan-200">
                            ⟳ Modified
                        </span>
                        @break
                    @default
                        <span class="inline-flex items-center px-6 py-3 rounded-full text-sm font-semibold bg-purple-100 text-purple-800 border border-purple-200">
                            {{ $newStatusText }}
                        </span>
                @endswitch
            </div>

            <!-- Alert for No-Show -->
            @if($newStatus === 'no_show')
                <div class="mx-6 mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">⚠️ Important: No-Show Status</h3>
                            <p class="mt-1 text-sm text-yellow-700">
                                Your reservation has been marked as a no-show because you did not check in by the required time. This may result in cancellation charges according to our policy.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Alert for Cancellation -->
            @if($newStatus === 'cancelled')
                <div class="mx-6 mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">📋 Reservation Cancelled</h3>
                            <p class="mt-1 text-sm text-red-700">
                                Your reservation has been automatically cancelled due to non-confirmation within the required time frame.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Reservation Details -->
            <div class="px-6 pb-6">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Reservation Details</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-600">Guest Name:</span>
                            <span class="text-sm text-gray-900 font-medium">{{ $reservation->guest->full_name ?? $reservation->guest->first_name . ' ' . $reservation->guest->last_name }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-600">Room:</span>
                            <span class="text-sm text-gray-900 font-medium">{{ $reservation->room->room_number ?? 'Not Assigned' }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-600">Check-in Date:</span>
                            <span class="text-sm text-gray-900 font-medium">{{ $reservation->check_in_date->format('M d, Y') }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-600">Check-out Date:</span>
                            <span class="text-sm text-gray-900 font-medium">{{ $reservation->check_out_date->format('M d, Y') }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-600">Nights:</span>
                            <span class="text-sm text-gray-900 font-medium">{{ $reservation->nights }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-600">Guests:</span>
                            <span class="text-sm text-gray-900 font-medium">{{ $reservation->adults }} Adults{{ $reservation->children > 0 ? ", {$reservation->children} Children" : '' }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-600">Total Amount:</span>
                            <span class="text-sm text-gray-900 font-medium">{{ number_format($reservation->total_amount, 2) }} FCFA</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm font-medium text-gray-600">Status Change:</span>
                            <span class="text-sm text-gray-900 font-medium">{{ $oldStatusText }} → {{ $newStatusText }}</span>
                        </div>
                    </div>
                </div>

                <!-- No-Show Policy -->
                @if($newStatus === 'no_show')
                    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-yellow-800 mb-3">No-Show Policy</h4>
                        <p class="text-sm text-yellow-700 mb-4">
                            According to our no-show policy, charges may apply for unconfirmed reservations that are not honoured. Please contact our front desk if you believe this status was applied in error.
                        </p>
                        <div class="space-y-2">
                            <p class="text-sm text-yellow-700">
                                <strong>Phone:</strong> +1 234 567 8900
                            </p>
                            <p class="text-sm text-yellow-700">
                                <strong>Email:</strong> reservations@hotel.com
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Check-out Complete -->
                @if($newStatus === 'checked_out')
                    <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-green-800 mb-3">Check-out Complete</h4>
                        <p class="text-sm text-green-700">
                            Thank you for staying with us! Your check-out has been processed automatically. If you have any questions about your bill or need assistance with anything else, please contact our front desk.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 text-center border-t border-gray-200">
                <p class="text-xs text-gray-500 mb-2">This is an automated message. Please do not reply to this email.</p>
                <p class="text-xs text-gray-500">© {{ date('Y') }} Hotel Management System. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
