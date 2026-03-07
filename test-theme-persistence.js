// Test script to verify theme persistence functionality
// This simulates the useTheme composable behavior

console.log('=== Theme Persistence Test ===');

// Test 1: Save theme to localStorage
function testSaveTheme() {
    console.log('\n1. Testing theme save...');
    
    const testTheme = {
        theme_mode: 'dark',
        theme_primary_color: '#FF6B6B',
        theme_secondary_color: '#4ECDC4',
        theme_background_color: '#1a1a1a',
        theme_sidebar_color: '#2d2d2d',
        theme_card_color: '#3a3a3a',
        theme_text_primary: '#ffffff',
        theme_text_secondary: '#cccccc',
        theme_text_tertiary: '#999999',
        theme_border_color: '#555555',
        theme_success_color: '#10B981',
        theme_warning_color: '#F97316',
        theme_danger_color: '#EF4444'
    };
    
    localStorage.setItem('kotel_theme', JSON.stringify(testTheme));
    console.log('✅ Theme saved to localStorage');
    
    // Verify it was saved
    const saved = localStorage.getItem('kotel_theme');
    if (saved) {
        const parsed = JSON.parse(saved);
        console.log('✅ Theme verified in localStorage:', parsed.theme_primary_color);
        return true;
    } else {
        console.log('❌ Failed to save theme to localStorage');
        return false;
    }
}

// Test 2: Load theme from localStorage
function testLoadTheme() {
    console.log('\n2. Testing theme load...');
    
    try {
        const savedTheme = localStorage.getItem('kotel_theme');
        if (savedTheme) {
            const theme = JSON.parse(savedTheme);
            console.log('✅ Theme loaded from localStorage');
            console.log('✅ Primary color:', theme.theme_primary_color);
            console.log('✅ Sidebar color:', theme.theme_sidebar_color);
            return theme;
        } else {
            console.log('❌ No theme found in localStorage');
            return null;
        }
    } catch (error) {
        console.log('❌ Error loading theme:', error.message);
        return null;
    }
}

// Test 3: Apply theme to CSS variables
function testApplyTheme(theme) {
    console.log('\n3. Testing theme application to CSS variables...');
    
    if (!theme) {
        console.log('❌ No theme provided for application');
        return false;
    }
    
    const root = document.documentElement;
    
    // Set CSS custom properties
    root.style.setProperty('--kotel-primary', theme.theme_primary_color);
    root.style.setProperty('--kotel-secondary', theme.theme_secondary_color);
    root.style.setProperty('--kotel-background', theme.theme_background_color);
    root.style.setProperty('--kotel-sidebar', theme.theme_sidebar_color);
    root.style.setProperty('--kotel-card', theme.theme_card_color);
    root.style.setProperty('--kotel-text-primary', theme.theme_text_primary);
    root.style.setProperty('--kotel-text-secondary', theme.theme_text_secondary);
    root.style.setProperty('--kotel-text-tertiary', theme.theme_text_tertiary);
    root.style.setProperty('--kotel-border', theme.theme_border_color);
    root.style.setProperty('--kotel-success', theme.theme_success_color);
    root.style.setProperty('--kotel-warning', theme.theme_warning_color);
    root.style.setProperty('--kotel-danger', theme.theme_danger_color);
    
    console.log('✅ CSS variables set successfully');
    
    // Verify by reading them back
    const computedStyle = getComputedStyle(root);
    const primaryColor = computedStyle.getPropertyValue('--kotel-primary').trim();
    console.log('✅ Verified primary color in CSS:', primaryColor);
    
    return primaryColor === theme.theme_primary_color;
}

// Test 4: Simulate page refresh (clear and reload)
function testPageRefresh() {
    console.log('\n4. Testing page refresh simulation...');
    
    // Save current theme
    const currentTheme = {
        theme_primary_color: '#FF6B6B',
        theme_sidebar_color: '#2d2d2d'
    };
    localStorage.setItem('kotel_theme', JSON.stringify(currentTheme));
    
    // Simulate page refresh by clearing current CSS vars and reloading
    const root = document.documentElement;
    root.style.setProperty('--kotel-primary', '');
    root.style.setProperty('--kotel-sidebar', '');
    
    console.log('🔄 CSS variables cleared (simulating refresh)');
    
    // Now reload from localStorage
    const loadedTheme = testLoadTheme();
    if (loadedTheme) {
        const success = testApplyTheme(loadedTheme);
        if (success) {
            console.log('✅ Theme successfully restored after refresh simulation');
            return true;
        }
    }
    
    console.log('❌ Failed to restore theme after refresh simulation');
    return false;
}

// Test 5: Test API endpoint (if available)
async function testThemeAPI() {
    console.log('\n5. Testing theme API endpoint...');
    
    try {
        const response = await fetch('/api/theme');
        if (response.ok) {
            const theme = await response.json();
            console.log('✅ Theme API endpoint is available');
            console.log('✅ API returned theme:', theme.theme_primary_color || 'default');
            return theme;
        } else {
            console.log('⚠️ Theme API endpoint returned:', response.status);
            return null;
        }
    } catch (error) {
        console.log('⚠️ Theme API endpoint not available:', error.message);
        console.log('ℹ️ This is expected - localStorage will be used instead');
        return null;
    }
}

// Run all tests
async function runAllTests() {
    console.log('Starting theme persistence tests...\n');
    
    const results = {
        saveTest: testSaveTheme(),
        loadTest: testLoadTheme(),
        applyTest: null,
        refreshTest: null,
        apiTest: await testThemeAPI()
    };
    
    if (results.loadTest) {
        results.applyTest = testApplyTheme(results.loadTest);
        results.refreshTest = testPageRefresh();
    }
    
    console.log('\n=== Test Results ===');
    console.log('Save Test:', results.saveTest ? '✅ PASS' : '❌ FAIL');
    console.log('Load Test:', results.loadTest ? '✅ PASS' : '❌ FAIL');
    console.log('Apply Test:', results.applyTest ? '✅ PASS' : '❌ FAIL');
    console.log('Refresh Test:', results.refreshTest ? '✅ PASS' : '❌ FAIL');
    console.log('API Test:', results.apiTest ? '✅ PASS' : '⚠️ NOT AVAILABLE');
    
    const allPassed = results.saveTest && results.loadTest && results.applyTest && results.refreshTest;
    console.log('\nOverall Result:', allPassed ? '✅ ALL TESTS PASSED' : '❌ SOME TESTS FAILED');
    
    return allPassed;
}

// Export for use in browser console
if (typeof window !== 'undefined') {
    window.testThemePersistence = runAllTests;
    console.log('💡 Test function available: run testThemePersistence() in console');
} else {
    // Node.js environment
    runAllTests();
}
