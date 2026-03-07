import React, { useState, useEffect } from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Ionicons } from '@expo/vector-icons';
import { ActivityIndicator, View, Text } from 'react-native';

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
        tabBarActiveTintColor: '#FFD700', // Yellow
        tabBarInactiveTintColor: '#6b7280',
        tabBarStyle: {
          backgroundColor: '#000000', // Black
          borderTopColor: '#87CEEB', // Sky Blue
          borderTopWidth: 2,
        },
        headerShown: true,
      })}
    >
      <Tab.Screen
        name="Tasks"
        component={TasksScreen}
        options={{ title: 'My Tasks' }}
      />
      <Tab.Screen
        name="Maintenance"
        component={MaintenanceScreen}
        options={{ title: 'Report Issue' }}
      />
      <Tab.Screen
        name="CheckMaintenance"
        component={CheckMaintenanceScreen}
        options={{ title: 'Check Status' }}
      />
      <Tab.Screen
        name="Profile"
        component={ProfileScreen}
        options={{ title: 'Profile' }}
      />
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
      
      // If no server URL is saved, show config screen
      if (!serverUrl) {
        setInitialRoute('ServerConfig');
        setLoading(false);
        return;
      }

      // Server URL exists, check connection (non-blocking)
      // We check connection but don't block the app if it fails
      // User can still try to login or change server URL from profile
      const connectionCheck = await checkServerConnection();
      
      if (!connectionCheck.connected) {
        console.warn('Server connection check failed:', connectionCheck.error);
        // Still proceed to login - user can change server URL if needed
      }

      // Check authentication status
      const isAuthenticated = await authService.isAuthenticated();
      
      if (!isAuthenticated) {
        setInitialRoute('Login');
      } else {
        setInitialRoute('Main');
      }
    } catch (error) {
      console.error('Error checking initial route:', error);
      // If there's an error but server URL exists, still try login
      const serverUrl = await Storage.getServerUrl();
      if (serverUrl) {
        setInitialRoute('Login');
      } else {
        setInitialRoute('ServerConfig');
      }
    } finally {
      setLoading(false);
    }
  };

  if (loading || !initialRoute) {
    return (
      <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: '#F5F5F5' }}>
        <ActivityIndicator size="large" color="#87CEEB" />
        <Text style={{ marginTop: 10, color: '#666' }}>Loading...</Text>
      </View>
    );
  }

  return (
    <NavigationContainer>
      <Stack.Navigator
        initialRouteName={initialRoute}
        screenOptions={{ headerShown: false }}
      >
        <Stack.Screen name="ServerConfig" component={ServerConfigScreen} />
        <Stack.Screen name="Login" component={LoginScreen} />
        <Stack.Screen name="Main" component={MainTabs} />
        <Stack.Screen
          name="CompleteTask"
          component={CompleteTaskScreen}
          options={{ headerShown: true, title: 'Complete Task' }}
        />
      </Stack.Navigator>
    </NavigationContainer>
  );
}
