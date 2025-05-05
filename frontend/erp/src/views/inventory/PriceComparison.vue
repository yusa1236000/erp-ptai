<!-- src/views/inventory/PriceComparison.vue -->
<template>
    <div class="container-fluid">
      <div class="page-header">
        <h1 class="h2 mb-4">Perbandingan Harga Item</h1>
      </div>
  
      <!-- Filter -->
      <div class="card mb-4">
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <label class="form-label">Pilih Item</label>
              <select class="form-select" v-model="selectedItemId" @change="loadPriceComparison">
                <option value="">-- Pilih Item --</option>
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
                v-model="quantity" 
                min="1"
                @change="loadPriceComparison"
              >
            </div>
            <div class="col-md-3">
              <label class="form-label">Currency</label>
              <select class="form-select" v-model="currency" @change="loadPriceComparison">
                <option value="IDR">IDR</option>
                <option value="USD">USD</option>
                <option value="EUR">EUR</option>
                <option value="SGD">SGD</option>
              </select>
            </div>
          </div>
        </div>
      </div>
  
      <div v-if="selectedItemId">
        <div class="row">
          <!-- Purchase Prices -->
          <div class="col-lg-6 mb-4">
            <div class="card">
              <div class="card-header">
                <h4 class="mb-0">Harga Beli Terbaik</h4>
              </div>
              <div class="card-body" v-if="purchasePrice">
                <div class="price-display mb-4">
                  <h3 class="price-amount">{{ formatPrice(purchasePrice.price) }}</h3>
                  <span class="price-currency">{{ purchasePrice.currency }}</span>
                </div>
                
                <dl class="row mb-0">
                  <dt class="col-sm-4">Item Code:</dt>
                  <dd class="col-sm-8">{{ purchasePrice.item_code }}</dd>
                  
                  <dt class="col-sm-4">Item Name:</dt>
                  <dd class="col-sm-8">{{ purchasePrice.item_name }}</dd>
                  
                  <dt class="col-sm-4">Quantity:</dt>
                  <dd class="col-sm-8">{{ purchasePrice.quantity }}</dd>
                  
                  <dt class="col-sm-4">Vendor:</dt>
                  <dd class="col-sm-8">{{ getVendorName(purchasePrice.vendor_id) || 'N/A' }}</dd>
                </dl>
  
                <button 
                  @click="viewPurchasePriceDetails"
                  class="btn btn-outline-primary btn-sm mt-3"
                >
                  Lihat Detail
                </button>
              </div>
              <div class="card-body" v-else-if="isLoading">
                <div class="text-center py-3">
                  <div class="spinner-border text-primary" role="status"></div>
                </div>
              </div>
              <div class="card-body" v-else>
                <div class="alert alert-info mb-0">
                  <i class="fas fa-info-circle me-2"></i>
                  Pilih item untuk melihat harga beli
                </div>
              </div>
            </div>
          </div>
  
          <!-- Sale Prices -->
          <div class="col-lg-6 mb-4">
            <div class="card">
              <div class="card-header">
                <h4 class="mb-0">Harga Jual Terbaik</h4>
              </div>
              <div class="card-body" v-if="salePrice">
                <div class="price-display mb-4">
                  <h3 class="price-amount">{{ formatPrice(salePrice.price) }}</h3>
                  <span class="price-currency">{{ salePrice.currency }}</span>
                </div>
                
                <dl class="row mb-0">
                  <dt class="col-sm-4">Item Code:</dt>
                  <dd class="col-sm-8">{{ salePrice.item_code }}</dd>
                  
                  <dt class="col-sm-4">Item Name:</dt>
                  <dd class="col-sm-8">{{ salePrice.item_name }}</dd>
                  
                  <dt class="col-sm-4">Quantity:</dt>
                  <dd class="col-sm-8">{{ salePrice.quantity }}</dd>
                  
                  <dt class="col-sm-4">Customer:</dt>
                  <dd class="col-sm-8">{{ getCustomerName(salePrice.customer_id) || 'N/A' }}</dd>
                </dl>
  
                <button 
                  @click="viewSalePriceDetails"
                  class="btn btn-outline-primary btn-sm mt-3"
                >
                  Lihat Detail
                </button>
              </div>
              <div class="card-body" v-else-if="isLoading">
                <div class="text-center py-3">
                  <div class="spinner-border text-primary" role="status"></div>
                </div>
              </div>
              <div class="card-body" v-else>
                <div class="alert alert-info mb-0">
                  <i class="fas fa-info-circle me-2"></i>
                  Pilih item untuk melihat harga jual
                </div>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Multi-currency comparison -->
        <div class="card mb-4" v-if="pricesInCurrencies">
          <div class="card-header">
            <h4 class="mb-0">Perbandingan Harga Multi-Currency</h4>
          </div>
          <div class="card-body">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Currency</th>
                  <th class="text-end">Purchase Price</th>
                  <th class="text-end">Sale Price</th>
                  <th class="text-center">Base Currency</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(prices, currency) in pricesInCurrencies.prices" :key="currency">
                  <td>{{ currency }}</td>
                  <td class="text-end">{{ formatPrice(prices.purchase_price) }}</td>
                  <td class="text-end">{{ formatPrice(prices.sale_price) }}</td>
                  <td class="text-center">
                    <span v-if="prices.is_base_currency" class="badge bg-primary">Base</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
  
      <!-- Price Detail Modal -->
      <PriceDetailModal
        v-if="showDetailModal"
        :priceType="detailModalType"
        :itemId="selectedItemId"
        :quantity="quantity"
        @close="showDetailModal = false"
      />
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import PriceDetailModal from './components/PriceDetailModal.vue';
  
  export default {
    name: 'PriceComparison',
    components: {
      PriceDetailModal
    },
    data() {
      return {
        items: [],
        vendors: [],
        customers: [],
        selectedItemId: '',
        quantity: 1,
        currency: 'IDR',
        purchasePrice: null,
        salePrice: null,
        pricesInCurrencies: null,
        isLoading: false,
        showDetailModal: false,
        detailModalType: null
      };
    },
    mounted() {
      this.loadItems();
      this.loadVendors();
      this.loadCustomers();
    },
    methods: {
      async loadItems() {
        try {
          const response = await axios.get('/items');
          this.items = response.data.data;
        } catch (error) {
          console.error('Failed to load items:', error);
          this.$toast.error('Gagal memuat daftar item');
        }
      },
      async loadVendors() {
        try {
          const response = await axios.get('/vendors');
          this.vendors = response.data.data;
        } catch (error) {
          console.error('Failed to load vendors:', error);
        }
      },
      async loadCustomers() {
        try {
          const response = await axios.get('/customers');
          this.customers = response.data.data;
        } catch (error) {
          console.error('Failed to load customers:', error);
        }
      },
      async loadPriceComparison() {
        if (!this.selectedItemId) {
          this.purchasePrice = null;
          this.salePrice = null;
          this.pricesInCurrencies = null;
          return;
        }
  
        try {
          this.isLoading = true;
          
          // Get best purchase price
          const purchaseResponse = await axios.get(`/items/${this.selectedItemId}/best-purchase-price`, {
            params: {
              quantity: this.quantity,
              currency_code: this.currency
            }
          });
          this.purchasePrice = purchaseResponse.data.data;
  
          // Get best sale price
          const saleResponse = await axios.get(`/items/${this.selectedItemId}/best-sale-price`, {
            params: {
              quantity: this.quantity,
              currency_code: this.currency
            }
          });
          this.salePrice = saleResponse.data.data;
  
          // Get prices in multiple currencies
          const currenciesResponse = await axios.get(`/items/${this.selectedItemId}/prices-in-currencies`, {
            params: {
              currencies: ['IDR', 'USD', 'EUR', 'SGD']
            }
          });
          this.pricesInCurrencies = currenciesResponse.data.data;
          
        } catch (error) {
          console.error('Failed to load price comparison:', error);
          this.$toast.error('Gagal memuat perbandingan harga');
        } finally {
          this.isLoading = false;
        }
      },
      getVendorName(vendorId) {
        if (!vendorId) return null;
        const vendor = this.vendors.find(v => v.vendor_id === vendorId);
        return vendor ? vendor.name : 'Unknown Vendor';
      },
      getCustomerName(customerId) {
        if (!customerId) return null;
        const customer = this.customers.find(c => c.customer_id === customerId);
        return customer ? customer.name : 'Unknown Customer';
      },
      formatPrice(value) {
        if (!value) return '0.00';
        return new Intl.NumberFormat('id-ID', {
          style: 'decimal',
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        }).format(value);
      },
      viewPurchasePriceDetails() {
        this.detailModalType = 'purchase';
        this.showDetailModal = true;
      },
      viewSalePriceDetails() {
        this.detailModalType = 'sale';
        this.showDetailModal = true;
      }
    }
  };
  </script>
  
  <style scoped>
  .page-header {
    margin-bottom: 2rem;
  }
  
  .price-display {
    text-align: center;
    padding: 1rem 0;
  }
  
  .price-amount {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 0;
    color: var(--primary-color);
  }
  
  .price-currency {
    font-size: 1.25rem;
    color: var(--gray-600);
  }
  
  .card-header {
    background-color: var(--gray-50);
    border-bottom: 1px solid var(--gray-200);
  }
  
  .card-header h4 {
    color: var(--gray-800);
  }
  
  dl.row dt {
    font-weight: 600;
    color: var(--gray-700);
  }
  
  dl.row dd {
    color: var(--gray-900);
    margin-bottom: 0.5rem;
  }
  
  .table th {
    font-weight: 600;
    color: var(--gray-700);
    background-color: var(--gray-50);
    border-top: 0;
  }
  
  .alert {
    border-radius: 6px;
  }
  
  .btn {
    border-radius: 4px;
    padding: 0.5rem 1rem;
  }
  </style>