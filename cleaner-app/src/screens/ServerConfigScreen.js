import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  Alert,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
} from 'react-native';
import { validateServerUrl, formatServerUrl } from '../utils/validation';
import { testServerUrl } from '../utils/connection';
import { Storage } from '../services/storage';
import { Colors } from '../constants/colors';

export default function ServerConfigScreen({ navigation }) {
  const [serverUrl, setServerUrl] = useState('');
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    loadServerUrl();
  }, []);

  const loadServerUrl = async () => {
    const url = await Storage.getServerUrl();
    if (url) {
      setServerUrl(url);
    }
  };

  const handleSave = async () => {
    const error = validateServerUrl(serverUrl);
    if (error) {
      Alert.alert('Validation Error', error);
      return;
    }

    setLoading(true);
    try {
      const formattedUrl = formatServerUrl(serverUrl);
      
      const connectionTest = await testServerUrl(formattedUrl);
      
      if (!connectionTest.success) {
        Alert.alert(
          'Connection Warning',
          `Could not connect to server: ${connectionTest.error}\n\nThe URL will be saved, but you may need to check your connection later.`,
          [
            { text: 'Cancel', style: 'cancel', onPress: () => setLoading(false) },
            { text: 'Save Anyway', onPress: () => saveUrl(formattedUrl) },
          ]
        );
        return;
      }
      
      await saveUrl(formattedUrl);
    } catch (error) {
      Alert.alert('Error', 'Failed to save server URL: ' + error.message);
      setLoading(false);
    }
  };

  const saveUrl = async (formattedUrl) => {
    try {
      await Storage.setServerUrl(formattedUrl);
      
      Alert.alert('Success', 'Server URL saved successfully', [
        {
          text: 'OK',
          onPress: () => navigation.replace('Login'),
        },
      ]);
    } catch (error) {
      Alert.alert('Error', 'Failed to save server URL');
      setLoading(false);
    }
  };

  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      style={styles.container}
    >
      <ScrollView contentContainerStyle={styles.scrollContent}>
        <View style={styles.content}>
          <Text style={styles.title}>Server Configuration</Text>
          <Text style={styles.subtitle}>
            Enter the server URL where the hotel management system is running.
            {'\n\n'}Example: http://192.168.1.100:8000
          </Text>

          <View style={styles.inputContainer}>
            <Text style={styles.label}>Server URL</Text>
            <TextInput
              style={styles.input}
              placeholder="http://192.168.1.100:8000"
              value={serverUrl}
              onChangeText={setServerUrl}
              autoCapitalize="none"
              autoCorrect={false}
              keyboardType="url"
              editable={!loading}
              placeholderTextColor={Colors.gray}
            />
          </View>

          <TouchableOpacity
            style={[styles.button, loading && styles.buttonDisabled]}
            onPress={handleSave}
            disabled={loading}
          >
            <Text style={styles.buttonText}>
              {loading ? 'Saving...' : 'Save & Continue'}
            </Text>
          </TouchableOpacity>

          <Text style={styles.hint}>
            💡 Tip: Ask your IT administrator for the correct server IP address
          </Text>
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
  scrollContent: {
    flexGrow: 1,
    justifyContent: 'center',
    padding: 20,
  },
  content: {
    backgroundColor: Colors.white,
    borderRadius: 10,
    padding: 20,
    shadowColor: Colors.black,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: Colors.black,
    marginBottom: 10,
    textAlign: 'center',
  },
  subtitle: {
    fontSize: 14,
    color: Colors.gray,
    marginBottom: 30,
    textAlign: 'center',
    lineHeight: 20,
  },
  inputContainer: {
    marginBottom: 20,
  },
  label: {
    fontSize: 16,
    fontWeight: '600',
    color: Colors.black,
    marginBottom: 8,
  },
  input: {
    borderWidth: 2,
    borderColor: Colors.skyBlue,
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
    backgroundColor: Colors.white,
    color: Colors.black,
  },
  button: {
    backgroundColor: Colors.black,
    borderRadius: 8,
    padding: 16,
    alignItems: 'center',
    marginTop: 10,
  },
  buttonDisabled: {
    backgroundColor: Colors.gray,
  },
  buttonText: {
    color: Colors.yellow,
    fontSize: 16,
    fontWeight: '600',
  },
  hint: {
    marginTop: 20,
    fontSize: 12,
    color: Colors.gray,
    textAlign: 'center',
    fontStyle: 'italic',
  },
});
