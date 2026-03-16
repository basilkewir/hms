import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  Alert,
  ScrollView,
  ActivityIndicator,
  KeyboardAvoidingView,
  Platform,
  Keyboard,
} from 'react-native';
import { taskService } from '../services/taskService';
import { Colors } from '../constants/colors';

export default function CompleteTaskScreen({ route, navigation }) {
  const { task } = route.params;
  const [notes, setNotes] = useState('');
  const [loading, setLoading] = useState(false);
  const [keyboardHeight, setKeyboardHeight] = useState(0);

  useEffect(() => {
    const showSub = Keyboard.addListener(
      Platform.OS === 'ios' ? 'keyboardWillShow' : 'keyboardDidShow',
      (e) => setKeyboardHeight(e.endCoordinates.height)
    );
    const hideSub = Keyboard.addListener(
      Platform.OS === 'ios' ? 'keyboardWillHide' : 'keyboardDidHide',
      () => setKeyboardHeight(0)
    );
    return () => {
      showSub.remove();
      hideSub.remove();
    };
  }, []);

  const handleComplete = async () => {
    if (!notes.trim()) {
      Alert.alert('Required', 'Please add completion notes');
      return;
    }

    setLoading(true);
    try {
      await taskService.completeTask(task.id, notes);
      Alert.alert('Success', 'Task marked as complete', [
        {
          text: 'OK',
          onPress: () => navigation.goBack(),
        },
      ]);
    } catch (error) {
      Alert.alert('Error', error.toString());
    } finally {
      setLoading(false);
    }
  };

  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === 'ios' ? 'padding' : 'padding'}
      style={styles.container}
      keyboardVerticalOffset={Platform.OS === 'ios' ? 88 : 60}
    >
      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={[
          styles.scrollContent,
          { paddingBottom: 40 + keyboardHeight },
        ]}
        keyboardShouldPersistTaps="handled"
        keyboardDismissMode="on-drag"
        showsVerticalScrollIndicator={true}
      >
        <View style={styles.content}>
          <View style={styles.taskInfo}>
            <Text style={styles.label}>Room Number</Text>
            <Text style={styles.value}>
              {task.room?.room_number || 'N/A'}
            </Text>

            <Text style={styles.label}>Task Type</Text>
            <Text style={styles.value}>
              {task.task_type?.replace('_', ' ').toUpperCase() || 'N/A'}
            </Text>

            {(task.task_type === 'check_cleaning' || task.task_type === 'inspection') && (
              <View style={styles.infoBox}>
                <Text style={styles.infoText}>
                  ℹ️ Completing this task will mark the room as clean and available.
                </Text>
              </View>
            )}

            {task.instructions && (
              <>
                <Text style={styles.label}>Instructions</Text>
                <Text style={styles.value}>{task.instructions}</Text>
              </>
            )}
          </View>

          <View style={styles.notesContainer}>
            <Text style={styles.label}>Completion Notes *</Text>
            <Text style={styles.hint}>
              Add notes about the work completed, any issues found, or items that
              need attention.
            </Text>
            <TextInput
              style={styles.textArea}
              placeholder="Enter completion notes..."
              value={notes}
              onChangeText={setNotes}
              multiline
              numberOfLines={6}
              textAlignVertical="top"
              editable={!loading}
              placeholderTextColor={Colors.gray}
            />
          </View>

          <TouchableOpacity
            style={[styles.button, loading && styles.buttonDisabled]}
            onPress={handleComplete}
            disabled={loading}
          >
            {loading ? (
              <ActivityIndicator color={Colors.yellow} />
            ) : (
              <Text style={styles.buttonText}>Mark as Complete</Text>
            )}
          </TouchableOpacity>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: Colors.background,
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    flexGrow: 1,
    paddingBottom: 40,
  },
  content: {
    padding: 20,
  },
  taskInfo: {
    backgroundColor: Colors.card,
    borderRadius: 10,
    padding: 15,
    marginBottom: 20,
    borderLeftWidth: 4,
    borderLeftColor: Colors.yellow,
  },
  label: {
    fontSize: 14,
    fontWeight: '600',
    color: Colors.textPrimary,
    marginTop: 10,
    marginBottom: 5,
  },
  value: {
    fontSize: 16,
    color: Colors.textPrimary,
  },
  infoBox: {
    backgroundColor: Colors.skyBlueLight,
    borderRadius: 8,
    padding: 12,
    marginTop: 10,
    borderLeftWidth: 4,
    borderLeftColor: Colors.skyBlue,
  },
  infoText: {
    fontSize: 13,
    color: Colors.textSecondary,
    fontStyle: 'italic',
  },
  notesContainer: {
    backgroundColor: Colors.card,
    borderRadius: 10,
    padding: 15,
    marginBottom: 20,
  },
  hint: {
    fontSize: 12,
    color: Colors.gray,
    marginBottom: 10,
    fontStyle: 'italic',
  },
  textArea: {
    borderWidth: 2,
    borderColor: Colors.border,
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
    minHeight: 120,
    backgroundColor: Colors.cardAlt,
    color: Colors.textPrimary,
  },
  button: {
    backgroundColor: Colors.black,
    borderRadius: 8,
    padding: 16,
    alignItems: 'center',
  },
  buttonDisabled: {
    backgroundColor: Colors.gray,
  },
  buttonText: {
    color: Colors.yellow,
    fontSize: 16,
    fontWeight: '600',
  },
});
