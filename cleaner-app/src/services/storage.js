import AsyncStorage from '@react-native-async-storage/async-storage';

export const Storage = {
  // Server URL
  async getServerUrl() {
    return await AsyncStorage.getItem('server_url');
  },
  
  async setServerUrl(url) {
    await AsyncStorage.setItem('server_url', url);
  },
  
  // Auth
  async getAuthToken() {
    return await AsyncStorage.getItem('auth_token');
  },
  
  async setAuthToken(token) {
    await AsyncStorage.setItem('auth_token', token);
  },
  
  async getUserData() {
    const data = await AsyncStorage.getItem('user_data');
    return data ? JSON.parse(data) : null;
  },
  
  async setUserData(user) {
    await AsyncStorage.setItem('user_data', JSON.stringify(user));
  },
  
  async clearAuth() {
    await AsyncStorage.removeItem('auth_token');
    await AsyncStorage.removeItem('user_data');
  },
  
  async clearAll() {
    await AsyncStorage.clear();
  },
};
