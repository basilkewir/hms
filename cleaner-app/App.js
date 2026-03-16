import React, { useState, useEffect } from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Ionicons } from '@expo/vector-icons';
import { ActivityIndicator, View, Text, Image, StyleSheet } from 'react-native';

import ServerConfigScreen from './src/screens/ServerConfigScreen';
import LoginScreen from './src/screens/LoginScreen';
import TasksScreen from './src/screens/TasksScreen';
import CompleteTaskScreen from './src/screens/CompleteTaskScreen';
import MaintenanceScreen from './src/screens/MaintenanceScreen';
import CheckMaintenanceScreen from './src/screens/CheckMaintenanceScreen';
import ProfileScreen from './src/screens/ProfileScreen';

import { Storage } from './src/services/storage';
import { authService } from './src/services/authService';
import { checkServerConnection } from './src/utils/connection';
import { Colors } from './src/constants/colors';

const Stack = createNativeStackNavigator();
const Tab = createBottomTabNavigator();

function MainTabs() {
  return (
    <Tab.Navigator
      screenOptions={({ route }) => ({
        tabBarIcon: ({ focused, color, size }) => {
          let iconName;
          if (route.name === 'Tasks') {
            iconName = focused ? 'list' : 'list-outline';
          } else if (route.name === 'Maintenance') {
            iconName = focused ? 'construct' : 'construct-outline';
          } else if (route.name === 'CheckMaintenance') {
            iconName = focused ? 'checkmark-circle' : 'checkmark-circle-outline';
          } else if (route.name === 'Profile') {
            iconName = focused ? 'person' : 'person-outline';
          }
          return <Ionicons name={iconName} size={size} color={color} />;
        },
        tabBarActiveTintColor: Colors.accent,
        tabBarInactiveTintColor: Colors.textTertiary,
        tabBarStyle: {
          backgroundColor: Colors.card,
          borderTopColor: Colors.primary,
          borderTopWidth: 2,
          paddingBottom: 4,
          height: 60,
        },
        tabBarLabelStyle: {
          fontSize: 11,
          fontWeight: '600',
        },
        headerStyle: {
          backgroundColor: Colors.card,
          borderBottomColor: Colors.primary,
          borderBottomWidth: 1,
        },
        headerTintColor: Colors.textPrimary,
        headerTitleStyle: { fontWeight: '700', fontSize: 17 },
        headerShown: true,
      })}
    >
      <Tab.Screen name="Tasks" component={TasksScreen} options={{ title: 'My Tasks' }} />
      <Tab.Screen name="Maintenance" component={MaintenanceScreen} options={{ title: 'Report Issue' }} />
      <Tab.Screen name="CheckMaintenance" component={CheckMaintenanceScreen} options={{ title: 'Check Status' }} />
      <Tab.Screen name="Profile" component={ProfileScreen} options={{ title: 'Profile' }} />
    </Tab.Navigator>
  );
}

export default function App() {
  const [initialRoute, setInitialRoute] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    checkInitialRoute();
  }, []);

  const checkInitialRoute = async () => {
    try {
      const serverUrl = await Storage.getServerUrl();
      if (!serverUrl) {
        setInitialRoute('ServerConfig');
        setLoading(false);
        return;
      }
      const connectionCheck = await checkServerConnection();
      if (!connectionCheck.connected) {
        console.warn('Server connection check failed:', connectionCheck.error);
      }
      const isAuthenticated = await authService.isAuthenticated();
      setInitialRoute(isAuthenticated ? 'Main' : 'Login');
    } catch (error) {
      console.error('Error checking initial route:', error);
      const serverUrl = await Storage.getServerUrl();
      setInitialRoute(serverUrl ? 'Login' : 'ServerConfig');
    } finally {
      setLoading(false);
    }
  };

  if (loading || !initialRoute) {
    return (
      <View style={styles.loadingScreen}>
        <Image source={require('./assets/kotelcleaner.png')} style={styles.loadingLogo} resizeMode="contain" />
        <ActivityIndicator size="large" color={Colors.primary} style={{ marginTop: 32 }} />
        <Text style={styles.loadingText}>Loading...</Text>
      </View>
    );
  }

  return (
    <NavigationContainer>
      <Stack.Navigator initialRouteName={initialRoute} screenOptions={{ headerShown: false }}>
        <Stack.Screen name="ServerConfig" component={ServerConfigScreen} />
        <Stack.Screen name="Login" component={LoginScreen} />
        <Stack.Screen name="Main" component={MainTabs} />
        <Stack.Screen
          name="CompleteTask"
          component={CompleteTaskScreen}
          options={{
            headerShown: true,
            title: 'Complete Task',
            headerStyle: { backgroundColor: Colors.card },
            headerTintColor: Colors.textPrimary,
            headerTitleStyle: { fontWeight: '700' },
          }}
        />
      </Stack.Navigator>
    </NavigationContainer>
  );
}

const styles = StyleSheet.create({
  loadingScreen: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: Colors.background,
  },
  loadingLogo: {
    width: 140,
    height: 140,
  },
  loadingText: {
    marginTop: 12,
    color: Colors.textSecondary,
    fontSize: 14,
    fontWeight: '500',
  },
});
