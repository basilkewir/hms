import React, { useState, useEffect } from 'react';
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
import { maintenanceService } from '../services/maintenanceService';
import { Colors } from '../constants/colors';

export default function CheckMaintenanceScreen({ route }) {
  const { roomId } = route.params || {};
  const [requests, setRequests] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    loadRequests();
  }, [roomId]);

  const loadRequests = async () => {
    try {
      setLoading(true);
      const response = await maintenanceService.getMaintenanceRequests(roomId);
      setRequests(response.data || response || []);
    } catch (error) {
      Alert.alert('Error', error.toString());
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadRequests();
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'open':
        return Colors.yellow;
      case 'assigned':
        return Colors.skyBlue;
      case 'in_progress':
        return Colors.skyBlueDark;
      case 'resolved':
        return Colors.success;
      case 'closed':
        return Colors.gray;
      default:
        return Colors.gray;
    }
  };

  const getPriorityColor = (priority) => {
    switch (priority) {
      case 'urgent':
        return Colors.error;
      case 'high':
        return Colors.warning;
      case 'normal':
        return Colors.skyBlue;
      case 'low':
        return Colors.gray;
      default:
        return Colors.gray;
    }
  };

  const renderRequest = ({ item }) => (
    <View style={styles.requestCard}>
      <View style={styles.requestHeader}>
        <Text style={styles.requestNumber}>{item.request_number}</Text>
        <View
          style={[
            styles.statusBadge,
            { backgroundColor: getStatusColor(item.status) },
          ]}
        >
          <Text style={styles.statusText}>
            {item.status?.replace('_', ' ').toUpperCase()}
          </Text>
        </View>
      </View>

      <Text style={styles.title}>{item.title}</Text>
      <Text style={styles.description}>{item.description}</Text>

      <View style={styles.metaContainer}>
        <View
          style={[
            styles.priorityBadge,
            { backgroundColor: getPriorityColor(item.priority) },
          ]}
        >
          <Text style={styles.priorityText}>
            {item.priority?.toUpperCase()}
          </Text>
        </View>
        <Text style={styles.category}>
          {item.category?.toUpperCase()}
        </Text>
      </View>

      {item.room && (
        <Text style={styles.room}>Room: {item.room.room_number}</Text>
      )}

      {item.assigned_to && (
        <Text style={styles.assigned}>
          Assigned to: {item.assigned_to.name}
        </Text>
      )}

      {item.resolved_at && (
        <Text style={styles.resolved}>
          Resolved: {new Date(item.resolved_at).toLocaleString()}
        </Text>
      )}

      {item.resolution_notes && (
        <View style={styles.notesContainer}>
          <Text style={styles.notesLabel}>Resolution Notes:</Text>
          <Text style={styles.notes}>{item.resolution_notes}</Text>
        </View>
      )}
    </View>
  );

  if (loading && !refreshing) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={Colors.skyBlue} />
        <Text style={styles.loadingText}>Loading maintenance requests...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <FlatList
        data={requests}
        renderItem={renderRequest}
        keyExtractor={(item) => item.id.toString()}
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
            <Text style={styles.emptyText}>No maintenance requests found</Text>
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
  requestCard: {
    backgroundColor: Colors.white,
    borderRadius: 10,
    padding: 15,
    marginBottom: 15,
    shadowColor: Colors.black,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    borderLeftWidth: 4,
    borderLeftColor: Colors.yellow,
  },
  requestHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  requestNumber: {
    fontSize: 14,
    fontWeight: '600',
    color: Colors.gray,
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
  title: {
    fontSize: 18,
    fontWeight: 'bold',
    color: Colors.black,
    marginBottom: 8,
  },
  description: {
    fontSize: 14,
    color: Colors.gray,
    marginBottom: 10,
  },
  metaContainer: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: 10,
  },
  priorityBadge: {
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 15,
  },
  priorityText: {
    color: Colors.white,
    fontSize: 12,
    fontWeight: '600',
  },
  category: {
    fontSize: 12,
    color: Colors.gray,
    paddingVertical: 5,
  },
  room: {
    fontSize: 14,
    color: Colors.black,
    marginBottom: 5,
  },
  assigned: {
    fontSize: 14,
    color: Colors.gray,
    marginBottom: 5,
  },
  resolved: {
    fontSize: 12,
    color: Colors.success,
    marginTop: 5,
    fontWeight: '600',
  },
  notesContainer: {
    marginTop: 10,
    padding: 10,
    backgroundColor: Colors.skyBlueLight,
    borderRadius: 8,
  },
  notesLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: Colors.black,
    marginBottom: 5,
  },
  notes: {
    fontSize: 14,
    color: Colors.gray,
  },
  emptyContainer: {
    padding: 40,
    alignItems: 'center',
  },
  emptyText: {
    fontSize: 16,
    color: Colors.gray,
  },
});
