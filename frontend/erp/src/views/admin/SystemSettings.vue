<!-- src/views/admin/SystemSettings.vue -->
<template>
    <div class="system-settings">
      <div class="page-header">
        <h1>System Settings</h1>
        <p>Configure system-wide settings for your ERP application</p>
      </div>
  
      <div class="loading-overlay" v-if="isLoading">
        <i class="fas fa-spinner fa-spin"></i>
        <span>Loading settings...</span>
      </div>
  
      <div class="settings-tabs">
        <div 
          v-for="tab in tabs" 
          :key="tab.id" 
          :class="['tab', { active: activeTab === tab.id }]"
          @click="setActiveTab(tab.id)"
        >
          <i :class="tab.icon"></i>
          {{ tab.label }}
        </div>
      </div>
  
      <div class="settings-container" v-if="!isLoading">
        <!-- All Settings Tab -->
        <div v-if="activeTab === 'all'" class="settings-section">
          <div class="section-header">
            <h2>All System Settings</h2>
            <button class="btn btn-primary" @click="saveAllSettings">Save Changes</button>
          </div>
  
          <div class="settings-table">
            <div class="settings-row header">
              <div class="col-key">Setting Key</div>
              <div class="col-group">Group</div>
              <div class="col-value">Value</div>
              <div class="col-description">Description</div>
            </div>
  
            <div v-for="setting in settings" :key="setting.setting_id" class="settings-row">
              <div class="col-key">{{ setting.setting_key }}</div>
              <div class="col-group">{{ setting.setting_group }}</div>
              <div class="col-value">
                <input 
                  v-if="getSettingType(setting) === 'boolean'" 
                  type="checkbox" 
                  v-model="setting.setting_value_parsed"
                  @change="updateSettingValue(setting)"
                />
                <select 
                  v-else-if="getSettingType(setting) === 'select'" 
                  v-model="setting.setting_value"
                  @change="updateSettingValue(setting)"
                >
                  <option v-for="option in getSettingOptions(setting)" :key="option" :value="option">
                    {{ option }}
                  </option>
                </select>
                <input 
                  v-else 
                  type="text" 
                  v-model="setting.setting_value"
                  @change="updateSettingValue(setting)"
                />
              </div>
              <div class="col-description">{{ setting.description }}</div>
            </div>
          </div>
        </div>
  
        <!-- By Group Tab -->
        <div v-if="activeTab === 'group'" class="settings-section">
          <div class="section-header">
            <h2>Settings by Group</h2>
            <div class="group-selector">
              <label for="group-select">Select Group:</label>
              <select id="group-select" v-model="selectedGroup" @change="fetchSettingsByGroup">
                <option v-for="group in groupsList" :key="group" :value="group">{{ group }}</option>
              </select>
            </div>
            <button class="btn btn-primary" @click="saveGroupSettings">Save Changes</button>
          </div>
  
          <div v-if="selectedGroup" class="settings-by-group">
            <div v-if="groupSettings.length === 0" class="no-settings">
              No settings found for this group.
            </div>
            <div v-else class="settings-table">
              <div class="settings-row header">
                <div class="col-key">Setting Key</div>
                <div class="col-value">Value</div>
                <div class="col-description">Description</div>
              </div>
  
              <div v-for="setting in groupSettings" :key="setting.setting_id" class="settings-row">
                <div class="col-key">{{ setting.setting_key }}</div>
                <div class="col-value">
                  <input 
                    v-if="getSettingType(setting) === 'boolean'" 
                    type="checkbox" 
                    v-model="setting.setting_value_parsed"
                    @change="updateSettingValue(setting)"
                  />
                  <select 
                    v-else-if="getSettingType(setting) === 'select'" 
                    v-model="setting.setting_value"
                    @change="updateSettingValue(setting)"
                  >
                    <option v-for="option in getSettingOptions(setting)" :key="option" :value="option">
                      {{ option }}
                    </option>
                  </select>
                  <input 
                    v-else 
                    type="text" 
                    v-model="setting.setting_value"
                    @change="updateSettingValue(setting)"
                  />
                </div>
                <div class="col-description">{{ setting.description }}</div>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Inventory Settings Tab -->
        <div v-if="activeTab === 'inventory'" class="settings-section">
          <div class="section-header">
            <h2>Inventory Settings</h2>
            <button class="btn btn-primary" @click="saveInventorySettings">Save Changes</button>
          </div>
  
          <div class="inventory-settings-form">
            <div class="setting-card">
              <div class="card-header">
                <i class="fas fa-boxes"></i>
                <h3>Stock Validation</h3>
              </div>
              <div class="card-body">
                <div class="form-group">
                  <div class="toggle-switch">
                    <input 
                      type="checkbox" 
                      id="enforce-stock-validation" 
                      v-model="inventorySettings.enforce_stock_validation" 
                    />
                    <label for="enforce-stock-validation">Enforce Stock Validation</label>
                  </div>
                  <div class="setting-description">
                    When enabled, the system will validate stock levels before allowing transactions.
                  </div>
                </div>
              </div>
            </div>
  
            <div class="setting-card">
              <div class="card-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Negative Stock</h3>
              </div>
              <div class="card-body">
                <div class="form-group">
                  <div class="toggle-switch">
                    <input 
                      type="checkbox" 
                      id="allow-negative-stock" 
                      v-model="inventorySettings.allow_negative_stock" 
                    />
                    <label for="allow-negative-stock">Allow Negative Stock</label>
                  </div>
                  <div class="setting-description">
                    When enabled, inventory levels can go below zero. This can be useful for backorder situations.
                  </div>
                </div>
              </div>
            </div>
  
            <div class="setting-card">
              <div class="card-header">
                <i class="fas fa-clipboard-check"></i>
                <h3>Batch Tracking</h3>
              </div>
              <div class="card-body">
                <div class="form-group">
                  <div class="toggle-switch">
                    <input 
                      type="checkbox" 
                      id="enable-batch-tracking" 
                      v-model="inventorySettings.enable_batch_tracking" 
                    />
                    <label for="enable-batch-tracking">Enable Batch Tracking</label>
                  </div>
                  <div class="setting-description">
                    When enabled, the system will track inventory by batch/lot numbers.
                  </div>
                </div>
              </div>
            </div>
  
            <div class="setting-card">
              <div class="card-header">
                <i class="fas fa-calendar-alt"></i>
                <h3>Expiry Date Tracking</h3>
              </div>
              <div class="card-body">
                <div class="form-group">
                  <div class="toggle-switch">
                    <input 
                      type="checkbox" 
                      id="track-expiry-dates" 
                      v-model="inventorySettings.track_expiry_dates" 
                    />
                    <label for="track-expiry-dates">Track Expiry Dates</label>
                  </div>
                  <div class="setting-description">
                    When enabled, the system will track expiry dates for perishable items.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Notifications -->
      <div v-if="notification.show" class="notification" :class="notification.type">
        <span>{{ notification.message }}</span>
        <button @click="notification.show = false" class="notification-close">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    name: 'SystemSettings',
    data() {
      return {
        tabs: [
          { id: 'all', label: 'All Settings', icon: 'fas fa-cogs' },
          { id: 'group', label: 'By Group', icon: 'fas fa-layer-group' },
          { id: 'inventory', label: 'Inventory Settings', icon: 'fas fa-boxes' }
        ],
        activeTab: 'all',
        isLoading: false,
        settings: [],
        groupsList: [],
        selectedGroup: '',
        groupSettings: [],
        inventorySettings: {
          enforce_stock_validation: false,
          allow_negative_stock: false,
          enable_batch_tracking: false,
          track_expiry_dates: false
        },
        notification: {
          show: false,
          message: '',
          type: 'success'
        },
        modifiedSettings: {}
      };
    },
    created() {
      this.fetchAllSettings();
    },
    methods: {
      setActiveTab(tabId) {
        this.activeTab = tabId;
        
        if (tabId === 'all') {
          this.fetchAllSettings();
        } else if (tabId === 'inventory') {
          this.fetchInventorySettings();
        } else if (tabId === 'group' && !this.groupsList.length) {
          this.extractGroups();
        }
      },
      
      // Fetch all settings
      async fetchAllSettings() {
        this.isLoading = true;
        try {
          const response = await axios.get('/api/settings');
          this.settings = response.data.data.map(setting => {
            return {
              ...setting,
              setting_value_parsed: this.parseSettingValue(setting.setting_value)
            };
          });
          
          // Extract unique groups for the group selector
          this.extractGroups();
          
          this.isLoading = false;
        } catch (error) {
          console.error('Error fetching settings:', error);
          this.showNotification('Error loading settings', 'error');
          this.isLoading = false;
        }
      },
      
      // Extract unique groups from settings
      extractGroups() {
        if (this.settings.length) {
          this.groupsList = [...new Set(this.settings.map(setting => setting.setting_group))];
          
          // Set default group if none selected
          if (!this.selectedGroup && this.groupsList.length) {
            this.selectedGroup = this.groupsList[0];
            this.fetchSettingsByGroup();
          }
        }
      },
      
      // Fetch settings by group
      async fetchSettingsByGroup() {
        if (!this.selectedGroup) return;
        
        this.isLoading = true;
        try {
          const response = await axios.get(`/api/settings/group/${this.selectedGroup}`);
          this.groupSettings = response.data.data.map(setting => {
            return {
              ...setting,
              setting_value_parsed: this.parseSettingValue(setting.setting_value)
            };
          });
          this.isLoading = false;
        } catch (error) {
          console.error('Error fetching group settings:', error);
          this.showNotification(`Error loading settings for group: ${this.selectedGroup}`, 'error');
          this.isLoading = false;
        }
      },
      
      // Fetch inventory settings
      async fetchInventorySettings() {
        this.isLoading = true;
        try {
          const response = await axios.get('/api/settings/inventory');
          this.inventorySettings = response.data.data;
          this.isLoading = false;
        } catch (error) {
          console.error('Error fetching inventory settings:', error);
          this.showNotification('Error loading inventory settings', 'error');
          this.isLoading = false;
        }
      },
      
      // Parse setting value (convert string "true"/"false" to boolean)
      parseSettingValue(value) {
        if (value === 'true') return true;
        if (value === 'false') return false;
        return value;
      },
      
      // Update setting value when changed
      updateSettingValue(setting) {
        // If the value is a parsed boolean, convert back to string for API
        if (typeof setting.setting_value_parsed === 'boolean') {
          setting.setting_value = setting.setting_value_parsed ? 'true' : 'false';
        }
        
        // Add to modified settings
        this.modifiedSettings[setting.setting_key] = setting.setting_value;
      },
      
      // Get setting type (boolean, select, text)
      getSettingType(setting) {
        // Check if it's a boolean setting
        if (setting.setting_value === 'true' || setting.setting_value === 'false') {
          return 'boolean';
        }
        
        // Check if it's a select setting (example: if setting key contains 'mode' or 'type')
        if (setting.setting_key.includes('mode') || setting.setting_key.includes('type')) {
          return 'select';
        }
        
        return 'text';
      },
      
      // Get options for select settings
      getSettingOptions(setting) {
        // This is just an example - in a real implementation, you'd have a mapping of settings to their options
        if (setting.setting_key.includes('mode')) {
          return ['simple', 'advanced', 'expert'];
        }
        
        if (setting.setting_key.includes('type')) {
          return ['default', 'custom', 'legacy'];
        }
        
        return [];
      },
      
      // Save all settings
      async saveAllSettings() {
        if (Object.keys(this.modifiedSettings).length === 0) {
          this.showNotification('No changes to save', 'info');
          return;
        }
        
        this.isLoading = true;
        try {
          const settingsArray = Object.keys(this.modifiedSettings).map(key => ({
            setting_key: key,
            setting_value: this.modifiedSettings[key]
          }));
          
          await axios.put('/api/settings/batch', { settings: settingsArray });
          this.showNotification('Settings saved successfully', 'success');
          this.modifiedSettings = {}; // Reset modified settings
          this.fetchAllSettings(); // Refresh settings
        } catch (error) {
          console.error('Error saving settings:', error);
          this.showNotification('Error saving settings', 'error');
        } finally {
          this.isLoading = false;
        }
      },
      
      // Save group settings
      async saveGroupSettings() {
        if (Object.keys(this.modifiedSettings).length === 0) {
          this.showNotification('No changes to save', 'info');
          return;
        }
        
        this.isLoading = true;
        try {
          const settingsArray = Object.keys(this.modifiedSettings).map(key => ({
            setting_key: key,
            setting_value: this.modifiedSettings[key]
          }));
          
          await axios.put('/api/settings/batch', { settings: settingsArray });
          this.showNotification('Group settings saved successfully', 'success');
          this.modifiedSettings = {}; // Reset modified settings
          this.fetchSettingsByGroup(); // Refresh group settings
        } catch (error) {
          console.error('Error saving group settings:', error);
          this.showNotification('Error saving group settings', 'error');
        } finally {
          this.isLoading = false;
        }
      },
      
      // Save inventory settings
      async saveInventorySettings() {
        this.isLoading = true;
        try {
          await axios.put('/api/settings/inventory', this.inventorySettings);
          this.showNotification('Inventory settings saved successfully', 'success');
        } catch (error) {
          console.error('Error saving inventory settings:', error);
          this.showNotification('Error saving inventory settings', 'error');
        } finally {
          this.isLoading = false;
        }
      },
      
      // Show notification
      showNotification(message, type = 'success') {
        this.notification = {
          show: true,
          message,
          type
        };
        
        // Auto-hide notification after 3 seconds
        setTimeout(() => {
          this.notification.show = false;
        }, 3000);
      }
    }
  };
  </script>
  
  <style scoped>
  .system-settings {
    max-width: 100%;
    position: relative;
  }
  
  .page-header {
    margin-bottom: 2rem;
  }
  
  .page-header h1 {
    margin-bottom: 0.5rem;
    color: var(--gray-800);
  }
  
  .page-header p {
    color: var(--gray-600);
  }
  
  .loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(255, 255, 255, 0.8);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 10;
  }
  
  .loading-overlay i {
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
  }
  
  .settings-tabs {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    border-bottom: 1px solid var(--gray-200);
    padding-bottom: 1rem;
  }
  
  .tab {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 500;
  }
  
  .tab i {
    color: var(--gray-600);
  }
  
  .tab:hover {
    background-color: var(--gray-100);
  }
  
  .tab.active {
    background-color: var(--primary-color);
    color: white;
  }
  
  .tab.active i {
    color: white;
  }
  
  .settings-section {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
  }
  
  .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
  }
  
  .section-header h2 {
    margin: 0;
    font-size: 1.25rem;
  }
  
  .btn {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
  }
  
  .btn-primary {
    background-color: var(--primary-color);
    color: white;
  }
  
  .btn-primary:hover {
    background-color: var(--primary-dark);
  }
  
  .settings-table {
    width: 100%;
    border: 1px solid var(--gray-200);
    border-radius: 0.375rem;
    overflow: hidden;
  }
  
  .settings-row {
    display: grid;
    grid-template-columns: 20% 15% 25% 40%;
    border-bottom: 1px solid var(--gray-200);
  }
  
  .settings-row:last-child {
    border-bottom: none;
  }
  
  .settings-row.header {
    background-color: var(--gray-100);
    font-weight: 600;
  }
  
  .settings-row > div {
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
  }
  
  .col-key {
    font-weight: 500;
  }
  
  .col-group {
    color: var(--gray-600);
  }
  
  .col-value input[type="text"],
  .col-value select {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid var(--gray-300);
    border-radius: 0.25rem;
  }
  
  .col-description {
    color: var(--gray-600);
    font-size: 0.875rem;
  }
  
  .group-selector {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }
  
  .group-selector select {
    padding: 0.5rem;
    border: 1px solid var(--gray-300);
    border-radius: 0.25rem;
  }
  
  .no-settings {
    padding: 2rem;
    text-align: center;
    color: var(--gray-500);
  }
  
  /* Inventory Settings Styles */
  .inventory-settings-form {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
  }
  
  .setting-card {
    background-color: white;
    border: 1px solid var(--gray-200);
    border-radius: 0.5rem;
    overflow: hidden;
  }
  
  .card-header {
    padding: 1rem;
    background-color: var(--gray-100);
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }
  
  .card-header h3 {
    margin: 0;
    font-size: 1rem;
  }
  
  .card-body {
    padding: 1.5rem;
  }
  
  .form-group {
    margin-bottom: 1rem;
  }
  
  .form-group:last-child {
    margin-bottom: 0;
  }
  
  .toggle-switch {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
  }
  
  .toggle-switch input[type="checkbox"] {
    height: 0;
    width: 0;
    visibility: hidden;
    position: absolute;
  }
  
  .toggle-switch label {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    font-weight: 500;
  }
  
  .toggle-switch label:before {
    content: '';
    display: inline-block;
    width: 2.5rem;
    height: 1.25rem;
    background: var(--gray-300);
    border-radius: 1rem;
    position: relative;
    margin-right: 0.75rem;
    transition: 0.2s;
  }
  
  .toggle-switch label:after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 1rem;
    height: 1rem;
    background: white;
    border-radius: 50%;
    transition: 0.2s;
  }
  
  .toggle-switch input:checked + label:before {
    background: var(--primary-color);
  }
  
  .toggle-switch input:checked + label:after {
    left: calc(100% - 1rem - 2px);
    transform: translateX(-100%);
  }
  
  .setting-description {
    font-size: 0.875rem;
    color: var(--gray-600);
  }
  
  .notification {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    padding: 1rem 1.5rem;
    border-radius: 0.375rem;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-width: 300px;
    z-index: 100;
    animation: slide-in 0.3s ease-out;
  }
  
  .notification.success {
    background-color: #10b981;
    color: white;
  }
  
  .notification.error {
    background-color: #ef4444;
    color: white;
  }
  
  .notification.info {
    background-color: #3b82f6;
    color: white;
  }
  
  .notification-close {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    margin-left: 1rem;
  }
  
  @keyframes slide-in {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
  
  @media (max-width: 768px) {
    .settings-row {
      grid-template-columns: 1fr;
    }
    
    .settings-row.header {
      display: none;
    }
    
    .settings-row > div {
      padding: 0.75rem;
    }
    
    .col-key {
      font-weight: 600;
      border-bottom: 1px solid var(--gray-100);
    }
    
    .col-key:before {
      content: 'Setting: ';
      font-weight: normal;
    }
    
    .col-group:before {
      content: 'Group: ';
      font-weight: 600;
    }
    
    .col-value:before {
      content: 'Value: ';
      font-weight: 600;
    }
    
    .col-description:before {
      content: 'Description: ';
      font-weight: 600;
    }
    
    .section-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 1rem;
    }
    
    .inventory-settings-form {
      grid-template-columns: 1fr;
    }
  }
  </style>