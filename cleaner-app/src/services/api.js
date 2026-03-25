import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const API = axios.create({
  timeout: 10000,
});

const normalizeServerUrl = (value) => {
  if (!value || typeof value !== 'string') return '';
  return value.trim().replace(/\/+$/, '');
};

// Add request interceptor to include auth token
API.interceptors.request.use(
  async (config) => {
    const token = await AsyncStorage.getItem('auth_token');
    const rawServerUrl = await AsyncStorage.getItem('server_url');
    const serverUrl = normalizeServerUrl(rawServerUrl);
    
    if (!serverUrl) {
      return Promise.reject(new Error('Server URL not configured. Please configure it first.'));
    }
    
    // If URL is relative, prepend normalized server URL
    if (config.url && !config.url.startsWith('http')) {
      config.url = `${serverUrl}${config.url.startsWith('/') ? '' : '/'}${config.url}`;
    }
    
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    config.headers.Accept = 'application/json';
    
    // Only set Content-Type to JSON if it's not FormData
    // FormData will set its own Content-Type with boundary
    if (!(config.data instanceof FormData)) {
      config.headers['Content-Type'] = 'application/json';
    }
    
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Add response interceptor for error handling
API.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      // Token expired or invalid
      await AsyncStorage.removeItem('auth_token');
      await AsyncStorage.removeItem('user_data');
    }
    return Promise.reject(error);
  }
);

export default API;
