# Hotel IPTV Client API Documentation

## Overview
This API provides endpoints for IPTV client applications to access hotel services, guest information, billing, and other hotel-related data. The API is designed to support Android TV applications and other IPTV client platforms.

## Base URL
```
https://your-hotel-domain.com/api/iptv
```

## Authentication
Most endpoints do not require authentication for client access. However, device identification is required through headers:

### Required Headers
```
X-Device-ID: unique_device_identifier
X-Room-Number: room_number (alternative to device ID)
Content-Type: application/json
Accept: application/json
```

## API Endpoints

### 1. Client Information
Get guest and room information for the current session.

**Endpoint:** `GET /client-info`

**Headers:**
- `X-Device-ID` or `X-Room-Number` (required)

**Response:**
```json
{
  "success": true,
  "data": {
    "room": {
      "number": "305",
      "type": "Deluxe Suite",
      "floor": 3,
      "building": "Main"
    },
    "guest": {
      "name": "John Doe",
      "title": "Mr.",
      "check_in_date": "2025-06-20",
      "check_out_date": "2025-06-23",
      "nights": 3,
      "reservation_number": "RES-2025-001234"
    },
    "hotel": {
      "name": "Grand Hotel",
      "address": "123 Hotel Street, City",
      "phone": "+1234567890",
      "email": "info@grandhotel.com",
      "website": "https://grandhotel.com",
      "logo_url": "https://grandhotel.com/logo.png"
    },
    "current_time": "2025-06-20T14:30:00Z",
    "timezone": "UTC"
  }
}
```

### 2. Location & Weather
Get hotel location and current weather information.

**Endpoint:** `GET /location-weather`

**Response:**
```json
{
  "success": true,
  "data": {
    "location": {
      "city": "Lagos",
      "country": "Nigeria",
      "latitude": "6.5244",
      "longitude": "3.3792",
      "timezone": "Africa/Lagos"
    },
    "weather": {
      "temperature": 28,
      "feels_like": 32,
      "humidity": 75,
      "description": "Partly cloudy",
      "icon": "02d",
      "wind_speed": 3.5,
      "pressure": 1013
    },
    "last_updated": "2025-06-20T14:30:00Z"
  }
}
```

### 3. Reception Contact
Get hotel contact information and department phone numbers.

**Endpoint:** `GET /reception-contact`

**Response:**
```json
{
  "success": true,
  "data": {
    "contacts": {
      "reception": {
        "name": "Front Desk",
        "phone": "+1234567890",
        "extension": "0",
        "email": "frontdesk@hotel.com",
        "hours": "24/7"
      },
      "concierge": {
        "name": "Concierge",
        "phone": "+1234567891",
        "extension": "1",
        "email": "concierge@hotel.com",
        "hours": "6:00 AM - 10:00 PM"
      },
      "room_service": {
        "name": "Room Service",
        "phone": "+1234567892",
        "extension": "2",
        "email": "roomservice@hotel.com",
        "hours": "24/7"
      },
      "housekeeping": {
        "name": "Housekeeping",
        "phone": "+1234567893",
        "extension": "3",
        "email": "housekeeping@hotel.com",
        "hours": "8:00 AM - 6:00 PM"
      },
      "maintenance": {
        "name": "Maintenance",
        "phone": "+1234567894",
        "extension": "4",
        "email": "maintenance@hotel.com",
        "hours": "24/7"
      },
      "emergency": {
        "name": "Emergency",
        "phone": "911",
        "extension": "911",
        "description": "For life-threatening emergencies"
      }
    },
    "hotel_phone": "+1234567890",
    "hotel_address": "123 Hotel Street, City"
  }
}
```

### 4. Client Bill
Get current guest bill and folio information.

**Endpoint:** `GET /client-bill`

**Headers:**
- `X-Device-ID` or `X-Room-Number` (required)

**Response:**
```json
{
  "success": true,
  "data": {
    "reservation_number": "RES-2025-001234",
    "guest_name": "John Doe",
    "room_number": "305",
    "check_in_date": "2025-06-20",
    "check_out_date": "2025-06-23",
    "currency": "$",
    "total_amount": 1250.00,
    "paid_amount": 500.00,
    "balance_amount": 750.00,
    "charges": [
      {
        "date": "2025-06-20",
        "description": "Room charges",
        "amount": 300.00,
        "category": "Room Charges"
      },
      {
        "date": "2025-06-20",
        "description": "Restaurant dinner",
        "amount": 85.50,
        "category": "Food & Beverage"
      }
    ],
    "payments": [
      {
        "date": "2025-06-20T10:00:00Z",
        "method": "Credit Card",
        "amount": 500.00,
        "status": "Completed"
      }
    ]
  }
}
```

### 5. Hotel Services
Get comprehensive hotel services information.

**Endpoint:** `GET /hotel-services`

**Response:**
```json
{
  "success": true,
  "data": {
    "services": {
      "dining": {
        "name": "Dining Services",
        "icon": "restaurant",
        "items": [
          {
            "name": "Main Restaurant",
            "description": "Fine dining experience",
            "hours": "6:00 AM - 11:00 PM",
            "phone": "+1234567895",
            "location": "Ground Floor"
          },
          {
            "name": "Room Service",
            "description": "24-hour room service",
            "hours": "24/7",
            "phone": "+1234567892",
            "extension": "2"
          }
        ]
      },
      "wellness": {
        "name": "Wellness & Spa",
        "icon": "spa",
        "items": [
          {
            "name": "Spa Services",
            "description": "Massage and beauty treatments",
            "hours": "9:00 AM - 9:00 PM",
            "phone": "+1234567896",
            "location": "2nd Floor"
          }
        ]
      }
    },
    "hotel_name": "Grand Hotel",
    "last_updated": "2025-06-20T14:30:00Z"
  }
}
```

### 6. IPTV Configuration
Get IPTV configuration and Xtream Codes settings for the room.

**Endpoint:** `GET /config`

**Headers:**
- `X-Device-ID` or `X-Room-Number` (required)

**Response:**
```json
{
  "success": true,
  "data": {
    "xtream_api": {
      "url": "http://xtream-server.com:8080",
      "username": "hotel_username",
      "password": "hotel_password"
    },
    "package": {
      "name": "Premium Package",
      "code": "premium",
      "includes_adult_content": false,
      "includes_premium_channels": true,
      "includes_international_channels": true,
      "xtream_categories": [1, 2, 3, 4, 5],
      "xtream_channel_groups": null
    },
    "room_settings": {
      "adult_content_enabled": false,
      "parental_control_pin": null,
      "volume_limit": 100,
      "quiet_hours_start": "22:00:00",
      "quiet_hours_end": "07:00:00",
      "language_preferences": ["en", "fr"],
      "auto_power_off": true,
      "auto_power_off_time": "02:00:00",
      "xtream_blocked_categories": [],
      "xtream_blocked_channels": []
    },
    "device_info": {
      "room_number": "305",
      "device_id": "device_12345",
      "last_updated": "2025-06-20T14:30:00Z"
    }
  }
}
```

### 7. Device Status Update
Update device status and activity information.

**Endpoint:** `POST /device-status`

**Request Body:**
```json
{
  "device_id": "device_12345",
  "status": "online",
  "last_activity": "2025-06-20T14:30:00Z",
  "ip_address": "192.168.1.100",
  "user_agent": "AndroidTV/1.0",
  "app_version": "1.2.3"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Device status updated successfully"
}
```

### 8. Emergency Information
Get emergency contacts and evacuation information.

**Endpoint:** `GET /emergency`

**Response:**
```json
{
  "success": true,
  "data": {
    "emergency_contacts": [
      {
        "name": "Hotel Security",
        "phone": "+1234567899",
        "extension": "911",
        "description": "Hotel security and emergency response",
        "priority": 1
      },
      {
        "name": "Fire Department",
        "phone": "911",
        "description": "Fire emergency services",
        "priority": 2
      }
    ],
    "evacuation_info": {
      "assembly_point": "Main Parking Area",
      "evacuation_routes": "Follow illuminated exit signs",
      "emergency_instructions": [
        "Stay calm and do not panic",
        "Follow staff instructions",
        "Use stairs, never elevators during emergency",
        "Proceed to the nearest exit",
        "Meet at the designated assembly point"
      ]
    },
    "hotel_emergency_contact": {
      "name": "Front Desk",
      "phone": "+1234567890",
      "extension": "0"
    }
  }
}
```

## Error Responses

All endpoints return consistent error responses:

```json
{
  "success": false,
  "message": "Error description",
  "error_code": "ERROR_CODE" // Optional
}
```

### Common HTTP Status Codes
- `200` - Success
- `400` - Bad Request (missing required parameters)
- `404` - Not Found (room/device not found)
- `500` - Internal Server Error

## Rate Limiting
- 100 requests per minute per device
- 1000 requests per hour per device

## Integration Examples

### Android TV Integration
```java
// Example API call in Android
public void getClientInfo(String deviceId) {
    OkHttpClient client = new OkHttpClient();
    
    Request request = new Request.Builder()
        .url("https://hotel-api.com/api/iptv/client-info")
        .addHeader("X-Device-ID", deviceId)
        .addHeader("Content-Type", "application/json")
        .build();
    
    client.newCall(request).enqueue(new Callback() {
        @Override
        public void onResponse(Call call, Response response) {
            // Handle response
        }
        
        @Override
        public void onFailure(Call call, IOException e) {
            // Handle error
        }
    });
}
```

### JavaScript Integration
```javascript
// Example API call in JavaScript
async function getHotelServices() {
    try {
        const response = await fetch('/api/iptv/hotel-services', {
            headers: {
                'Content-Type': 'application/json',
                'X-Device-ID': 'device_12345'
            }
        });
        
        const data = await response.json();
        if (data.success) {
            console.log('Hotel services:', data.data);
        }
    } catch (error) {
        console.error('API Error:', error);
    }
}
```

## Support
For API support and questions, contact: api-support@hotel.com
