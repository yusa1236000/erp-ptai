<!-- src/views/manufacturing/BOMList.vue -->
<template>
  <div class="bom-list">
    <div class="card">
      <div class="card-body">
        <h2 class="card-title">Bill of Materials</h2>
        
        <SearchFilter 
          v-model:value="searchTerm" 
          placeholder="Search by BOM code or product name"
          @search="loadBOMs"
          @clear="resetSearch"
        >
          <template #filters>
            <div class="filter-group">
              <label for="status-filter">Status</label>
              <select id="status-filter" v-model="filters.status" @change="loadBOMs">
                <option value="">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="obsolete">Obsolete</option>
              </select>
            </div>
          </template>
          
          <template #actions>
            <router-link to="/manufacturing/boms/create" class="btn btn-primary">
              <i class="fas fa-plus mr-2"></i> New BOM
            </router-link>
          </template>
        </SearchFilter>
        
        <div class="table-container">
          <DataTable
            :columns="columns"
            :items="boms"
            :is-loading="isLoading"
            :empty-title="'No BOMs Found'"
            :empty-message="'No bill of materials match your search criteria.'"
            :initial-sort-key="'bom_code'"
            @sort="handleSort"
            keyField="bom_id"
          >
            <template #status="{ value }">
              <span :class="getStatusBadgeClass(value)">{{ value }}</span>
            </template>
            
            <template #effective_date="{ value }">
              {{ formatDate(value) }}
            </template>
            
            <template #actions="{ item }">
              <div class="btn-group">
                <router-link :to="`/manufacturing/boms/${item.bom_id}`" class="btn btn-sm btn-secondary">
                  <i class="fas fa-eye"></i>
                </router-link>
                <router-link :to="`/manufacturing/boms/${item.bom_id}/edit`" class="btn btn-sm btn-primary">
                  <i class="fas fa-edit"></i>
                </router-link>
                <button @click="confirmDelete(item)" class="btn btn-sm btn-danger">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </template>
          </DataTable>
        </div>
        
        <div class="mt-4">
          <PaginationComponent
            :current-page="currentPage"
            :total-pages="totalPages"
            :from="from"
            :to="to"
            :total="total"
            @page-changed="handlePageChange"
          />
        </div>
      </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      v-if="showDeleteModal"
      title="Delete BOM"
      :message="`Are you sure you want to delete BOM <strong>${bomToDelete?.bom_code}</strong>?<br>This action cannot be undone.`"
      confirm-button-text="Delete"
      confirm-button-class="btn btn-danger"
      @confirm="deleteBOM"
      @close="cancelDelete"
    />
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import DataTable from '@/components/common/DataTable.vue';

export default {
  name: 'BOMList',
  components: {
    DataTable
  },
  setup() {
    const boms = ref([]);
    const isLoading = ref(true);
    const searchTerm = ref('');
    const filters = reactive({
      status: ''
    });
    
    // Pagination
    const currentPage = ref(1);
    const totalPages = ref(1);
    const from = ref(0);
    const to = ref(0);
    const total = ref(0);
    const perPage = ref(10);
    
    // Sorting
    const sortField = ref('bom_code');
    const sortOrder = ref('asc');
    
    // Delete modal
    const showDeleteModal = ref(false);
    const bomToDelete = ref(null);
    
    const columns = [
      { key: 'bom_code', label: 'BOM Code', sortable: true },
      { key: 'item.name', label: 'Item', sortable: true },
      { key: 'revision', label: 'Revision', sortable: true },
      { key: 'effective_date', label: 'Effective Date', sortable: true, template: 'effective_date' },
      { key: 'status', label: 'Status', sortable: true, template: 'status', class: 'text-center' }
    ];
    
    const loadBOMs = async () => {
      isLoading.value = true;
      
      try {
        const response = await axios.get('/boms', {
          params: {
            page: currentPage.value,
            per_page: perPage.value,
            search: searchTerm.value,
            status: filters.status,
            sort_field: sortField.value,
            sort_order: sortOrder.value
          }
        });
        
        boms.value = response.data.data;
        
        // Update pagination
        const meta = response.data.meta;
        currentPage.value = meta.current_page;
        totalPages.value = meta.last_page;
        from.value = (meta.current_page - 1) * meta.per_page + 1;
        to.value = Math.min(meta.current_page * meta.per_page, meta.total);
        total.value = meta.total;
      } catch (error) {
        console.error('Error loading BOMs:', error);
      } finally {
        isLoading.value = false;
      }
    };
    
    const resetSearch = () => {
      searchTerm.value = '';
      loadBOMs();
    };
    
    const handlePageChange = (page) => {
      currentPage.value = page;
      loadBOMs();
    };
    
    const handleSort = ({ key, order }) => {
      sortField.value = key;
      sortOrder.value = order;
      loadBOMs();
    };
    
    const getStatusBadgeClass = (status) => {
      const classes = 'badge ';
      switch (status.toLowerCase()) {
        case 'draft': return classes + 'badge-secondary';
        case 'active': return classes + 'badge-success';
        case 'inactive': return classes + 'badge-warning';
        case 'obsolete': return classes + 'badge-danger';
        default: return classes + 'badge-secondary';
      }
    };
    
    const formatDate = (dateString) => {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString();
    };
    
    const confirmDelete = (bom) => {
      bomToDelete.value = bom;
      showDeleteModal.value = true;
    };
    
    const cancelDelete = () => {
      bomToDelete.value = null;
      showDeleteModal.value = false;
    };
    
    const deleteBOM = async () => {
      if (!bomToDelete.value) return;
      
      try {
        await axios.delete(`/boms/${bomToDelete.value.bom_id}`);
        loadBOMs();
      } catch (error) {
        console.error('Error deleting BOM:', error);
        
        // Show error message if from backend
        if (error.response && error.response.data && error.response.data.message) {
          alert(error.response.data.message);
        } else {
          alert('Failed to delete BOM. It might be in use or there was a server error.');
        }
      } finally {
        cancelDelete();
      }
    };
    
    onMounted(() => {
      loadBOMs();
    });
    
    return {
      boms,
      isLoading,
      columns,
      searchTerm,
      filters,
      currentPage,
      totalPages,
      from,
      to,
      total,
      showDeleteModal,
      bomToDelete,
      loadBOMs,
      resetSearch,
      handlePageChange,
      handleSort,
      getStatusBadgeClass,
      formatDate,
      confirmDelete,
      cancelDelete,
      deleteBOM
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

.card-title {
  margin-bottom: 1.5rem;
}

.btn-group {
  display: flex;
  gap: 0.25rem;
}
</style>