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
    const timeoutId = setTimeout(() => controller.abort(), 10000); // Increased to 10 seconds

    try {
      // Try ping endpoint first (simpler)
      let response;
      try {
        response = await fetch(`${serverUrl}/api/ping`, {
          method: 'GET',
          headers: {
            Accept: 'application/json',
          },
          signal: controller.signal,
        });
      } catch (pingError) {
        // Fallback to health endpoint
        response = await fetch(`${serverUrl}/api/health`, {
          method: 'GET',
          headers: {
            Accept: 'application/json',
          },
          signal: controller.signal,
        });
      }

      clearTimeout(timeoutId);

      if (response.ok) {
        return {
          connected: true,
          serverUrl,
        };
      } else {
        return {
          connected: false,
          error: `Server returned status ${response.status}`,
        };
      }
    } catch (error) {
      clearTimeout(timeoutId);
      
      if (error.name === 'AbortError') {
        return {
          connected: false,
          error: 'Connection timeout (10s). Check network and server URL.',
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
    const timeoutId = setTimeout(() => controller.abort(), 10000); // Increased timeout

    try {
      // Try ping endpoint first (simpler)
      let response;
      try {
        response = await fetch(`${formattedUrl}/api/ping`, {
          method: 'GET',
          headers: {
            Accept: 'application/json',
          },
          signal: controller.signal,
        });
      } catch (pingError) {
        // Fallback to health endpoint
        response = await fetch(`${formattedUrl}/api/health`, {
          method: 'GET',
          headers: {
            Accept: 'application/json',
          },
          signal: controller.signal,
        });
      }

      clearTimeout(timeoutId);

      return {
        success: response.ok,
        error: response.ok ? null : `Server returned status ${response.status}`,
      };
    } catch (error) {
      clearTimeout(timeoutId);
      
      if (error.name === 'AbortError') {
        return {
          success: false,
          error: 'Connection timeout (10s)',
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
