<template>
  <div class="print-settings-page">
    <div class="page-header">
      <h1>🖨️ Print Settings</h1>
      <p>Configure receipt printing settings for POS and Front Desk thermal printers</p>
    </div>

    <div class="settings-container">
      <!-- POS Print Settings -->
      <div class="settings-card">
        <div class="card-header pos-header">
          <div class="header-icon">🏪</div>
          <div class="header-text">
            <h2>POS Terminal Receipt</h2>
            <p>Settings for POS terminal thermal printer</p>
          </div>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label>Paper Width (mm)</label>
            <div class="input-with-presets">
              <input
                type="number"
                v-model.number="settings.pos_print_paper_width"
                min="58"
                max="112"
                class="form-input"
              />
              <div class="preset-buttons">
                <button @click="settings.pos_print_paper_width = 58" class="preset-btn">58mm</button>
                <button @click="settings.pos_print_paper_width = 80" class="preset-btn">80mm</button>
                <button @click="settings.pos_print_paper_width = 112" class="preset-btn">112mm</button>
              </div>
            </div>
            <span class="hint">Common sizes: 58mm (small), 80mm (standard), 112mm (large)</span>
          </div>

          <div class="form-group">
            <label>Font Size</label>
            <select v-model.number="settings.pos_print_font_size" class="form-input">
              <option :value="10">Small (10px)</option>
              <option :value="12">Medium (12px)</option>
              <option :value="14">Large (14px)</option>
              <option :value="16">Extra Large (16px)</option>
            </select>
          </div>

          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input type="checkbox" v-model="settings.pos_print_show_logo" />
              <span>Show Hotel Logo on Receipt</span>
            </label>
          </div>

          <div class="preview-section">
            <h3>Preview</h3>
            <div class="receipt-preview" :style="getPreviewStyle('pos')">
              <div v-if="settings.pos_print_show_logo" class="preview-logo">🏨</div>
              <div class="preview-title">RECEIPT</div>
              <div class="preview-divider">────────────────────────────────</div>
              <div class="preview-row">Item 1 x2 ........... 1,000</div>
              <div class="preview-row">Item 2 x1 ........... 2,500</div>
              <div class="preview-divider">────────────────────────────────</div>
              <div class="preview-row">Subtotal .......... 4,000</div>
              <div class="preview-row">Tax ................... 320</div>
              <div class="preview-total">TOTAL ............ 4,320</div>
              <div class="preview-footer">Thank you!</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Front Desk Print Settings -->
      <div class="settings-card">
        <div class="card-header frontdesk-header">
          <div class="header-icon">🛎️</div>
          <div class="header-text">
            <h2>Front Desk Receipt</h2>
            <p>Settings for front desk thermal printer</p>
          </div>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label>Paper Width (mm)</label>
            <div class="input-with-presets">
              <input
                type="number"
                v-model.number="settings.frontdesk_print_paper_width"
                min="58"
                max="112"
                class="form-input"
              />
              <div class="preset-buttons">
                <button @click="settings.frontdesk_print_paper_width = 58" class="preset-btn">58mm</button>
                <button @click="settings.frontdesk_print_paper_width = 80" class="preset-btn">80mm</button>
                <button @click="settings.frontdesk_print_paper_width = 112" class="preset-btn">112mm</button>
              </div>
            </div>
            <span class="hint">Common sizes: 58mm (small), 80mm (standard), 112mm (large)</span>
          </div>

          <div class="form-group">
            <label>Font Size</label>
            <select v-model.number="settings.frontdesk_print_font_size" class="form-input">
              <option :value="10">Small (10px)</option>
              <option :value="12">Medium (12px)</option>
              <option :value="14">Large (14px)</option>
              <option :value="16">Extra Large (16px)</option>
            </select>
          </div>

          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input type="checkbox" v-model="settings.frontdesk_print_show_logo" />
              <span>Show Hotel Logo on Receipt</span>
            </label>
          </div>

          <div class="preview-section">
            <h3>Preview</h3>
            <div class="receipt-preview" :style="getPreviewStyle('frontdesk')">
              <div v-if="settings.frontdesk_print_show_logo" class="preview-logo">🏨</div>
              <div class="preview-title">RECEIPT</div>
              <div class="preview-divider">────────────────────────────────</div>
              <div class="preview-row">Guest: John Doe</div>
              <div class="preview-row">Room: 101 ........... 25,000</div>
              <div class="preview-divider">────────────────────────────────</div>
              <div class="preview-total">TOTAL ............ 25,000</div>
              <div class="preview-footer">Thank you!</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="action-bar">
      <button @click="resetToDefaults" class="btn-reset">Reset to Defaults</button>
      <button @click="saveSettings" class="btn-save" :disabled="isSaving">
        {{ isSaving ? 'Saving...' : '💾 Save Settings' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const isSaving = ref(false)
const settings = ref({
  pos_print_paper_width: 80,
  pos_print_font_size: 12,
  pos_print_show_logo: true,
  frontdesk_print_paper_width: 80,
  frontdesk_print_font_size: 12,
  frontdesk_print_show_logo: true
})

const getPreviewStyle = (type) => {
  const width = type === 'pos' ? settings.value.pos_print_paper_width : settings.value.frontdesk_print_paper_width
  const fontSize = type === 'pos' ? settings.value.pos_print_font_size : settings.value.frontdesk_print_font_size

  // Convert mm to approximate pixels (1mm ≈ 3.78px at 96dpi)
  const pxWidth = Math.round(width * 3.78)

  return {
    width: `${pxWidth}px`,
    fontSize: `${fontSize}px`
  }
}

const resetToDefaults = () => {
  if (confirm('Reset all print settings to default values?')) {
    settings.value = {
      pos_print_paper_width: 80,
      pos_print_font_size: 12,
      pos_print_show_logo: true,
      frontdesk_print_paper_width: 80,
      frontdesk_print_font_size: 12,
      frontdesk_print_show_logo: true
    }
  }
}

const saveSettings = async () => {
  isSaving.value = true
  try {
    await router.post('/admin/settings/print', settings.value, {
      onSuccess: () => {
        alert('Print settings saved successfully!')
      },
      onError: () => {
        alert('Failed to save settings')
      }
    })
  } catch (error) {
    console.error('Error saving settings:', error)
    alert('Error saving settings')
  } finally {
    isSaving.value = false
  }
}

onMounted(() => {
  // Load existing settings
  const printSettings = window.printSettings || {}
  if (printSettings.pos_print_paper_width) settings.value.pos_print_paper_width = printSettings.pos_print_paper_width
  if (printSettings.pos_print_font_size) settings.value.pos_print_font_size = printSettings.pos_print_font_size
  if (printSettings.pos_print_show_logo !== undefined) settings.value.pos_print_show_logo = printSettings.pos_print_show_logo
  if (printSettings.frontdesk_print_paper_width) settings.value.frontdesk_print_paper_width = printSettings.frontdesk_print_paper_width
  if (printSettings.frontdesk_print_font_size) settings.value.frontdesk_print_font_size = printSettings.frontdesk_print_font_size
  if (printSettings.frontdesk_print_show_logo !== undefined) settings.value.frontdesk_print_show_logo = printSettings.frontdesk_print_show_logo
})
</script>

<style scoped>
.print-settings-page {
  padding: 24px;
  background: #f1f5f9;
  min-height: 100vh;
}

.page-header {
  margin-bottom: 24px;
}

.page-header h1 {
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 8px 0;
}

.page-header p {
  color: #64748b;
  margin: 0;
}

.settings-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 24px;
  margin-bottom: 24px;
}

.settings-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-header {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  color: white;
}

.pos-header {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.frontdesk-header {
  background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
}

.header-icon {
  font-size: 36px;
}

.header-text h2 {
  font-size: 18px;
  font-weight: 600;
  margin: 0 0 4px 0;
}

.header-text p {
  font-size: 12px;
  opacity: 0.9;
  margin: 0;
}

.card-body {
  padding: 24px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-weight: 500;
  color: #374151;
  margin-bottom: 8px;
}

.input-with-presets {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.form-input {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.preset-buttons {
  display: flex;
  gap: 8px;
}

.preset-btn {
  flex: 1;
  padding: 8px 12px;
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.preset-btn:hover {
  background: #e5e7eb;
  border-color: #9ca3af;
}

.preset-btn.active {
  background: #dbeafe;
  border-color: #3b82f6;
  color: #1d4ed8;
}

.hint {
  display: block;
  font-size: 11px;
  color: #9ca3af;
  margin-top: 6px;
}

.checkbox-group {
  margin-top: 16px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
}

.checkbox-label input {
  width: 18px;
  height: 18px;
  accent-color: #3b82f6;
}

.preview-section {
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid #e5e7eb;
}

.preview-section h3 {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
  margin: 0 0 12px 0;
}

.receipt-preview {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 16px;
  margin: 0 auto;
  font-family: 'Courier New', monospace;
  text-align: center;
  overflow: hidden;
  white-space: pre;
  min-height: 200px;
}

.preview-logo {
  font-size: 32px;
  margin-bottom: 8px;
}

.preview-title {
  font-size: 18px;
  font-weight: 700;
}

.preview-divider {
  margin: 8px 0;
  color: #d1d5db;
  font-size: 12px;
  letter-spacing: -1px;
}

.preview-row {
  text-align: left;
  margin: 4px 0;
}

.preview-total {
  font-weight: 700;
  margin-top: 8px;
}

.preview-footer {
  margin-top: 12px;
  font-weight: 600;
}

.action-bar {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.btn-reset {
  padding: 12px 24px;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  cursor: pointer;
  color: #6b7280;
}

.btn-save {
  padding: 12px 24px;
  background: linear-gradient(135deg, #059669 0%, #10b981 100%);
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  color: white;
}

.btn-save:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
