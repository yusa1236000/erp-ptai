<!-- src/views/manufacturing/BOMForm.vue -->
<template>
    <div class="bom-form">
      <div class="card">
        <div class="card-body">
          <h2 class="card-title mb-4">{{ isEditMode ? 'Edit BOM' : 'Create New BOM' }}</h2>
          
          <form @submit.prevent="submitForm">
            <div class="row">
              <!-- BOM Header Section -->
              <div class="col-md-6">
                <div class="form-group">
                  <label for="item_id">Item</label>
                  <select 
                    id="item_id" 
                    v-model="form.item_id" 
                    class="form-control" 
                    required
                    :disabled="isEditMode"
                  >
                    <option value="">Select Item</option>
                    <option v-for="item in items" :key="item.item_id" :value="item.item_id">
                      {{ item.name }} ({{ item.item_code }})
                    </option>
                  </select>
                </div>
                
                <div class="form-group">
                  <label for="bom_code">BOM Code</label>
                  <input 
                    type="text" 
                    id="bom_code" 
                    v-model="form.bom_code" 
                    class="form-control" 
                    required
                    maxlength="50"
                  />
                </div>
                
                <div class="form-group">
                  <label for="revision">Revision</label>
                  <input 
                    type="text" 
                    id="revision" 
                    v-model="form.revision" 
                    class="form-control" 
                    required
                    maxlength="10"
                  />
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="form-group">
                  <label for="effective_date">Effective Date</label>
                  <input 
                    type="date" 
                    id="effective_date" 
                    v-model="form.effective_date" 
                    class="form-control" 
                    required
                  />
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="standard_quantity">Standard Quantity</label>
                      <input 
                        type="number" 
                        id="standard_quantity" 
                        v-model="form.standard_quantity" 
                        class="form-control" 
                        required
                        step="0.01"
                        min="0.01"
                      />
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="uom_id">Unit of Measure</label>
                      <select 
                        id="uom_id" 
                        v-model="form.uom_id" 
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
                
                <div class="form-group">
                  <label for="status">Status</label>
                  <select 
                    id="status" 
                    v-model="form.status" 
                    class="form-control" 
                    required
                  >
                    <option value="draft">Draft</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="obsolete">Obsolete</option>
                  </select>
                </div>
              </div>
            </div>
            
            <!-- BOM Lines Section -->
            <div class="bom-lines mt-4">
              <h4 class="mb-3">Components</h4>
              
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th style="width: 35%">Item</th>
                      <th style="width: 15%">Quantity</th>
                      <th style="width: 20%">UOM</th>
                      <th style="width: 10%">Critical</th>
                      <th style="width: 15%">Notes</th>
                      <th style="width: 5%"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(line, index) in form.bom_lines" :key="index">
                      <td>
                        <select 
                          v-model="line.item_id" 
                          class="form-control" 
                          required
                        >
                          <option value="">Select Item</option>
                          <option v-for="item in items" :key="item.item_id" :value="item.item_id">
                            {{ item.name }} ({{ item.item_code }})
                          </option>
                        </select>
                      </td>
                      <td>
                        <input 
                          type="number" 
                          v-model="line.quantity" 
                          class="form-control" 
                          required
                          step="0.01"
                          min="0.01"
                        />
                      </td>
                      <td>
                        <select 
                          v-model="line.uom_id" 
                          class="form-control" 
                          required
                        >
                          <option value="">Select UOM</option>
                          <option v-for="uom in uoms" :key="uom.uom_id" :value="uom.uom_id">
                            {{ uom.name }} ({{ uom.symbol }})
                          </option>
                        </select>
                      </td>
                      <td class="text-center">
                        <div class="form-check d-flex justify-content-center">
                          <input 
                            type="checkbox" 
                            v-model="line.is_critical" 
                            class="form-check-input" 
                          />
                        </div>
                      </td>
                      <td>
                        <input 
                          type="text" 
                          v-model="line.notes" 
                          class="form-control" 
                        />
                      </td>
                      <td class="text-center">
                        <button 
                          type="button" 
                          class="btn btn-sm btn-danger" 
                          @click="removeLine(index)"
                        >
                          <i class="fas fa-trash"></i>
                        </button>
                      </td>
                    </tr>
                    
                    <!-- Empty state -->
                    <tr v-if="form.bom_lines.length === 0">
                      <td colspan="6" class="text-center p-4">
                        <p class="text-muted mb-0">No components added yet</p>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="6">
                        <button 
                          type="button" 
                          class="btn btn-secondary" 
                          @click="addLine"
                        >
                          <i class="fas fa-plus mr-1"></i> Add Component
                        </button>
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
            
            <div class="form-actions mt-4">
              <button type="button" class="btn btn-secondary mr-2" @click="cancelForm">
                Cancel
              </button>
              <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                <i v-if="isSubmitting" class="fas fa-spinner fa-spin mr-1"></i>
                {{ isEditMode ? 'Update BOM' : 'Create BOM' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, reactive, onMounted, computed } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import axios from 'axios';
  
  export default {
    name: 'BOMForm',
    setup() {
      const route = useRoute();
      const router = useRouter();
      const bomId = route.params.id;
      const isEditMode = computed(() => !!bomId);
      
      const items = ref([]);
      const uoms = ref([]);
      const isLoading = ref(false);
      const isSubmitting = ref(false);
      
      // Form data
      const form = reactive({
        item_id: '',
        bom_code: '',
        revision: '',
        effective_date: new Date().toISOString().substr(0, 10), // Default to today
        standard_quantity: 1,
        uom_id: '',
        status: 'draft',
        bom_lines: []
      });
      
      // Fetch reference data (items, UOMs)
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
          
          // Fetch BOM lines
          if (!bomData.bom_lines) {
            await fetchBOMLines();
          } else {
            form.bom_lines = bomData.bom_lines.map(line => ({
              item_id: line.item_id,
              quantity: line.quantity,
              uom_id: line.uom_id,
              is_critical: line.is_critical,
              notes: line.notes || '',
              line_id: line.line_id // Keep track of existing line IDs
            }));
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
            item_id: line.item_id,
            quantity: line.quantity,
            uom_id: line.uom_id,
            is_critical: line.is_critical,
            notes: line.notes || '',
            line_id: line.line_id // Keep track of existing line IDs
          }));
        } catch (error) {
          console.error('Error fetching BOM lines:', error);
        }
      };
      
      // Add a new empty BOM line
      const addLine = () => {
        form.bom_lines.push({
          item_id: '',
          quantity: 1,
          uom_id: '',
          is_critical: false,
          notes: ''
        });
      };
      
      // Remove a BOM line
      const removeLine = (index) => {
        form.bom_lines.splice(index, 1);
      };
      
      // Submit the form
      const submitForm = async () => {
        isSubmitting.value = true;
        
        try {
          if (isEditMode.value) {
            // Update existing BOM
            await updateBOM();
          } else {
            // Create new BOM
            await createBOM();
          }
          
          // Redirect to BOM list
          router.push('/manufacturing/boms');
        } catch (error) {
          console.error('Error saving BOM:', error);
          
          // Display validation errors if available
          if (error.response && error.response.data && error.response.data.errors) {
            const errors = error.response.data.errors;
            const errorMessages = Object.values(errors).flat().join('\n');
            alert(`Please correct the following errors:\n${errorMessages}`);
          } else {
            alert('Failed to save BOM');
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
        
        const response = await axios.post('/boms', payload);
        return response.data;
      };
      
      // Update an existing BOM
      const updateBOM = async () => {
        // First, update the BOM itself
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
        
        // Then, handle the BOM lines
        for (const line of form.bom_lines) {
          if (line.line_id) {
            // Update existing line
            const linePayload = {
              item_id: line.item_id,
              quantity: line.quantity,
              uom_id: line.uom_id,
              is_critical: line.is_critical,
              notes: line.notes
            };
            
            await axios.put(`/boms/${bomId}/lines/${line.line_id}`, linePayload);
          } else {
            // Create new line
            const linePayload = {
              item_id: line.item_id,
              quantity: line.quantity,
              uom_id: line.uom_id,
              is_critical: line.is_critical,
              notes: line.notes
            };
            
            await axios.post(`/boms/${bomId}/lines`, linePayload);
          }
        }
      };
      
      // Cancel form submission
      const cancelForm = () => {
        router.push(isEditMode.value ? `/manufacturing/boms/${bomId}` : '/manufacturing/boms');
      };
      
      onMounted(() => {
        fetchReferenceData();
        fetchBOM();
      });
      
      return {
        isEditMode,
        form,
        items,
        uoms,
        isLoading,
        isSubmitting,
        addLine,
        removeLine,
        submitForm,
        cancelForm
      };
    }
  };
  </script>
  
  <style scoped>
/* Style untuk BOM Form */
.bom-form .card {
  border-radius: 0.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: box-shadow 0.3s ease;
  margin-bottom: 1.5rem;
}

.bom-form .card:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.bom-form .card-title {
  font-size: 1.5rem;
  color: var(--gray-800);
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--gray-200);
}

.bom-form .form-group {
  margin-bottom: 1.5rem;
}

.bom-form label {
  font-weight: 600;
  color: var(--gray-700);
  margin-bottom: 0.5rem;
  display: block;
}

.bom-form .form-control {
  border-radius: 0.375rem;
  border: 1px solid var(--gray-300);
  padding: 0.625rem;
  transition: border-color 0.2s, box-shadow 0.2s;
  background-color: var(--gray-50);
}

.bom-form .form-control:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
  background-color: white;
}

.bom-form .form-control:disabled {
  background-color: var(--gray-100);
  opacity: 0.75;
}

.bom-form .bom-lines {
  border-top: 1px solid var(--gray-200);
  padding-top: 1.5rem;
  margin-top: 2rem;
}

.bom-form .bom-lines h4 {
  font-size: 1.25rem;
  color: var(--gray-800);
  margin-bottom: 1rem;
}

.bom-form .table {
  border: 1px solid var(--gray-200);
  border-radius: 0.375rem;
  overflow: hidden;
}

.bom-form .table th {
  background-color: var(--gray-100);
  color: var(--gray-700);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.05em;
  padding: 0.75rem 1rem;
}

.bom-form .table td {
  vertical-align: middle;
  padding: 0.75rem 0.5rem;
}

.bom-form .table tr:hover {
  background-color: var(--gray-50);
}

.bom-form .table tfoot td {
  background-color: var(--gray-50);
  padding: 1rem;
}

.bom-form .form-check-input {
  width: 1.125rem;
  height: 1.125rem;
}

.bom-form .btn-sm {
  padding: 0.375rem;
  border-radius: 0.25rem;
}

.bom-form .form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--gray-200);
}

.bom-form .text-muted {
  color: var(--gray-500);
  font-style: italic;
}

/* Animasi untuk transisi */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.bom-form .table tbody tr {
  animation: fadeIn 0.3s ease-out;
}

/* Responsive design */
@media (max-width: 768px) {
  .bom-form .table-responsive {
    border-radius: 0.375rem;
    border: 1px solid var(--gray-200);
  }
  
  .bom-form .table {
    border: none;
  }
  
  .bom-form .form-actions {
    flex-direction: column-reverse;
    gap: 0.75rem;
  }
  
  .bom-form .btn {
    width: 100%;
  }
}
  </style>