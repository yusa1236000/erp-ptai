<!-- src/views/manufacturing/BOMDetail.vue -->
<template>
  <div class="bom-detail-container">
    <!-- BOM Header Section -->
    <div class="card mb-4">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h2 class="card-title">
            <span>{{ bom.bom_code }}</span>
            <span :class="getStatusBadgeClass(bom.status)" class="status-badge ml-2">{{ bom.status }}</span>
          </h2>
          <div class="action-buttons">
            <router-link :to="`/manufacturing/boms/${bom.bom_id}/edit`" class="btn btn-primary">
              <i class="fas fa-edit mr-1"></i> Edit
            </router-link>
            <button @click="confirmDelete" class="btn btn-danger">
              <i class="fas fa-trash mr-1"></i> Delete
            </button>
          </div>
        </div>
      </div>
      
      <div class="card-body">
        <div v-if="isLoading" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Loading BOM details...</p>
        </div>
        
        <div v-else class="bom-details">
          <div class="row">
            <div class="col-md-6">
              <div class="detail-item">
                <label>Item:</label>
                <p>{{ bom.item?.name || 'N/A' }} {{ bom.item?.item_code ? `(${bom.item.item_code})` : '' }}</p>
              </div>
              
              <div class="detail-item">
                <label>Revision:</label>
                <p>{{ bom.revision || 'N/A' }}</p>
              </div>
              
              <div class="detail-item">
                <label>Effective Date:</label>
                <p>{{ formatDate(bom.effective_date) }}</p>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="detail-item">
                <label>Standard Quantity:</label>
                <p>{{ bom.standard_quantity || 0 }} {{ bom.unitOfMeasure?.symbol || '' }}</p>
              </div>
              
              <div class="detail-item">
                <label>Status:</label>
                <p>{{ bom.status || 'N/A' }}</p>
              </div>
              
              <div class="detail-item">
                <label>BOM Type:</label>
                <p>
                  <span v-if="hasYieldBasedComponents" class="badge badge-info">
                    <i class="fas fa-flask mr-1"></i> Yield-Based
                  </span>
                  <span v-else class="badge badge-secondary">Standard</span>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Maximum Production Capacity Card (for yield-based BOMs) -->
    <div v-if="hasYieldBasedComponents" class="card mb-4">
      <div class="card-header">
        <h3 class="card-subtitle">Maximum Production Capacity</h3>
      </div>
      <div class="card-body">
        <div v-if="isLoadingCapacity" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Calculating maximum production capacity...</p>
        </div>
        <div v-else-if="!capacityData" class="text-center py-4">
          <button @click="calculateCapacity" class="btn btn-primary">
            <i class="fas fa-calculator mr-1"></i> Calculate Maximum Capacity
          </button>
        </div>
        <div v-else class="production-capacity">
          <div class="capacity-result">
            <div class="capacity-value">
              <span class="number">{{ formatNumber(capacityData.maximum_yield) }}</span>
              <span class="unit">{{ capacityData.finished_product?.uom }}</span>
            </div>
            <p class="capacity-label">Maximum Possible Production</p>
          </div>
          
          <h4 class="mt-4 mb-3">Based on Current Materials Stock:</h4>
          <div class="table-container">
            <table class="table">
              <thead>
                <tr>
                  <th>Material</th>
                  <th>Current Stock</th>
                  <th>Yield Ratio</th>
                  <th>Potential Yield</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="material in capacityData.materials" :key="material.item_id">
                  <td>{{ material.item_name }} ({{ material.item_code }})</td>
                  <td>{{ formatNumber(material.current_stock) }} {{ material.uom }}</td>
                  <td>{{ material.yield_ratio ? formatNumber(material.yield_ratio) : 'N/A' }}</td>
                  <td>{{ formatNumber(material.potential_yield) }}</td>
                  <td>
                    <span 
                      class="badge"
                      :class="isLimitingMaterial(material) ? 'badge-danger' : 'badge-success'"
                    >
                      {{ isLimitingMaterial(material) ? 'Limiting Material' : 'Sufficient' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- BOM Components Section -->
    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-subtitle">Components</h3>
          <button @click="showAddLineModal = true" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Add Component
          </button>
        </div>
      </div>
      
      <div class="card-body">
        <div class="table-container">
          <table class="table" v-if="!isLoadingLines && bomLines.length > 0">
            <thead>
              <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>UOM</th>
                <th>Critical</th>
                <th v-if="hasYieldBasedComponents">Yield-Based</th>
                <th v-if="hasYieldBasedComponents">Yield Ratio</th>
                <th>Notes</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="line in bomLines" :key="line.line_id">
                <td>{{ line.item?.name }} ({{ line.item?.item_code }})</td>
                <td>{{ formatNumber(line.quantity) }}</td>
                <td>{{ line.unitOfMeasure?.symbol }}</td>
                <td class="text-center">
                  <span v-if="line.is_critical" class="badge badge-danger">Yes</span>
                  <span v-else class="badge badge-secondary">No</span>
                </td>
                <td class="text-center" v-if="hasYieldBasedComponents">
                  <span v-if="line.is_yield_based" class="badge badge-info">Yes</span>
                  <span v-else class="badge badge-secondary">No</span>
                </td>
                <td v-if="hasYieldBasedComponents">
                  {{ line.is_yield_based ? formatNumber(line.yield_ratio) : 'N/A' }}
                </td>
                <td>{{ line.notes || '-' }}</td>
                <td>
                  <div class="action-group">
                    <button @click="editLine(line)" class="btn btn-sm btn-primary">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button @click="confirmDeleteLine(line)" class="btn btn-sm btn-danger">
                      <i class="fas fa-trash"></i>
                    </button>
                    <button 
                      v-if="line.is_yield_based" 
                      @click="calculateYield(line)" 
                      class="btn btn-sm btn-info"
                      title="Calculate potential yield"
                    >
                      <i class="fas fa-calculator"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          
          <div v-else-if="isLoadingLines" class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading components...</p>
          </div>
          
          <div v-else class="empty-state">
            <i class="fas fa-boxes"></i>
            <p>No components found for this BOM.</p>
            <button @click="showAddLineModal = true" class="btn btn-primary mt-3">
              <i class="fas fa-plus mr-1"></i> Add First Component
            </button>
          </div>
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

    <!-- Yield Calculation Result Modal -->
    <div v-if="showYieldModal" class="modal">
      <div class="modal-backdrop" @click="showYieldModal = false"></div>
      <div class="modal-content">
        <div class="modal-header">
          <h3>Yield Calculation Result</h3>
          <button class="close-btn" @click="showYieldModal = false">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <div v-if="yieldResult" class="yield-result">
            <div class="result-section">
              <h4>Material</h4>
              <div class="detail-row">
                <label>Item:</label>
                <p>{{ yieldResult.material.item_name }} ({{ yieldResult.material.item_code }})</p>
              </div>
              <div class="detail-row">
                <label>Quantity:</label>
                <p>{{ formatNumber(yieldResult.material.quantity) }} {{ yieldResult.material.uom }}</p>
              </div>
            </div>
            
            <div class="result-section">
              <h4>Finished Product</h4>
              <div class="detail-row">
                <label>Item:</label>
                <p>{{ yieldResult.finished_product.item_name }} ({{ yieldResult.finished_product.item_code }})</p>
              </div>
              <div class="detail-row highlight">
                <label>Yield Quantity:</label>
                <p>{{ formatNumber(yieldResult.finished_product.yield_quantity) }} {{ yieldResult.finished_product.uom }}</p>
              </div>
            </div>
            
            <div class="result-section">
              <h4>Yield Parameters</h4>
              <div class="detail-row">
                <label>Yield Ratio:</label>
                <p>{{ formatNumber(yieldResult.yield_ratio) }}</p>
              </div>
              <div class="detail-row" v-if="yieldResult.shrinkage_factor > 0">
                <label>Shrinkage Factor:</label>
                <p>{{ formatPercentage(yieldResult.shrinkage_factor) }}</p>
              </div>
            </div>
            
            <div class="formula-explanation">
              <p>
                <strong>Formula:</strong> Material Quantity × Yield Ratio × (1 - Shrinkage Factor) = Yield Quantity
              </p>
              <p>
                {{ formatNumber(yieldResult.material.quantity) }} × 
                {{ formatNumber(yieldResult.yield_ratio) }} × 
                (1 - {{ formatNumber(yieldResult.shrinkage_factor) }}) = 
                {{ formatNumber(yieldResult.finished_product.yield_quantity) }}
              </p>
            </div>
          </div>
          
          <div v-else class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Calculating yield...</p>
          </div>
          
          <div class="modal-footer">
            <button @click="showYieldModal = false" class="btn btn-primary">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit BOM Line Modal -->
    <BOMLineFormModal
      v-if="showAddLineModal || showEditLineModal"
      :is-edit-mode="showEditLineModal"
      :line-data="lineToEdit"
      :bom-id="Number(bom.bom_id)"
      @save="saveLineForm"
      @close="cancelLineModal"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import BOMLineFormModal from '@/components/manufacturing/BOMLineFormModal.vue';

export default {
  name: 'BOMDetail',
  components: {
    BOMLineFormModal
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const bomId = route.params.id;
    
    // Data
    const bom = ref({});
    const bomLines = ref([]);
    const isLoading = ref(true);
    const isLoadingLines = ref(true);
    const isLoadingCapacity = ref(false);
    const capacityData = ref(null);
    
    // Modals state
    const showDeleteModal = ref(false);
    const showDeleteLineModal = ref(false);
    const showAddLineModal = ref(false);
    const showEditLineModal = ref(false);
    const showYieldModal = ref(false);
    
    // Temp storage for modal operations
    const lineToDelete = ref(null);
    const lineToEdit = ref({});
    const yieldResult = ref(null);
    
    // Computed
    const hasYieldBasedComponents = computed(() => {
      return bomLines.value.some(line => line.is_yield_based);
    });
    
    // Fetch BOM data
    const fetchBOM = async () => {
      isLoading.value = true;
      
      try {
        const response = await axios.get(`/boms/${bomId}`);
        bom.value = response.data.data;
        
        // If BOM lines are included in the response
        if (bom.value.bomLines) {
          bomLines.value = bom.value.bomLines;
          isLoadingLines.value = false;
        } else {
          await fetchBOMLines();
        }
      } catch (error) {
        console.error('Error fetching BOM:', error);
      } finally {
        isLoading.value = false;
      }
    };
    
    // Fetch BOM lines
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
    
    // Calculate maximum production capacity
    const calculateCapacity = async () => {
      isLoadingCapacity.value = true;
      
      try {
        const response = await axios.get(`/boms/${bomId}/maximum-yield`);
        capacityData.value = response.data.data;
      } catch (error) {
        console.error('Error calculating capacity:', error);
      } finally {
        isLoadingCapacity.value = false;
      }
    };
    
    // Calculate yield for a specific component
    const calculateYield = async (line) => {
      if (!line.is_yield_based) return;
      
      showYieldModal.value = true;
      yieldResult.value = null;
      
      try {
        // Show form to input material quantity
        const materialQuantity = prompt('Enter material quantity for yield calculation:', line.quantity);
        
        if (materialQuantity === null) {
          // User cancelled
          showYieldModal.value = false;
          return;
        }
        
        const quantity = parseFloat(materialQuantity);
        if (isNaN(quantity) || quantity <= 0) {
          alert('Please enter a valid quantity greater than zero.');
          showYieldModal.value = false;
          return;
        }
        
        const response = await axios.post(`/boms/${bomId}/lines/${line.line_id}/calculate-yield`, {
          material_quantity: quantity
        });
        
        yieldResult.value = response.data.data;
      } catch (error) {
        console.error('Error calculating yield:', error);
        showYieldModal.value = false;
        alert('Failed to calculate yield.');
      }
    };
    
    // Check if this material is the limiting factor
    const isLimitingMaterial = (material) => {
      if (!capacityData.value || !capacityData.value.maximum_yield) return false;
      
      // The limiting material is the one whose potential yield equals the maximum yield
      return Math.abs(material.potential_yield - capacityData.value.maximum_yield) < 0.001;
    };
    
    // Delete operations
    const confirmDelete = () => {
      showDeleteModal.value = true;
    };
    
    const deleteBOM = async () => {
      try {
        await axios.delete(`/boms/${bomId}`);
        router.push('/manufacturing/boms');
      } catch (error) {
        console.error('Error deleting BOM:', error);
        
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
        await fetchBOMLines();
      } catch (error) {
        console.error('Error deleting BOM line:', error);
        alert('Failed to delete component');
      } finally {
        cancelDeleteLine();
      }
    };
    
    // Line form operations
    const editLine = (line) => {
      lineToEdit.value = { ...line };
      showEditLineModal.value = true;
    };
    
    const cancelLineModal = () => {
      showAddLineModal.value = false;
      showEditLineModal.value = false;
      lineToEdit.value = {};
    };
    
    const saveLineForm = async (formData) => {
      try {
        if (showEditLineModal.value) {
          // Update existing line
          await axios.put(`/boms/${bomId}/lines/${formData.line_id}`, formData);
        } else {
          // Add new line
          await axios.post(`/boms/${bomId}/lines`, formData);
        }
        
        // Refresh BOM lines
        await fetchBOMLines();
        cancelLineModal();
      } catch (error) {
        console.error('Error saving BOM line:', error);
        
        if (error.response && error.response.data && error.response.data.errors) {
          const errors = error.response.data.errors;
          const errorMessages = Object.values(errors).flat().join('\n');
          alert(`Please correct the following errors:\n${errorMessages}`);
        } else {
          alert('Failed to save component');
        }
      }
    };
    
    // Helper functions
    const getStatusBadgeClass = (status) => {
      if (!status) return 'badge-secondary';
      
      const statusLower = status.toLowerCase();
      if (statusLower === 'draft') return 'badge-secondary';
      if (statusLower === 'active') return 'badge-success';
      if (statusLower === 'inactive') return 'badge-warning';
      if (statusLower === 'obsolete') return 'badge-danger';
      return 'badge-secondary';
    };
    
    const formatDate = (dateString) => {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
    };
    
    const formatNumber = (value) => {
      if (value === null || value === undefined) return 'N/A';
      return parseFloat(value).toLocaleString(undefined, { 
        minimumFractionDigits: 0,
        maximumFractionDigits: 4
      });
    };
    
    const formatPercentage = (value) => {
      if (value === null || value === undefined) return 'N/A';
      return (value * 100).toLocaleString(undefined, { 
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
      }) + '%';
    };
    
    onMounted(() => {
      fetchBOM();
    });
    
    return {
      bom,
      bomLines,
      isLoading,
      isLoadingLines,
      isLoadingCapacity,
      capacityData,
      hasYieldBasedComponents,
      showDeleteModal,
      showDeleteLineModal,
      showAddLineModal,
      showEditLineModal,
      showYieldModal,
      lineToDelete,
      lineToEdit,
      yieldResult,
      calculateCapacity,
      calculateYield,
      isLimitingMaterial,
      getStatusBadgeClass,
      formatDate,
      formatNumber,
      formatPercentage,
      confirmDelete,
      deleteBOM,
      confirmDeleteLine,
      cancelDeleteLine,
      deleteLine,
      editLine,
      cancelLineModal,
      saveLineForm
    };
  }
};
</script>

<style scoped>
.bom-detail-container {
  padding: 1rem;
}

.card {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: box-shadow 0.3s ease;
  margin-bottom: 1.5rem;
}

.card:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
  display: flex;
  align-items: center;
}

.card-subtitle {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--gray-800);
  margin: 0;
}

.card-body {
  padding: 1.5rem;
}

.status-badge {
  margin-left: 0.75rem;
  padding: 0.35em 0.65em;
  font-size: 0.75rem;
  font-weight: 500;
  color: white;
  border-radius: 0.375rem;
}

.badge {
  display: inline-block;
  padding: 0.35em 0.65em;
  font-size: 0.75rem;
  font-weight: 500;
  color: white;
  text-align: center;
  white-space: nowrap;
  vertical-align: baseline;
  border-radius: 0.375rem;
}

.badge-secondary {
  background-color: var(--gray-500);
}

.badge-success {
  background-color: var(--success-color);
}

.badge-info {
  background-color: #0ea5e9;
}

.badge-warning {
  background-color: var(--warning-color);
}

.badge-danger {
  background-color: var(--danger-color);
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.action-group {
  display: flex;
  gap: 0.25rem;
}

.action-group .btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

.bom-details {
  margin-top: 1rem;
}

.detail-item {
  margin-bottom: 1rem;
}

.detail-item label {
  display: block;
  font-weight: 600;
  color: var(--gray-600);
  margin-bottom: 0.25rem;
  font-size: 0.875rem;
}

.detail-item p {
  margin: 0;
  color: var(--gray-800);
}

.table-container {
  overflow-x: auto;
  margin-top: 1rem;
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table th {
  text-align: left;
  padding: 0.75rem 1rem;
  font-weight: 600;
  color: var(--gray-700);
  background-color: var(--gray-100);
  border-bottom: 2px solid var(--gray-200);
}

.table td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid var(--gray-200);
  vertical-align: middle;
}

.table tbody tr:hover {
  background-color: var(--gray-50);
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
  color: var(--gray-500);
}

.loading-state i {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.loading-state p {
  margin: 0;
  font-size: 1rem;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
  color: var(--gray-500);
  text-align: center;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.empty-state p {
  margin: 0 0 1rem;
  font-size: 1rem;
}

.production-capacity {
  padding: 1rem 0;
}

.capacity-result {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin-bottom: 2rem;
}

.capacity-value {
  display: flex;
  align-items: baseline;
  font-size: 3rem;
  font-weight: 700;
  color: var(--primary-color);
}

.capacity-value .unit {
  font-size: 1.5rem;
  margin-left: 0.25rem;
  color: var(--gray-600);
}

.capacity-label {
  margin-top: 0.5rem;
  font-size: 1.125rem;
  color: var(--gray-700);
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
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--gray-200);
}

.yield-result {
  color: var(--gray-800);
}

.result-section {
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--gray-200);
}

.result-section h4 {
  font-size: 1.125rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
  color: var(--gray-700);
}

.detail-row {
  display: flex;
  margin-bottom: 0.5rem;
}

.detail-row label {
  font-weight: 600;
  width: 150px;
  color: var(--gray-600);
}

.detail-row p {
  margin: 0;
}

.highlight {
  background-color: rgba(14, 165, 233, 0.1);
  padding: 0.5rem;
  border-radius: 0.25rem;
  margin-bottom: 0.75rem;
}

.formula-explanation {
  background-color: var(--gray-50);
  padding: 1rem;
  border-radius: 0.375rem;
  margin-top: 1rem;
}

.formula-explanation p {
  margin: 0 0 0.5rem;
  font-size: 0.875rem;
}

.formula-explanation p:last-child {
  margin-bottom: 0;
  font-family: monospace;
  white-space: nowrap;
  overflow-x: auto;
  padding-bottom: 0.25rem;
}

@media (max-width: 768px) {
  .card-title {
    font-size: 1.25rem;
  }
  
  .action-buttons {
    flex-direction: column;
    margin-top: 1rem;
  }
  
  .action-buttons .btn {
    width: 100%;
    margin-bottom: 0.5rem;
  }
  
  .detail-row {
    flex-direction: column;
  }
  
  .detail-row label {
    width: 100%;
    margin-bottom: 0.25rem;
  }
  
  .capacity-value {
    font-size: 2rem;
  }
  
  .capacity-value .unit {
    font-size: 1rem;
  }
}

@media (max-width: 480px) {
  .modal-content {
    width: 95%;
    max-height: 80vh;
  }
}
</style>