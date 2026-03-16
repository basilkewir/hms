import React, { useState, useEffect, useRef, useCallback } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  FlatList,
  RefreshControl,
  Alert,
  ActivityIndicator,
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { taskService } from '../services/taskService';
import { notificationService } from '../services/notificationService';
import { Colors } from '../constants/colors';

export default function TasksScreen({ navigation }) {
  const [tasks, setTasks] = useState([]);
  const [completedTasks, setCompletedTasks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [activeTab, setActiveTab] = useState('active'); // 'active' or 'completed'
  const intervalRef = useRef(null);
  // null = not yet initialized (first load), Set after first fetch
  const knownTaskIdsRef = useRef(null);

  // Auto-refresh interval: 30 seconds
  const REFRESH_INTERVAL = 30000;

  // Load active tasks function - wrapped in useCallback to avoid stale closures
  const loadTasks = useCallback(async (showLoading = true) => {
    try {
      if (showLoading) {
        setLoading(true);
      }
      const response = await taskService.getMyTasks();
      const incoming = response.data || response || [];

      // Detect newly assigned tasks on silent background refreshes
      if (!showLoading && knownTaskIdsRef.current !== null) {
        const knownIds = knownTaskIdsRef.current;
        const newTasks = incoming.filter(t => !knownIds.has(t.id));
        if (newTasks.length > 0) {
          const roomNumbers = newTasks
            .map(t => t.room?.room_number)
            .filter(Boolean);
          notificationService.notifyNewTasks(newTasks.length, roomNumbers);
        }
      }

      // Always update the known task IDs after every successful fetch
      knownTaskIdsRef.current = new Set(incoming.map(t => t.id));
      setTasks(incoming);
    } catch (error) {
      // Only show alert if it's a manual refresh, not auto-refresh
      if (showLoading) {
        Alert.alert('Error', error.toString());
      }
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  // Load completed tasks function
  const loadCompletedTasks = useCallback(async (showLoading = true) => {
    try {
      if (showLoading && activeTab === 'completed') {
        setLoading(true);
      }
      const response = await taskService.getCompletedTasks();
      // Use grouped_by_date if available, otherwise use data
      setCompletedTasks(response.grouped_by_date || response.data || response || []);
    } catch (error) {
      if (showLoading) {
        Alert.alert('Error', error.toString());
      }
    } finally {
      if (activeTab === 'completed') {
        setLoading(false);
      }
      setRefreshing(false);
    }
  }, [activeTab]);

  // Set up auto-refresh interval
  useEffect(() => {
    // Initial load
    loadTasks();
    loadCompletedTasks(false); // Load completed tasks in background

    // Set up interval for auto-refresh (every 30 seconds)
    intervalRef.current = setInterval(() => {
      // Silently refresh without showing loading indicator
      loadTasks(false);
      if (activeTab === 'completed') {
        loadCompletedTasks(false);
      }
    }, REFRESH_INTERVAL);

    // Cleanup interval on unmount
    return () => {
      if (intervalRef.current) {
        clearInterval(intervalRef.current);
      }
    };
  }, [loadTasks, loadCompletedTasks, activeTab]);

  // Refresh when screen comes into focus
  useFocusEffect(
    useCallback(() => {
      // Refresh tasks when screen is focused (silently, no loading indicator)
      loadTasks(false);
      if (activeTab === 'completed') {
        loadCompletedTasks(false);
      }
    }, [loadTasks, loadCompletedTasks, activeTab])
  );

  // Load completed tasks when switching to completed tab
  useEffect(() => {
    if (activeTab === 'completed' && completedTasks.length === 0) {
      loadCompletedTasks(true);
    }
  }, [activeTab, completedTasks.length, loadCompletedTasks]);

  const onRefresh = () => {
    setRefreshing(true);
    if (activeTab === 'active') {
      loadTasks(true);
    } else {
      loadCompletedTasks(true);
    }
  };

  const handleStartTask = async (taskId) => {
    Alert.alert(
      'Start Task',
      'Do you want to start this task?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Start',
          onPress: async () => {
            try {
              await taskService.startTask(taskId);
              Alert.alert('Success', 'Task started');
              loadTasks();
            } catch (error) {
              Alert.alert('Error', error.toString());
            }
          },
        },
      ]
    );
  };

  const handleCompleteTask = (task) => {
    navigation.navigate('CompleteTask', { task });
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'pending':
        return Colors.yellow;
      case 'in_progress':
        return Colors.skyBlue;
      case 'completed':
        return Colors.success;
      default:
        return Colors.gray;
    }
  };

  const getStatusText = (status) => {
    return status.replace('_', ' ').toUpperCase();
  };

  const getTaskTypeText = (type) => {
    const types = {
      checkout: 'Checkout Cleaning',
      cleaning: 'Cleaning',
      stayover: 'Stayover Service',
      deep_clean: 'Deep Clean',
      inspection: 'Inspection',
    };
    return types[type] || type;
  };

  const getPriorityColor = (priority) => {
    switch (priority) {
      case 'high':   return '#ef4444';
      case 'medium': return '#f59e0b';
      case 'low':    return '#22c55e';
      default:       return Colors.gray;
    }
  };

  const renderTask = ({ item }) => (
    <View style={[styles.taskCard, item.is_unassigned && styles.unassignedTaskCard]}>
      <View style={styles.taskHeader}>
        <Text style={styles.roomNumber}>
          Room {item.room?.room_number || 'N/A'}
        </Text>
        <View style={styles.badgeRow}>
          {item.is_unassigned && (
            <View style={styles.openBadge}>
              <Text style={styles.openBadgeText}>OPEN</Text>
            </View>
          )}
          <View
            style={[
              styles.statusBadge,
              { backgroundColor: getStatusColor(item.status) },
            ]}
          >
            <Text style={styles.statusText}>{getStatusText(item.status)}</Text>
          </View>
        </View>
      </View>

      <Text style={styles.taskType}>{getTaskTypeText(item.task_type)}</Text>

      <View style={styles.metaRow}>
        <Text style={[styles.priority, { color: getPriorityColor(item.priority) }]}>
          ● {item.priority?.toUpperCase() || 'NORMAL'} PRIORITY
        </Text>
        {item.room?.housekeeping_status && (
          <Text style={styles.hkStatus}>
            🧹 {item.room.housekeeping_status.replace('_', ' ').toUpperCase()}
          </Text>
        )}
      </View>

      {item.scheduled_time && (
        <Text style={styles.date}>⏰ Scheduled: {item.scheduled_time}</Text>
      )}

      {item.instructions && (
        <Text style={styles.instructions}>{item.instructions}</Text>
      )}

      <View style={styles.actions}>
        {item.status === 'pending' && (
          <TouchableOpacity
            style={[styles.button, styles.startButton]}
            onPress={() => handleStartTask(item.id)}
          >
            <Text style={styles.buttonText}>Start Task</Text>
          </TouchableOpacity>
        )}

        {item.status === 'in_progress' && (
          <TouchableOpacity
            style={[styles.button, styles.completeButton]}
            onPress={() => handleCompleteTask(item)}
          >
            <Text style={styles.buttonText}>Mark Complete</Text>
          </TouchableOpacity>
        )}
      </View>
    </View>
  );

  const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    
    if (date.toDateString() === today.toDateString()) {
      return 'Today';
    } else if (date.toDateString() === yesterday.toDateString()) {
      return 'Yesterday';
    } else {
      return date.toLocaleDateString('en-US', { 
        weekday: 'short', 
        month: 'short', 
        day: 'numeric',
        year: date.getFullYear() !== today.getFullYear() ? 'numeric' : undefined
      });
    }
  };

  const renderCompletedTask = ({ item }) => {
    // If item has a 'date' property, it's a grouped date section
    if (item.date) {
      return (
        <View style={styles.dateSection}>
          <View style={styles.dateHeader}>
            <Text style={styles.dateText}>{formatDate(item.date)}</Text>
            <Text style={styles.dateCount}>{item.count} room{item.count !== 1 ? 's' : ''}</Text>
          </View>
          {item.tasks && item.tasks.map((task) => (
            <View key={task.id} style={styles.compactTaskCard}>
              <View style={styles.compactTaskRow}>
                <Text style={styles.compactRoomNumber}>
                  Room {task.room?.room_number || 'N/A'}
                </Text>
                <Text style={styles.compactTime}>
                  {task.completed_time || new Date(task.completed_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}
                </Text>
              </View>
              <Text style={styles.compactTaskType}>{getTaskTypeText(task.task_type)}</Text>
            </View>
          ))}
        </View>
      );
    }
    
    // Fallback for non-grouped format (backward compatibility)
    return (
      <View style={[styles.taskCard, styles.completedTaskCard]}>
        <View style={styles.taskHeader}>
          <Text style={styles.roomNumber}>
            Room {item.room?.room_number || 'N/A'}
          </Text>
          <View
            style={[
              styles.statusBadge,
              { backgroundColor: Colors.success },
            ]}
          >
            <Text style={styles.statusText}>COMPLETED</Text>
          </View>
        </View>

        <Text style={styles.taskType}>{getTaskTypeText(item.task_type)}</Text>
        <Text style={styles.priority}>
          Priority: {item.priority?.toUpperCase() || 'NORMAL'}
        </Text>

        {item.completed_at && (
          <Text style={styles.date}>
            Completed: {new Date(item.completed_at).toLocaleString()}
          </Text>
        )}

        {item.completion_notes && (
          <Text style={styles.instructions}>
            Notes: {item.completion_notes}
          </Text>
        )}
      </View>
    );
  };

  if (loading && !refreshing) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={Colors.skyBlue} />
        <Text style={styles.loadingText}>Loading tasks...</Text>
      </View>
    );
  }

  const currentTasks = activeTab === 'active' ? tasks : completedTasks;

  return (
    <View style={styles.container}>
      {/* Tab Selector */}
      <View style={styles.tabContainer}>
        <TouchableOpacity
          style={[
            styles.tab,
            activeTab === 'active' && styles.activeTab,
          ]}
          onPress={() => setActiveTab('active')}
        >
          <Text
            style={[
              styles.tabText,
              activeTab === 'active' && styles.activeTabText,
            ]}
          >
            Active Tasks
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[
            styles.tab,
            activeTab === 'completed' && styles.activeTab,
          ]}
          onPress={() => setActiveTab('completed')}
        >
          <Text
            style={[
              styles.tabText,
              activeTab === 'completed' && styles.activeTabText,
            ]}
          >
            Cleaned Rooms
          </Text>
        </TouchableOpacity>
      </View>

      <FlatList
        data={currentTasks}
        renderItem={activeTab === 'active' ? renderTask : renderCompletedTask}
        keyExtractor={(item, index) => {
          // For grouped data, use date as key; for regular items, use id
          if (item.date) {
            return `date-${item.date}-${index}`;
          }
          return item.id ? item.id.toString() : `item-${index}`;
        }}
        contentContainerStyle={styles.listContent}
        refreshControl={
          <RefreshControl 
            refreshing={refreshing} 
            onRefresh={onRefresh}
            tintColor={Colors.skyBlue}
          />
        }
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyText}>
              {activeTab === 'active'
                ? 'No tasks for today'
                : 'No cleaned rooms yet'}
            </Text>
          </View>
        }
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: Colors.background,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 10,
    color: Colors.gray,
  },
  listContent: {
    padding: 15,
  },
  taskCard: {
    backgroundColor: Colors.card,
    borderRadius: 10,
    padding: 15,
    marginBottom: 15,
    shadowColor: Colors.black,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 3,
    borderLeftWidth: 4,
    borderLeftColor: Colors.yellow,
  },
  taskHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  roomNumber: {
    fontSize: 20,
    fontWeight: 'bold',
    color: Colors.textPrimary,
  },
  statusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 15,
  },
  statusText: {
    color: Colors.black,
    fontSize: 12,
    fontWeight: '600',
  },
  taskType: {
    fontSize: 16,
    fontWeight: '600',
    color: Colors.textPrimary,
    marginBottom: 5,
  },
  priority: {
    fontSize: 14,
    color: Colors.gray,
    marginBottom: 5,
  },
  date: {
    fontSize: 14,
    color: Colors.gray,
    marginBottom: 5,
  },
  instructions: {
    fontSize: 14,
    color: Colors.gray,
    marginTop: 10,
    fontStyle: 'italic',
  },
  actions: {
    marginTop: 15,
    flexDirection: 'row',
    gap: 10,
  },
  button: {
    flex: 1,
    padding: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  startButton: {
    backgroundColor: Colors.skyBlue,
  },
  completeButton: {
    backgroundColor: Colors.black,
  },
  buttonText: {
    color: Colors.white,
    fontWeight: '600',
    fontSize: 14,
  },
  emptyContainer: {
    padding: 40,
    alignItems: 'center',
  },
  emptyText: {
    fontSize: 16,
    color: Colors.gray,
  },
  tabContainer: {
    flexDirection: 'row',
    backgroundColor: Colors.card,
    borderBottomWidth: 2,
    borderBottomColor: Colors.border,
  },
  tab: {
    flex: 1,
    paddingVertical: 15,
    alignItems: 'center',
    borderBottomWidth: 3,
    borderBottomColor: 'transparent',
  },
  activeTab: {
    borderBottomColor: Colors.yellow,
  },
  tabText: {
    fontSize: 16,
    fontWeight: '600',
    color: Colors.gray,
  },
  activeTabText: {
    color: Colors.textPrimary,
  },
  completedTaskCard: {
    borderLeftColor: Colors.success,
    opacity: 0.9,
  },
  unassignedTaskCard: {
    borderLeftColor: '#f59e0b',
    backgroundColor: 'rgba(245, 158, 11, 0.12)',
  },
  badgeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  openBadge: {
    backgroundColor: '#f59e0b',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 10,
  },
  openBadgeText: {
    color: Colors.white,
    fontSize: 11,
    fontWeight: '700',
  },
  metaRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 5,
  },
  hkStatus: {
    fontSize: 12,
    color: '#ef4444',
    fontWeight: '600',
  },
  dateSection: {
    marginBottom: 20,
  },
  dateHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 10,
    paddingHorizontal: 15,
    backgroundColor: Colors.card,
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
    marginBottom: 8,
  },
  dateText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: Colors.textPrimary,
  },
  dateCount: {
    fontSize: 14,
    color: Colors.gray,
    fontWeight: '600',
  },
  compactTaskCard: {
    backgroundColor: Colors.card,
    borderRadius: 8,
    padding: 12,
    marginBottom: 8,
    marginHorizontal: 15,
    shadowColor: Colors.black,
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.2,
    shadowRadius: 2,
    elevation: 2,
    borderLeftWidth: 3,
    borderLeftColor: Colors.success,
  },
  compactTaskRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 4,
  },
  compactRoomNumber: {
    fontSize: 16,
    fontWeight: 'bold',
    color: Colors.textPrimary,
  },
  compactTime: {
    fontSize: 13,
    color: Colors.gray,
    fontWeight: '500',
  },
  compactTaskType: {
    fontSize: 13,
    color: Colors.gray,
    marginTop: 2,
  },
});
