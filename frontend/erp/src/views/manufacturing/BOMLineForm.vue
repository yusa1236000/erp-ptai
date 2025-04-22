<!-- src/views/manufacturing/BOMLineForm.vue -->
<template>
    <div class="modal">
      <div class="modal-backdrop" @click="$emit('cancel')"></div>
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ isEditMode ? 'Edit Component' : 'Add Component' }}</h3>
          <button class="close-btn" @click="$emit('cancel')">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitForm">
            <div class="form-group">
              <label for="item_id">Item</label>
              <select id="item_id" v-model="form.item_id" class="form-control" required>
                <option value="">Select Item</option>
                <option v-for="item in items" :key="item.item_id" :value="item.item_id">
                  {{ item.name }} ({{ item.item_code }})
                </option>
              </select>
            </div>
  
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="quantity">Quantity</label>
                  <input 
                    type="number" 
                    id="quantity" 
                    v-model="form.quantity" 
                    class="form-control" 
                    step="0.01" 
                    min="0.01" 
                    required
                  >
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="uom_id">Unit of Measure</label>
                  <select id="uom_id" v-model="form.uom_id" class="form-control" required>
                    <option value="">Select UOM</option>
                    <option v-for="uom in uoms" :key="uom.uom_id" :value="uom.uom_id">
                      {{ uom.name }} ({{ uom.symbol }})
                    </option>
                  </select>
                </div>
              </div>
            </div>
  
            <div class="form-group">
              <div class="form-check">
                <input type="checkbox" id="is_critical" v-model="form.is_critical" class="form-check-input">
                <label for="is_critical" class="form-check-label">Critical Component</label>
                <small class="form-text text-muted">
                  Critical components are essential for the production process
                </small>
              </div>
            </div>
  
            <div class="form-group">
              <label for="notes">Notes</label>
              <textarea id="notes" v-model="form.notes" class="form-control" rows="3"></textarea>
            </div>
  
            <div class="form-actions">
              <button type="button" class="btn btn-secondary" @click="$emit('cancel')">Cancel</button>
              <button 
                type="submit" 
                class="btn btn-primary" 
                :disabled="isSubmitting"
              >
                <i v-if="isSubmitting" class="fas fa-spinner fa-spin mr-1"></i>
                {{ isEditMode ? 'Update' : 'Add' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, reactive, computed, onMounted } from 'vue';
  import axios from 'axios';
  
  export default {
    name: 'BOMLineForm',
    props: {
      bomId: {
        type: [String, Number],
        required: true
      },
      line: {
        type: Object,
        default: null
      }
    },
    emits: ['cancel', 'saved'],
    setup(props, { emit }) {
      const isEditMode = computed(() => !!props.line);
      const isSubmitting = ref(false);
      const items = ref([]);
      const uoms = ref([]);
      
      // Form data
      const form = reactive({
        item_id: '',
        quantity: 1,
        uom_id: '',
        is_critical: false,
        notes: ''
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
      
      // Initialize form if editing
      const initializeForm = () => {
        if (isEditMode.value && props.line) {
          form.item_id = props.line.item_id;
          form.quantity = props.line.quantity;
          form.uom_id = props.line.uom_id;
          form.is_critical = props.line.is_critical;
          form.notes = props.line.notes || '';
        }
      };
      
      // Submit form
      const submitForm = async () => {
        isSubmitting.value = true;
        
        try {
          if (isEditMode.value) {
            // Update existing line
            await axios.put(`/boms/${props.bomId}/lines/${props.line.line_id}`, form);
          } else {
            // Create new line
            await axios.post(`/boms/${props.bomId}/lines`, form);
          }
          
          // Notify parent that save was successful
          emit('saved');
        } catch (error) {
          console.error('Error saving BOM line:', error);
          
          // Display validation errors if available
          if (error.response && error.response.data && error.response.data.errors) {
            const errors = error.response.data.errors;
            const errorMessages = Object.values(errors).flat().join('\n');
            alert(`Please correct the following errors:\n${errorMessages}`);
          } else {
            alert('Failed to save component');
          }
        } finally {
          isSubmitting.value = false;
        }
      };
      
      onMounted(() => {
        fetchReferenceData();
        initializeForm();
      });
      
      return {
        isEditMode,
        isSubmitting,
        form,
        items,
        uoms,
        submitForm
      };
    }
  };
  </script>
  
  <style scoped>
/* Style untuk BOM Line Form Modal */
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
  backdrop-filter: blur(2px);
}

.modal-content {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  width: 100%;
  max-width: 500px;
  z-index: 60;
  overflow: hidden;
  animation: modalFadeIn 0.3s ease-out;
}

.modal-lg {
  max-width: 800px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  background-color: var(--primary-bg);
  border-bottom: 1px solid var(--primary-color);
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--primary-color);
}

.close-btn {
  background: none;
  border: none;
  color: var(--gray-500);
  cursor: pointer;
  font-size: 1.25rem;
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 9999px;
  transition: all 0.2s;
}

.close-btn:hover {
  background-color: rgba(0, 0, 0, 0.05);
  color: var(--gray-700);
}

.modal-body {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  font-weight: 600;
  color: var(--gray-700);
  margin-bottom: 0.5rem;
  display: block;
}

.form-control {
  border-radius: 0.375rem;
  border: 1px solid var(--gray-300);
  padding: 0.75rem;
  width: 100%;
  font-size: 0.95rem;
  transition: all 0.2s;
  background-color: var(--gray-50);
}

.form-control:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
  background-color: white;
  outline: none;
}

select.form-control {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 16px 12px;
  padding-right: 2.5rem;
}

.form-check {
  padding-left: 1.75rem;
  margin-top: 0.75rem;
  margin-bottom: 0.75rem;
}

.form-check-input {
  position: absolute;
  margin-left: -1.75rem;
  width: 1.125rem;
  height: 1.125rem;
  cursor: pointer;
}

.form-check-label {
  cursor: pointer;
  font-weight: 500;
  color: var(--gray-700);
}

.form-text {
  display: block;
  margin-top: 0.375rem;
  font-size: 0.875rem;
  color: var(--gray-500);
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--gray-200);
}

/* Animasi */
@keyframes modalFadeIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

/* Responsive design */
@media (max-width: 640px) {
  .modal-content {
    width: 95%;
    margin: 0 10px;
    max-height: 90vh;
    overflow-y: auto;
  }
  
  .form-actions {
    flex-direction: column-reverse;
    gap: 0.75rem;
  }
  
  .form-actions .btn {
    width: 100%;
  }
}
  </style>