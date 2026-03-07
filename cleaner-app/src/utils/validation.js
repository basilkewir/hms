export const validateServerUrl = (url) => {
  if (!url || url.trim() === '') {
    return 'Server URL is required';
  }
  
  // Remove trailing slash
  const cleanUrl = url.trim().replace(/\/+$/, '');
  
  // Basic URL validation
  try {
    const urlObj = new URL(cleanUrl);
    if (!['http:', 'https:'].includes(urlObj.protocol)) {
      return 'URL must start with http:// or https://';
    }
    return null;
  } catch (error) {
    return 'Invalid URL format';
  }
};

export const formatServerUrl = (url) => {
  if (!url) return '';
  return url.trim().replace(/\/+$/, '');
};
