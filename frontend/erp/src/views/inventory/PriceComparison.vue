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
    <div v-if="showDetailModal" class="modal" tabindex="-1" style="display: block;">
      <div class="modal-backdrop fade show"></div>
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              Detail Harga {{ detailModalType === 'purchase' ? 'Beli' : 'Jual' }}
            </h5>
            <button type="button" class="btn-close" @click="showDetailModal = false"></button>
          </div>
          <div class="modal-body">
            <div v-if="isLoadingDetails" class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <div v-else-if="priceDetail">
              <h6 class="mb-3">Detail Item</h6>
              <div class="row mb-4">
                <div class="col-sm-6">
                  <strong>Item Code:</strong> {{ priceDetail.item_code }}
                </div>
                <div class="col-sm-6">
                  <strong>Item Name:</strong> {{ priceDetail.item_name }}
                </div>
              </div>
  
              <h6 class="mb-3">Perbandingan Harga</h6>
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Price Type</th>
                    <th>Specific {{ detailModalType === 'purchase' ? 'Vendor' : 'Customer' }}</th>
                    <th>Min Quantity</th>
                    <th class="text-end">Price (Base Currency)</th>
                    <th class="text-end">Price ({{ priceDetail.currency }})</th>
                    <th class="text-center">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Row untuk harga khusus -->
                  <tr v-if="priceDetail[detailModalType === 'purchase' ? 'vendor_id' : 'customer_id']" class="table-success">
                    <td>{{ detailModalType === 'purchase' ? 'Vendor' : 'Customer' }} Specific</td>
                    <td>{{ getSpecificName() }}</td>
                    <td>{{ priceDetail.quantity }}</td>
                    <td class="text-end">{{ formatPrice(priceDetail.price_in_base_currency) }}</td>
                    <td class="text-end">{{ formatPrice(priceDetail.price) }}</td>
                    <td class="text-center">
                      <span class="badge bg-primary">Selected</span>
                    </td>
                  </tr>
                  <!-- Row untuk harga umum -->
                  <tr v-if="priceDetail[detailModalType === 'purchase' ? 'vendor_id' : 'customer_id']">
                    <td>General</td>
                    <td>-</td>
                    <td>{{ priceDetail.quantity }}</td>
                    <td class="text-end">{{ formatPrice(generalPriceDetail?.price_in_base_currency) }}</td>
                    <td class="text-end">{{ formatPrice(generalPriceDetail?.price) }}</td>
                    <td class="text-center">
                      <span class="badge bg-secondary">Alternative</span>
                    </td>
                  </tr>
                  <!-- Row jika yang dipilih adalah harga umum -->
                  <tr v-else class="table-success">
                    <td>General</td>
                    <td>-</td>
                    <td>{{ priceDetail.quantity }}</td>
                    <td class="text-end">{{ formatPrice(priceDetail.price_in_base_currency) }}</td>
                    <td class="text-end">{{ formatPrice(priceDetail.price) }}</td>
                    <td class="text-center">
                      <span class="badge bg-primary">Selected</span>
                    </td>
                  </tr>
                </tbody>
              </table>
  
              <div class="alert alert-info mt-4">
                <h6 class="alert-heading">Informasi</h6>
                <p class="mb-0">
                  {{ detailModalType === 'purchase' 
                     ? 'Harga beli terbaik dipilih berdasarkan prioritas: harga vendor spesifik → harga umum.' 
                     : 'Harga jual terbaik dipilih berdasarkan prioritas: harga customer spesifik → harga umum.' }}
                </p>
                <p class="mb-0 mt-2">
                  Quantity yang ditampilkan: {{ quantity }} unit
                </p>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showDetailModal = false">
              Tutup
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
  name: 'PriceComparison',
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
      detailModalType: null,
      priceDetail: null,
      generalPriceDetail: null,
      isLoadingDetails: false
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
        const response = await axios.get('/purchasing/vendors');
        this.vendors = response.data.data || [];
      } catch (error) {
        console.error('Failed to load vendors:', error);
      }
    },
    async loadCustomers() {
      try {
        const response = await axios.get('/sales/customers');
        this.customers = response.data.data || [];
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
    async loadPriceDetails() {
      try {
        this.isLoadingDetails = true;
        
        const endpoint = this.detailModalType === 'purchase' 
          ? `/items/${this.selectedItemId}/best-purchase-price`
          : `/items/${this.selectedItemId}/best-sale-price`;
        
        const response = await axios.get(endpoint, {
          params: {
            quantity: this.quantity
          }
        });
        this.priceDetail = response.data.data;
        
        // Load general price for comparison if specific price is selected
        if (this.priceDetail[this.detailModalType === 'purchase' ? 'vendor_id' : 'customer_id']) {
          const generalResponse = await axios.get(endpoint, {
            params: {
              quantity: this.quantity
            }
          });
          this.generalPriceDetail = generalResponse.data.data;
        }
      } catch (error) {
        console.error('Failed to load price details:', error);
        this.$toast.error('Gagal memuat detail harga');
      } finally {
        this.isLoadingDetails = false;
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
    getSpecificName() {
      if (this.detailModalType === 'purchase') {
        const vendor = this.vendors.find(v => v.vendor_id === this.priceDetail.vendor_id);
        return vendor ? vendor.name : 'Unknown Vendor';
      } else {
        const customer = this.customers.find(c => c.customer_id === this.priceDetail.customer_id);
        return customer ? customer.name : 'Unknown Customer';
      }
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
      this.loadPriceDetails();
    },
    viewSalePriceDetails() {
      this.detailModalType = 'sale';
      this.showDetailModal = true;
      this.loadPriceDetails();
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

.modal {
  background-color: rgba(0, 0, 0, 0.5);
}

.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.3);
}

.alert-heading {
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.badge {
  padding: 0.5rem;
}
</style>