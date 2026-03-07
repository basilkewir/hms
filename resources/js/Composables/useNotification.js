import { createApp, h } from 'vue';
import Notification from '@/Components/Notification.vue';

let notificationContainer = null;
let notificationId = 0;

function ensureContainer() {
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'notification-container';
        notificationContainer.className = 'fixed top-4 right-4 z-50 space-y-2 flex flex-col items-end';
        document.body.appendChild(notificationContainer);
    }
    return notificationContainer;
}

function showNotification(message, type = 'success', duration = 5000) {
    ensureContainer();
    const id = notificationId++;
    const container = document.createElement('div');
    notificationContainer.appendChild(container);

    const close = () => {
        setTimeout(() => {
            if (container.parentNode) {
                container.parentNode.removeChild(container);
            }
            if (notificationContainer && notificationContainer.children.length === 0) {
                notificationContainer.remove();
                notificationContainer = null;
            }
        }, 300);
    };

    const app = createApp({
        render: () => h(Notification, {
            message,
            type,
            duration,
            onClose: close
        })
    });

    app.mount(container);

    if (duration > 0) {
        setTimeout(() => {
            close();
            app.unmount();
        }, duration);
    }

    return { id, close };
}

export function useNotification() {
    return {
        show: showNotification,
        success: (message, duration) => showNotification(message, 'success', duration),
        error: (message, duration) => showNotification(message, 'error', duration),
        warning: (message, duration) => showNotification(message, 'warning', duration),
        info: (message, duration) => showNotification(message, 'info', duration)
    };
}

// Global notification functions for easy access
export const notify = {
    success: (message, duration = 5000) => showNotification(message, 'success', duration),
    error: (message, duration = 5000) => showNotification(message, 'error', duration),
    warning: (message, duration = 5000) => showNotification(message, 'warning', duration),
    info: (message, duration = 5000) => showNotification(message, 'info', duration)
};
