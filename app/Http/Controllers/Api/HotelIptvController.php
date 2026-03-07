<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Reservation;
use App\Models\Guest;
use App\Models\GuestFolio;
use App\Models\Setting;
use App\Models\IptvDevice;
use App\Models\Payment;
use App\Models\FolioCharge;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Hotel IPTV API Controller
 * Provides APIs for IPTV client applications to access hotel services
 */
class HotelIptvController extends Controller
{
    /**
     * Require a registered IPTV device by X-Device-ID header.
     * Returns the IptvDevice or null if not found/not active.
     * Used on sensitive endpoints (client-info, client-bill) to prevent
     * unauthenticated access to guest PII.
     */
    private function verifyDevice(Request $request): ?IptvDevice
    {
        $deviceId = $request->header('X-Device-ID');
        if (!$deviceId) {
            return null;
        }
        return IptvDevice::where('device_id', $deviceId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get client information (guest name, room number, etc.)
     * GET /api/iptv/client-info
     */
    public function getClientInfo(Request $request)
    {
        try {
            // Require a registered, active device — prevents unauthenticated PII access
            $device = $this->verifyDevice($request);
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'A registered X-Device-ID header is required',
                ], 401);
            }

            $room = $device->room;

            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room not found'
                ], 404);
            }

            // Get current reservation
            $currentReservation = Reservation::where('room_id', $room->id)
                ->where('status', 'checked_in')
                ->whereDate('check_in_date', '<=', now())
                ->whereDate('check_out_date', '>=', now())
                ->with('guest')
                ->first();

            $guestInfo = null;
            if ($currentReservation && $currentReservation->guest) {
                $guest = $currentReservation->guest;
                $guestInfo = [
                    'name' => $guest->first_name . ' ' . $guest->last_name,
                    'title' => $guest->title ?? 'Guest',
                    'check_in_date' => $currentReservation->check_in_date,
                    'check_out_date' => $currentReservation->check_out_date,
                    'nights' => Carbon::parse($currentReservation->check_in_date)
                        ->diffInDays(Carbon::parse($currentReservation->check_out_date)),
                    'reservation_number' => $currentReservation->reservation_number,
                ];
            }

            // Get hotel information
            $hotelInfo = [
                'name' => Setting::getValue('hotel_name', 'Grand Hotel'),
                'address' => Setting::getValue('hotel_address', ''),
                'phone' => Setting::getValue('hotel_phone', ''),
                'email' => Setting::getValue('hotel_email', ''),
                'website' => Setting::getValue('hotel_website', ''),
                'logo_url' => Setting::getValue('hotel_logo_url', ''),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'room' => [
                        'number' => $room->room_number,
                        'type' => $room->roomType->name ?? 'Standard',
                        'floor' => $room->floor,
                        'building' => $room->building ?? 'Main',
                    ],
                    'guest' => $guestInfo,
                    'hotel' => $hotelInfo,
                    'current_time' => now()->toISOString(),
                    'timezone' => config('app.timezone', 'UTC'),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('IPTV getClientInfo error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Unable to retrieve client info'], 500);
        }
    }

    /**
     * Get location and weather information
     * GET /api/iptv/location-weather
     */
    public function getLocationWeather(Request $request)
    {
        try {
            // Get hotel location from settings
            $latitude = Setting::getValue('hotel_latitude', '6.5244'); // Lagos default
            $longitude = Setting::getValue('hotel_longitude', '3.3792');
            $city = Setting::getValue('hotel_city', 'Lagos');
            $country = Setting::getValue('hotel_country', 'Nigeria');

            // Get weather from OpenWeatherMap API (if API key is configured)
            $weatherApiKey = Setting::getValue('openweather_api_key');
            $weatherData = null;

            if ($weatherApiKey) {
                try {
                    $weatherResponse = Http::timeout(10)->get('https://api.openweathermap.org/data/2.5/weather', [
                        'lat' => $latitude,
                        'lon' => $longitude,
                        'appid' => $weatherApiKey,
                        'units' => 'metric'
                    ]);

                    if ($weatherResponse->successful()) {
                        $weather = $weatherResponse->json();
                        $weatherData = [
                            'temperature' => round($weather['main']['temp']),
                            'feels_like' => round($weather['main']['feels_like']),
                            'humidity' => $weather['main']['humidity'],
                            'description' => ucfirst($weather['weather'][0]['description']),
                            'icon' => $weather['weather'][0]['icon'],
                            'wind_speed' => $weather['wind']['speed'] ?? 0,
                            'pressure' => $weather['main']['pressure'],
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::warning('Weather API failed: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'location' => [
                        'city' => $city,
                        'country' => $country,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'timezone' => config('app.timezone', 'UTC'),
                    ],
                    'weather' => $weatherData,
                    'last_updated' => now()->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve location/weather data'
            ], 500);
        }
    }

    /**
     * Get reception contact information
     * GET /api/iptv/reception-contact
     */
    public function getReceptionContact(Request $request)
    {
        try {
            $contacts = [
                'reception' => [
                    'name' => 'Front Desk',
                    'phone' => Setting::getValue('reception_phone', '0'),
                    'extension' => Setting::getValue('reception_extension', '0'),
                    'email' => Setting::getValue('reception_email', ''),
                    'hours' => Setting::getValue('reception_hours', '24/7'),
                ],
                'concierge' => [
                    'name' => 'Concierge',
                    'phone' => Setting::getValue('concierge_phone', ''),
                    'extension' => Setting::getValue('concierge_extension', '1'),
                    'email' => Setting::getValue('concierge_email', ''),
                    'hours' => Setting::getValue('concierge_hours', '6:00 AM - 10:00 PM'),
                ],
                'room_service' => [
                    'name' => 'Room Service',
                    'phone' => Setting::getValue('room_service_phone', ''),
                    'extension' => Setting::getValue('room_service_extension', '2'),
                    'email' => Setting::getValue('room_service_email', ''),
                    'hours' => Setting::getValue('room_service_hours', '24/7'),
                ],
                'housekeeping' => [
                    'name' => 'Housekeeping',
                    'phone' => Setting::getValue('housekeeping_phone', ''),
                    'extension' => Setting::getValue('housekeeping_extension', '3'),
                    'email' => Setting::getValue('housekeeping_email', ''),
                    'hours' => Setting::getValue('housekeeping_hours', '8:00 AM - 6:00 PM'),
                ],
                'maintenance' => [
                    'name' => 'Maintenance',
                    'phone' => Setting::getValue('maintenance_phone', ''),
                    'extension' => Setting::getValue('maintenance_extension', '4'),
                    'email' => Setting::getValue('maintenance_email', ''),
                    'hours' => Setting::getValue('maintenance_hours', '24/7'),
                ],
                'emergency' => [
                    'name' => 'Emergency',
                    'phone' => Setting::getValue('emergency_phone', '911'),
                    'extension' => Setting::getValue('emergency_extension', '911'),
                    'description' => 'For life-threatening emergencies',
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'contacts' => $contacts,
                    'hotel_phone' => Setting::getValue('hotel_phone', ''),
                    'hotel_address' => Setting::getValue('hotel_address', ''),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve contact info'
            ], 500);
        }
    }

    /**
     * Get client bill information
     * GET /api/iptv/client-bill
     */
    public function getClientBill(Request $request)
    {
        try {
            // Require a registered, active device — bill contains financial + guest PII
            $device = $this->verifyDevice($request);
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'A registered X-Device-ID header is required',
                ], 401);
            }

            $room = $device->room;

            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room not found'
                ], 404);
            }

            $currentReservation = Reservation::where('room_id', $room->id)
                ->where('status', 'checked_in')
                ->whereDate('check_in_date', '<=', now())
                ->whereDate('check_out_date', '>=', now())
                ->with('guest')
                ->first();

            if (!$currentReservation) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active reservation found'
                ], 404);
            }

            // Get guest folio
            $folio = GuestFolio::where('reservation_id', $currentReservation->id)
                ->where('status', 'open')
                ->with(['charges', 'payments'])
                ->first();

            $billData = [
                'reservation_number' => $currentReservation->reservation_number,
                'guest_name' => $currentReservation->guest->first_name . ' ' . $currentReservation->guest->last_name,
                'room_number' => $room->room_number,
                'check_in_date' => $currentReservation->check_in_date,
                'check_out_date' => $currentReservation->check_out_date,
                'currency' => Setting::getValue('currency_symbol', '$'),
                'total_amount' => 0,
                'paid_amount' => 0,
                'balance_amount' => 0,
                'charges' => [],
                'payments' => [],
            ];

            if ($folio) {
                $billData['total_amount'] = $folio->total_amount;
                $billData['paid_amount'] = $folio->paid_amount;
                $billData['balance_amount'] = $folio->balance_amount;

                // Get charges
                $billData['charges'] = $folio->charges->map(function ($charge) {
                    return [
                        'date' => $charge->charge_date,
                        'description' => $charge->description,
                        'amount' => $charge->net_amount,
                        'category' => $this->getChargeCategory($charge->charge_code),
                    ];
                });

                // Get payments
                $billData['payments'] = $folio->payments->map(function ($payment) {
                    return [
                        'date' => $payment->processed_at,
                        'method' => ucfirst(str_replace('_', ' ', $payment->payment_method)),
                        'amount' => $payment->local_amount,
                        'status' => ucfirst($payment->status),
                    ];
                });
            }

            return response()->json([
                'success' => true,
                'data' => $billData
            ]);

        } catch (\Exception $e) {
            Log::error('IPTV getClientBill error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Unable to retrieve bill info'], 500);
        }
    }

    /**
     * Get hotel services information
     * GET /api/iptv/hotel-services
     */
    public function getHotelServices(Request $request)
    {
        try {
            $services = [
                'dining' => [
                    'name' => 'Dining Services',
                    'icon' => 'restaurant',
                    'items' => [
                        [
                            'name' => 'Main Restaurant',
                            'description' => 'Fine dining experience',
                            'hours' => '6:00 AM - 11:00 PM',
                            'phone' => Setting::getValue('restaurant_phone', ''),
                            'location' => 'Ground Floor',
                        ],
                        [
                            'name' => 'Room Service',
                            'description' => '24-hour room service',
                            'hours' => '24/7',
                            'phone' => Setting::getValue('room_service_phone', ''),
                            'extension' => '2',
                        ],
                        [
                            'name' => 'Bar & Lounge',
                            'description' => 'Cocktails and light snacks',
                            'hours' => '4:00 PM - 2:00 AM',
                            'phone' => Setting::getValue('bar_phone', ''),
                            'location' => 'Lobby Level',
                        ],
                    ]
                ],
                'wellness' => [
                    'name' => 'Wellness & Spa',
                    'icon' => 'spa',
                    'items' => [
                        [
                            'name' => 'Spa Services',
                            'description' => 'Massage and beauty treatments',
                            'hours' => '9:00 AM - 9:00 PM',
                            'phone' => Setting::getValue('spa_phone', ''),
                            'location' => '2nd Floor',
                        ],
                        [
                            'name' => 'Fitness Center',
                            'description' => '24-hour gym access',
                            'hours' => '24/7',
                            'location' => '2nd Floor',
                        ],
                        [
                            'name' => 'Swimming Pool',
                            'description' => 'Outdoor pool and deck',
                            'hours' => '6:00 AM - 10:00 PM',
                            'location' => 'Pool Deck',
                        ],
                    ]
                ],
                'business' => [
                    'name' => 'Business Services',
                    'icon' => 'business',
                    'items' => [
                        [
                            'name' => 'Business Center',
                            'description' => 'Computers, printing, fax',
                            'hours' => '24/7',
                            'location' => 'Lobby Level',
                        ],
                        [
                            'name' => 'Conference Rooms',
                            'description' => 'Meeting and event spaces',
                            'hours' => 'By appointment',
                            'phone' => Setting::getValue('events_phone', ''),
                            'location' => '3rd Floor',
                        ],
                    ]
                ],
                'transportation' => [
                    'name' => 'Transportation',
                    'icon' => 'car',
                    'items' => [
                        [
                            'name' => 'Airport Shuttle',
                            'description' => 'Complimentary airport transfer',
                            'hours' => '5:00 AM - 11:00 PM',
                            'phone' => Setting::getValue('shuttle_phone', ''),
                            'advance_booking' => '2 hours',
                        ],
                        [
                            'name' => 'Taxi Service',
                            'description' => 'Local taxi arrangements',
                            'hours' => '24/7',
                            'phone' => Setting::getValue('taxi_phone', ''),
                        ],
                    ]
                ],
                'other' => [
                    'name' => 'Other Services',
                    'icon' => 'services',
                    'items' => [
                        [
                            'name' => 'Laundry Service',
                            'description' => 'Same-day laundry and dry cleaning',
                            'hours' => '8:00 AM - 6:00 PM',
                            'phone' => Setting::getValue('laundry_phone', ''),
                        ],
                        [
                            'name' => 'Lost & Found',
                            'description' => 'Lost item assistance',
                            'hours' => '24/7',
                            'phone' => Setting::getValue('reception_phone', ''),
                        ],
                    ]
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'services' => $services,
                    'hotel_name' => Setting::getValue('hotel_name', 'Grand Hotel'),
                    'last_updated' => now()->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve hotel services'
            ], 500);
        }
    }

    /**
     * Get IPTV configuration for the room
     * GET /api/iptv/config
     */
    public function getIptvConfig(Request $request)
    {
        try {
            $deviceId = $request->header('X-Device-ID') ?? $request->get('device_id');
            $roomNumber = $request->header('X-Room-Number') ?? $request->get('room_number');

            // Find room
            $room = null;
            if ($deviceId) {
                $device = IptvDevice::where('device_id', $deviceId)->first();
                $room = $device ? $device->room : null;
            } elseif ($roomNumber) {
                $room = Room::where('room_number', $roomNumber)->first();
            }

            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room not found'
                ], 404);
            }

            // Get IPTV settings for the room
            $iptvSettings = $room->iptvSettings;
            $iptvPackage = $iptvSettings ? $iptvSettings->package : null;

            $config = [
                'xtream_api' => [
                    'url' => Setting::getValue('xtream_api_url'),
                    'username' => Setting::getValue('xtream_username'),
                    'password' => Setting::getValue('xtream_password'),
                ],
                'package' => $iptvPackage ? [
                    'name' => $iptvPackage->name,
                    'code' => $iptvPackage->code,
                    'includes_adult_content' => $iptvPackage->includes_adult_content,
                    'includes_premium_channels' => $iptvPackage->includes_premium_channels,
                    'includes_international_channels' => $iptvPackage->includes_international_channels,
                    'xtream_categories' => $iptvPackage->xtream_categories,
                    'xtream_channel_groups' => $iptvPackage->xtream_channel_groups,
                ] : null,
                'room_settings' => $iptvSettings ? [
                    'adult_content_enabled' => $iptvSettings->adult_content_enabled,
                    'parental_control_pin' => $iptvSettings->parental_control_pin,
                    'volume_limit' => $iptvSettings->volume_limit,
                    'quiet_hours_start' => $iptvSettings->quiet_hours_start,
                    'quiet_hours_end' => $iptvSettings->quiet_hours_end,
                    'language_preferences' => $iptvSettings->language_preferences,
                    'auto_power_off' => $iptvSettings->auto_power_off,
                    'auto_power_off_time' => $iptvSettings->auto_power_off_time,
                    'xtream_blocked_categories' => $iptvSettings->xtream_blocked_categories,
                    'xtream_blocked_channels' => $iptvSettings->xtream_blocked_channels,
                ] : null,
                'device_info' => [
                    'room_number' => $room->room_number,
                    'device_id' => $deviceId,
                    'last_updated' => now()->toISOString(),
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $config
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve IPTV configuration'
            ], 500);
        }
    }

    /**
     * Update IPTV device status
     * POST /api/iptv/device-status
     */
    public function updateDeviceStatus(Request $request)
    {
        try {
            $request->validate([
                'device_id' => 'required|string',
                'status' => 'required|string|in:online,offline,error',
                'last_activity' => 'nullable|date',
                'ip_address' => 'nullable|ip',
                'user_agent' => 'nullable|string',
                'app_version' => 'nullable|string',
            ]);

            $device = IptvDevice::where('device_id', $request->device_id)->first();

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found'
                ], 404);
            }

            $device->update([
                'status' => $request->status,
                'last_activity' => $request->last_activity ?? now(),
                'ip_address' => $request->ip_address ?? $request->ip(),
                'user_agent' => $request->user_agent,
                'app_version' => $request->app_version,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update device status'
            ], 500);
        }
    }

    /**
     * Get emergency information
     * GET /api/iptv/emergency
     */
    public function getEmergencyInfo(Request $request)
    {
        try {
            $emergencyInfo = [
                'emergency_contacts' => [
                    [
                        'name' => 'Hotel Security',
                        'phone' => Setting::getValue('security_phone', '0'),
                        'extension' => Setting::getValue('security_extension', '911'),
                        'description' => 'Hotel security and emergency response',
                        'priority' => 1,
                    ],
                    [
                        'name' => 'Fire Department',
                        'phone' => Setting::getValue('fire_department_phone', '911'),
                        'description' => 'Fire emergency services',
                        'priority' => 2,
                    ],
                    [
                        'name' => 'Police',
                        'phone' => Setting::getValue('police_phone', '911'),
                        'description' => 'Police emergency services',
                        'priority' => 3,
                    ],
                    [
                        'name' => 'Medical Emergency',
                        'phone' => Setting::getValue('medical_emergency_phone', '911'),
                        'description' => 'Medical emergency services',
                        'priority' => 4,
                    ],
                ],
                'evacuation_info' => [
                    'assembly_point' => Setting::getValue('assembly_point', 'Main Parking Area'),
                    'evacuation_routes' => Setting::getValue('evacuation_routes', 'Follow illuminated exit signs'),
                    'emergency_instructions' => [
                        'Stay calm and do not panic',
                        'Follow staff instructions',
                        'Use stairs, never elevators during emergency',
                        'Proceed to the nearest exit',
                        'Meet at the designated assembly point',
                    ],
                ],
                'hotel_emergency_contact' => [
                    'name' => 'Front Desk',
                    'phone' => Setting::getValue('reception_phone', '0'),
                    'extension' => '0',
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $emergencyInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve emergency info'
            ], 500);
        }
    }

    /**
     * Helper method to get charge category name
     */
    private function getChargeCategory($chargeCode)
    {
        $categories = [
            'ROOM' => 'Room Charges',
            'FOOD' => 'Food & Beverage',
            'BEVERAGE' => 'Food & Beverage',
            'CONFERENCE' => 'Conference Services',
            'IPTV' => 'IPTV Services',
            'OTHER' => 'Other Services',
            'TAX' => 'Taxes',
            'SERVICE' => 'Service Charges',
        ];

        return $categories[$chargeCode] ?? 'Other';
    }
}
