import { Storage } from '../services/storage';
import { formatServerUrl } from './validation';

/**
 * Check if server is reachable
 */
export const checkServerConnection = async () => {
  try {
    const serverUrl = formatServerUrl(await Storage.getServerUrl());
    
    if (!serverUrl) {
      return {
        connected: false,
        error: 'Server URL not configured',
      };
    }

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 second timeout

    try {
      const response = await fetch(`${serverUrl}/api/health`, {
        method: 'GET',
        headers: {
          Accept: 'application/json',
        },
        signal: controller.signal,
      });

      clearTimeout(timeoutId);

      if (response.ok) {
        return {
          connected: true,
          serverUrl,
        };
      } else {
        return {
          connected: false,
          error: 'Server returned an error',
        };
      }
    } catch (error) {
      clearTimeout(timeoutId);
      
      if (error.name === 'AbortError') {
        return {
          connected: false,
          error: 'Connection timeout. Check your network and server URL.',
        };
      }
      
      return {
        connected: false,
        error: error.message || 'Failed to connect to server',
      };
    }
  } catch (error) {
    return {
      connected: false,
      error: error.message || 'Unknown error',
    };
  }
};

/**
 * Test server URL before saving
 */
export const testServerUrl = async (url) => {
  try {
    const formattedUrl = formatServerUrl(url);
    
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 5000);

    try {
      const response = await fetch(`${formattedUrl}/api/health`, {
        method: 'GET',
        headers: {
          Accept: 'application/json',
        },
        signal: controller.signal,
      });

      clearTimeout(timeoutId);

      return {
        success: response.ok,
        error: response.ok ? null : 'Server returned an error',
      };
    } catch (error) {
      clearTimeout(timeoutId);
      
      if (error.name === 'AbortError') {
        return {
          success: false,
          error: 'Connection timeout',
        };
      }
      
      return {
        success: false,
        error: error.message || 'Failed to connect',
      };
    }
  } catch (error) {
    return {
      success: false,
      error: error.message || 'Invalid URL',
    };
  }
};
