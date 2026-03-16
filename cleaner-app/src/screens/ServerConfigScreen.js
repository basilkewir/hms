import React, { useState, useEffect } from 'react';
import {
  View, Text, TextInput, TouchableOpacity, StyleSheet,
  Alert, KeyboardAvoidingView, Platform, ScrollView, Image,
} from 'react-native';
import { validateServerUrl, formatServerUrl } from '../utils/validation';
import { testServerUrl } from '../utils/connection';
import { Storage } from '../services/storage';
import { Colors } from '../constants/colors';

export default function ServerConfigScreen({ navigation }) {
  const [serverUrl, setServerUrl] = useState('');
  const [loading, setLoading] = useState(false);

  useEffect(() => { loadServerUrl(); }, []);

  const loadServerUrl = async () => {
    const url = await Storage.getServerUrl();
    if (url) setServerUrl(url);
  };

  const handleSave = async () => {
    const error = validateServerUrl(serverUrl);
    if (error) { Alert.alert('Validation Error', error); return; }

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
        { text: 'OK', onPress: () => navigation.replace('Login') },
      ]);
    } catch (error) {
      Alert.alert('Error', 'Failed to save server URL');
      setLoading(false);
    }
  };

  return (
    <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={styles.container}>
      <ScrollView contentContainerStyle={styles.scrollContent}>

        {/* Logo */}
        <View style={styles.logoContainer}>
          <Image source={require('../../assets/kotelcleaner.png')} style={styles.logo} resizeMode="contain" />
          <Text style={styles.appName}>Kotel Cleaner</Text>
        </View>

        {/* Card */}
        <View style={styles.card}>
          <Text style={styles.title}>Server Setup</Text>
          <Text style={styles.subtitle}>
            Enter the URL of your hotel management server.{'\n\n'}
            Example: http://192.168.1.100:8000
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
              placeholderTextColor={Colors.textTertiary}
            />
          </View>

          <TouchableOpacity
            style={[styles.button, loading && styles.buttonDisabled]}
            onPress={handleSave}
            disabled={loading}
          >
            <Text style={styles.buttonText}>{loading ? 'Saving...' : 'Save & Continue'}</Text>
          </TouchableOpacity>

          <View style={styles.hintBox}>
            <Text style={styles.hintText}>
              💡 Ask your IT administrator for the correct server IP address
            </Text>
          </View>
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
    padding: 24,
  },
  logoContainer: {
    alignItems: 'center',
    marginBottom: 32,
  },
  logo: {
    width: 90,
    height: 90,
    marginBottom: 12,
  },
  appName: {
    fontSize: 24,
    fontWeight: '800',
    color: Colors.textPrimary,
    letterSpacing: 0.5,
  },
  card: {
    backgroundColor: Colors.card,
    borderRadius: 16,
    padding: 24,
    borderWidth: 1,
    borderColor: Colors.info,
    shadowColor: Colors.info,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.25,
    shadowRadius: 12,
    elevation: 8,
  },
  title: {
    fontSize: 22,
    fontWeight: '700',
    color: Colors.textPrimary,
    marginBottom: 6,
    textAlign: 'center',
  },
  subtitle: {
    fontSize: 13,
    color: Colors.textSecondary,
    marginBottom: 28,
    textAlign: 'center',
    lineHeight: 20,
  },
  inputContainer: {
    marginBottom: 20,
  },
  label: {
    fontSize: 13,
    fontWeight: '600',
    color: Colors.textSecondary,
    marginBottom: 6,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  input: {
    borderWidth: 1.5,
    borderColor: Colors.border,
    borderRadius: 10,
    padding: 13,
    fontSize: 15,
    backgroundColor: Colors.cardAlt,
    color: Colors.textPrimary,
  },
  button: {
    backgroundColor: Colors.info,
    borderRadius: 10,
    padding: 15,
    alignItems: 'center',
    shadowColor: Colors.info,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.4,
    shadowRadius: 8,
    elevation: 6,
  },
  buttonDisabled: {
    backgroundColor: Colors.grayLight,
    shadowOpacity: 0,
  },
  buttonText: {
    color: Colors.white,
    fontSize: 16,
    fontWeight: '700',
    letterSpacing: 0.5,
  },
  hintBox: {
    marginTop: 20,
    backgroundColor: Colors.infoLight,
    borderRadius: 8,
    padding: 12,
    borderWidth: 1,
    borderColor: Colors.info,
  },
  hintText: {
    fontSize: 12,
    color: Colors.info,
    textAlign: 'center',
    lineHeight: 18,
  },
});
