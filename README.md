# 🏨 Hotel Management System with IPTV Integration

A comprehensive hotel management system built with **Laravel Jetstream**, **Inertia.js**, and **Vue.js** that seamlessly integrates with Android IPTV room client systems for complete hotel operations management.

## ✨ Features

### 🏢 Core Hotel Management
- **Multi-Role System**: Admin, Manager, Accountant, Front Desk, Housekeeping, Maintenance, Staff
- **Room Management**: Room types, pricing, availability, housekeeping status
- **Guest Registration**: Complete guest profiles with police verification details
- **Reservation System**: Booking management with real-time availability
- **Check-in/Check-out**: Streamlined guest services with folio management

### 📺 IPTV Integration
- **Room IPTV Control**: Manage IPTV settings per room and room type
- **Channel Management**: Configure available channels per room type (Basic/Premium/VIP packages)
- **VOD Content**: Video on demand library with rental tracking
- **Usage Analytics**: Real-time IPTV usage monitoring and reporting
- **Device Management**: Android device registration and status monitoring
- **Guest Preferences**: Personalized channel preferences and parental controls

### 👥 Staff Management & HRM
- **Employee Profiles**: Complete staff information with emergency contacts
- **Role-Based Access**: Granular permissions system with 40+ permissions
- **Shift Management**: Schedule and track work shifts (Morning/Evening/Night)
- **Time Tracking**: Clock in/out with GPS location and attendance monitoring
- **Leave Management**: Leave requests, approvals, and balance tracking
- **Payroll Integration**: Automated payroll calculations with overtime

### 💰 Financial Management
- **Guest Folios**: Detailed billing with room charges, services, taxes
- **Payment Processing**: Multiple payment methods (Cash, Card, Bank Transfer)
- **Expense Tracking**: Hotel operational expenses with approval workflow
- **Financial Reports**: Revenue, occupancy, profit/loss statements
- **Audit Trail**: Complete financial transaction logging

### 🚔 Police Integration & Compliance
- **Guest Verification**: Police-required guest details and document storage
- **ID Management**: Passport, visa, and national ID verification
- **Compliance Reports**: Automated police reporting for guest registrations
- **Security Alerts**: Flagged guest notifications and blacklist management
- **Document Expiry**: Automatic alerts for expiring documents

## 🛠️ Technology Stack

- **Backend**: Laravel 10 with Jetstream
- **Frontend**: Vue.js 3 with Inertia.js
- **Database**: MySQL (kewirhms)
- **Authentication**: Laravel Sanctum with role-based permissions
- **Styling**: Tailwind CSS with responsive design
- **Charts**: Chart.js for analytics and reporting
- **File Storage**: Laravel Storage with document management
- **API**: RESTful APIs for IPTV client integration

## 🚀 Quick Installation

### Prerequisites
- PHP 8.1+
- MySQL 8.0+
- Node.js 16+
- Composer
- npm

### Automated Setup
```bash
# Clone the repository
git clone <repository-url> hotel-management-system
cd hotel-management-system

# Run the automated setup script
chmod +x setup.sh
./setup.sh
```

### Manual Installation
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create database
mysql -u root -e "CREATE DATABASE kewirhms;"

# Run migrations and seed data
php artisan migrate --seed

# Build frontend assets
npm run build

# Start the application
php artisan serve
```

## 👤 Default User Accounts

| Role | Email | Password | Permissions |
|------|-------|----------|-------------|
| **Admin** | admin@hotel.com | password | Full system access |
| **Manager** | manager@hotel.com | password | Hotel operations management |
| **Accountant** | accountant@hotel.com | password | Financial management |
| **Front Desk** | frontdesk@hotel.com | password | Guest services & reservations |
| **Staff** | staff@hotel.com | password | Basic staff functions |

## 📱 IPTV Android Client Integration

### API Endpoints
```
POST /api/iptv/authenticate     - Device authentication
GET  /api/iptv/channels         - Get available channels
GET  /api/iptv/vod             - Get VOD content
POST /api/iptv/log-usage       - Log viewing activity
POST /api/iptv/heartbeat       - Device status update
```

### Authentication Flow
1. **Device Registration**: Android client registers with device ID and MAC address
2. **Room Association**: System associates device with specific room
3. **Channel Configuration**: Client receives channels based on room's IPTV package
4. **Usage Tracking**: All viewing activity is logged for analytics

### Sample Android Integration
```java
// Authenticate device
POST /api/iptv/authenticate
{
    "device_id": "android_device_123",
    "mac_address": "AA:BB:CC:DD:EE:FF",
    "room_number": "101",
    "android_version": "11",
    "app_version": "1.0.0"
}

// Get channels
GET /api/iptv/channels?device_id=android_device_123

// Log channel change
POST /api/iptv/log-usage
{
    "device_id": "android_device_123",
    "action": "channel_change",
    "channel_id": 25,
    "action_data": {"previous_channel": 24}
}
```

## 🏗️ System Architecture

### Database Schema
- **Users & Roles**: Multi-role authentication system
- **Guests & Reservations**: Complete guest lifecycle management
- **Rooms & Types**: Flexible room configuration
- **IPTV System**: Channel packages, devices, usage logs
- **Financial**: Folios, payments, expenses, payroll
- **Time Tracking**: Shifts, attendance, leave management

### Permission System
40+ granular permissions across 8 categories:
- User Management
- Reservations
- Guest Management
- Room Management
- Financial
- IPTV
- Staff Management
- Time Tracking
- Reports
- System Administration

## 📊 Reports & Analytics

### Management Dashboard
- **Occupancy Rates**: Real-time and historical data
- **Revenue Analytics**: Daily, weekly, monthly reports
- **Staff Performance**: Attendance, productivity metrics
- **IPTV Usage**: Channel popularity, viewing patterns
- **Guest Analytics**: Demographics, preferences, satisfaction

### Financial Reports
- **Revenue Reports**: Room revenue, service charges, total income
- **Expense Reports**: Operational costs, staff costs, utilities
- **Profit & Loss**: Comprehensive financial statements
- **Payment Analytics**: Payment methods, outstanding balances

### Operational Reports
- **Housekeeping Reports**: Room status, cleaning schedules
- **Maintenance Reports**: Equipment status, repair schedules
- **Guest Reports**: Check-ins, check-outs, no-shows
- **Staff Reports**: Attendance, overtime, leave balances

## 🔧 Configuration

### Environment Variables
```env
# Hotel Information
HOTEL_NAME="Grand Hotel"
HOTEL_ADDRESS="123 Hotel Street, City"
HOTEL_PHONE="+1234567890"
HOTEL_EMAIL="info@hotel.com"

# IPTV Integration
IPTV_API_URL=http://localhost:8080/api
IPTV_API_KEY=your-iptv-api-key
IPTV_DEFAULT_CHANNELS=50

# Police Integration
POLICE_API_ENABLED=true
POLICE_API_URL=https://police-api.gov
POLICE_API_KEY=your-police-api-key
```

### IPTV Packages
- **Basic Package**: 50 channels, no adult content
- **Premium Package**: 150 channels, premium content
- **VIP Package**: Unlimited channels, all content

## 🚦 Development

### Start Development Servers
```bash
# Backend (Laravel)
php artisan serve

# Frontend (Vite)
npm run dev

# Watch for changes
npm run watch
```

### Database Management
```bash
# Fresh migration with seeding
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_table_name

# Create new model
php artisan make:model ModelName -m
```

### Testing
```bash
# Run PHP tests
php artisan test

# Run JavaScript tests
npm run test
```

## 📚 Documentation

- **Laravel**: https://laravel.com/docs
- **Jetstream**: https://jetstream.laravel.com
- **Inertia.js**: https://inertiajs.com
- **Vue.js**: https://vuejs.org
- **Tailwind CSS**: https://tailwindcss.com

## 🔒 Security Features

- **Role-based Access Control**: Granular permissions system
- **API Authentication**: Sanctum token-based authentication
- **Data Encryption**: Sensitive data encryption at rest
- **Audit Logging**: Complete user action logging
- **Document Security**: Secure document storage and access
- **Police Compliance**: Automated compliance reporting

## 🌐 Production Deployment

### Server Requirements
- **PHP**: 8.1+ with required extensions
- **MySQL**: 8.0+ or MariaDB 10.3+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **SSL Certificate**: Required for production
- **Memory**: 2GB+ RAM recommended
- **Storage**: 10GB+ for documents and logs

### Deployment Steps
1. **Server Setup**: Configure web server and PHP
2. **Database**: Create production database
3. **Code Deployment**: Deploy code and run migrations
4. **SSL Configuration**: Set up HTTPS
5. **Backup Strategy**: Configure automated backups
6. **Monitoring**: Set up application monitoring

## 🤝 Support

For technical support and customization:
- **Documentation**: Check the docs/ directory
- **Issues**: Report bugs and feature requests
- **Training**: Staff training materials available
- **Customization**: Professional services available

## 📄 License

Proprietary - Hotel Management System
© 2024 Hotel Management Solutions

---

**🏨 Ready to revolutionize your hotel operations with integrated IPTV management!**
