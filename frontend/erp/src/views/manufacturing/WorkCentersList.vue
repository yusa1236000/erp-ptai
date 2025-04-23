<!-- src/views/manufacturing/WorkCentersList.vue -->
<template>
    <div class="work-centers-container">
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h2 class="card-title">Work Centers</h2>
          <router-link to="/manufacturing/work-centers/create" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Add Work Center
          </router-link>
        </div>
        <div class="card-body">
          <SearchFilter
            placeholder="Search work centers..."
            v-model:value="searchQuery"
            @search="handleSearch"
            @clear="resetFilters"
          >
            <template #filters>
              <div class="filter-group">
                <label>Status</label>
                <select v-model="statusFilter" class="form-control" @change="handleSearch">
                  <option value="">All Status</option>
                  <option value="true">Active</option>
                  <option value="false">Inactive</option>
                </select>
              </div>
            </template>
            <template #actions>
              <button class="btn btn-secondary" @click="resetFilters">
                <i class="fas fa-redo mr-2"></i> Reset
              </button>
            </template>
          </SearchFilter>
  
          <div v-if="isLoading" class="text-center p-5">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Loading work centers...</p>
          </div>
  
          <div v-else-if="workCenters.length === 0" class="empty-state">
            <div class="empty-icon">
              <i class="fas fa-industry"></i>
            </div>
            <h3>No Work Centers Found</h3>
            <p>No work centers match your search criteria or no work centers have been created yet.</p>
            <router-link to="/manufacturing/work-centers/create" class="btn btn-primary mt-3">
              <i class="fas fa-plus mr-2"></i> Add Work Center
            </router-link>
          </div>
  
          <DataTable v-else
            :columns="columns"
            :items="workCenters"
            :is-loading="isLoading"
            keyField="workcenter_id"
            :initial-sort-key="sortKey"
            :initial-sort-order="sortOrder"
            @sort="handleSort"
          >
            <template #status="{ value }">
              <span :class="['badge', value ? 'badge-success' : 'badge-secondary']">
                {{ value ? 'Active' : 'Inactive' }}
              </span>
            </template>
            
            <template #capacity="{ value }">
              {{ value }} units/hour
            </template>
            
            <template #cost_per_hour="{ value }">
              {{ formatCurrency(value) }}
            </template>
            
            <template #actions="{ item }">
              <div class="btn-group">
                <router-link :to="`/manufacturing/work-centers/${item.workcenter_id}`" class="btn btn-sm btn-info">
                  <i class="fas fa-eye"></i>
                </router-link>
                <router-link :to="`/manufacturing/work-centers/${item.workcenter_id}/edit`" class="btn btn-sm btn-primary">
                  <i class="fas fa-edit"></i>
                </router-link>
                <router-link :to="`/manufacturing/work-centers/${item.workcenter_id}/schedule`" class="btn btn-sm btn-success">
                  <i class="fas fa-calendar-alt"></i>
                </router-link>
                <button @click="confirmDelete(item)" class="btn btn-sm btn-danger">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </template>
            
            <template #footer>
              <PaginationComponent
                :current-page="currentPage"
                :total-pages="totalPages"
                :from="from"
                :to="to"
                :total="total"
                @page-changed="handlePageChange"
              />
            </template>
          </DataTable>
        </div>
      </div>
      
      <!-- Confirmation Modal -->
      <ConfirmationModal
        v-if="showDeleteModal"
        :title="'Delete Work Center'"
        :message="deleteMessage"
        confirm-button-text="Delete"
        confirm-button-class="btn btn-danger"
        @confirm="deleteWorkCenter"
        @close="showDeleteModal = false"
      />
    </div>
  </template>
  
  <script>
import { ref/*, /*reactive*/, onMounted, computed } from 'vue';
import axios from 'axios';
// import { useRouter } from 'vue-router';

export default {
  name: 'WorkCentersList',
  setup() {
    // const router = useRouter();
    const workCenters = ref([]);
      const isLoading = ref(true);
      const searchQuery = ref('');
      const statusFilter = ref('');
      const sortKey = ref('name');
      const sortOrder = ref('asc');
      const currentPage = ref(1);
      const totalPages = ref(1);
      const from = ref(0);
      const to = ref(0);
      const total = ref(0);
      const perPage = ref(10);
      
      // Delete confirmation
      const showDeleteModal = ref(false);
      const workCenterToDelete = ref(null);
      const deleteMessage = computed(() => {
        if (!workCenterToDelete.value) return '';
        return `Are you sure you want to delete the work center <strong>${workCenterToDelete.value.name}</strong>? This action cannot be undone.`;
      });
  
      const columns = [
        { key: 'code', label: 'Code', sortable: true },
        { key: 'name', label: 'Name', sortable: true },
        { key: 'capacity', label: 'Capacity', sortable: true, template: 'capacity' },
        { key: 'cost_per_hour', label: 'Cost/Hour', sortable: true, template: 'cost_per_hour' },
        { key: 'is_active', label: 'Status', sortable: true, template: 'status' }
      ];
  
      const loadWorkCenters = async () => {
        isLoading.value = true;
        try {
          const params = {
            page: currentPage.value,
            per_page: perPage.value,
            search: searchQuery.value,
            sort_field: sortKey.value,
            sort_order: sortOrder.value
          };
  
          if (statusFilter.value !== '') {
            params.is_active = statusFilter.value;
          }
  
          const response = await axios.get('/work-centers', { params });
          workCenters.value = response.data.data;
          
          // Check if pagination data is available
          if (response.data.meta) {
            currentPage.value = response.data.meta.current_page;
            totalPages.value = response.data.meta.last_page;
            from.value = response.data.meta.from || 0;
            to.value = response.data.meta.to || 0;
            total.value = response.data.meta.total || 0;
          }
        } catch (error) {
          console.error('Error loading work centers:', error);
          alert('Failed to load work centers. Please try again later.');
        } finally {
          isLoading.value = false;
        }
      };
  
      const handleSearch = () => {
        currentPage.value = 1;
        loadWorkCenters();
      };
  
      const handleSort = (sortData) => {
        sortKey.value = sortData.key;
        sortOrder.value = sortData.order;
        loadWorkCenters();
      };
  
      const handlePageChange = (page) => {
        currentPage.value = page;
        loadWorkCenters();
      };
  
      const resetFilters = () => {
        searchQuery.value = '';
        statusFilter.value = '';
        currentPage.value = 1;
        sortKey.value = 'name';
        sortOrder.value = 'asc';
        loadWorkCenters();
      };
  
      const confirmDelete = (workCenter) => {
        workCenterToDelete.value = workCenter;
        showDeleteModal.value = true;
      };
  
      const deleteWorkCenter = async () => {
        if (!workCenterToDelete.value) return;
        
        try {
          await axios.delete(`/work-centers/${workCenterToDelete.value.workcenter_id}`);
          showDeleteModal.value = false;
          loadWorkCenters();
        } catch (error) {
          if (error.response && error.response.status === 400) {
            alert(error.response.data.message || 'This work center cannot be deleted because it is in use.');
          } else {
            console.error('Error deleting work center:', error);
            alert('Failed to delete work center. Please try again later.');
          }
          showDeleteModal.value = false;
        }
      };
  
      const formatCurrency = (value) => {
        return new Intl.NumberFormat('en-US', {
          style: 'currency',
          currency: 'USD',
          minimumFractionDigits: 2
        }).format(value);
      };
  
      onMounted(() => {
        loadWorkCenters();
      });
  
      return {
        workCenters,
        isLoading,
        columns,
        searchQuery,
        statusFilter,
        sortKey,
        sortOrder,
        currentPage,
        totalPages,
        from,
        to,
        total,
        showDeleteModal,
        deleteMessage,
        formatCurrency,
        handleSearch,
        handleSort,
        handlePageChange,
        resetFilters,
        confirmDelete,
        deleteWorkCenter
      };
    }
  };
  </script>
  
  <style scoped>
  .work-centers-container {
    padding: 1rem 0;
  }
  
  .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 0;
    text-align: center;
  }
  
  .empty-icon {
    font-size: 3rem;
    color: var(--gray-300);
    margin-bottom: 1rem;
  }
  
  .empty-state h3 {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
  }
  
  .empty-state p {
    color: var(--gray-500);
    max-width: 30rem;
    margin-bottom: 1rem;
  }
  </style>