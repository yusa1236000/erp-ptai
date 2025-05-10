<!-- src/views/materials/MaterialPlanningList.vue -->
<template>
    <div class="material-planning-container">
      <!-- Material Planning Header -->
      <div class="page-header">
        <h1 class="page-title">Material Planning</h1>
        <div class="header-actions">
          <button class="btn btn-primary" @click="showGenerateModal = true">
            <i class="fas fa-cogs"></i> Generate Material Plans
          </button>
        </div>
      </div>
  
      <!-- Filters -->
      <div class="search-filter card" style="margin-bottom: 24px;">
        <SearchFilter
          v-model:value="filters.search"
          placeholder="Search material plans..."
          @search="fetchPlans"
        >
          <template #filters>
            <div class="filter-group">
              <label>Planning Period</label>
              <input
                type="month"
                v-model="filters.planningPeriod"
                class="form-control"
                @change="fetchPlans"
              />
            </div>
            <div class="filter-group">
              <label>Material Type</label>
              <select v-model="filters.materialType" class="form-control" @change="fetchPlans">
                <option value="">All Types</option>
                <option value="FG">Finished Goods</option>
                <option value="RM">Raw Materials</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Status</label>
              <select v-model="filters.status" class="form-control" @change="fetchPlans">
                <option value="">All Status</option>
                <option value="Draft">Draft</option>
                <option value="Requisitioned">Requisitioned</option>
                <option value="Approved">Approved</option>
              </select>
            </div>
          </template>
          <template #actions>
            <button class="btn btn-outline" @click="exportPlans">
              <i class="fas fa-file-export"></i> Export
            </button>
          </template>
        </SearchFilter>
      </div>
  
      <!-- Material Plans Table -->
      <div class="card">
        <DataTable
          :columns="columns"
          :items="plans"
          :is-loading="isLoading"
          @sort="handleSort"
        >
          <template #status="{ value }">
            <span :class="statusClasses[value]" class="status-chip">
              {{ value }}
            </span>
          </template>
          <template #quantity="{ value }">
            {{ formatNumber(value) }}
          </template>
          <template #actions="{ item }">
            <div class="action-buttons">
              <button 
                class="btn-icon" 
                @click="viewDetails(item)"
                title="View Details"
              >
                <i class="fas fa-eye"></i>
              </button>
              <button 
                v-if="item.status === 'Draft'" 
                class="btn-icon btn-icon-edit" 
                @click="editPlan(item)"
                title="Edit"
              >
                <i class="fas fa-pencil-alt"></i>
              </button>
              <button 
                v-if="item.status === 'Draft'" 
                class="btn-icon btn-icon-danger" 
                @click="deletePlan(item)"
                title="Delete"
              >
                <i class="fas fa-trash"></i>
              </button>
              <button 
                v-if="item.material_type === 'RM' && item.status === 'Draft' && item.net_requirement > 0" 
                class="btn-icon btn-icon-success" 
                @click="generatePurchaseRequisition(item)"
                title="Generate Purchase Requisition"
              >
                <i class="fas fa-file-invoice"></i>
              </button>
            </div>
          </template>
        </DataTable>
  
        <PaginationComponent
          :current-page="currentPage"
          :total-pages="totalPages"
          :from="from"
          :to="to"
          :total="total"
          @page-changed="handlePageChange"
        />
      </div>
  
      <!-- Generate Material Plans Modal -->
      <div v-if="showGenerateModal" class="modal">
        <div class="modal-backdrop" @click="closeGenerateModal"></div>
        <div class="modal-content modal-md">
          <div class="modal-header">
            <h2>Generate Material Plans</h2>
            <button class="close-btn" @click="closeGenerateModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="generatePlans">
              <div class="form-group">
                <label>Start Period <span class="required">*</span></label>
                <input
                  type="month"
                  v-model="generateForm.startPeriod"
                  class="form-control"
                  required
                />
              </div>
              <div class="form-group">
                <label>Buffer Percentage <span class="required">*</span></label>
                <input
                  type="number"
                  v-model="generateForm.bufferPercentage"
                  class="form-control"
                  min="0"
                  max="100"
                  required
                />
              </div>
              <div class="form-group">
                <label>Select Items (Optional)</label>
                <multiselect
                  v-model="generateForm.itemIds"
                  :options="itemOptions"
                  :multiple="true"
                  :searchable="true"
                  track-by="value"
                  label="label"
                  placeholder="Leave empty to generate for all items"
                />
              </div>
              <div class="form-actions">
                <button type="button" class="btn btn-secondary" @click="closeGenerateModal">
                  Cancel
                </button>
                <button type="submit" class="btn btn-primary" :disabled="isGenerating">
                  <i class="fas fa-cogs" :class="{ 'fa-spin': isGenerating }"></i>
                  {{ isGenerating ? 'Generating...' : 'Generate' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
  
      <!-- Purchase Requisition Generation Modal -->
      <div v-if="showPRModal" class="modal">
        <div class="modal-backdrop" @click="closePRModal"></div>
        <div class="modal-content modal-md">
          <div class="modal-header">
            <h2>Generate Purchase Requisition</h2>
            <button class="close-btn" @click="closePRModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitPR">
              <div class="form-group">
                <label>Planning Period</label>
                <input
                  type="month"
                  v-model="prForm.period"
                  class="form-control"
                  readonly
                />
              </div>
              <div class="form-group">
                <label>Lead Time (days) <span class="required">*</span></label>
                <input
                  type="number"
                  v-model="prForm.leadTimeDays"
                  class="form-control"
                  min="0"
                  required
                />
              </div>
              <div class="form-actions">
                <button type="button" class="btn btn-secondary" @click="closePRModal">
                  Cancel
                </button>
                <button type="submit" class="btn btn-primary" :disabled="isGeneratingPR">
                  <i class="fas fa-file-invoice" :class="{ 'fa-spin': isGeneratingPR }"></i>
                  {{ isGeneratingPR ? 'Generating...' : 'Generate PR' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
  
      <!-- Confirmation Modals -->
      <ConfirmationModal
        v-if="showDeleteModal"
        title="Delete Material Plan"
        message="Are you sure you want to delete this material plan?"
        @confirm="confirmDelete"
        @close="closeDeleteModal"
      />
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import SearchFilter from '@/components/common/SearchFilter.vue';
  import DataTable from '@/components/common/DataTable.vue';
  import PaginationComponent from '@/components/common/Pagination.vue';
  import ConfirmationModal from '@/components/common/ConfirmationModal.vue';
  import Multiselect from 'vue-multiselect';
  
  export default {
    name: 'MaterialPlanningList',
    components: {
      SearchFilter,
      DataTable,
      PaginationComponent,
      ConfirmationModal,
      Multiselect
    },
    data() {
      return {
        plans: [],
        isLoading: false,
        currentPage: 1,
        totalPages: 1,
        from: 0,
        to: 0,
        total: 0,
        filters: {
          search: '',
          planningPeriod: '',
          materialType: '',
          status: ''
        },
        columns: [
          { 
            key: 'planning_period', 
            label: 'Planning Period', 
            sortable: true 
          },
          { 
            key: 'item', 
            label: 'Item', 
            sortable: false,
            formatter: (value) => `${value.item_code} - ${value.name}` 
          },
          { 
            key: 'material_type', 
            label: 'Type', 
            sortable: true 
          },
          { 
            key: 'forecast_quantity', 
            label: 'Forecast Qty', 
            sortable: true,
            template: 'quantity'
          },
          { 
            key: 'available_stock', 
            label: 'Available Stock', 
            sortable: true,
            template: 'quantity'
          },
          { 
            key: 'net_requirement', 
            label: 'Net Requirement', 
            sortable: true,
            template: 'quantity',
            class: 'text-bold'
          },
          { 
            key: 'planned_order_quantity', 
            label: 'Planned Order Qty', 
            sortable: true,
            template: 'quantity'
          },
          { 
            key: 'status', 
            label: 'Status', 
            sortable: true,
            template: 'status'
          },
          { 
            key: 'actions', 
            label: 'Actions', 
            sortable: false,
            template: 'actions',
            class: 'actions-column'
          }
        ],
        statusClasses: {
          'Draft': 'bg-gray',
          'Requisitioned': 'bg-blue',
          'Approved': 'bg-green'
        },
        showGenerateModal: false,
        isGenerating: false,
        generateForm: {
          startPeriod: '',
          bufferPercentage: 10,
          itemIds: []
        },
        itemOptions: [],
        showPRModal: false,
        isGeneratingPR: false,
        prForm: {
          period: '',
          leadTimeDays: 7
        },
        selectedPlan: null,
        showDeleteModal: false,
        deleteItemId: null
      };
    },
    mounted() {
      this.fetchPlans();
      this.fetchItemOptions();
    },
    methods: {
      async fetchPlans() {
        this.isLoading = true;
        try {
          const response = await axios.get('/material-planning', {
            params: {
              page: this.currentPage,
              search: this.filters.search,
              planning_period: this.filters.planningPeriod,
              material_type: this.filters.materialType,
              status: this.filters.status
            }
          });
          
          this.plans = response.data.data;
          this.total = response.data.total ?? 0;
          this.from = response.data.from ?? 0;
          this.to = response.data.to ?? 0;
          this.currentPage = response.data.current_page ?? 1;
          this.totalPages = response.data.last_page ?? 1;
        } catch (error) {
          console.error('Error fetching material plans:', error);
          alert('Failed to fetch material plans');
        } finally {
          this.isLoading = false;
        }
      },
      
      async fetchItemOptions() {
        try {
          const response = await axios.get('/items');
          this.itemOptions = response.data.data.map(item => ({
            value: item.item_id,
            label: `${item.item_code} - ${item.name}`
          }));
        } catch (error) {
          console.error('Error fetching items:', error);
        }
      },
      
      async generatePlans() {
        if (!this.generateForm.startPeriod || !this.generateForm.bufferPercentage) {
          alert('Please fill in all required fields');
          return;
        }
        
        this.isGenerating = true;
        try {
          const payload = {
            start_period: this.generateForm.startPeriod + '-01',
            buffer_percentage: this.generateForm.bufferPercentage,
            item_ids: this.generateForm.itemIds.map(item => item.value)
          };
          
          const response = await axios.post('/material-planning/generate', payload);
          this.closeGenerateModal();
          this.fetchPlans();
          alert(response.data.message);
        } catch (error) {
          console.error('Error generating material plans:', error);
          alert('Failed to generate material plans');
        } finally {
          this.isGenerating = false;
        }
      },
      
      async submitPR() {
        if (!this.prForm.leadTimeDays) {
          alert('Please fill in all required fields');
          return;
        }
        
        this.isGeneratingPR = true;
        try {
          const payload = {
            period: this.prForm.period + '-01',
            lead_time_days: this.prForm.leadTimeDays
          };
          
          const response = await axios.post('/material-planning/purchase-requisition', payload);
          
          if (response.data.data) {
            // Redirect to PR detail page
            this.$router.push(`/purchasing/requisitions/${response.data.data.pr_id}`);
          }
          
          this.closePRModal();
          this.fetchPlans();
          alert(response.data.message);
        } catch (error) {
          console.error('Error generating purchase requisition:', error);
          const message = error.response?.data?.message || 'Failed to generate purchase requisition';
          alert(message);
        } finally {
          this.isGeneratingPR = false;
        }
      },
      
      viewDetails(plan) {
        this.$router.push(`/materials/plans/${plan.plan_id}`);
      },
      
      editPlan(plan) {
        this.$router.push(`/materials/plans/${plan.plan_id}/edit`);
      },
      
      deletePlan(plan) {
        this.deleteItemId = plan.plan_id;
        this.showDeleteModal = true;
      },
      
      async confirmDelete() {
        try {
          await axios.delete(`/material-plans/${this.deleteItemId}`);
          this.fetchPlans();
          alert('Material plan deleted successfully');
        } catch (error) {
          console.error('Error deleting material plan:', error);
          alert('Failed to delete material plan');
        } finally {
          this.closeDeleteModal();
        }
      },
      
      generatePurchaseRequisition(plan) {
        this.selectedPlan = plan;
        this.prForm.period = plan.planning_period.substring(0, 7);
        this.showPRModal = true;
      },
      
      exportPlans() {
        // Implementation for export functionality
        console.log('Exporting plans...');
      },
      
      formatNumber(value) {
        return Number(value).toLocaleString();
      },
      
      closeGenerateModal() {
        this.showGenerateModal = false;
        this.generateForm = {
          startPeriod: '',
          bufferPercentage: 10,
          itemIds: []
        };
      },
      
      closePRModal() {
        this.showPRModal = false;
        this.prForm = {
          period: '',
          leadTimeDays: 7
        };
        this.selectedPlan = null;
      },
      
      closeDeleteModal() {
        this.showDeleteModal = false;
        this.deleteItemId = null;
      },
      
      handlePageChange(page) {
        this.currentPage = page;
        this.fetchPlans();
      },
      
      handleSort(sort) {
        // Implementation for sorting
        console.log('Sorting:', sort);
      }
    }
  };
  </script>
  
  <style scoped>
  .material-planning-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 16px;
  }
  
  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
  }
  
  .page-title {
    font-size: 24px;
    font-weight: 600;
    color: var(--gray-800);
  }
  
  .header-actions {
    display: flex;
    gap: 12px;
  }
  
  .btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  
  .btn-primary {
    background: var(--primary-color);
    color: white;
  }
  
  .btn-primary:hover {
    background: var(--primary-dark);
  }
  
  .btn-outline {
    border: 1px solid var(--gray-200);
    background: white;
    color: var(--gray-700);
  }
  
  .btn-outline:hover {
    background: var(--gray-50);
  }
  
  .btn-secondary {
    background: var(--gray-100);
    color: var(--gray-800);
  }
  
  .btn-secondary:hover {
    background: var(--gray-200);
  }
  
  .card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
  }
  
  .action-buttons {
    display: flex;
    gap: 8px;
  }
  
  .btn-icon {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: none;
    background: var(--gray-100);
    color: var(--gray-600);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .btn-icon:hover {
    background: var(--gray-200);
  }
  
  .btn-icon-edit:hover {
    background: var(--primary-bg);
    color: var(--primary-color);
  }
  
  .btn-icon-danger:hover {
    background: #fee2e2;
    color: #dc2626;
  }
  
  .btn-icon-success:hover {
    background: #dcfce7;
    color: #16a34a;
  }
  
  .status-chip {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
  }
  
  .bg-gray {
    background: var(--gray-100);
    color: var(--gray-800);
  }
  
  .bg-blue {
    background: #dbeafe;
    color: #1e40af;
  }
  
  .bg-green {
    background: #dcfce7;
    color: #15803d;
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
    background: rgba(0, 0, 0, 0.5);
    z-index: 50;
  }
  
  .modal-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    width: 100%;
    z-index: 60;
    overflow: hidden;
  }
  
  .modal-md {
    max-width: 600px;
  }
  
  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    border-bottom: 1px solid var(--gray-200);
  }
  
  .modal-header h2 {
    font-size: 18px;
    font-weight: 600;
    color: var(--gray-800);
  }
  
  .close-btn {
    background: none;
    border: none;
    color: var(--gray-600);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 4px;
  }
  
  .close-btn:hover {
    background: var(--gray-100);
    color: var(--gray-800);
  }
  
  .modal-body {
    padding: 24px;
  }
  
  .form-group {
    margin-bottom: 16px;
  }
  
  .form-group label {
    display: block;
    font-weight: 500;
    margin-bottom: 8px;
    color: var(--gray-700);
  }
  
  .form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid var(--gray-200);
    border-radius: 6px;
    font-size: 14px;
  }
  
  .form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid var(--gray-200);
  }
  
  .required {
    color: #dc2626;
  }
  
  .text-bold {
    font-weight: 600;
  }
  
  .fa-spin {
    animation: spin 1s linear infinite;
  }
  
  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }
  </style>