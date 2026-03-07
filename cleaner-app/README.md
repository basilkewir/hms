# Hotel Cleaner Mobile App

A React Native Expo app for hotel cleaners to manage their tasks, mark rooms as cleaned, and report maintenance issues.

## Features

- **Server Configuration**: Configure the server URL on first launch
- **Authentication**: Login with housekeeping credentials
- **Task Management**: View and manage assigned cleaning tasks
- **Room Cleaning**: Mark rooms as cleaned with notes
- **Maintenance Reporting**: Report maintenance issues from mobile devices
- **Maintenance Status**: Check maintenance request status

## Setup

1. Install dependencies:
```bash
npm install
```

2. Start the development server:
```bash
npm start
```

3. Run on Android:
```bash
npm run android
```

4. Run on iOS:
```bash
npm run ios
```

## Configuration

On first launch, the app will prompt for the server URL. Enter the IP address of your Ubuntu server where the hotel management system is running.

Example: `http://192.168.1.100:8000`

## API Endpoints

The app communicates with the Laravel backend using the following endpoints:

- `POST /api/login` - User authentication
- `GET /api/housekeeping/tasks/my-tasks` - Get assigned tasks
- `POST /api/housekeeping/tasks/{id}/update-status` - Update task status
- `POST /api/housekeeping/rooms/{id}/mark-clean` - Mark room as clean
- `POST /api/maintenance-requests` - Create maintenance request
- `GET /api/maintenance-requests` - Get maintenance requests
- `GET /api/maintenance-requests/room/{id}/status` - Check room maintenance status

## Requirements

- Node.js 18+
- Expo CLI
- Android Studio (for Android development)
- Xcode (for iOS development, macOS only)

## Building for Production

To build the app for production:

```bash
# Android
eas build --platform android

# iOS
eas build --platform ios
```
