# Cleaner App Setup Guide

## Project Structure

```
cleaner-app/
├── src/
│   ├── screens/
│   │   ├── ServerConfigScreen.js    # Server URL configuration
│   │   ├── LoginScreen.js            # User authentication
│   │   ├── TasksScreen.js            # View and manage cleaning tasks
│   │   ├── CompleteTaskScreen.js     # Complete tasks with notes
│   │   ├── MaintenanceScreen.js      # Report maintenance issues
│   │   ├── CheckMaintenanceScreen.js # Check maintenance status
│   │   └── ProfileScreen.js          # User profile and settings
│   ├── services/
│   │   ├── api.js                    # Axios API client
│   │   ├── storage.js                # AsyncStorage utilities
│   │   ├── authService.js            # Authentication service
│   │   ├── taskService.js            # Task management service
│   │   └── maintenanceService.js     # Maintenance request service
│   └── utils/
│       └── validation.js             # URL validation utilities
├── App.js                             # Main app component with navigation
└── package.json                       # Dependencies
```

## API Integration

The app connects to the Laravel backend at the configured server URL. All API calls use Laravel Sanctum for authentication.

### Authentication Flow

1. User enters server URL on first launch
2. User logs in with email/password
3. Backend returns Sanctum token
4. Token is stored and used for all subsequent API calls

### Required Laravel API Routes

The following routes have been added to `routes/api.php`:

- `POST /api/login` - Mobile app login
- `GET /api/housekeeping/tasks/my-tasks` - Get cleaner's tasks
- `GET /api/housekeeping/tasks/{id}` - Get task details
- `POST /api/housekeeping/tasks/{id}/update-status` - Update task status
- `POST /api/housekeeping/rooms/{id}/mark-clean` - Mark room as clean
- `POST /api/maintenance-requests` - Create maintenance request
- `GET /api/maintenance-requests` - List maintenance requests
- `GET /api/maintenance-requests/{id}` - Get request details
- `GET /api/maintenance-requests/room/{id}/status` - Check room status

## Running the App

### Development

```bash
# Start Expo development server
npm start

# Run on Android device/emulator
npm run android

# Run on iOS simulator (macOS only)
npm run ios

# Run on web browser
npm run web
```

### Testing on Physical Device

1. Install Expo Go app on your phone
2. Start the development server: `npm start`
3. Scan the QR code with Expo Go (Android) or Camera app (iOS)

### Network Configuration

For the app to connect to your local server:

1. Ensure your phone and server are on the same network
2. Find your server's local IP address:
   ```bash
   # Linux/Ubuntu
   ip addr show | grep inet
   
   # Or
   hostname -I
   ```
3. Enter the IP in the app: `http://10.0.0.10` (replace with your actual server IP)
4. If HMS runs via Nginx on port 80, no extra port is needed.
5. If you run Laravel directly for testing, allow network connections:
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

## User Roles

The app is designed for users with the following roles:
- `housekeeping` - Primary role for cleaners
- `staff` - Alternative role
- `admin` - Full access

Users without these roles will be denied access during login.

## Features

### Task Management
- View all assigned cleaning tasks
- Start tasks (changes status to "in_progress")
- Complete tasks with completion notes
- See task details (room, type, priority, instructions)

### Room Cleaning
- Mark rooms as clean directly from task completion
- Add notes when marking rooms clean
- Automatically updates related tasks

### Maintenance Reporting
- Report maintenance issues with:
  - Title and description
  - Category (plumbing, electrical, HVAC, etc.)
  - Priority level
  - Room association (optional)
- View maintenance request status
- Check if maintenance was completed

### Profile
- View user information
- Change server URL
- Logout

## Troubleshooting

### Connection Issues
- Verify server URL is correct (include http://)
- Check server is running and accessible
- Ensure phone and server are on same network
- Check firewall settings on server

### Authentication Issues
- Verify user has housekeeping/staff/admin role
- Check credentials are correct
- Ensure Laravel Sanctum is properly configured

### Task Not Showing
- Verify user is assigned to tasks in the system
- Check task status (only pending/in_progress tasks show)
- Refresh the tasks list

## Building for Production

Install EAS CLI:
```bash
npm install -g eas-cli
```

Login to Expo:
```bash
eas login
```

Configure project:
```bash
eas build:configure
```

Build for Android:
```bash
eas build --platform android
```

Build for iOS:
```bash
eas build --platform ios
```
