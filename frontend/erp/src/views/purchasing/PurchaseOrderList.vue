<!-- src/views/purchasing/PurchaseOrderList.vue -->
<template>
    <div class="po-list-container">
      <div class="page-header">
        <h1>Purchase Orders</h1>
        <div class="header-actions">
          <router-link to="/purchasing/orders/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Purchase Order
          </router-link>
        </div>
      </div>
  
      <div class="filter-section">
        <div class="card">
          <div class="card-body">
            <div class="filter-form">
              <div class="filter-row">
                <div class="filter-group">
                  <label for="search-input">Search</label>
                  <div class="search-box">
                    <input
                      id="search-input"
                      type="text"
                      v-model="filters.search"
                      class="form-control"
                      placeholder="Search PO number..."
                      @keyup.enter="fetchPurchaseOrders"
                    />
                    <button class="search-btn" @click="fetchPurchaseOrders">
                      <i class="fas fa-search"></i>
                    </button>
                  </div>
                </div>
                
                <div class="filter-group">
                  <label for="status-filter">Status</label>
                  <select
                    id="status-filter"
                    v-model="filters.status"
                    class="form-control"
                    @change="fetchPurchaseOrders"
                  >
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="submitted">Submitted</option>
                    <option value="approved">Approved</option>
                    <option value="sent">Sent</option>
                    <option value="partial">Partially Received</option>
                    <option value="received">Received</option>
                    <option value="completed">Completed</option>
                    <option value="canceled">Canceled</option>
                  </select>
                </div>
                
                <div class="filter-group">
                  <label for="vendor-filter">Vendor</label>
                  <select
                    id="vendor-filter"
                    v-model="filters.vendor_id"
                    class="form-control"
                    @change="fetchPurchaseOrders"
                  >
                    <option value="">All Vendors</option>
                <option
                  v-for="vendor in filteredVendors"
                  :key="vendor.vendor_id"
                  :value="vendor.vendor_id"
                >
                  {{ vendor.name }}
                </option>
                  </select>
                </div>
              </div>
  
              <div class="filter-row">
                <div class="filter-group">
                  <label for="date-from">Date From</label>
                  <input
                    type="date"
                    id="date-from"
                    v-model="filters.date_from"
                    class="form-control"
                  />
                </div>
  
                <div class="filter-group">
                  <label for="date-to">Date To</label>
                  <input
                    type="date"
                    id="date-to"
                    v-model="filters.date_to"
                    class="form-control"
                  />
                </div>
                
                <div class="filter-group filter-actions">
                  <button class="btn btn-secondary" @click="fetchPurchaseOrders">
                    <i class="fas fa-filter"></i> Apply Filters
                  </button>
                  <button class="btn btn-light" @click="resetFilters">
                    <i class="fas fa-times"></i> Clear
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
  
      <div class="data-container">
        <div class="card">
          <div v-if="isLoading" class="card-body">
            <div class="loading-container">
              <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
              </div>
              <p>Loading purchase orders...</p>
            </div>
          </div>
          
          <div v-else-if="purchaseOrders.length === 0" class="card-body">
            <div class="empty-state">
              <div class="empty-icon">
                <i class="fas fa-file-invoice"></i>
              </div>
              <h3>No Purchase Orders Found</h3>
              <p>
                Try adjusting your search criteria or create a new purchase order.
              </p>
              <router-link to="/purchasing/orders/create" class="btn btn-primary">
                Create Purchase Order
              </router-link>
            </div>
          </div>
          
          <div v-else>
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th @click="sortBy('po_number')" class="sortable">
                      PO Number
                      <span v-if="sortKey === 'po_number'" class="sort-icon">
                        <i :class="sortIconClass"></i>
                      </span>
                    </th>
                    <th @click="sortBy('po_date')" class="sortable">
                      PO Date
                      <span v-if="sortKey === 'po_date'" class="sort-icon">
                        <i :class="sortIconClass"></i>
                      </span>
                    </th>
                    <th>Vendor</th>
                    <th @click="sortBy('expected_delivery')" class="sortable">
                      Expected Delivery
                      <span v-if="sortKey === 'expected_delivery'" class="sort-icon">
                        <i :class="sortIconClass"></i>
                      </span>
                    </th>
                    <th @click="sortBy('status')" class="sortable">
                      Status
                      <span v-if="sortKey === 'status'" class="sort-icon">
                        <i :class="sortIconClass"></i>
                      </span>
                    </th>
                    <th @click="sortBy('total_amount')" class="sortable text-right">
                      Total Amount
                      <span v-if="sortKey === 'total_amount'" class="sort-icon">
                        <i :class="sortIconClass"></i>
                      </span>
                    </th>
                    <th class="actions-column">Actions</th>
                  </tr>
                </thead>
                <tbody>
                <tr v-for="(po, index) in filteredPurchaseOrders" :key="po.po_id">
                    <td>{{ po.po_number }}</td>
                    <td>{{ formatDate(po.po_date) }}</td>
                    <td>{{ po.vendor ? po.vendor.name : 'N/A' }}</td>
                    <td>{{ formatDate(po.expected_delivery) }}</td>
                    <td>
                      <span :class="['status-badge', getStatusClass(po.status)]">
                        {{ formatStatus(po.status) }}
                      </span>
                    </td>
                    <td class="text-right">{{ formatCurrency(po.total_amount, po.currency_code) }}</td>
                    <td>
                      <div class="action-buttons">
                        <router-link
                          :to="`/purchasing/orders/${po.po_id}`"
                          class="action-btn view-btn"
                          title="View Details"
                        >
                          <i class="fas fa-eye"></i>
                        </router-link>
                        <router-link
                          v-if="po.status === 'draft'"
                          :to="`/purchasing/orders/${po.po_id}/edit`"
                          class="action-btn edit-btn"
                          title="Edit PO"
                        >
                          <i class="fas fa-edit"></i>
                        </router-link>
                        <button
                          v-if="po.status === 'draft'"
                          @click="confirmDelete(po)"
                          class="action-btn delete-btn"
                          title="Delete PO"
                        >
                          <i class="fas fa-trash"></i>
                        </button>
                        <router-link
                          :to="`/purchasing/orders/${po.po_id}/track`"
                          class="action-btn track-btn"
                          title="Track Status"
                        >
                          <i class="fas fa-chart-line"></i>
                        </router-link>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            
            <div class="pagination-container" v-if="totalPages > 1">
              <div class="pagination-info">
                Showing {{ paginationFrom }} to {{ paginationTo }} of {{ totalPOs }} items
              </div>
              <div class="pagination">
                <button 
                  @click="changePage(currentPage - 1)" 
                  class="pagination-btn" 
                  :disabled="currentPage === 1"
                >
                  <i class="fas fa-chevron-left"></i>
                </button>
                
                <button 
                  v-for="page in paginationButtons" 
                  :key="page"
                  @click="changePage(page)"
                  :class="['pagination-btn', { active: currentPage === page }]"
                >
                  {{ page }}
                </button>
                
                <button 
                  @click="changePage(currentPage + 1)" 
                  class="pagination-btn" 
                  :disabled="currentPage === totalPages"
                >
                  <i class="fas fa-chevron-right"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Confirmation Modal for Delete -->
      <div v-if="showDeleteModal" class="modal-backdrop">
        <div class="modal-container">
          <div class="modal-content">
            <div class="modal-header">
              <h3>Delete Purchase Order</h3>
              <button @click="closeDeleteModal" class="modal-close-btn">
                <i class="fas fa-times"></i>
              </button>
            </div>
            <div class="modal-body">
              <p>
                Are you sure you want to delete purchase order
                <strong>{{ selectedPO ? selectedPO.po_number : '' }}</strong>?
                This action cannot be undone.
              </p>
            </div>
            <div class="modal-footer">
              <button @click="closeDeleteModal" class="btn btn-secondary">
                Cancel
              </button>
              <button @click="deletePurchaseOrder" class="btn btn-danger">
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import PurchaseOrderService from '@/services/PurchaseOrderService';
  
  export default {
    name: 'PurchaseOrderList',
    data() {
      return {
        purchaseOrders: [],
        vendors: [],
        isLoading: true,
        currentPage: 1,
        totalPages: 1,
        totalPOs: 0,
        itemsPerPage: 10,
        filters: {
          search: '',
          status: '',
          vendor_id: '',
          date_from: '',
          date_to: ''
        },
        sortKey: 'po_date',
        sortDirection: 'desc',
        showDeleteModal: false,
        selectedPO: null
      };
    },
    computed: {
      filteredPurchaseOrders() {
        return this.purchaseOrders.filter(po => po != null);
      },
      filteredVendors() {
        return this.vendors.filter(vendor => vendor != null);
      },
      paginationFrom() {
        return (this.currentPage - 1) * this.itemsPerPage + 1;
      },
      paginationTo() {
        return Math.min(this.currentPage * this.itemsPerPage, this.totalPOs);
      },
      paginationButtons() {
        const buttons = [];
        const maxButtons = 5;
        const halfMax = Math.floor(maxButtons / 2);
        
        let start = Math.max(1, this.currentPage - halfMax);
        let end = Math.min(this.totalPages, start + maxButtons - 1);
        
        if (end - start + 1 < maxButtons) {
          start = Math.max(1, end - maxButtons + 1);
        }
        
        for (let i = start; i <= end; i++) {
          buttons.push(i);
        }
        
        return buttons;
      },
      sortIconClass() {
        return this.sortDirection === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
      }
    },
    methods: {
      async fetchPurchaseOrders() {
        this.isLoading = true;
        
        try {
          // Prepare query parameters
          const params = {
            page: this.currentPage,
            per_page: this.itemsPerPage,
            sort_field: this.sortKey,
            sort_direction: this.sortDirection,
            ...this.filters
          };
          
          const response = await PurchaseOrderService.getAllPurchaseOrders(params);
          
          if (response.data && Array.isArray(response.data.data)) {
            this.purchaseOrders = response.data.data;
            
            if (response.data.meta) {
              this.totalPOs = response.data.meta.total || 0;
              this.totalPages = response.data.meta.last_page || 1;
              this.currentPage = response.data.meta.current_page || 1;
            }
          } else {
            this.purchaseOrders = [];
            this.totalPOs = 0;
            this.totalPages = 1;
          }
        } catch (error) {
          console.error('Error fetching purchase orders:', error);
          this.purchaseOrders = [];
        } finally {
          this.isLoading = false;
        }
      },
      
      async fetchVendors() {
        try {
          const response = await PurchaseOrderService.getVendors();
          
          if (response.data && Array.isArray(response.data.data)) {
            this.vendors = response.data.data;
          } else {
            this.vendors = [];
          }
        } catch (error) {
          console.error('Error fetching vendors:', error);
          this.vendors = [];
        }
      },
      
      sortBy(key) {
        if (this.sortKey === key) {
          this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
          this.sortKey = key;
          this.sortDirection = 'asc';
        }
        
        this.fetchPurchaseOrders();
      },
      
      changePage(page) {
        if (page > 0 && page <= this.totalPages && page !== this.currentPage) {
          this.currentPage = page;
          this.fetchPurchaseOrders();
        }
      },
      
      resetFilters() {
        this.filters = {
          search: '',
          status: '',
          vendor_id: '',
          date_from: '',
          date_to: ''
        };
        
        this.fetchPurchaseOrders();
      },
      
      confirmDelete(po) {
        this.selectedPO = po;
        this.showDeleteModal = true;
      },
      
      closeDeleteModal() {
        this.showDeleteModal = false;
        this.selectedPO = null;
      },
      
      async deletePurchaseOrder() {
        if (!this.selectedPO) return;
        
        try {
          await PurchaseOrderService.deletePurchaseOrder(this.selectedPO.po_id);
          
          // Refresh the list
          this.fetchPurchaseOrders();
          
          // Close modal
          this.closeDeleteModal();
        } catch (error) {
          console.error('Error deleting purchase order:', error);
          
          if (error.response && error.response.data && error.response.data.message) {
            alert(error.response.data.message);
          } else {
            alert('Failed to delete purchase order. Please try again.');
          }
          
          this.closeDeleteModal();
        }
      },
      
      formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: '2-digit'
        });
      },
      
      formatCurrency(amount, currencyCode = 'USD') {
        if (amount === null || amount === undefined) return 'N/A';
        
        return new Intl.NumberFormat('en-US', {
          style: 'currency',
          currency: currencyCode
        }).format(amount);
      },
      
      formatStatus(status) {
        switch (status) {
          case 'draft':
            return 'Draft';
          case 'submitted':
            return 'Submitted';
          case 'approved':
            return 'Approved';
          case 'sent':
            return 'Sent';
          case 'partial':
            return 'Partially Received';
          case 'received':
            return 'Received';
          case 'completed':
            return 'Completed';
          case 'canceled':
            return 'Canceled';
          default:
            return status;
        }
      },
      
      getStatusClass(status) {
        switch (status) {
          case 'draft':
            return 'status-draft';
          case 'submitted':
            return 'status-pending';
          case 'approved':
            return 'status-approved';
          case 'sent':
            return 'status-sent';
          case 'partial':
            return 'status-partial';
          case 'received':
            return 'status-received';
          case 'completed':
            return 'status-completed';
          case 'canceled':
            return 'status-canceled';
          default:
            return 'status-draft';
        }
      }
    },
    mounted() {
      this.fetchPurchaseOrders();
      this.fetchVendors();
    }
  };
  </script>
  
  <style scoped>
  .po-list-container {
    padding: 1rem;
  }
  
  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
  }
  
  .page-header h1 {
    margin: 0;
    font-size: 1.5rem;
    color: var(--gray-800);
  }
  
  .header-actions {
    display: flex;
    gap: 0.75rem;
  }
  
  .card {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 1.5rem;
  }
  
  .card-body {
    padding: 1.5rem;
  }
  
  .filter-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  
  .filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: flex-end;
  }
  
  .filter-group {
    flex: 1;
    min-width: 200px;
  }
  
  .filter-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
  }
  
  .search-box {
    position: relative;
  }
  
  .search-box input {
    padding-right: 2.5rem;
  }
  
  .search-btn {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    width: 2.5rem;
    background: none;
    border: none;
    color: var(--gray-500);
    cursor: pointer;
  }
  
  .search-btn:hover {
    color: var(--primary-color);
  }
  
  .form-control {
    display: block;
    width: 100%;
    padding: 0.5rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: var(--gray-700);
    background-color: #fff;
    border: 1px solid var(--gray-300);
    border-radius: 0.375rem;
    transition: border-color 0.15s ease-in-out;
  }
  
  .form-control:focus {
    border-color: var(--primary-color);
    outline: 0;
  }
  
  label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--gray-700);
  }
  
  .loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 0;
  }
  
  .loading-spinner {
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
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
  
  .table-responsive {
    overflow-x: auto;
  }
  
  .table {
    width: 100%;
    margin-bottom: 0;
    color: var(--gray-700);
    border-collapse: collapse;
  }
  
  .table th,
  .table td {
    padding: 0.75rem 1rem;
    vertical-align: middle;
  }
  
  .table th {
    background-color: var(--gray-50);
    font-weight: 500;
    text-align: left;
    border-bottom: 2px solid var(--gray-200);
  }
  
  .table td {
    border-bottom: 1px solid var(--gray-100);
  }
  
  .table tbody tr:hover td {
    background-color: var(--gray-50);
  }
  
  .sortable {
    cursor: pointer;
    position: relative;
  }
  
  .sort-icon {
    display: inline-block;
    margin-left: 0.5rem;
  }
  
  .text-right {
    text-align: right;
  }
  
  .status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
  }
  
  .status-draft {
    background-color: var(--gray-100);
    color: var(--gray-700);
  }
  
  .status-pending {
    background-color: #fef3c7;
    color: #92400e;
  }
  
  .status-approved {
    background-color: #dcfce7;
    color: #166534;
  }
  
  .status-sent {
    background-color: #dbeafe;
    color: #1e40af;
  }
  
  .status-partial {
    background-color: #fef9c3;
    color: #854d0e;
  }
  
  .status-received {
    background-color: #d1fae5;
    color: #065f46;
  }
  
  .status-completed {
    background-color: #bbf7d0;
    color: #15803d;
  }
  
  .status-canceled {
    background-color: #fee2e2;
    color: #b91c1c;
  }
  
  .actions-column {
    width: 120px;
    text-align: center;
  }
  
  .action-buttons {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
  }
  
  .action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 0.375rem;
    border: none;
    background: none;
    cursor: pointer;
    transition: background-color 0.2s;
  }
  
  .view-btn {
    color: var(--primary-color);
  }
  
  .view-btn:hover {
    background-color: var(--primary-bg);
  }
  
  .edit-btn {
    color: var(--warning-color);
  }
  
  .edit-btn:hover {
    background-color: var(--warning-bg);
  }
  
  .delete-btn {
    color: var(--danger-color);
  }
  
  .delete-btn:hover {
    background-color: var(--danger-bg);
  }
  
  .track-btn {
    color: var(--success-color);
  }
  
  .track-btn:hover {
    background-color: var(--success-bg);
  }
  
  .pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-top: 1px solid var(--gray-200);
  }
  
  .pagination-info {
    font-size: 0.875rem;
    color: var(--gray-600);
  }
  
  .pagination {
    display: flex;
    gap: 0.25rem;
  }
  
  .pagination-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2rem;
    height: 2rem;
    padding: 0 0.5rem;
    border-radius: 0.375rem;
    border: 1px solid var(--gray-200);
    background-color: white;
    color: var(--gray-700);
    cursor: pointer;
    transition: all 0.2s;
  }
  
  .pagination-btn:hover:not(:disabled) {
    background-color: var(--gray-100);
    border-color: var(--gray-300);
  }
  
  .pagination-btn.active {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
  }
  
  .pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
  
  .btn {
    display: inline-block;
    font-weight: 500;
    text-align: center;
    vertical-align: middle;
    cursor: pointer;
    padding: 0.5rem 1rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: 0.375rem;
    transition: all 0.15s ease-in-out;
    border: none;
  }
  
  .btn-primary {
    background-color: var(--primary-color);
    color: white;
  }
  
  .btn-primary:hover {
    background-color: var(--primary-dark);
  }
  
  .btn-secondary {
    background-color: var(--gray-200);
    color: var(--gray-700);
  }
  
  .btn-secondary:hover {
    background-color: var(--gray-300);
  }
  
  .btn-light {
    background-color: white;
    color: var(--gray-700);
    border: 1px solid var(--gray-300);
  }
  
  .btn-light:hover {
    background-color: var(--gray-100);
  }
  
  .btn-danger {
    background-color: var(--danger-color);
    color: white;
  }
  
  .btn-danger:hover {
    background-color: #b91c1c;
  }
  
  .filter-section {
    margin-bottom: 1.5rem;
  }
  
  .data-container {
    margin-bottom: 1.5rem;
  }
  
  .modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
  }
  
  .modal-container {
    max-width: 500px;
    width: 100%;
    margin: 0 1rem;
  }
  
  .modal-content {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.08);
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
    color: var(--gray-800);
  }
  
  .modal-close-btn {
    background: none;
    border: none;
    color: var(--gray-500);
    cursor: pointer;
    font-size: 1.25rem;
    padding: 0.25rem;
  }
  
  .modal-close-btn:hover {
    color: var(--gray-700);
  }
  
  .modal-body {
    padding: 1.5rem;
    color: var(--gray-700);
  }
  
  .modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--gray-200);
  }
  
  @media (max-width: 768px) {
    .filter-row {
      flex-direction: column;
    }
    
    .filter-group {
      width: 100%;
    }
    
    .pagination-container {
      flex-direction: column;
      gap: 1rem;
    }
  }
  </style>