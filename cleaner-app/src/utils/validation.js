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

    if (!urlObj.hostname) {
      return 'URL must include a valid host (IP or domain)';
    }

    const host = urlObj.hostname;
    const isNumericIp = /^[0-9.]+$/.test(host);

    if (isNumericIp) {
      const octets = host.split('.');
      if (octets.length !== 4) {
        return 'Invalid IP format. Use 4 parts, e.g. 10.0.0.10';
      }

      for (const octet of octets) {
        if (!/^\d+$/.test(octet)) {
          return 'Invalid IP format. Use numbers only in each part.';
        }

        const value = Number(octet);
        if (value < 0 || value > 255) {
          return 'Invalid IP format. Each part must be between 0 and 255.';
        }
      }
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
