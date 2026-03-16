import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  Alert,
  ScrollView,
  ActivityIndicator,
  Image,
  FlatList,
} from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import { maintenanceService } from '../services/maintenanceService';
import { Colors } from '../constants/colors';

export default function MaintenanceScreen({ navigation, route }) {
  const roomId = route?.params?.roomId;
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [category, setCategory] = useState('other');
  const [priority, setPriority] = useState('normal');
  const [loading, setLoading] = useState(false);
  const [images, setImages] = useState([]);

  const categories = [
    { label: 'Plumbing', value: 'plumbing' },
    { label: 'Electrical', value: 'electrical' },
    { label: 'HVAC', value: 'hvac' },
    { label: 'Furniture', value: 'furniture' },
    { label: 'Appliances', value: 'appliances' },
    { label: 'Security', value: 'security' },
    { label: 'IT', value: 'it' },
    { label: 'Other', value: 'other' },
  ];

  const priorities = [
    { label: 'Low', value: 'low' },
    { label: 'Normal', value: 'normal' },
    { label: 'High', value: 'high' },
    { label: 'Urgent', value: 'urgent' },
  ];

  const requestImagePermission = async () => {
    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert(
        'Permission Required',
        'We need permission to access your photos to attach images to maintenance requests.'
      );
      return false;
    }
    return true;
  };

  const pickImage = async () => {
    const hasPermission = await requestImagePermission();
    if (!hasPermission) return;

    try {
      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ImagePicker.MediaTypeOptions.Images,
        allowsMultipleSelection: true,
        quality: 0.8,
        allowsEditing: false,
      });

      if (!result.canceled && result.assets) {
        const newImages = result.assets.map((asset) => ({
          uri: asset.uri,
          type: 'image/jpeg',
          name: `photo_${Date.now()}_${Math.random().toString(36).substr(2, 9)}.jpg`,
        }));
        setImages([...images, ...newImages]);
      }
    } catch (error) {
      Alert.alert('Error', 'Failed to pick image: ' + error.message);
    }
  };

  const takePhoto = async () => {
    const { status } = await ImagePicker.requestCameraPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert(
        'Permission Required',
        'We need permission to access your camera to take photos.'
      );
      return;
    }

    try {
      const result = await ImagePicker.launchCameraAsync({
        mediaTypes: ImagePicker.MediaTypeOptions.Images,
        quality: 0.8,
        allowsEditing: false,
      });

      if (!result.canceled && result.assets && result.assets.length > 0) {
        const asset = result.assets[0];
        const newImage = {
          uri: asset.uri,
          type: 'image/jpeg',
          name: `photo_${Date.now()}_${Math.random().toString(36).substr(2, 9)}.jpg`,
        };
        setImages([...images, newImage]);
      }
    } catch (error) {
      Alert.alert('Error', 'Failed to take photo: ' + error.message);
    }
  };

  const removeImage = (index) => {
    const newImages = images.filter((_, i) => i !== index);
    setImages(newImages);
  };

  const handleSubmit = async () => {
    if (!title.trim() || !description.trim()) {
      Alert.alert('Required', 'Please fill in all required fields');
      return;
    }

    setLoading(true);
    try {
      // Create FormData for multipart/form-data request
      const formData = new FormData();
      
      formData.append('room_id', roomId || '');
      formData.append('title', title.trim());
      formData.append('description', description.trim());
      formData.append('category', category);
      formData.append('priority', priority);

      // Append images
      images.forEach((image, index) => {
        formData.append(`photos[${index}]`, {
          uri: image.uri,
          type: image.type,
          name: image.name,
        });
      });

      await maintenanceService.createMaintenanceRequest(formData);

      Alert.alert('Success', 'Maintenance request submitted', [
        {
          text: 'OK',
          onPress: () => {
            setTitle('');
            setDescription('');
            setCategory('other');
            setPriority('normal');
            setImages([]);
            if (navigation.canGoBack()) {
              navigation.goBack();
            }
          },
        },
      ]);
    } catch (error) {
      Alert.alert('Error', error.toString());
    } finally {
      setLoading(false);
    }
  };

  const renderImage = ({ item, index }) => (
    <View style={styles.imageContainer}>
      <Image source={{ uri: item.uri }} style={styles.imagePreview} />
      <TouchableOpacity
        style={styles.removeImageButton}
        onPress={() => removeImage(index)}
      >
        <Text style={styles.removeImageText}>×</Text>
      </TouchableOpacity>
    </View>
  );

  return (
    <ScrollView style={styles.container}>
      <View style={styles.content}>
        <Text style={styles.title}>Report Maintenance Issue</Text>

        <View style={styles.inputContainer}>
          <Text style={styles.label}>Title *</Text>
          <TextInput
            style={styles.input}
            placeholder="Brief description of the issue"
            value={title}
            onChangeText={setTitle}
            editable={!loading}
            placeholderTextColor={Colors.gray}
          />
        </View>

        <View style={styles.inputContainer}>
          <Text style={styles.label}>Description *</Text>
          <TextInput
            style={styles.textArea}
            placeholder="Detailed description of the maintenance issue..."
            value={description}
            onChangeText={setDescription}
            multiline
            numberOfLines={5}
            textAlignVertical="top"
            editable={!loading}
            placeholderTextColor={Colors.gray}
          />
        </View>

        <View style={styles.inputContainer}>
          <Text style={styles.label}>Attach Photos</Text>
          <View style={styles.imageButtonsContainer}>
            <TouchableOpacity
              style={[styles.imageButton, styles.pickImageButton]}
              onPress={pickImage}
              disabled={loading}
            >
              <Text style={styles.imageButtonText}>📷 Choose from Gallery</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.imageButton, styles.takePhotoButton]}
              onPress={takePhoto}
              disabled={loading}
            >
              <Text style={styles.imageButtonText}>📸 Take Photo</Text>
            </TouchableOpacity>
          </View>
          
          {images.length > 0 && (
            <View style={styles.imagesContainer}>
              <FlatList
                data={images}
                renderItem={renderImage}
                keyExtractor={(item, index) => index.toString()}
                horizontal
                showsHorizontalScrollIndicator={false}
              />
            </View>
          )}
        </View>

        <View style={styles.inputContainer}>
          <Text style={styles.label}>Category</Text>
          <View style={styles.optionsContainer}>
            {categories.map((cat) => (
              <TouchableOpacity
                key={cat.value}
                style={[
                  styles.option,
                  category === cat.value && styles.optionSelected,
                ]}
                onPress={() => setCategory(cat.value)}
                disabled={loading}
              >
                <Text
                  style={[
                    styles.optionText,
                    category === cat.value && styles.optionTextSelected,
                  ]}
                >
                  {cat.label}
                </Text>
              </TouchableOpacity>
            ))}
          </View>
        </View>

        <View style={styles.inputContainer}>
          <Text style={styles.label}>Priority</Text>
          <View style={styles.optionsContainer}>
            {priorities.map((pri) => (
              <TouchableOpacity
                key={pri.value}
                style={[
                  styles.option,
                  priority === pri.value && styles.optionSelected,
                ]}
                onPress={() => setPriority(pri.value)}
                disabled={loading}
              >
                <Text
                  style={[
                    styles.optionText,
                    priority === pri.value && styles.optionTextSelected,
                  ]}
                >
                  {pri.label}
                </Text>
              </TouchableOpacity>
            ))}
          </View>
        </View>

        <TouchableOpacity
          style={[styles.button, loading && styles.buttonDisabled]}
          onPress={handleSubmit}
          disabled={loading}
        >
          {loading ? (
            <ActivityIndicator color={Colors.white} />
          ) : (
            <Text style={styles.buttonText}>Submit Request</Text>
          )}
        </TouchableOpacity>
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: Colors.background,
  },
  content: {
    padding: 20,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: Colors.textPrimary,
    marginBottom: 20,
  },
  inputContainer: {
    marginBottom: 20,
  },
  label: {
    fontSize: 16,
    fontWeight: '600',
    color: Colors.textPrimary,
    marginBottom: 8,
  },
  input: {
    borderWidth: 1,
    borderColor: Colors.border,
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
    backgroundColor: Colors.cardAlt,
    color: Colors.textPrimary,
  },
  textArea: {
    borderWidth: 1,
    borderColor: Colors.border,
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
    minHeight: 120,
    backgroundColor: Colors.cardAlt,
    textAlignVertical: 'top',
    color: Colors.textPrimary,
  },
  imageButtonsContainer: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: 15,
  },
  imageButton: {
    flex: 1,
    padding: 12,
    borderRadius: 8,
    alignItems: 'center',
    borderWidth: 2,
  },
  pickImageButton: {
    backgroundColor: Colors.skyBlue,
    borderColor: Colors.skyBlueDark,
  },
  takePhotoButton: {
    backgroundColor: Colors.yellow,
    borderColor: Colors.yellowDark,
  },
  imageButtonText: {
    color: Colors.black,
    fontSize: 14,
    fontWeight: '600',
  },
  imagesContainer: {
    marginTop: 10,
  },
  imageContainer: {
    position: 'relative',
    marginRight: 10,
  },
  imagePreview: {
    width: 100,
    height: 100,
    borderRadius: 8,
    backgroundColor: Colors.grayLight,
  },
  removeImageButton: {
    position: 'absolute',
    top: -5,
    right: -5,
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: Colors.error,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: Colors.white,
  },
  removeImageText: {
    color: Colors.white,
    fontSize: 18,
    fontWeight: 'bold',
    lineHeight: 20,
  },
  optionsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
  },
  option: {
    paddingHorizontal: 15,
    paddingVertical: 10,
    borderRadius: 20,
    borderWidth: 2,
    borderColor: Colors.border,
    backgroundColor: Colors.card,
  },
  optionSelected: {
    backgroundColor: Colors.yellow,
    borderColor: Colors.yellowDark,
  },
  optionText: {
    fontSize: 14,
    color: Colors.textSecondary,
    fontWeight: '500',
  },
  optionTextSelected: {
    color: Colors.black,
    fontWeight: '600',
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
});
