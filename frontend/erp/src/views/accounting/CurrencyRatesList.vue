<!-- src/views/accounting/CurrencyRatesList.vue -->
<template>
    <div class="currency-rates-container">
      <div class="page-header">
        <h1>Currency Exchange Rates</h1>
        <router-link to="/currency-rates/create" class="btn btn-primary">
          <i class="fas fa-plus"></i> New Exchange Rate
        </router-link>
      </div>
  
      <div class="filters-container">
        <div class="search-filters">
          <div class="form-group">
            <label for="from-currency">From Currency</label>
            <input
              type="text"
              id="from-currency"
              v-model="filters.fromCurrency"
              placeholder="USD"
              class="form-control"
              maxlength="3"
            />
          </div>
          
          <div class="form-group">
            <label for="to-currency">To Currency</label>
            <input
              type="text"
              id="to-currency"
              v-model="filters.toCurrency"
              placeholder="IDR"
              class="form-control"
              maxlength="3"
            />
          </div>
  
          <div class="form-group">
            <label for="status">Status</label>
            <select id="status" v-model="filters.isActive" class="form-control">
              <option value="">All</option>
              <option :value="true">Active</option>
              <option :value="false">Inactive</option>
            </select>
          </div>
  
          <div class="form-group">
            <label for="date">Effective Date</label>
            <input
              type="date"
              id="date"
              v-model="filters.effectiveDate"
              class="form-control"
            />
          </div>
  
          <button @click="applyFilters" class="btn btn-secondary">
            <i class="fas fa-search"></i> Search
          </button>
          <button @click="clearFilters" class="btn btn-outline-secondary">
            <i class="fas fa-times"></i> Clear
          </button>
        </div>
      </div>
  
      <!-- Loading indicator -->
      <div v-if="loading" class="loading-container">
        <i class="fas fa-spinner fa-spin"></i> Loading exchange rates...
      </div>
  
      <!-- Error message -->
      <div v-else-if="error" class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> {{ error }}
      </div>
  
      <!-- Empty state -->
      <div v-else-if="rates.length === 0" class="empty-state">
        <i class="fas fa-money-bill-wave empty-icon"></i>
        <h3>No Exchange Rates Found</h3>
        <p>There are no exchange rates matching your criteria. Try adjusting your filters or add a new rate.</p>
        <router-link to="/currency-rates/create" class="btn btn-primary">
          Add Exchange Rate
        </router-link>
      </div>
  
      <!-- Exchange rates table -->
      <div v-else class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th @click="sortBy('from_currency')" class="sortable">
                From Currency
                <i v-if="sortField === 'from_currency'" :class="sortIconClass"></i>
              </th>
              <th @click="sortBy('to_currency')" class="sortable">
                To Currency
                <i v-if="sortField === 'to_currency'" :class="sortIconClass"></i>
              </th>
              <th @click="sortBy('rate')" class="sortable">
                Rate
                <i v-if="sortField === 'rate'" :class="sortIconClass"></i>
              </th>
              <th @click="sortBy('effective_date')" class="sortable">
                Effective Date
                <i v-if="sortField === 'effective_date'" :class="sortIconClass"></i>
              </th>
              <th @click="sortBy('end_date')" class="sortable">
                End Date
                <i v-if="sortField === 'end_date'" :class="sortIconClass"></i>
              </th>
              <th @click="sortBy('is_active')" class="sortable">
                Status
                <i v-if="sortField === 'is_active'" :class="sortIconClass"></i>
              </th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="rate in sortedRates" :key="rate.rate_id">
              <td>{{ rate.from_currency }}</td>
              <td>{{ rate.to_currency }}</td>
              <td>{{ formatNumber(rate.rate) }}</td>
              <td>{{ formatDate(rate.effective_date) }}</td>
              <td>{{ rate.end_date ? formatDate(rate.end_date) : 'Indefinite' }}</td>
              <td>
                <span
                  :class="[
                    'status-badge',
                    rate.is_active ? 'status-active' : 'status-inactive'
                  ]"
                >
                  {{ rate.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="actions-cell">
                <router-link
                  :to="`/currency-rates/${rate.rate_id}`"
                  class="btn btn-sm btn-info"
                  title="View details"
                >
                  <i class="fas fa-eye"></i>
                </router-link>
                <router-link
                  :to="`/currency-rates/${rate.rate_id}/edit`"
                  class="btn btn-sm btn-primary"
                  title="Edit"
                >
                  <i class="fas fa-edit"></i>
                </router-link>
                <button
                  @click="confirmDelete(rate)"
                  class="btn btn-sm btn-danger"
                  title="Delete"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
  
      <!-- Confirmation Modal -->
      <div v-if="showDeleteModal" class="modal">
        <div class="modal-backdrop" @click="cancelDelete"></div>
        <div class="modal-content">
          <div class="modal-header">
            <h3>Confirm Deletion</h3>
            <button @click="cancelDelete" class="close-btn">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="modal-body">
            <p>
              Are you sure you want to delete the exchange rate from
              <strong>{{ selectedRate.from_currency }}</strong> to
              <strong>{{ selectedRate.to_currency }}</strong>?
            </p>
            <div class="form-actions">
              <button @click="cancelDelete" class="btn btn-secondary">
                Cancel
              </button>
              <button @click="deleteRate" class="btn btn-danger">
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    name: 'CurrencyRatesList',
    data() {
      return {
        rates: [],
        loading: true,
        error: null,
        filters: {
          fromCurrency: '',
          toCurrency: '',
          isActive: '',
          effectiveDate: ''
        },
        sortField: 'effective_date',
        sortDirection: 'desc',
        showDeleteModal: false,
        selectedRate: {}
      };
    },
    computed: {
      sortIconClass() {
        return this.sortDirection === 'asc'
          ? 'fas fa-sort-up'
          : 'fas fa-sort-down';
      },
      sortedRates() {
        return [...this.rates].sort((a, b) => {
          let aValue = a[this.sortField];
          let bValue = b[this.sortField];
          
          // Handle null values
          if (aValue === null) return 1;
          if (bValue === null) return -1;
          
          // Handle dates
          if (this.sortField === 'effective_date' || this.sortField === 'end_date') {
            aValue = aValue ? new Date(aValue) : new Date(0);
            bValue = bValue ? new Date(bValue) : new Date(0);
          }
          
          // Compare values
          if (aValue < bValue) return this.sortDirection === 'asc' ? -1 : 1;
          if (aValue > bValue) return this.sortDirection === 'asc' ? 1 : -1;
          return 0;
        });
      }
    },
    mounted() {
      this.fetchRates();
    },
    methods: {
      async fetchRates() {
        this.loading = true;
        this.error = null;
        
        try {
          // Build query parameters
          const params = {};
          if (this.filters.fromCurrency) params.from_currency = this.filters.fromCurrency;
          if (this.filters.toCurrency) params.to_currency = this.filters.toCurrency;
          if (this.filters.isActive !== '') params.is_active = this.filters.isActive;
          if (this.filters.effectiveDate) params.effective_date = this.filters.effectiveDate;
          
          const response = await axios.get('/accounting/currency-rates', { params });
          
          if (response.data.status === 'success') {
            this.rates = response.data.data;
          } else {
            this.error = 'Failed to fetch exchange rates';
          }
        } catch (error) {
          console.error('Error fetching exchange rates:', error);
          this.error = error.response?.data?.message || 'An error occurred while fetching exchange rates';
        } finally {
          this.loading = false;
        }
      },
      formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString();
      },
      formatNumber(value) {
        return new Intl.NumberFormat('en-US', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 6
        }).format(value);
      },
      applyFilters() {
        this.fetchRates();
      },
      clearFilters() {
        this.filters = {
          fromCurrency: '',
          toCurrency: '',
          isActive: '',
          effectiveDate: ''
        };
        this.fetchRates();
      },
      sortBy(field) {
        if (this.sortField === field) {
          this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
          this.sortField = field;
          this.sortDirection = 'asc';
        }
      },
      confirmDelete(rate) {
        this.selectedRate = rate;
        this.showDeleteModal = true;
      },
      cancelDelete() {
        this.showDeleteModal = false;
        this.selectedRate = {};
      },
      async deleteRate() {
        try {
          const response = await axios.delete(`/accounting/currency-rates/${this.selectedRate.rate_id}`);
          
          if (response.data.status === 'success') {
            this.rates = this.rates.filter(rate => rate.rate_id !== this.selectedRate.rate_id);
            this.$toast.success('Exchange rate deleted successfully!');
          } else {
            this.$toast.error('Failed to delete exchange rate');
          }
        } catch (error) {
          console.error('Error deleting exchange rate:', error);
          this.$toast.error(error.response?.data?.message || 'An error occurred while deleting exchange rate');
        } finally {
          this.showDeleteModal = false;
          this.selectedRate = {};
        }
      }
    }
  };
  </script>
  
  <style scoped>
  .currency-rates-container {
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
  
  .filters-container {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 1rem;
    margin-bottom: 1.5rem;
  }
  
  .search-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: flex-end;
  }
  
  .form-group {
    min-width: 150px;
    flex: 1;
  }
  
  .form-group label {
    display: block;
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
    color: var(--gray-600);
  }
  
  .form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid var(--gray-300);
    border-radius: 0.25rem;
    font-size: 0.875rem;
  }
  
  .loading-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2rem;
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }
  
  .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 3rem 1rem;
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    text-align: center;
  }
  
  .empty-icon {
    font-size: 3rem;
    color: var(--gray-400);
    margin-bottom: 1rem;
  }
  
  .empty-state h3 {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
  }
  
  .empty-state p {
    max-width: 400px;
    margin-bottom: 1.5rem;
    color: var(--gray-600);
  }
  
  .table {
    width: 100%;
    border-collapse: collapse;
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
  }
  
  .table th,
  .table td {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid var(--gray-200);
  }
  
  .table th {
    font-weight: 600;
    background-color: var(--gray-50);
  }
  
  .sortable {
    cursor: pointer;
    user-select: none;
  }
  
  .sortable:hover {
    background-color: var(--gray-100);
  }
  
  .status-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
  }
  
  .status-active {
    background-color: #a7f3d0;
    color: #065f46;
  }
  
  .status-inactive {
    background-color: #fecaca;
    color: #7f1d1d;
  }
  
  .actions-cell {
    display: flex;
    gap: 0.5rem;
  }
  
  .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
  }
  
  .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
  }
  
  .btn-primary {
    background-color: var(--primary-color);
    color: white;
  }
  
  .btn-primary:hover {
    background-color: var(--primary-dark);
  }
  
  .btn-secondary {
    background-color: var(--gray-600);
    color: white;
  }
  
  .btn-secondary:hover {
    background-color: var(--gray-700);
  }
  
  .btn-info {
    background-color: #0ea5e9;
    color: white;
  }
  
  .btn-info:hover {
    background-color: #0284c7;
  }
  
  .btn-danger {
    background-color: #dc2626;
    color: white;
  }
  
  .btn-danger:hover {
    background-color: #b91c1c;
  }
  
  .btn-outline-secondary {
    background-color: transparent;
    border: 1px solid var(--gray-600);
    color: var(--gray-600);
  }
  
  .btn-outline-secondary:hover {
    background-color: var(--gray-100);
  }
  
  /* Modal Styles */
  .modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 999;
  }
  
  .modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
  }
  
  .modal-content {
    width: 100%;
    max-width: 500px;
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    overflow: hidden;
  }
  
  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid var(--gray-200);
  }
  
  .modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
  }
  
  .close-btn {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: var(--gray-500);
    cursor: pointer;
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
  
  /* Responsive styles */
  @media (max-width: 768px) {
    .search-filters {
      flex-direction: column;
      align-items: stretch;
    }
    
    .form-group {
      min-width: 100%;
    }
    
    .table-responsive {
      overflow-x: auto;
    }
  }
  </style>