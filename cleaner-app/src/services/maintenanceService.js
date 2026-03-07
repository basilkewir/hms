import API from './api';

export const maintenanceService = {
  async getMaintenanceRequests(roomId = null) {
    try {
      const url = roomId 
        ? `/api/maintenance-requests?room_id=${roomId}`
        : '/api/maintenance-requests';
      const response = await API.get(url);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || error.message || 'Failed to fetch maintenance requests';
    }
  },
  
  async createMaintenanceRequest(data) {
    try {
      // If data is FormData, it will be sent as multipart/form-data
      // Otherwise, send as JSON
      const response = await API.post('/api/maintenance-requests', data, {
        headers: data instanceof FormData 
          ? { 'Content-Type': 'multipart/form-data' }
          : { 'Content-Type': 'application/json' },
      });
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || error.message || 'Failed to create maintenance request';
    }
  },
  
  async getMaintenanceRequestById(requestId) {
    try {
      const response = await API.get(`/api/maintenance-requests/${requestId}`);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || error.message || 'Failed to fetch maintenance request';
    }
  },
  
  async checkMaintenanceStatus(roomId) {
    try {
      const response = await API.get(`/api/maintenance-requests/room/${roomId}/status`);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || error.message || 'Failed to check maintenance status';
    }
  },
};
