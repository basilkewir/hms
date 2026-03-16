import * as Notifications from 'expo-notifications';
import { Platform, Vibration } from 'react-native';

// Configure how notifications are displayed while the app is in the foreground
Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: true,
    shouldSetBadge: false,
  }),
});

export const notificationService = {
  /**
   * Request notification permissions from the OS.
   * Should be called once on app startup.
   * Returns true if granted.
   */
  async requestPermissions() {
    try {
      if (Platform.OS === 'android') {
        await Notifications.setNotificationChannelAsync('tasks', {
          name: 'Task Notifications',
          importance: Notifications.AndroidImportance.HIGH,
          sound: 'default',
          vibrationPattern: [0, 300, 100, 300],
          enableVibrate: true,
        });
      }

      const { status: existingStatus } = await Notifications.getPermissionsAsync();
      if (existingStatus === 'granted') return true;

      const { status } = await Notifications.requestPermissionsAsync();
      return status === 'granted';
    } catch (err) {
      console.warn('Notification permission request failed:', err);
      return false;
    }
  },

  /**
   * Fire an immediate local notification with system sound when new tasks arrive.
   * @param {number} count - number of new tasks
   * @param {string[]} roomNumbers - list of room numbers for the new tasks
   */
  async notifyNewTasks(count, roomNumbers = []) {
    try {
      // Haptic vibration pattern: short-pause-short
      Vibration.vibrate([0, 250, 100, 250]);

      const title = count === 1
        ? '🧹 New Task Assigned!'
        : `🧹 ${count} New Tasks Assigned!`;

      const body = roomNumbers.length > 0
        ? `Room${roomNumbers.length > 1 ? 's' : ''}: ${roomNumbers.slice(0, 3).join(', ')}${roomNumbers.length > 3 ? ` +${roomNumbers.length - 3} more` : ''}`
        : 'You have new cleaning tasks. Tap to view.';

      await Notifications.scheduleNotificationAsync({
        content: {
          title,
          body,
          sound: 'default',
          data: { screen: 'Tasks' },
        },
        trigger: null, // fire immediately
      });
    } catch (err) {
      // Never crash the app over a notification failure — just log
      console.warn('Failed to send task notification:', err);
      // Still vibrate as fallback
      try { Vibration.vibrate([0, 300, 100, 300]); } catch (_) {}
    }
  },
};
