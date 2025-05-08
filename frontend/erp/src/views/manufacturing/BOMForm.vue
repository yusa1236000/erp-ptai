<!-- src/views/manufacturing/BOMForm.vue -->
<template>
  <div class="bom-form-container">
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">{{ isEditMode ? 'Edit BOM' : 'Create New BOM' }}</h2>
      </div>
      
      <div class="card-body">
        <form @submit.prevent="submitForm">
          <!-- BOM Header Information -->
          <div class="form-section">
            <h3 class="section-title">Basic Information</h3>
            
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="item_id">Item <span class="required">*</span></label>
                  <div class="dropdown">
                    <input 
                      type="text" 
                      id="item_id" 
                      v-model="itemSearch" 
                      class="form-control" 
                      :class="{ 'is-invalid': errors.item_id }"
                      placeholder="Search for an item..."
                      @focus="showDropdown = true"
                      @blur="hideDropdown"
                    />
                    <div v-if="showDropdown" class="dropdown-menu">
                      <div v-for="item in filteredItems" :key="item.item_id" @click="selectItem(item)" class="dropdown-item">
                        {{ item.name }} ({{ item.item_code }})
                      </div>
                      <div v-if="filteredItems.length === 0" class="dropdown-item text-muted">No items found</div>
                    </div>
                  </div>
                  <div v-if="errors.item_id" class="invalid-feedback">{{ errors.item_id }}</div>
                </div>
                
                <div class="form-group">
                  <label for="bom_code">BOM Code <span class="required">*</span></label>
                  <input 
                    type="text" 
                    id="bom_code" 
                    v-model="form.bom_code" 
                    class="form-control" 
                    :class="{ 'is-invalid': errors.bom_code }"
                    required
                    maxlength="50"
                    :disabled="isEditMode"
                    placeholder="Enter unique BOM code"
                  />
                  <div v-if="errors.bom_code" class="invalid-feedback">{{ errors.bom_code }}</div>
                </div>
                
                <div class="form-group">
                  <label for="revision">Revision <span class="required">*</span></label>
                  <input 
                    type="text" 
                    id="revision" 
                    v-model="form.revision" 
                    class="form-control" 
                    :class="{ 'is-invalid': errors.revision }"
                    required
                    maxlength="10"
                    placeholder="e.g. 1.0"
                  />
                  <div v-if="errors.revision" class="invalid-feedback">{{ errors.revision }}</div>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="form-group">
                  <label for="effective_date">Effective Date <span class="required">*</span></label>
                  <input 
                    type="date" 
                    id="effective_date" 
                    v-model="form.effective_date" 
                    class="form-control" 
                    :class="{ 'is-invalid': errors.effective_date }"
                    required
                  />
                  <div v-if="errors.effective_date" class="invalid-feedback">{{ errors.effective_date }}</div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="standard_quantity">Standard Quantity <span class="required">*</span></label>
                      <input 
                        type="number" 
                        id="standard_quantity" 
                        v-model="form.standard_quantity" 
                        class="form-control" 
                        :class="{ 'is-invalid': errors.standard_quantity }"
                        required
                        step="0.01"
                        min="0.01"
                        placeholder="e.g. 1.00"
                      />
                      <div v-if="errors.standard_quantity" class="invalid-feedback">{{ errors.standard_quantity }}</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="uom_id">Unit of Measure <span class="required">*</span></label>
                      <select 
                        id="uom_id" 
                        v-model="form.uom_id" 
                        class="form-control" 
                        :class="{ 'is-invalid': errors.uom_id }"
                        required
                      >
                        <option value="">Select UOM</option>
                        <option v-if="selectedItemUOM" :value="selectedItemUOM.uom_id">
                          {{ selectedItemUOM.name }} ({{ selectedItemUOM.symbol }})
                        </option>
                      </select>
                      <div v-if="errors.uom_id" class="invalid-feedback">{{ errors.uom_id }}</div>
                    </div>
                  </div>
                </div>
                
                <div class="form-group">
                  <label for="status">Status <span class="required">*</span></label>
                  <select 
                    id="status" 
                    v-model="form.status" 
                    class="form-control" 
                    :class="{ 'is-invalid': errors.status }"
                    required
                  >
                    <option value="Draft">Draft</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                    <option value="Obsolete">Obsolete</option>
                  </select>
                  <div v-if="errors.status" class="invalid-feedback">{{ errors.status }}</div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- BOM Components Section -->
          <div class="form-section">
            <div class="section-header">
              <h3 class="section-title">Components</h3>
              <button 
                type="button" 
                class="btn btn-primary btn-sm" 
                @click="showAddComponentModal = true"
              >
                <i class="fas fa-plus mr-1"></i> Add Component
              </button>
            </div>
            
            <div class="table-container">
              <table class="table table-bordered" v-if="form.bom_lines.length > 0">
                <thead>
                  <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>UOM</th>
                    <th>Critical</th>
                    <th>Yield-Based</th>
                    <th>Yield Ratio</th>
                    <th>Shrinkage</th>
                    <th>Notes</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(line, index) in form.bom_lines" :key="index">
                    <td>
                      <span v-if="getItemName(line.item_id)">
                        {{ getItemName(line.item_id) }}
                      </span>
                      <span v-else class="text-muted">Item not found</span>
                    </td>
                    <td>{{ formatNumber(line.quantity) }}</td>
                    <td>{{ getUomSymbol(line.uom_id) }}</td>
                    <td class="text-center">
                      <i 
                        v-if="line.is_critical" 
                        class="fas fa-check-circle text-success"
                        title="Critical component"
                      ></i>
                      <i 
                        v-else 
                        class="fas fa-times-circle text-muted"
                        title="Optional component"
                      ></i>
                    </td>
                    <td class="text-center">
                      <i 
                        v-if="line.is_yield_based" 
                        class="fas fa-flask text-info"
                        title="Yield-based component"
                      ></i>
                      <i 
                        v-else 
                        class="fas fa-minus text-muted"
                        title="Standard component"
                      ></i>
                    </td>
                    <td>
                      {{ line.is_yield_based ? formatNumber(line.yield_ratio) : '-' }}
                    </td>
                    <td>
                      {{ line.is_yield_based ? formatPercentage(line.shrinkage_factor) : '-' }}
                    </td>
                    <td>
                      <span 
                        v-if="line.notes" 
                        class="notes-preview" 
                        :title="line.notes"
                      >
                        {{ truncateText(line.notes, 30) }}
                      </span>
                      <span v-else class="text-muted">-</span>
                    </td>
                    <td class="text-center">
                      <div class="action-group">
                        <button 
                          type="button" 
                          class="btn btn-sm btn-info" 
                          @click="editComponent(index)"
                          title="Edit component"
                        >
                          <i class="fas fa-edit"></i>
                        </button>
                        <button 
                          type="button" 
                          class="btn btn-sm btn-danger" 
                          @click="removeComponent(index)"
                          title="Remove component"
                        >
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              
              <div v-else class="empty-components">
                <p>No components added yet</p>
                <button 
                  type="button" 
                  class="btn btn-primary" 
                  @click="showAddComponentModal = true"
                >
                  <i class="fas fa-plus mr-1"></i> Add First Component
                </button>
              </div>
            </div>
          </div>
          
          <!-- Form Actions -->
          <div class="form-actions">
            <button type="button" class="btn btn-secondary" @click="cancelForm">
              Cancel
            </button>
            <button 
              type="submit" 
              class="btn btn-primary" 
              :disabled="isSubmitting || form.bom_lines.length === 0"
            >
              <i v-if="isSubmitting" class="fas fa-spinner fa-spin mr-1"></i>
              {{ isEditMode ? 'Update BOM' : 'Create BOM' }}
            </button>
          </div>
        </form>
      </div>
    </div>
    
    <!-- Add/Edit Component Modal -->
    <div v-if="showAddComponentModal || showEditComponentModal" class="modal">
      <div class="modal-backdrop" @click="cancelComponentModal"></div>
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ showEditComponentModal ? 'Edit Component' : 'Add Component' }}</h3>
          <button type="button" class="close-btn" @click="cancelComponentModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveComponent">
            <div class="form-group">
              <label for="component_item_id">Item <span class="required">*</span></label>
              <select 
                id="component_item_id" 
                v-model="componentForm.item_id" 
                class="form-control" 
                required
              >
                <option value="">Select Item</option>
                <option v-for="item in items" :key="item.item_id" :value="item.item_id">
                  {{ item.name }} ({{ item.item_code }})
                </option>
              </select>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="component_quantity">Quantity <span class="required">*</span></label>
                  <input 
                    type="number" 
                    id="component_quantity" 
                    v-model="componentForm.quantity" 
                    class="form-control" 
                    step="0.01"
                    min="0.01"
                    required
                  />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="component_uom_id">Unit of Measure <span class="required">*</span></label>
                  <select 
                    id="component_uom_id" 
                    v-model="componentForm.uom_id" 
                    class="form-control" 
                    required
                  >
                    <option value="">Select UOM</option>
                    <option v-for="uom in uoms" :key="uom.uom_id" :value="uom.uom_id">
                      {{ uom.name }} ({{ uom.symbol }})
                    </option>
                  </select>
                </div>
              </div>
            </div>
            
            <div class="form-group form-check">
              <input 
                type="checkbox" 
                id="component_is_critical" 
                v-model="componentForm.is_critical" 
                class="form-check-input"
              />
              <label for="component_is_critical" class="form-check-label">
                Critical Component
              </label>
              <small class="form-text text-muted">
                Mark as critical if this component is essential for the BOM
              </small>
            </div>
            
            <div class="form-group form-check">
              <input 
                type="checkbox" 
                id="component_is_yield_based" 
                v-model="componentForm.is_yield_based" 
                class="form-check-input"
              />
              <label for="component_is_yield_based" class="form-check-label">
                Yield-Based Component
              </label>
              <small class="form-text text-muted">
                Enable if this component's quantity affects the final yield of the product
              </small>
            </div>
            
            <div v-if="componentForm.is_yield_based" class="yield-based-fields">
              <div class="form-group">
                <label for="component_yield_ratio">Yield Ratio <span class="required">*</span></label>
                <input 
                  type="number" 
                  id="component_yield_ratio" 
                  v-model="componentForm.yield_ratio" 
                  class="form-control" 
                  step="0.0001"
                  min="0.0001"
                  required
                  placeholder="Number of finished products per unit of this material"
                />
                <small class="form-text text-muted">
                  How many units of the finished product can be produced per unit of this material
                </small>
              </div>
              
              <div class="form-group">
                <label for="component_shrinkage_factor">Shrinkage Factor</label>
                <div class="input-group">
                  <input 
                    type="number" 
                    id="component_shrinkage_factor" 
                    v-model="componentForm.shrinkage_factor" 
                    class="form-control" 
                    step="0.01"
                    min="0"
                    max="0.99"
                    placeholder="e.g. 0.05 for 5% shrinkage"
                  />
                  <div class="input-group-append">
                    <span class="input-group-text">%</span>
                  </div>
                </div>
                <small class="form-text text-muted">
                  Factor to account for material waste/shrinkage (0-99%)
                </small>
              </div>
            </div>
            
            <div class="form-group">
              <label for="component_notes">Notes</label>
              <textarea 
                id="component_notes" 
                v-model="componentForm.notes" 
                class="form-control" 
                rows="3"
                placeholder="Enter any special instructions or notes about this component"
              ></textarea>
            </div>
            
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="cancelComponentModal">
                Cancel
              </button>
              <button type="submit" class="btn btn-primary">
                {{ showEditComponentModal ? 'Update Component' : 'Add Component' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { watch } from 'vue';

export default {
  name: 'BOMForm',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const bomId = route.params.id;
    const isEditMode = computed(() => !!bomId);
    
    // Form state
    const isLoading = ref(false);
    const isSubmitting = ref(false);
    const errors = ref({});
    
    // Reference data
    const items = ref([]);
    const uoms = ref([]);
    
    // Modal state
    const showAddComponentModal = ref(false);
    const showEditComponentModal = ref(false);
    const editingComponentIndex = ref(-1);
    
    // Main form data
    const form = reactive({
      item_id: '',
      bom_code: '',
      revision: '1.0',
      effective_date: new Date().toISOString().substr(0, 10), // Default to today
      standard_quantity: 1,
      uom_id: '',
      status: 'Draft',
      bom_lines: []
    });
    
    // Component form data
    const componentForm = reactive({
      item_id: '',
      quantity: 1,
      uom_id: '',
      is_critical: false,
      is_yield_based: false,
      yield_ratio: 1,
      shrinkage_factor: 0,
      notes: ''
    });
    

    const itemSearch = ref(''); // Reactive property for search input
    const showDropdown = ref(false); // Control dropdown visibility
    // Computed property to filter items based on search input
    const filteredItems = computed(() => {
      if (!itemSearch.value) {
        return items.value; // Return all items if no search input
      }
      return items.value.filter(item => 
        item.name.toLowerCase().includes(itemSearch.value.toLowerCase()) || 
        item.item_code.toLowerCase().includes(itemSearch.value.toLowerCase())
      );
    });
    // Method to select an item from the dropdown
    const selectItem = (item) => {
      form.item_id = item.item_id; // Set selected item ID
      itemSearch.value = `${item.name} (${item.item_code})`; // Set input value
      showDropdown.value = false; // Hide dropdown
    };
    // Method to hide dropdown
    const hideDropdown = () => {
      setTimeout(() => {
        showDropdown.value = false; // Delay to allow click event to register
      }, 200);
    };
    // Computed property to get the UOM of the selected item
    const selectedItemUOM = computed(() => {
      const selectedItem = items.value.find(item => item.item_id === form.item_id);
      return selectedItem ? selectedItem.unit_of_measure : null; // Ambil unit_of_measure langsung
    });

    watch(() => form.item_id, (newItemId) => {
      const selectedItem = items.value.find(item => item.item_id === newItemId);
      if (selectedItem) {
        form.uom_id = selectedItem.unit_of_measure.uom_id; // Set UOM ID berdasarkan item yang dipilih
      } else {
        form.uom_id = ''; // Reset UOM ID jika tidak ada item yang dipilih
      }
    });
    
    // Fetch reference data
    const fetchReferenceData = async () => {
      try {
        // Fetch items
        const itemsResponse = await axios.get('/items');
        items.value = itemsResponse.data.data || itemsResponse.data;
        
        // Fetch UOMs
        const uomsResponse = await axios.get('/unit-of-measures');
        uoms.value = uomsResponse.data.data || uomsResponse.data;
      } catch (error) {
        console.error('Error fetching reference data:', error);
      }
    };
    
    // Fetch BOM for editing
    const fetchBOM = async () => {
      if (!isEditMode.value) return;
      
      isLoading.value = true;
      
      try {
        const response = await axios.get(`/boms/${bomId}`);
        const bomData = response.data.data;
        
        // Update form with BOM data
        form.item_id = bomData.item_id;
        form.bom_code = bomData.bom_code;
        form.revision = bomData.revision;
        form.effective_date = bomData.effective_date ? new Date(bomData.effective_date).toISOString().substr(0, 10) : '';
        form.standard_quantity = bomData.standard_quantity;
        form.uom_id = bomData.uom_id;
        form.status = bomData.status;
        
        // If BOM lines are included in the response
        if (bomData.bomLines && Array.isArray(bomData.bomLines)) {
          form.bom_lines = bomData.bomLines.map(line => ({
            line_id: line.line_id,
            item_id: line.item_id,
            quantity: line.quantity,
            uom_id: line.uom_id,
            is_critical: line.is_critical || false,
            is_yield_based: line.is_yield_based || false,
            yield_ratio: line.yield_ratio || 1,
            shrinkage_factor: line.shrinkage_factor || 0,
            notes: line.notes || ''
          }));
        } else {
          // Fetch BOM lines separately
          await fetchBOMLines();
        }
      } catch (error) {
        console.error('Error fetching BOM:', error);
      } finally {
        isLoading.value = false;
      }
    };
    
    // Fetch BOM lines separately if not included in BOM data
    const fetchBOMLines = async () => {
      try {
        const response = await axios.get(`/boms/${bomId}/lines`);
        form.bom_lines = response.data.data.map(line => ({
          line_id: line.line_id,
          item_id: line.item_id,
          quantity: line.quantity,
          uom_id: line.uom_id,
          is_critical: line.is_critical || false,
          is_yield_based: line.is_yield_based || false,
          yield_ratio: line.yield_ratio || 1,
          shrinkage_factor: line.shrinkage_factor || 0,
          notes: line.notes || ''
        }));
      } catch (error) {
        console.error('Error fetching BOM lines:', error);
      }
    };
    
    // Component modal handlers
    const openAddComponentModal = () => {
      // Reset component form
      Object.keys(componentForm).forEach(key => {
        if (key === 'quantity' || key === 'yield_ratio') {
          componentForm[key] = 1;
        } else if (key === 'is_critical' || key === 'is_yield_based') {
          componentForm[key] = false;
        } else if (key === 'shrinkage_factor') {
          componentForm[key] = 0;
        } else {
          componentForm[key] = '';
        }
      });
      
      showAddComponentModal.value = true;
    };
    
    const editComponent = (index) => {
      const line = form.bom_lines[index];
      
      // Populate component form with line data
      componentForm.item_id = line.item_id;
      componentForm.quantity = line.quantity;
      componentForm.uom_id = line.uom_id;
      componentForm.is_critical = line.is_critical;
      componentForm.is_yield_based = line.is_yield_based;
      componentForm.yield_ratio = line.yield_ratio || 1;
      componentForm.shrinkage_factor = line.shrinkage_factor || 0;
      componentForm.notes = line.notes || '';
      
      editingComponentIndex.value = index;
      showEditComponentModal.value = true;
    };
    
    const cancelComponentModal = () => {
      showAddComponentModal.value = false;
      showEditComponentModal.value = false;
      editingComponentIndex.value = -1;
    };
    
    const saveComponent = () => {
      // Basic validation
      if (!componentForm.item_id || !componentForm.quantity || !componentForm.uom_id) {
        alert('Please fill in all required fields');
        return;
      }
      
      if (componentForm.is_yield_based && (!componentForm.yield_ratio || componentForm.yield_ratio <= 0)) {
        alert('Yield ratio must be greater than zero for yield-based components');
        return;
      }
      
      if (showEditComponentModal.value) {
        // Update existing component
        if (editingComponentIndex.value >= 0 && editingComponentIndex.value < form.bom_lines.length) {
          const line = form.bom_lines[editingComponentIndex.value];
          line.item_id = componentForm.item_id;
          line.quantity = componentForm.quantity;
          line.uom_id = componentForm.uom_id;
          line.is_critical = componentForm.is_critical;
          line.is_yield_based = componentForm.is_yield_based;
          line.yield_ratio = componentForm.is_yield_based ? componentForm.yield_ratio : 1;
          line.shrinkage_factor = componentForm.is_yield_based ? componentForm.shrinkage_factor : 0;
          line.notes = componentForm.notes;
        }
      } else {
        // Add new component
        form.bom_lines.push({
          item_id: componentForm.item_id,
          quantity: componentForm.quantity,
          uom_id: componentForm.uom_id,
          is_critical: componentForm.is_critical,
          is_yield_based: componentForm.is_yield_based,
          yield_ratio: componentForm.is_yield_based ? componentForm.yield_ratio : 1,
          shrinkage_factor: componentForm.is_yield_based ? componentForm.shrinkage_factor : 0,
          notes: componentForm.notes
        });
      }
      
      cancelComponentModal();
    };
    
    const removeComponent = (index) => {
      if (confirm(`Are you sure you want to remove this component?`)) {
        form.bom_lines.splice(index, 1);
      }
    };
    
    // Form submission
    const submitForm = async () => {
      errors.value = {}; // Clear previous errors
      
      // Validate form
      if (!form.item_id) errors.value.item_id = 'Item is required';
      if (!form.bom_code) errors.value.bom_code = 'BOM Code is required';
      if (!form.revision) errors.value.revision = 'Revision is required';
      if (!form.effective_date) errors.value.effective_date = 'Effective Date is required';
      if (!form.standard_quantity || form.standard_quantity <= 0) {
        errors.value.standard_quantity = 'Standard Quantity must be greater than zero';
      }
      if (!form.uom_id) errors.value.uom_id = 'Unit of Measure is required';
      if (!form.status) errors.value.status = 'Status is required';
      
      // Check if we have components
      if (form.bom_lines.length === 0) {
        alert('You must add at least one component to the BOM');
        return;
      }
      
      // Return if we have errors
      if (Object.keys(errors.value).length > 0) return;
      
      isSubmitting.value = true;
      
      try {
        if (isEditMode.value) {
          // Update existing BOM
          await updateBOM();
        } else {
          // Create new BOM
          await createBOM();
        }
        
        // Redirect to BOM detail page or list
        router.push(isEditMode.value ? `/manufacturing/boms/${bomId}` : '/manufacturing/boms');
      } catch (error) {
        console.error('Error saving BOM:', error);
        
        // Handle validation errors from backend
        if (error.response && error.response.data && error.response.data.errors) {
          const serverErrors = error.response.data.errors;
          Object.keys(serverErrors).forEach(key => {
            errors.value[key] = serverErrors[key][0]; // Display first error message
          });
        } else {
          // Generic error message
          alert('Failed to save BOM. Please try again.');
        }
      } finally {
        isSubmitting.value = false;
      }
    };
    
    // Create a new BOM
    const createBOM = async () => {
      const payload = {
        item_id: form.item_id,
        bom_code: form.bom_code,
        revision: form.revision,
        effective_date: form.effective_date,
        standard_quantity: form.standard_quantity,
        uom_id: form.uom_id,
        status: form.status,
        bom_lines: form.bom_lines
      };
      
      await axios.post('/boms', payload);
    };
    
    // Update an existing BOM
    const updateBOM = async () => {
      // First, update the BOM header
      const bomPayload = {
        item_id: form.item_id,
        bom_code: form.bom_code,
        revision: form.revision,
        effective_date: form.effective_date,
        standard_quantity: form.standard_quantity,
        uom_id: form.uom_id,
        status: form.status
      };
      
      await axios.put(`/boms/${bomId}`, bomPayload);
      
      // Handle BOM lines - update existing, create new ones
      const updatePromises = [];
      
      for (const line of form.bom_lines) {
        const linePayload = {
          item_id: line.item_id,
          quantity: line.quantity,
          uom_id: line.uom_id,
          is_critical: line.is_critical,
          is_yield_based: line.is_yield_based,
          yield_ratio: line.is_yield_based ? line.yield_ratio : null,
          shrinkage_factor: line.is_yield_based ? line.shrinkage_factor : null,
          notes: line.notes
        };
        
        if (line.line_id) {
          // Update existing line
          updatePromises.push(axios.put(`/boms/${bomId}/lines/${line.line_id}`, linePayload));
        } else {
          // Create new line
          updatePromises.push(axios.post(`/boms/${bomId}/lines`, linePayload));
        }
      }
      
      await Promise.all(updatePromises);
    };
    
    // Cancel form submission
    const cancelForm = () => {
      router.push(isEditMode.value ? `/manufacturing/boms/${bomId}` : '/manufacturing/boms');
    };
    
    // Helper functions
    const getItemName = (itemId) => {
      const item = items.value.find(i => i.item_id === itemId);
      return item ? `${item.name} (${item.item_code})` : '';
    };
    
    const getUomSymbol = (uomId) => {
      const uom = uoms.value.find(u => u.uom_id === uomId);
      return uom ? uom.symbol : '';
    };
    
    const formatNumber = (value) => {
      if (value === null || value === undefined) return '-';
      return parseFloat(value).toLocaleString(undefined, { 
        minimumFractionDigits: 0,
        maximumFractionDigits: 4
      });
    };
    
    const formatPercentage = (value) => {
      if (value === null || value === undefined) return '-';
      return (value * 100).toLocaleString(undefined, { 
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
      }) + '%';
    };
    
    const truncateText = (text, maxLength) => {
      if (!text) return '';
      return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    };
    
    onMounted(() => {
      fetchReferenceData();
      fetchBOM();
    });
    
    return {
      isEditMode,
      isLoading,
      isSubmitting,
      form,
      componentForm,
      errors,
      items,
      uoms,
      showAddComponentModal,
      showEditComponentModal,
      editingComponentIndex,
      openAddComponentModal,
      editComponent,
      cancelComponentModal,
      saveComponent,
      removeComponent,
      submitForm,
      cancelForm,
      getItemName,
      getUomSymbol,
      formatNumber,
      formatPercentage,
      selectedItemUOM,
      itemSearch,
      truncateText,
      showDropdown,
      filteredItems,
      selectItem,
      hideDropdown
    };
  }
};
</script>

<style scoped>
.bom-form-container {
  padding: 1rem;
}

.card {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  margin-bottom: 1.5rem;
}

.card-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--gray-200);
}

.card-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--gray-800);
  margin: 0;
}

.card-body {
  padding: 1.5rem;
}

.form-section {
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--gray-200);
}

.form-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--gray-800);
  margin-bottom: 1.25rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: var(--gray-700);
  margin-bottom: 0.5rem;
}

.required {
  color: var(--danger-color);
}

.form-control {
  width: 100%;
  padding: 0.625rem;
  border: 1px solid var(--gray-300);
  border-radius: 0.375rem;
  font-size: 0.875rem;
  transition: border-color 0.2s, box-shadow 0.2s;
  background-color: white;
}

.form-control:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
  outline: none;
}

.form-control.is-invalid {
  border-color: var(--danger-color);
}

.invalid-feedback {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: var(--danger-color);
}

.form-control:disabled {
  background-color: var(--gray-100);
  opacity: 0.75;
}

.form-check {
  display: flex;
  align-items: flex-start;
  margin-bottom: 0.5rem;
}

.form-check-input {
  margin-top: 0.25rem;
  margin-right: 0.5rem;
}

.form-check-label {
  font-weight: 600;
}

.form-text {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: var(--gray-500);
}

.yield-based-fields {
  background-color: rgba(14, 165, 233, 0.05);
  padding: 1rem;
  margin: 1rem 0;
  border-radius: 0.375rem;
  border-left: 4px solid #0ea5e9;
}

.table-container {
  overflow-x: auto;
  margin-top: 1rem;
}

.table {
  width: 100%;
  border-collapse: collapse;
  border: 1px solid var(--gray-200);
  margin-bottom: 1rem;
}

.table th {
  text-align: left;
  padding: 0.75rem 1rem;
  font-weight: 600;
  background-color: var(--gray-100);
  color: var(--gray-700);
  border-bottom: 2px solid var(--gray-200);
  white-space: nowrap;
}

.table td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid var(--gray-200);
  vertical-align: middle;
}

.table tbody tr:hover {
  background-color: var(--gray-50);
}

.action-group {
  display: flex;
  gap: 0.25rem;
  justify-content: center;
}

.action-group .btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}

.input-group {
  display: flex;
  align-items: stretch;
  width: 100%;
}

.input-group .form-control {
  flex: 1;
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}

.input-group-append {
  display: flex;
  align-items: center;
}

.input-group-text {
  padding: 0.625rem 0.75rem;
  background-color: var(--gray-100);
  border: 1px solid var(--gray-300);
  border-left: none;
  border-top-right-radius: 0.375rem;
  border-bottom-right-radius: 0.375rem;
  font-size: 0.875rem;
  color: var(--gray-700);
}

.empty-components {
  text-align: center;
  padding: 3rem 2rem;
  background-color: var(--gray-50);
  border-radius: 0.375rem;
  border: 1px dashed var(--gray-300);
}

.empty-components p {
  color: var(--gray-500);
  margin-bottom: 1rem;
}

.notes-preview {
  display: inline-block;
  max-width: 200px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.text-muted {
  color: var(--gray-500);
}

.text-success {
  color: var(--success-color);
}

.text-info {
  color: #0ea5e9;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 50;
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 50;
}

.modal-content {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 650px;
  max-height: 90vh;
  overflow-y: auto;
  z-index: 60;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--gray-200);
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--gray-800);
}

.close-btn {
  background: transparent;
  border: none;
  color: var(--gray-500);
  cursor: pointer;
  font-size: 1.25rem;
  transition: color 0.2s;
}

.close-btn:hover {
  color: var(--gray-700);
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--gray-200);
}

.row {
  display: flex;
  flex-wrap: wrap;
  margin: -0.5rem;
}

.col-md-6 {
  flex: 0 0 calc(50% - 1rem);
  padding: 0.5rem;
}

@media (max-width: 768px) {
  .row {
    flex-direction: column;
  }
  
  .col-md-6 {
    flex: 0 0 100%;
  }
  
  .form-actions {
    flex-direction: column-reverse;
    gap: 0.75rem;
  }
  
  .form-actions .btn {
    width: 100%;
  }
  
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }
  
  .section-header .btn {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .modal-content {
    width: 95%;
    max-height: 80vh;
  }
}
</style>