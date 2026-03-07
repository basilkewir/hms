# Border Color Debug Guide

## 🔍 **Issue Description**
When changing the border color in admin settings and saving, after page refresh the border color doesn't apply anymore.

## 🧪 **Debug Steps**

### **Step 1: Test with Debug Page**
1. Open `test-theme.html` in your browser
2. Click "Debug Theme" button
3. Check console for detailed theme information
4. Look specifically for:
   - `Border color from CSS:` 
   - `Border color from localStorage:`
   - Any error messages

### **Step 2: Test in Real Application**
1. Navigate to `http://localhost:8000/admin/settings`
2. Open browser console (F12)
3. Change border color to something noticeable (like red `#ff0000`)
4. Click "Save Changes"
5. Check console for:
   - `🎨 Applying theme with border color: #ff0000`
   - `✅ Border color applied to CSS: #ff0000`
6. Refresh page (F5)
7. Check console for:
   - `🔄 Loading theme...`
   - `✅ Theme loaded from localStorage: #ff0000`
   - `🎨 Applying theme with border color: #ff0000`
   - `✅ Border color applied to CSS: #ff0000`

### **Step 3: Manual Console Checks**
In browser console, run these commands:

```javascript
// Check localStorage
localStorage.getItem('kotel_theme')

// Check CSS variables
getComputedStyle(document.documentElement).getPropertyValue('--kotel-border')

// Check if theme is being applied
document.documentElement.style.getPropertyValue('--kotel-border')
```

## 🔧 **Expected vs Actual Behavior**

### **✅ Expected Behavior:**
1. User changes border color in settings
2. Clicks "Save Changes"
3. Console shows: `🎨 Applying theme with border color: #ff0000`
4. Border color changes immediately on screen
5. Theme is saved to localStorage
6. Page refresh shows: `✅ Theme loaded from localStorage: #ff0000`
7. Border color persists after refresh

### **❌ Actual Behavior (Issue):**
1. User changes border color in settings
2. Clicks "Save Changes"
3. Border color changes immediately ✅
4. Page refresh
5. Border color reverts to default ❌

## 🐛 **Possible Causes**

### **Cause 1: localStorage Not Updated**
- Theme saved to database but not localStorage
- Check: `localStorage.getItem('kotel_theme')` after save

### **Cause 2: Theme Not Loaded on Refresh**
- localStorage has theme but not loaded
- Check: Console shows `🔄 Loading theme...` but no `✅ Theme loaded from localStorage`

### **Cause 3: CSS Variable Not Applied**
- Theme loaded but CSS not updated
- Check: `getComputedStyle(document.documentElement).getPropertyValue('--kotel-border')`

### **Cause 4: Server Override**
- Server theme overrides localStorage on load
- Check: Console shows `✅ Theme loaded from API:` instead of localStorage

## 🛠️ **Debug Commands**

### **Check Current State:**
```javascript
// Check what's in localStorage
console.log('localStorage:', localStorage.getItem('kotel_theme'))

// Check CSS variables
const root = document.documentElement
console.log('CSS Border:', getComputedStyle(root).getPropertyValue('--kotel-border'))
console.log('CSS Primary:', getComputedStyle(root).getPropertyValue('--kotel-primary'))
console.log('CSS Background:', getComputedStyle(root).getPropertyValue('--kotel-background'))
```

### **Force Apply Theme:**
```javascript
// Manually apply a theme with red border
const testTheme = {
    theme_border_color: '#ff0000',
    theme_primary_color: '#facc15',
    theme_background_color: '#0b0b0b',
    theme_sidebar_color: '#0f172a',
    theme_card_color: '#111827',
    theme_text_primary: '#f3f4f6',
    theme_text_secondary: '#9ca3af',
    theme_text_tertiary: '#6b7280'
}

// Apply to CSS
Object.keys(testTheme).forEach(key => {
    const cssVar = `--kotel-${key.replace('theme_', '')}`
    document.documentElement.style.setProperty(cssVar, testTheme[key])
})

// Save to localStorage
localStorage.setItem('kotel_theme', JSON.stringify(testTheme))
```

### **Clear and Reset:**
```javascript
// Clear theme
localStorage.removeItem('kotel_theme')

// Reset CSS variables
const root = document.documentElement
root.style.setProperty('--kotel-border', '')
root.style.setProperty('--kotel-primary', '')
// ... add all other variables

// Refresh page
location.reload()
```

## 📊 **Debug Log Analysis**

### **Good Log Example:**
```
🔄 Loading theme...
✅ Theme loaded from localStorage: #ff0000
🎨 Applying theme with border color: #ff0000
✅ Border color applied to CSS: #ff0000
```

### **Bad Log Examples:**

**Example 1 - localStorage empty:**
```
🔄 Loading theme...
📡 No theme in localStorage, trying API endpoint...
✅ Theme loaded from API: #374151
🎨 Applying theme with border color: #374151
```

**Example 2 - CSS not applied:**
```
🔄 Loading theme...
✅ Theme loaded from localStorage: #ff0000
🎨 Applying theme with border color: #ff0000
✅ Border color applied to CSS: (empty)
```

## 🚀 **Fix Implementation**

If the issue is identified, here are the potential fixes:

### **Fix 1: localStorage Issue**
```javascript
// In Settings.vue saveSettings function, ensure theme is saved to localStorage
const themeSettings = { /* theme data */ }
localStorage.setItem('kotel_theme', JSON.stringify(themeSettings))
applyTheme(themeSettings)
```

### **Fix 2: Load Order Issue**
```javascript
// In DashboardLayout.vue, ensure loadTheme is called before other theme operations
onMounted(() => {
    loadTheme() // Load first
    loadThemeSettings() // Then load other settings
    // ... other initialization
})
```

### **Fix 3: API Override Issue**
```javascript
// In useTheme.js, prioritize localStorage over API
const loadTheme = async () => {
    const savedTheme = localStorage.getItem('kotel_theme')
    if (savedTheme) {
        // Always use localStorage first
        const theme = JSON.parse(savedTheme)
        applyTheme(theme)
        return
    }
    // Only try API if no localStorage
    // ... API logic
}
```

## 📝 **Test Results Template**

Copy and paste this format when reporting results:

```
=== Border Color Debug Results ===

Test Environment:
- Browser: [Chrome/Firefox/etc]
- URL: [admin/settings or test page]

Steps Performed:
1. [What you did]
2. [What you did next]
3. [etc]

Console Output:
[Copy relevant console messages here]

localStorage Content:
[Output of localStorage.getItem('kotel_theme')]

CSS Variables:
[Output of getComputedStyle checks]

Visual Result:
[Describe what you see on screen]

Issue Confirmed: [Yes/No]
If Yes, at which step does it fail?
```

---

**Ready for debugging!** 🎯
