import API from './api';

export const taskService = {
  async getMyTasks() {
    try {
      const response = await API.get('/api/housekeeping/tasks/my-tasks');
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || error.message || 'Failed to fetch tasks';
    }
  },
  
  async getCompletedTasks() {
    try {
      const response = await API.get('/api/housekeeping/tasks/completed');
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || error.message || 'Failed to fetch completed tasks';
    }
  },
  
  async getTaskById(taskId) {
    try {
      const response = await API.get(`/api/housekeeping/tasks/${taskId}`);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || error.message || 'Failed to fetch task';
    }
  },
  
  async updateTaskStatus(taskId, status, notes = '', completionNotes = '') {
    try {
      const payload = {
        status,
      };
      
      // Add notes if provided
      if (notes) {
        payload.notes = notes;
      }
      
      // Add completion_notes if provided (for completed tasks)
      if (completionNotes) {
        payload.completion_notes = completionNotes;
      }
      
      const response = await API.post(`/api/housekeeping/tasks/${taskId}/update-status`, payload);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || error.message || 'Failed to update task';
    }
  },
  
  async markRoomClean(roomId, notes = '') {
    try {
      const response = await API.post(`/api/housekeeping/rooms/${roomId}/mark-clean`, {
        notes,
      });
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || error.message || 'Failed to mark room as clean';
    }
  },
  
  async startTask(taskId) {
    return this.updateTaskStatus(taskId, 'in_progress');
  },
  
  async completeTask(taskId, completionNotes = '') {
    return this.updateTaskStatus(taskId, 'completed', '', completionNotes);
  },
};
