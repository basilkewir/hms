// Quick Theme Test Script
// Run this in browser console on admin settings page

console.log('🧪 Quick Theme Test Started');

// Test 1: Check current state
console.log('\n=== Current State ===');
const currentLocalStorage = localStorage.getItem('kotel_theme');
const currentCSS = getComputedStyle(document.documentElement).getPropertyValue('--kotel-border').trim();

console.log('Current localStorage:', currentLocalStorage ? 'EXISTS' : 'EMPTY');
console.log('Current CSS border color:', currentCSS);

// Test 2: Manually save a test theme
console.log('\n=== Manual Test Save ===');
const testTheme = {
    theme_mode: 'dark',
    theme_primary_color: '#ff0000',  // Red primary for testing
    theme_secondary_color: '#00ff00', // Green secondary
    theme_background_color: '#000000',
    theme_sidebar_color: '#1a1a1a',
    theme_card_color: '#111827',
    theme_text_primary: '#ffffff',
    theme_text_secondary: '#cccccc',
    theme_text_tertiary: '#999999',
    theme_border_color: '#0000ff', // Blue border for testing
    theme_success_color: '#22c55e',
    theme_warning_color: '#f59e0b',
    theme_danger_color: '#ef4444'
};

console.log('Saving test theme with border color:', testTheme.theme_border_color);

// Save to localStorage
localStorage.setItem('kotel_theme', JSON.stringify(testTheme));

// Apply to CSS
const root = document.documentElement;
Object.keys(testTheme).forEach(key => {
    const cssVar = `--kotel-${key.replace('theme_', '')}`;
    root.style.setProperty(cssVar, testTheme[key]);
});

console.log('✅ Test theme saved and applied');

// Test 3: Verify it was applied
setTimeout(() => {
    const newCSS = getComputedStyle(root).getPropertyValue('--kotel-border').trim();
    const savedTheme = localStorage.getItem('kotel_theme');
    
    console.log('\n=== Verification ===');
    console.log('CSS border color after test:', newCSS);
    console.log('localStorage contains theme:', savedTheme ? 'YES' : 'NO');
    
    if (savedTheme) {
        const parsed = JSON.parse(savedTheme);
        console.log('Border color in localStorage:', parsed.theme_border_color);
        console.log('Test PASSED:', newCSS === parsed.theme_border_color ? '✅' : '❌');
    }
    
    console.log('\n🎯 Now refresh the page to test persistence!');
    console.log('After refresh, run: checkThemePersistence()');
    
}, 100);

// Test 4: Function to check persistence after refresh
window.checkThemePersistence = function() {
    console.log('\n=== Persistence Check ===');
    
    const savedTheme = localStorage.getItem('kotel_theme');
    const currentCSS = getComputedStyle(document.documentElement).getPropertyValue('--kotel-border').trim();
    
    console.log('localStorage after refresh:', savedTheme ? 'EXISTS' : 'EMPTY');
    console.log('CSS border color after refresh:', currentCSS);
    
    if (savedTheme) {
        const parsed = JSON.parse(savedTheme);
        console.log('Border color in localStorage:', parsed.theme_border_color);
        
        const matches = currentCSS === parsed.theme_border_color;
        console.log('Persistence Test:', matches ? '✅ PASSED' : '❌ FAILED');
        
        if (!matches) {
            console.log('⚠️ Issue: CSS and localStorage don\'t match');
            console.log('Expected:', parsed.theme_border_color);
            console.log('Actual:', currentCSS);
        }
    } else {
        console.log('❌ FAILED: No theme in localStorage');
    }
};

console.log('💡 After refresh, run checkThemePersistence() to test persistence');
