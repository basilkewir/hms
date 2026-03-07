import API from './api';
import { Storage } from './storage';

export const authService = {
  async login(email, password) {
    try {
      const serverUrl = await Storage.getServerUrl();
      if (!serverUrl) {
        throw new Error('Server URL not configured');
      }
      
      const response = await API.post('/api/login', {
        email,
        password,
      });
      
      if (response.data.token) {
        await Storage.setAuthToken(response.data.token);
        await Storage.setUserData(response.data.user);
        return response.data;
      }
      
      throw new Error('Login failed');
    } catch (error) {
      throw error.response?.data?.message || error.message || 'Login failed';
    }
  },
  
  async logout() {
    await Storage.clearAuth();
  },
  
  async getCurrentUser() {
    return await Storage.getUserData();
  },
  
  async isAuthenticated() {
    const token = await Storage.getAuthToken();
    return !!token;
  },
};
