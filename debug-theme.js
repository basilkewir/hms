// Debug script to check theme persistence issues
console.log('=== Theme Debug Script ===');

// Check what's in localStorage
console.log('1. Checking localStorage...');
const kotelTheme = localStorage.getItem('kotel_theme');
const regularTheme = localStorage.getItem('theme');

console.log('kotel_theme:', kotelTheme);
console.log('theme:', regularTheme);

// Check CSS custom properties
console.log('\n2. Checking CSS custom properties...');
const root = document.documentElement;
const computedStyle = getComputedStyle(root);

const cssVars = {
    '--kotel-primary': computedStyle.getPropertyValue('--kotel-primary').trim(),
    '--kotel-secondary': computedStyle.getPropertyValue('--kotel-secondary').trim(),
    '--kotel-background': computedStyle.getPropertyValue('--kotel-background').trim(),
    '--kotel-sidebar': computedStyle.getPropertyValue('--kotel-sidebar').trim(),
    '--kotel-card': computedStyle.getPropertyValue('--kotel-card').trim(),
    '--kotel-text-primary': computedStyle.getPropertyValue('--kotel-text-primary').trim(),
    '--kotel-text-secondary': computedStyle.getPropertyValue('--kotel-text-secondary').trim(),
    '--kotel-text-tertiary': computedStyle.getPropertyValue('--kotel-text-tertiary').trim(),
    '--kotel-border': computedStyle.getPropertyValue('--kotel-border').trim(),
    '--kotel-success': computedStyle.getPropertyValue('--kotel-success').trim(),
    '--kotel-warning': computedStyle.getPropertyValue('--kotel-warning').trim(),
    '--kotel-danger': computedStyle.getPropertyValue('--kotel-danger').trim()
};

console.log('CSS Variables:', cssVars);

// Check if border color is specifically working
console.log('\n3. Border color debug...');
console.log('Border color from CSS:', cssVars['--kotel-border']);
console.log('Border color from localStorage:', kotelTheme ? JSON.parse(kotelTheme).theme_border_color : 'Not found');

// Test setting border color manually
console.log('\n4. Testing manual border color set...');
root.style.setProperty('--kotel-border', '#ff0000'); // Red for testing
setTimeout(() => {
    const newBorderColor = getComputedStyle(root).getPropertyValue('--kotel-border').trim();
    console.log('Manual set border color:', newBorderColor);
    
    // Restore original
    if (kotelTheme) {
        const theme = JSON.parse(kotelTheme);
        root.style.setProperty('--kotel-border', theme.theme_border_color || '#374151');
        console.log('Restored border color:', getComputedStyle(root).getPropertyValue('--kotel-border').trim());
    }
}, 1000);

// Check if useTheme is available
console.log('\n5. Checking useTheme availability...');
if (typeof window !== 'undefined' && window.Vue && window.Vue.createApp) {
    console.log('Vue is available');
} else {
    console.log('Vue not directly accessible');
}

// Check for any errors in theme loading
console.log('\n6. Theme loading simulation...');
try {
    if (kotelTheme) {
        const theme = JSON.parse(kotelTheme);
        console.log('Parsed theme from localStorage:', theme);
        
        // Apply theme manually to test
        Object.keys(theme).forEach(key => {
            const cssVar = `--kotel-${key.replace('theme_', '')}`;
            const value = theme[key];
            if (value) {
                root.style.setProperty(cssVar, value);
                console.log(`Set ${cssVar} to ${value}`);
            }
        });
        
        console.log('Theme applied manually');
    } else {
        console.log('No kotel_theme found in localStorage');
    }
} catch (error) {
    console.error('Error in theme loading simulation:', error);
}

console.log('\n=== Debug Complete ===');

// Function to fix theme if needed
window.fixTheme = function() {
    console.log('Attempting to fix theme...');
    
    // Default theme
    const defaultTheme = {
        theme_primary_color: '#facc15',
        theme_secondary_color: '#3b82f6',
        theme_background_color: '#0b0b0b',
        theme_sidebar_color: '#0f172a',
        theme_card_color: '#111827',
        theme_text_primary: '#f3f4f6',
        theme_text_secondary: '#9ca3af',
        theme_text_tertiary: '#6b7280',
        theme_border_color: '#374151',
        theme_success_color: '#22c55e',
        theme_warning_color: '#f59e0b',
        theme_danger_color: '#ef4444'
    };
    
    // Save to localStorage
    localStorage.setItem('kotel_theme', JSON.stringify(defaultTheme));
    
    // Apply to CSS
    const root = document.documentElement;
    Object.keys(defaultTheme).forEach(key => {
        const cssVar = `--kotel-${key.replace('theme_', '')}`;
        const value = defaultTheme[key];
        root.style.setProperty(cssVar, value);
    });
    
    console.log('Theme fixed and applied');
    return defaultTheme;
};

console.log('💡 Run fixTheme() in console to apply default theme');
