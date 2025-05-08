<template>
    <div class="container-fluid">
      <!-- Page Header -->
      <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Item Prices Management</h1>
      </div>
  
      <!-- Tab Navigation -->
      <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
          <a class="nav-link" :class="{ active: activeTab === 'list' }" @click="activeTab = 'list'">
            <i class="fas fa-list me-2"></i>Price List
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" :class="{ active: activeTab === 'comparison' }" @click="activeTab = 'comparison'">
            <i class="fas fa-balance-scale me-2"></i>Price Comparison
          </a>
        </li>
      </ul>
  
      <!-- Item Price List Tab -->
      <div v-if="activeTab === 'list'" class="tab-content">
        <!-- Filter Section -->
        <div class="card mb-4">
          <div class="card-body">
            <div class="row align-items-end">
              <div class="col-md-8">
                <label class="form-label">Select Item</label>
                <select class="form-select" v-model="selectedItemId" @change="loadItemPrices">
                  <option value="">-- Select Item --</option>
                  <option v-for="item in items" :key="item.item_id" :value="item.item_id">
                    {{ item.item_code }} - {{ item.name }}
                  </option>
                </select>
              </div>
              <div class="col-md-4 text-end">
                <button 
                  class="btn btn-primary" 
                  @click="openAddPriceModal"
                  :disabled="!selectedItemId"
                >
                  <i class="fas fa-plus me-2"></i>Add Price
                </button>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Price Filters -->
        <div v-if="selectedItemId" class="card mb-4">
          <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <label class="form-label">Price Type</label>
                <select class="form-select" v-model="priceTypeFilter" @change="filterPrices">
                  <option value="">All</option>
                  <option value="purchase">Purchase</option>
                  <option value="sale">Sale</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" v-model="activeFilter" @change="filterPrices">
                  <option value="">All</option>
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Currency</label>
                <select class="form-select" v-model="currencyFilter" @change="filterPrices">
                  <option value="">All</option>
                  <option v-for="currency in currencies" :key="currency" :value="currency">
                    {{ currency }}
                  </option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="form-check">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    v-model="currentOnlyFilter" 
                    @change="filterPrices"
                    id="currentOnlyCheck"
                  >
                  <label class="form-check-label" for="currentOnlyCheck">
                    Currently Valid Only
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Price List Table -->
        <div v-if="selectedItemId" class="card">
          <div class="card-body">
            <div v-if="isLoading" class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <div v-else-if="filteredPrices.length === 0" class="text-center py-5">
              <i class="fas fa-tags fa-3x text-muted mb-3"></i>
              <h4>No Price Data</h4>
              <p class="text-muted">No prices have been set for this item yet.</p>
            </div>
            <div v-else class="table-responsive">
              <table class="table table-hover table-striped">
                <thead>
                  <tr>
                    <th @click="sortTable('price_type')">
                      Type 
                      <i v-if="sortKey === 'price_type'" 
                         :class="sortOrder === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down'"></i>
                    </th>
                    <th @click="sortTable('price')">
                      Price 
                      <i v-if="sortKey === 'price'" 
                         :class="sortOrder === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down'"></i>
                    </th>
                    <th>Currency</th>
                    <th>Min Qty.</th>
                    <th>Status</th>
                    <th>Valid From</th>
                    <th>Valid To</th>
                    <th>Customer/Vendor</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
<tr v-for="(price, index) in sortedPrices" :key="price.price_id || index">
                    <td>{{ price.price_type === 'purchase' ? 'Purchase' : 'Sale' }}</td>
                    <td>{{ formatPrice(price.price) }}</td>
                    <td>{{ price.currency_code }}</td>
                    <td>{{ price.min_quantity }}</td>
                    <td>
                      <span :class="['badge', price.is_active ? 'bg-success' : 'bg-secondary']">
                        {{ price.is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </td>
                    <td>{{ formatDate(price.start_date) }}</td>
                    <td>{{ formatDate(price.end_date) }}</td>
                    <td>
                      <span v-if="price.customer && price.customer.customer_code">
                        {{ price.customer.customer_code }} - {{ price.customer.name }}
                      </span>
                      <span v-else-if="price.customer_id">
                        {{ getCustomerName(price.customer_id) || 'Customer #' + price.customer_id }}
                      </span>
                      <span v-else-if="price.vendor && price.vendor.vendor_code">
                        {{ price.vendor.vendor_code }} - {{ price.vendor.name }}
                      </span>
                      <span v-else-if="price.vendor_id">
                        {{ getVendorName(price.vendor_id) || 'Vendor #' + price.vendor_id }}
                      </span>
                      <span v-else>-</span>
                    </td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary me-1" @click="editPrice(price)" title="Edit">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(price)" title="Delete">
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Price Comparison Tab -->
      <div v-if="activeTab === 'comparison'" class="tab-content">
        <div class="card mb-4">
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <label class="form-label">Select Item</label>
                <select class="form-select" v-model="comparisonItemId" @change="loadPriceComparison">
                  <option value="">-- Select Item --</option>
                  <option v-for="item in items" :key="item.item_id" :value="item.item_id">
                    {{ item.item_code }} - {{ item.name }}
                  </option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Quantity</label>
                <input 
                  type="number" 
                  class="form-control" 
                  v-model.number="comparisonQuantity" 
                  min="1"
                  @change="loadPriceComparison"
                >
              </div>
              <div class="col-md-3">
                <label class="form-label">Currency</label>
                <select class="form-select" v-model="comparisonCurrency" @change="loadPriceComparison">
                  <option v-for="currency in currencies" :key="currency" :value="currency">
                    {{ currency }}
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>
  
        <div v-if="comparisonItemId" class="row">
          <!-- Purchase Price Card -->
          <div class="col-md-6 mb-4">
            <div class="card h-100">
              <div class="card-header bg-light">
                <h5 class="mb-0">Best Purchase Price</h5>
              </div>
              <div class="card-body" v-if="comparisonLoading">
                <div class="text-center py-5">
                  <div class="spinner-border text-primary" role="status"></div>
                </div>
              </div>
              <div class="card-body d-flex flex-column" v-else-if="purchasePrice">
                <div class="text-center py-3 mb-3 border-bottom">
                  <h3 class="text-primary display-6 mb-0">{{ formatPrice(purchasePrice.price) }}</h3>
                  <p class="text-muted">{{ purchasePrice.currency }}</p>
                </div>
                
                <div class="row mb-2">
                  <div class="col-5"><strong>Item:</strong></div>
                  <div class="col-7">{{ purchasePrice.item_code }}</div>
                </div>
                <div class="row mb-2">
                  <div class="col-5"><strong>Name:</strong></div>
                  <div class="col-7">{{ purchasePrice.item_name }}</div>
                </div>
                <div class="row mb-2">
                  <div class="col-5"><strong>Quantity:</strong></div>
                  <div class="col-7">{{ purchasePrice.quantity }}</div>
                </div>
                <div class="row mb-4">
                  <div class="col-5"><strong>Vendor:</strong></div>
                  <div class="col-7">{{ getVendorName(purchasePrice.vendor_id) || 'No specific vendor' }}</div>
                </div>
                
                <div class="mt-auto text-center">
                  <button class="btn btn-outline-primary" @click="viewPurchasePriceDetails">
                    View Details
                  </button>
                </div>
              </div>
              <div class="card-body" v-else>
                <div class="alert alert-info">
                  <i class="fas fa-info-circle me-2"></i>
                  Select an item to view the best purchase price.
                </div>
              </div>
            </div>
          </div>
  
          <!-- Sale Price Card -->
          <div class="col-md-6 mb-4">
            <div class="card h-100">
              <div class="card-header bg-light">
                <h5 class="mb-0">Best Sale Price</h5>
              </div>
              <div class="card-body" v-if="comparisonLoading">
                <div class="text-center py-5">
                  <div class="spinner-border text-primary" role="status"></div>
                </div>
              </div>
              <div class="card-body d-flex flex-column" v-else-if="salePrice">
                <div class="text-center py-3 mb-3 border-bottom">
                  <h3 class="text-success display-6 mb-0">{{ formatPrice(salePrice.price) }}</h3>
                  <p class="text-muted">{{ salePrice.currency }}</p>
                </div>
                
                <div class="row mb-2">
                  <div class="col-5"><strong>Item:</strong></div>
                  <div class="col-7">{{ salePrice.item_code }}</div>
                </div>
                <div class="row mb-2">
                  <div class="col-5"><strong>Name:</strong></div>
                  <div class="col-7">{{ salePrice.item_name }}</div>
                </div>
                <div class="row mb-2">
                  <div class="col-5"><strong>Quantity:</strong></div>
                  <div class="col-7">{{ salePrice.quantity }}</div>
                </div>
                <div class="row mb-4">
                  <div class="col-5"><strong>Customer:</strong></div>
                  <div class="col-7">{{ getCustomerName(salePrice.customer_id) || 'No specific customer' }}</div>
                </div>
                
                <div class="mt-auto text-center">
                  <button class="btn btn-outline-success" @click="viewSalePriceDetails">
                    View Details
                  </button>
                </div>
              </div>
              <div class="card-body" v-else>
                <div class="alert alert-info">
                  <i class="fas fa-info-circle me-2"></i>
                  Select an item to view the best sale price.
                </div>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Multi-currency Comparison -->
        <div v-if="comparisonItemId && pricesInCurrencies" class="card mb-4">
          <div class="card-header bg-light">
            <h5 class="mb-0">Multi-Currency Price Comparison</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead class="table-light">
                  <tr>
                    <th>Currency</th>
                    <th class="text-end">Purchase Price</th>
                    <th class="text-end">Sale Price</th>
                    <th class="text-center">Margin</th>
                    <th class="text-center">Base Currency</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(prices, currency) in pricesInCurrencies?.prices || {}" :key="currency">
                    <td>
                      <strong>{{ currency }}</strong>
                    </td>
                    <td class="text-end">{{ formatPrice(prices?.purchase_price) }}</td>
                    <td class="text-end">{{ formatPrice(prices?.sale_price) }}</td>
                    <td class="text-center">
                      {{ calculateMargin(prices?.purchase_price, prices?.sale_price) }}
                    </td>
                    <td class="text-center">
                      <span v-if="prices?.is_base_currency" class="badge bg-primary">Base</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Add/Edit Price Modal -->
      <div v-if="showPriceModal" class="modal-overlay">
        <div class="modal-dialog-container">
          <div class="modal-dialog modal-lg">
            <div class="modal-content solid-modal">
              <div class="modal-header solid-header">
                <h5 class="modal-title fw-bold">{{ isEditing ? 'Edit Price' : 'Add New Price' }}</h5>
                <button type="button" class="btn-close" @click="showPriceModal = false"></button>
              </div>
              <form @submit.prevent="savePrice">
                <div class="modal-body p-4">
                  <div class="mb-3">
                    <label class="form-label">Price Type</label>
                    <select class="form-select" v-model="priceForm.price_type" required>
                      <option value="purchase">Purchase</option>
                      <option value="sale">Sale</option>
                    </select>
                  </div>
                  
                  <div class="mb-3">
                    <label class="form-label">Price</label>
                    <div class="input-group">
                      <input type="number" class="form-control" v-model="priceForm.price" step="0.01" min="0" required>
                      <select class="form-select" v-model="priceForm.currency_code" required style="max-width: 100px;">
                        <option v-for="currency in currencies" :key="currency" :value="currency">
                          {{ currency }}
                        </option>
                      </select>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label class="form-label">Minimum Quantity</label>
                    <input type="number" class="form-control" v-model="priceForm.min_quantity" min="1" required>
                  </div>
                  
                  <div v-if="priceForm.price_type === 'purchase'" class="mb-3">
                    <label class="form-label">Vendor (Optional)</label>
                    <select class="form-select" v-model="priceForm.vendor_id">
                      <option :value="null">-- General Price --</option>
                      <option v-for="vendor in vendors" :key="vendor.vendor_id" :value="vendor.vendor_id">
                        {{ vendor.vendor_code }} - {{ vendor.name }}
                      </option>
                    </select>
                  </div>
                  
                  <div v-if="priceForm.price_type === 'sale'" class="mb-3">
                    <label class="form-label">Customer (Optional)</label>
                    <select class="form-select" v-model="priceForm.customer_id">
                      <option :value="''">-- General Price --</option>
                      <option v-for="customer in customers" :key="customer.customer_id" :value="customer.customer_id">
                        {{ customer.customer_code }} - {{ customer.name }}
                      </option>
                    </select>
                  </div>
                  
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Valid From</label>
                      <input type="date" class="form-control" v-model="priceForm.start_date">
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Valid To</label>
                      <input type="date" class="form-control" v-model="priceForm.end_date">
                    </div>
                  </div>
                  
                  <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" v-model="priceForm.is_active" id="is-active">
                    <label class="form-check-label" for="is-active">Active</label>
                  </div>
                </div>
                
                <div class="modal-footer solid-footer">
                  <button type="button" class="btn btn-outline-secondary" @click="showPriceModal = false">Cancel</button>
                  <button type="submit" class="btn btn-primary" :disabled="saveLoading">
                    <span v-if="saveLoading" class="spinner-border spinner-border-sm me-1"></span>
                    {{ isEditing ? 'Update' : 'Save' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Delete Confirmation Modal -->
      <div v-if="showDeleteModal" class="modal fade show" style="display: block;" tabindex="-1">
        <div class="modal-dialog modal-sm">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Confirm Delete</h5>
              <button type="button" class="btn-close" @click="showDeleteModal = false"></button>
            </div>
            <div class="modal-body">
              <p>Are you sure you want to delete this price?</p>
              <div v-if="priceToDelete" class="alert alert-warning">
                <small>
                  {{ priceToDelete.price_type === 'purchase' ? 'Purchase' : 'Sale' }} Price: 
                  {{ formatPrice(priceToDelete.price) }} {{ priceToDelete.currency_code }}
                </small>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="showDeleteModal = false">Cancel</button>
              <button type="button" class="btn btn-danger" @click="deletePrice" :disabled="deleteLoading">
                <span v-if="deleteLoading" class="spinner-border spinner-border-sm me-1"></span>
                Delete
              </button>
            </div>
          </div>
        </div>
        <div class="modal-backdrop fade show"></div>
      </div>
  
      <!-- Price Detail Modal -->
      <div v-if="showDetailModal" class="modal fade show" style="display: block;" tabindex="-1">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                Price Details: {{ detailModalType === 'purchase' ? 'Purchase' : 'Sale' }}
              </h5>
              <button type="button" class="btn-close" @click="showDetailModal = false"></button>
            </div>
            <div class="modal-body">
              <div v-if="detailsLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
              </div>
              <div v-else>
                <h6 class="border-bottom pb-2 mb-3">Item Information</h6>
                <div class="row mb-4">
                  <div class="col-md-6">
                    <div class="row mb-2">
                      <div class="col-5"><strong>Item Code:</strong></div>
                      <div class="col-7">{{ comparisonItemId ? selectedItemInfo?.item_code : '-' }}</div>
                    </div>
                    <div class="row mb-2">
                      <div class="col-5"><strong>Item Name:</strong></div>
                      <div class="col-7">{{ comparisonItemId ? selectedItemInfo?.name : '-' }}</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="row mb-2">
                      <div class="col-5"><strong>Category:</strong></div>
                      <div class="col-7">{{ comparisonItemId ? selectedItemInfo?.category?.name : '-' }}</div>
                    </div>
                    <div class="row mb-2">
                      <div class="col-5"><strong>UOM:</strong></div>
                      <div class="col-7">{{ comparisonItemId ? selectedItemInfo?.unitOfMeasure?.symbol : '-' }}</div>
                    </div>
                  </div>
                </div>
  
                <h6 class="border-bottom pb-2 mb-3">Price Calculation</h6>
                <div class="table-responsive">
                  <table class="table table-bordered">
                    <thead class="table-light">
                      <tr>
                        <th>Type</th>
                        <th>{{ detailModalType === 'purchase' ? 'Vendor' : 'Customer' }}</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr class="table-success" v-if="selectedDetailPrice">
                        <td>
                          <span class="badge bg-primary">Selected</span>
                        </td>
                        <td>
                          {{ detailModalType === 'purchase' 
                             ? (getVendorName(selectedDetailPrice.vendor_id) || 'No specific vendor')
                             : (getCustomerName(selectedDetailPrice.customer_id) || 'No specific customer') }}
                        </td>
                        <td class="text-end">{{ comparisonQuantity }}</td>
                        <td class="text-end">{{ formatPrice(selectedDetailPrice.price) }}</td>
                        <td class="text-end">{{ formatPrice(selectedDetailPrice.price * comparisonQuantity) }}</td>
                      </tr>
                      <tr v-for="(price, index) in availablePrices" :key="index">
                        <td>
                          <span class="badge bg-secondary">Alternative</span>
                        </td>
                        <td>
                          {{ detailModalType === 'purchase' 
                             ? (getVendorName(price?.vendor?.vendor_id) || 'No specific vendor') 
                             : (getCustomerName(price?.customer?.customer_id) || 'No specific customer') }}
                        </td>
                        <td class="text-end">{{ comparisonQuantity }}</td>
                        <td class="text-end">{{ formatPrice(price.price) }}</td>
                        <td class="text-end">{{ formatPrice(price.price * comparisonQuantity) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
  
                <div class="alert alert-info mt-3">
                  <strong>Price Selection Logic:</strong> 
                  <p class="mb-0">
                    {{ detailModalType === 'purchase' 
                       ? 'Purchase prices are selected with this priority: vendor-specific → general price.' 
                       : 'Sale prices are selected with this priority: customer-specific → general price.' }}
                    Prices for a specific quantity take precedence over generic prices.
                  </p>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="showDetailModal = false">Close</button>
            </div>
          </div>
        </div>
        <div class="modal-backdrop fade show"></div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    name: 'ItemPriceManagement',
    data() {
      return {
        // General
        activeTab: 'list',
        items: [],
        vendors: [],
        customers: [],
        currencies: ['IDR', 'USD', 'EUR', 'SGD', 'JPY', 'CNY'],
        
        // Price List Tab
        selectedItemId: '',
        prices: [],
        filteredPrices: [],
        isLoading: false,
        priceTypeFilter: '',
        activeFilter: '',
        currencyFilter: '',
        currentOnlyFilter: false,
        sortKey: 'price_type',
        sortOrder: 'asc',
        
        // Price Form Modal
        showPriceModal: false,
        isEditing: false,
        selectedPriceId: null,
        saveLoading: false,
        priceForm: {
          price_type: 'purchase',
          price: 0,
          currency_code: 'IDR',
          min_quantity: 1,
          vendor_id: null,
          customer_id: null,
          start_date: '',
          end_date: '',
          is_active: true
        },
        
        // Delete Modal
        showDeleteModal: false,
        priceToDelete: null,
        deleteLoading: false,
        
        // Price Comparison Tab
        comparisonItemId: '',
        comparisonQuantity: 1,
        comparisonCurrency: 'IDR',
        comparisonLoading: false,
        purchasePrice: null,
        salePrice: null,
        pricesInCurrencies: null,
        
        // Price Details Modal
        showDetailModal: false,
        detailModalType: null,
        detailsLoading: false,
        selectedDetailPrice: null,
        availablePrices: [],
        selectedItemInfo: null
      };
    },
    computed: {
sortedPrices() {
        // Return sorted prices based on current sort key and order
        return [...this.filteredPrices].sort((a, b) => {
          let aValue = a[this.sortKey];
          let bValue = b[this.sortKey];
          
          // Defensive checks for undefined or null
          if (aValue === undefined || aValue === null) aValue = '';
          if (bValue === undefined || bValue === null) bValue = '';
          
          // Handle nested properties or special cases
          if (this.sortKey === 'customer_id') {
            aValue = a.customer && a.customer.name ? a.customer.name : '';
            bValue = b.customer && b.customer.name ? b.customer.name : '';
          } else if (this.sortKey === 'vendor_id') {
            aValue = a.vendor && a.vendor.name ? a.vendor.name : '';
            bValue = b.vendor && b.vendor.name ? b.vendor.name : '';
          }
          
          // Handle number vs string comparison
          if (typeof aValue === 'number' && typeof bValue === 'number') {
            return this.sortOrder === 'asc' ? aValue - bValue : bValue - aValue;
          } else {
            // String comparison
            aValue = String(aValue).toLowerCase();
            bValue = String(bValue).toLowerCase();
            return this.sortOrder === 'asc' 
              ? aValue.localeCompare(bValue) 
              : bValue.localeCompare(aValue);
          }
        });
      }
    },
    mounted() {
      this.loadItems();
      this.loadVendors();
      this.loadCustomers();
    },
    methods: {
      // Helper methods
      formatPrice(value) {
        if (value === null || value === undefined) return '-';
        return new Intl.NumberFormat('id-ID', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        }).format(value);
      },
      
      formatDate(value) {
        if (!value) return '-';
        return new Date(value).toLocaleDateString('id-ID');
      },
      
      getVendorName(vendorId) {
        if (!vendorId) return null;
        const vendor = this.vendors.find(v => v.vendor_id === vendorId);
        return vendor ? vendor.name : null;
      },
      
      getCustomerName(customerId) {
        if (!customerId) return null;
        const customer = this.customers.find(c => c.customer_id === customerId);
        return customer ? customer.name : null;
      },
      
      calculateMargin(purchasePrice, salePrice) {
        // Handle null, undefined or zero values
        if (purchasePrice === null || purchasePrice === undefined || 
            salePrice === null || salePrice === undefined || 
            purchasePrice === 0) {
          return '-';
        }
        
        try {
          const margin = ((salePrice - purchasePrice) / purchasePrice) * 100;
          return isFinite(margin) ? `${margin.toFixed(2)}%` : '-';
        } catch (error) {
          console.error('Error calculating margin:', error);
          return '-';
        }
      },
      
      sortTable(key) {
        if (this.sortKey === key) {
          this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
        } else {
          this.sortKey = key;
          this.sortOrder = 'asc';
        }
      },
  
      // Data Loading Functions
      async loadItems() {
        try {
          this.isLoading = true;
          const response = await axios.get('items');
          this.items = response.data.data || [];
        } catch (error) {
          console.error('Failed to load items:', error);
          alert('Error loading items. Please try again.');
        } finally {
          this.isLoading = false;
        }
      },
      
async loadVendors() {
        try {
          const response = await axios.get('/vendors');
          const vendorsData = response.data.data.data; // Access nested data array
          this.vendors = Array.isArray(vendorsData) ? vendorsData.filter(v => v) : [];
        } catch (error) {
          console.error('Failed to load vendors:', error);
        }
      },
      
      async loadCustomers() {
        try {
          const response = await axios.get('/customers');
          const customersData = response.data.data;
          this.customers = Array.isArray(customersData) ? customersData.filter(c => c) : [];
        } catch (error) {
          console.error('Failed to load customers:', error);
        }
      },
      
      async loadItemPrices() {
        if (!this.selectedItemId) {
          this.prices = [];
          this.filteredPrices = [];
          return;
        }
        
        try {
          this.isLoading = true;
          
          const params = {};
          if (this.priceTypeFilter) params.price_type = this.priceTypeFilter;
          if (this.activeFilter !== '') params.is_active = this.activeFilter;
          if (this.currentOnlyFilter) params.current_only = true;
          
          const response = await axios.get(`items/${this.selectedItemId}/prices`, { params });
          this.prices = response.data.data || [];
          
          this.filterPrices();
        } catch (error) {
          console.error('Failed to load item prices:', error);
          alert('Error loading price data. Please try again.');
        } finally {
          this.isLoading = false;
        }
      },
      
      filterPrices() {
        let filtered = [...this.prices];
        
        if (this.priceTypeFilter) {
          filtered = filtered.filter(price => price.price_type === this.priceTypeFilter);
        }
        
        if (this.activeFilter !== '') {
          const isActive = this.activeFilter === '1';
          filtered = filtered.filter(price => price.is_active === isActive);
        }
        
        if (this.currencyFilter) {
          filtered = filtered.filter(price => price.currency_code === this.currencyFilter);
        }
        
        if (this.currentOnlyFilter) {
          const today = new Date();
          filtered = filtered.filter(price => {
            const startDate = price.start_date ? new Date(price.start_date) : null;
            const endDate = price.end_date ? new Date(price.end_date) : null;
            
            return (!startDate || startDate <= today) && (!endDate || endDate >= today);
          });
        }
        
        this.filteredPrices = filtered;
      },
      
      async loadPriceComparison() {
        if (!this.comparisonItemId) {
          this.purchasePrice = null;
          this.salePrice = null;
          this.pricesInCurrencies = null;
          return;
        }
        
        try {
          this.comparisonLoading = true;
          
          // Get item details
          const itemResponse = await axios.get(`items/${this.comparisonItemId}`);
          this.selectedItemInfo = itemResponse.data.data;
          
          // Get best purchase price
          const purchaseResponse = await axios.get(`items/${this.comparisonItemId}/best-purchase-price`, {
            params: {
              quantity: this.comparisonQuantity,
              currency_code: this.comparisonCurrency
            }
          });
          this.purchasePrice = purchaseResponse.data.data;
          
          // Get best sale price
          const saleResponse = await axios.get(`items/${this.comparisonItemId}/best-sale-price`, {
            params: {
              quantity: this.comparisonQuantity,
              currency_code: this.comparisonCurrency
            }
          });
          this.salePrice = saleResponse.data.data;
          
          // Get multi-currency comparison
          const currenciesResponse = await axios.get(`items/${this.comparisonItemId}/prices-in-currencies`, {
            params: {
              currencies: this.currencies
            }
          });
          this.pricesInCurrencies = currenciesResponse.data.data;
          
        } catch (error) {
          console.error('Failed to load price comparison:', error);
          alert('Error loading price comparison data. Please try again.');
        } finally {
          this.comparisonLoading = false;
        }
      },
      
      // Price Management Functions
async openAddPriceModal() {
        this.isEditing = false;
        this.selectedPriceId = null;
        this.priceForm = {
          price_type: 'purchase',
          price: 0,
          currency_code: 'IDR',
          min_quantity: 1,
          vendor_id: null,
          customer_id: '',
          start_date: '',
          end_date: '',
          is_active: true
        };
        if (!this.vendors || this.vendors.length === 0) {
          await this.loadVendors();
        }
        this.showPriceModal = true;
      },
      
      editPrice(price) {
        if (!price || !price.price_id) {
          console.error('Cannot edit: Invalid price data');
          return;
        }
        
        this.isEditing = true;
        this.selectedPriceId = price.price_id;
        
        const vendorId = price.vendor_id || (price.vendor ? price.vendor.vendor_id : null);
        const customerId = price.customer_id || (price.customer ? price.customer.customer_id : null);
        
        this.priceForm = {
          price_type: price.price_type || 'purchase',
          price: price.price || 0,
          currency_code: price.currency_code || 'IDR',
          min_quantity: price.min_quantity || 1,
          vendor_id: vendorId,
          customer_id: customerId,
          start_date: price.start_date || '',
          end_date: price.end_date || '',
          is_active: price.is_active !== undefined ? price.is_active : true
        };
        
        this.showPriceModal = true;
      },
      
      async savePrice() {
        try {
          this.saveLoading = true;
          
          const url = this.isEditing 
            ? `items/${this.selectedItemId}/prices/${this.selectedPriceId}`
            : `items/${this.selectedItemId}/prices`;
          
          const method = this.isEditing ? 'put' : 'post';
          
          await axios[method](url, this.priceForm);
          
          this.showPriceModal = false;
          alert(this.isEditing ? 'Price updated successfully' : 'Price added successfully');
          this.loadItemPrices();
          
          // Reload comparison if we're editing the same item
          if (this.comparisonItemId === this.selectedItemId) {
            this.loadPriceComparison();
          }
          
        } catch (error) {
          console.error('Failed to save price:', error);
          alert('Error saving price data. Please try again.');
        } finally {
          this.saveLoading = false;
        }
      },
      
      confirmDelete(price) {
        this.priceToDelete = price;
        this.showDeleteModal = true;
      },
      
      async deletePrice() {
        if (!this.priceToDelete) return;
        
        try {
          this.deleteLoading = true;
          
          await axios.delete(`items/${this.selectedItemId}/prices/${this.priceToDelete.price_id}`);
          
          this.showDeleteModal = false;
          this.priceToDelete = null;
          alert('Price deleted successfully');
          this.loadItemPrices();
          
          // Reload comparison if we're editing the same item
          if (this.comparisonItemId === this.selectedItemId) {
            this.loadPriceComparison();
          }
          
        } catch (error) {
          console.error('Failed to delete price:', error);
          alert('Error deleting price. Please try again.');
        } finally {
          this.deleteLoading = false;
        }
      },
      
      // Price Details Functions
      viewPurchasePriceDetails() {
        if (!this.purchasePrice) {
          console.warn('Purchase price data is not available');
          return;
        }
        this.detailModalType = 'purchase';
        this.selectedDetailPrice = { ...this.purchasePrice }; // Create a copy to avoid reference issues
        this.loadPriceDetails();
      },
      
      viewSalePriceDetails() {
        if (!this.salePrice) {
          console.warn('Sale price data is not available');
          return;
        }
        this.detailModalType = 'sale';
        this.selectedDetailPrice = { ...this.salePrice }; // Create a copy to avoid reference issues
        this.loadPriceDetails();
      },
      
      async loadPriceDetails() {
        this.showDetailModal = true;
        this.detailsLoading = true;
        
        try {
          // Check if we have valid data
          if (!this.comparisonItemId || !this.selectedDetailPrice) {
            console.warn('Missing required data for price details');
            this.availablePrices = [];
            return;
          }
          
          // Get all alternative prices for this item
          const priceType = this.detailModalType;
          
          const response = await axios.get(`items/${this.comparisonItemId}/prices`, {
            params: { price_type: priceType, is_active: 1 }
          });
          
          const allPrices = response.data.data || [];
          
          // Filter out the selected price
          this.availablePrices = allPrices.filter(price => {
            // Check if the current selected price has vendor info
            const selectedVendorId = this.selectedDetailPrice.vendor_id || 
              (this.selectedDetailPrice.vendor ? this.selectedDetailPrice.vendor.vendor_id : null);
              
            // Check if the current selected price has customer info
            const selectedCustomerId = this.selectedDetailPrice.customer_id || 
              (this.selectedDetailPrice.customer ? this.selectedDetailPrice.customer.customer_id : null);
              
            // Get the vendor and customer IDs for the price we're checking
            const priceVendorId = price.vendor_id || (price.vendor ? price.vendor.vendor_id : null);
            const priceCustomerId = price.customer_id || (price.customer ? price.customer.customer_id : null);
            
            // Filter vendor-specific prices
            if (selectedVendorId && priceVendorId) {
              return priceVendorId !== selectedVendorId;
            }
            
            // Filter customer-specific prices
            if (selectedCustomerId && priceCustomerId) {
              return priceCustomerId !== selectedCustomerId;
            }
            
            // Don't show general price if our selected price is general
            if (!selectedVendorId && !selectedCustomerId) {
              return !(!priceVendorId && !priceCustomerId);
            }
            
            return true;
          });
          
        } catch (error) {
          console.error('Failed to load price details:', error);
          this.availablePrices = [];
        } finally {
          this.detailsLoading = false;
        }
      }
    }
  };
  </script>
  
  <style scoped>
  .page-header {
    margin-bottom: 1.5rem;
  }
  
  .nav-tabs .nav-link {
    cursor: pointer;
    color: var(--bs-gray-700);
  }
  
  .nav-tabs .nav-link.active {
    font-weight: 500;
    color: var(--bs-primary);
  }
  
  .card {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border-radius: 0.5rem;
    border: 1px solid rgba(0, 0, 0, 0.125);
  }
  
  .card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    background-color: rgba(0, 0, 0, 0.02);
  }
  
  .table th {
    font-weight: 500;
    color: var(--bs-gray-700);
  }
  
  .table th i {
    margin-left: 0.25rem;
    font-size: 0.75rem;
  }
  
  th[role="button"],
  th[onClick] {
    cursor: pointer;
  }
  
  th:hover {
    background-color: rgba(0, 0, 0, 0.03);
  }
  
  .modal-backdrop {
    opacity: 0.5;
  }
  
  .badge {
    font-weight: 500;
    padding: 0.5em 0.7em;
  }
  
  .alert {
    border-radius: 0.375rem;
  }
  
  /* For the price cards in comparison view */
  .display-6 {
    font-size: 2.25rem;
    font-weight: 500;
  }
  
  /* Table hover effects for the multi-currency table */
  .table-bordered tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
  }
  /* Dialog Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.85);
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-dialog-container {
  width: 100%;
  max-width: 500px;
  margin: 1.75rem auto;
  position: relative;
  z-index: 1051;
}

.solid-modal {
  border-radius: 0.5rem;
  background-color: #ffffff;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
  border: none;
}

.solid-header {
  background-color: #ffffff;
  border-bottom: 1px solid #dee2e6;
  padding: 1rem;
}

.solid-footer {
  background-color: #ffffff;
  border-top: 1px solid #dee2e6;
  padding: 1rem;
}

.modal-title {
  color: #333;
  font-size: 1.25rem;
}
.modal-body {
  padding: 1.5rem;
}
.modal-form-container {
  width: 85%;
  margin: 0 auto;
}

/* Form Elements */
.form-control, .form-select {
  border: 1px solid #ced4da;
  background-color: #fff;
  padding: 0.5rem 0.75rem;
  border-radius: 0.25rem;
}

.form-control:focus, .form-select:focus {
  border-color: #80bdff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
  background-color: #fff;
}

/* Button styling */
.btn-primary {
  background-color: #0d6efd;
  border-color: #0d6efd;
  color: white;
}

.btn-primary:hover {
  background-color: #0b5ed7;
  border-color: #0a58ca;
}

.btn-outline-secondary {
  color: #6c757d;
  border-color: #6c757d;
  background-color: transparent;
}

.btn-outline-secondary:hover {
  color: #fff;
  background-color: #6c757d;
  border-color: #6c757d;
}

/* Animation for modal entry */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-overlay {
  animation: fadeIn 0.3s ease-out forwards;
}

.solid-modal {
  animation: slideDown 0.3s ease-out forwards;
}

@keyframes slideDown {
  from {
    transform: translateY(-30px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
  </style>