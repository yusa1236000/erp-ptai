<!-- src/views/manufacturing/BOMDetail.vue -->
<template>
  <div class="bom-detail">
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="card-title mb-0">
            <span>BOM Details</span>
            <span :class="getStatusBadgeClass(bom.status)" class="ml-2">{{ bom.status }}</span>
          </h2>
          <div class="btn-group">
            <router-link :to="`/manufacturing/boms/${bom.bom_id}/edit`" class="btn btn-primary">
              <i class="fas fa-edit mr-1"></i> Edit BOM
            </router-link>
            <button @click="confirmDelete" class="btn btn-danger">
              <i class="fas fa-trash mr-1"></i> Delete
            </button>
          </div>
        </div>

        <div v-if="isLoading" class="text-center py-5">
          <i class="fas fa-spinner fa-spin fa-2x"></i>
          <p class="mt-2">Loading BOM details...</p>
        </div>

        <div v-else class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="font-weight-bold">BOM Code:</label>
              <p>{{ bom.bom_code }}</p>
            </div>
            <div class="mb-3">
              <label class="font-weight-bold">Item:</label>
              <p>{{ bom.item?.name || 'N/A' }}</p>
            </div>
            <div class="mb-3">
              <label class="font-weight-bold">Revision:</label>
              <p>{{ bom.revision }}</p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="font-weight-bold">Effective Date:</label>
              <p>{{ formatDate(bom.effective_date) }}</p>
            </div>
            <div class="mb-3">
              <label class="font-weight-bold">Standard Quantity:</label>
              <p>{{ bom.standard_quantity }} {{ bom.unitOfMeasure?.symbol || '' }}</p>
            </div>
            <div class="mb-3">
              <label class="font-weight-bold">Status:</label>
              <p>{{ bom.status }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- BOM Lines -->
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h3 class="card-title mb-0">Components</h3>
          <button @click="showAddLineModal = true" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Add Component
          </button>
        </div>

        <div class="table-container">
          <DataTable
            :columns="lineColumns"
            :items="bomLines"
            :is-loading="isLoadingLines"
            :empty-title="'No Components'"
            :empty-message="'This BOM has no components yet.'"
            :empty-icon="'fas fa-puzzle-piece'"
            keyField="line_id"
          >
            <template #is_critical="{ value }">
              <span v-if="value" class="badge badge-danger">Critical</span>
              <span v-else class="badge badge-secondary">Optional</span>
            </template>
            
            <template #actions="{ item }">
              <div class="btn-group">
                <button @click="editLine(item)" class="btn btn-sm btn-primary">
                  <i class="fas fa-edit"></i>
                </button>
                <button @click="confirmDeleteLine(item)" class="btn btn-sm btn-danger">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </template>
          </DataTable>
        </div>
      </div>
    </div>

    <!-- Delete BOM Confirmation Modal -->
    <ConfirmationModal
      v-if="showDeleteModal"
      title="Delete BOM"
      :message="`Are you sure you want to delete BOM <strong>${bom.bom_code}</strong>?<br>This action cannot be undone.`"
      confirm-button-text="Delete"
      confirm-button-class="btn btn-danger"
      @confirm="deleteBOM"
      @close="showDeleteModal = false"
    />

    <!-- Delete Line Confirmation Modal -->
    <ConfirmationModal
      v-if="showDeleteLineModal"
      title="Delete Component"
      :message="`Are you sure you want to remove <strong>${lineToDelete?.item?.name || 'this component'}</strong> from the BOM?`"
      confirm-button-text="Delete"
      confirm-button-class="btn btn-danger"
      @confirm="deleteLine"
      @close="cancelDeleteLine"
    />

    <!-- Add/Edit BOM Line Modal -->
    <div v-if="showAddLineModal || showEditLineModal" class="modal">
      <div class="modal-backdrop" @click="cancelLineModal"></div>
      <div class="modal-content modal-lg">
        <div class="modal-header">
          <h3>{{ showEditLineModal ? 'Edit Component' : 'Add Component' }}</h3>
          <button class="close-btn" @click="cancelLineModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitLineForm">
            <div class="form-group">
              <label for="item_id">Item</label>
              <select id="item_id" v-model="lineForm.item_id" class="form-control" required>
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
                  <input type="number" id="quantity" v-model="lineForm.quantity" class="form-control" step="0.01" min="0.01" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="uom_id">Unit of Measure</label>
                  <select id="uom_id" v-model="lineForm.uom_id" class="form-control" required>
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
                <input type="checkbox" id="is_critical" v-model="lineForm.is_critical" class="form-check-input">
                <label for="is_critical" class="form-check-label">Critical Component</label>
              </div>
            </div>

            <div class="form-group">
              <label for="notes">Notes</label>
              <textarea id="notes" v-model="lineForm.notes" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-actions">
              <button type="button" class="btn btn-secondary" @click="cancelLineModal">Cancel</button>
              <button type="submit" class="btn btn-primary">{{ showEditLineModal ? 'Update' : 'Add' }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import DataTable from '@/components/common/DataTable.vue';

export default {
  name: 'BOMDetail',
  components: {
    DataTable
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const bomId = route.params.id;
    
    const bom = ref({});
    const bomLines = ref([]);
    const items = ref([]);
    const uoms = ref([]);
    const isLoading = ref(true);
    const isLoadingLines = ref(true);
    
    // Modals
    const showDeleteModal = ref(false);
    const showDeleteLineModal = ref(false);
    const showAddLineModal = ref(false);
    const showEditLineModal = ref(false);
    const lineToDelete = ref(null);
    
    // Form for BOM line
    const lineForm = reactive({
      item_id: '',
      quantity: 1,
      uom_id: '',
      is_critical: false,
      notes: ''
    });
    
    const lineColumns = [
      { key: 'item.name', label: 'Item', sortable: true },
      { key: 'item.item_code', label: 'Item Code', sortable: true },
      { key: 'quantity', label: 'Quantity', sortable: true },
      { key: 'unitOfMeasure.symbol', label: 'UOM', sortable: true },
      { key: 'is_critical', label: 'Critical', template: 'is_critical', class: 'text-center' },
      { key: 'notes', label: 'Notes' },
      { key: 'actions', label: 'Actions', template: 'actions', class: 'text-right' }
    ];
    
    const fetchBOM = async () => {
      isLoading.value = true;
      
      try {
        const response = await axios.get(`/boms/${bomId}`);
        bom.value = response.data.data;
        
        // If BOM lines are included in the response
        if (bom.value.bom_lines) {
          bomLines.value = bom.value.bom_lines;
          isLoadingLines.value = false;
        } else {
          fetchBOMLines();
        }
      } catch (error) {
        console.error('Error fetching BOM:', error);
      } finally {
        isLoading.value = false;
      }
    };
    
    const fetchBOMLines = async () => {
      isLoadingLines.value = true;
      
      try {
        const response = await axios.get(`/boms/${bomId}/lines`);
        bomLines.value = response.data.data;
      } catch (error) {
        console.error('Error fetching BOM lines:', error);
      } finally {
        isLoadingLines.value = false;
      }
    };
    
    const fetchItems = async () => {
      try {
        const response = await axios.get('/items');
        items.value = response.data.data || response.data;
      } catch (error) {
        console.error('Error fetching items:', error);
      }
    };
    
    const fetchUOMs = async () => {
      try {
        const response = await axios.get('/unit-of-measures');
        uoms.value = response.data.data || response.data;
      } catch (error) {
        console.error('Error fetching UOMs:', error);
      }
    };
    
    const getStatusBadgeClass = (status) => {
      const classes = 'badge ';
      switch (status?.toLowerCase()) {
        case 'draft': return classes + 'badge-secondary';
        case 'active': return classes + 'badge-success';
        case 'inactive': return classes + 'badge-warning';
        case 'obsolete': return classes + 'badge-danger';
        default: return classes + 'badge-secondary';
      }
    };
    
    const formatDate = (dateString) => {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleDateString();
    };
    
    const confirmDelete = () => {
      showDeleteModal.value = true;
    };
    
    const deleteBOM = async () => {
      try {
        await axios.delete(`/boms/${bomId}`);
        router.push('/manufacturing/boms');
      } catch (error) {
        console.error('Error deleting BOM:', error);
        
        // Show error message if from backend
        if (error.response && error.response.data && error.response.data.message) {
          alert(error.response.data.message);
        } else {
          alert('Failed to delete BOM. It might be in use or there was a server error.');
        }
        
        showDeleteModal.value = false;
      }
    };
    
    const confirmDeleteLine = (line) => {
      lineToDelete.value = line;
      showDeleteLineModal.value = true;
    };
    
    const cancelDeleteLine = () => {
      lineToDelete.value = null;
      showDeleteLineModal.value = false;
    };
    
    const deleteLine = async () => {
      if (!lineToDelete.value) return;
      
      try {
        await axios.delete(`/boms/${bomId}/lines/${lineToDelete.value.line_id}`);
        fetchBOMLines();
      } catch (error) {
        console.error('Error deleting BOM line:', error);
        alert('Failed to delete component');
      } finally {
        cancelDeleteLine();
      }
    };
    
    const openAddLineModal = () => {
      // Reset form
      Object.keys(lineForm).forEach(key => {
        if (key === 'quantity') {
          lineForm[key] = 1;
        } else if (key === 'is_critical') {
          lineForm[key] = false;
        } else {
          lineForm[key] = '';
        }
      });
      
      showAddLineModal.value = true;
    };
    
    const editLine = (line) => {
      // Populate form with line data
      lineForm.item_id = line.item_id;
      lineForm.quantity = line.quantity;
      lineForm.uom_id = line.uom_id;
      lineForm.is_critical = line.is_critical;
      lineForm.notes = line.notes || '';
      
      lineToDelete.value = line; // Reuse this ref to hold the line being edited
      showEditLineModal.value = true;
    };
    
    const cancelLineModal = () => {
      showAddLineModal.value = false;
      showEditLineModal.value = false;
      lineToDelete.value = null;
    };
    
    const submitLineForm = async () => {
      try {
        if (showEditLineModal.value) {
          // Update existing line
          await axios.put(`/boms/${bomId}/lines/${lineToDelete.value.line_id}`, lineForm);
        } else {
          // Add new line
          await axios.post(`/boms/${bomId}/lines`, lineForm);
        }
        
        // Refresh BOM lines
        fetchBOMLines();
        cancelLineModal();
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
      }
    };
    
    onMounted(() => {
      fetchBOM();
      fetchItems();
      fetchUOMs();
    });
    
    return {
      bom,
      bomLines,
      items,
      uoms,
      isLoading,
      isLoadingLines,
      lineColumns,
      showDeleteModal,
      showDeleteLineModal,
      showAddLineModal,
      showEditLineModal,
      lineToDelete,
      lineForm,
      getStatusBadgeClass,
      formatDate,
      confirmDelete,
      deleteBOM,
      confirmDeleteLine,
      cancelDeleteLine,
      deleteLine,
      openAddLineModal,
      editLine,
      cancelLineModal,
      submitLineForm
    };
  }
};
</script>

<style scoped>
.badge {
  padding: 0.5em 0.75em;
  border-radius: 0.375rem;
  font-weight: 500;
  font-size: 0.75rem;
}

label.font-weight-bold {
  font-weight: 600;
  color: var(--gray-600);
  margin-bottom: 0.25rem;
  display: block;
}

p {
  margin-bottom: 0;
  color: var(--gray-800);
}

.btn-group {
  display: flex;
  gap: 0.5rem;
}

.card-title {
  display: flex;
  align-items: center;
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
  max-width: 800px;
  z-index: 60;
  overflow: hidden;
}

.modal-lg {
  max-width: 800px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--gray-200);
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.close-btn {
  background: none;
  border: none;
  color: var(--gray-500);
  cursor: pointer;
  font-size: 1.25rem;
}

.modal-body {
  padding: 1.5rem;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
}
</style>