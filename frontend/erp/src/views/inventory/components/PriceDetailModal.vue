<!-- src/views/inventory/components/PriceDetailModal.vue -->
<template>
    <div class="modal" tabindex="-1" style="display: block;">
      <div class="modal-backdrop fade show"></div>
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              Detail Harga {{ priceType === 'purchase' ? 'Beli' : 'Jual' }}
            </h5>
            <button type="button" class="btn-close" @click="close"></button>
          </div>
          <div class="modal-body">
            <div v-if="isLoading" class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <div v-else-if="priceData">
              <h6 class="mb-3">Detail Item</h6>
              <div class="row mb-4">
                <div class="col-sm-6">
                  <strong>Item Code:</strong> {{ priceData.item_code }}
                </div>
                <div class="col-sm-6">
                  <strong>Item Name:</strong> {{ priceData.item_name }}
                </div>
              </div>
  
              <h6 class="mb-3">Perbandingan Harga</h6>
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Price Type</th>
                    <th>Specific {{ priceType === 'purchase' ? 'Vendor' : 'Customer' }}</th>
                    <th>Min Quantity</th>
                    <th class="text-end">Price (Base Currency)</th>
                    <th class="text-end">Price ({{ priceData.currency }})</th>
                    <th class="text-center">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Row untuk harga khusus -->
                  <tr v-if="priceData[priceType === 'purchase' ? 'vendor_id' : 'customer_id']" class="table-success">
                    <td>{{ priceType === 'purchase' ? 'Vendor' : 'Customer' }} Specific</td>
                    <td>{{ getSpecificName() }}</td>
                    <td>{{ priceData.quantity }}</td>
                    <td class="text-end">{{ formatPrice(priceData.price_in_base_currency) }}</td>
                    <td class="text-end">{{ formatPrice(priceData.price) }}</td>
                    <td class="text-center">
                      <span class="badge bg-primary">Selected</span>
                    </td>
                  </tr>
                  <!-- Row untuk harga umum -->
                  <tr v-if="priceData[priceType === 'purchase' ? 'vendor_id' : 'customer_id']">
                    <td>General</td>
                    <td>-</td>
                    <td>{{ priceData.quantity }}</td>
                    <td class="text-end">{{ formatPrice(generalPrice?.price_in_base_currency) }}</td>
                    <td class="text-end">{{ formatPrice(generalPrice?.price) }}</td>
                    <td class="text-center">
                      <span class="badge bg-secondary">Alternative</span>
                    </td>
                  </tr>
                  <!-- Row jika yang dipilih adalah harga umum -->
                  <tr v-else class="table-success">
                    <td>General</td>
                    <td>-</td>
                    <td>{{ priceData.quantity }}</td>
                    <td class="text-end">{{ formatPrice(priceData.price_in_base_currency) }}</td>
                    <td class="text-end">{{ formatPrice(priceData.price) }}</td>
                    <td class="text-center">
                      <span class="badge bg-primary">Selected</span>
                    </td>
                  </tr>
                </tbody>
              </table>
  
              <div class="alert alert-info mt-4">
                <h6 class="alert-heading">Informasi</h6>
                <p class="mb-0">
                  {{ priceType === 'purchase' 
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
            <button type="button" class="btn btn-secondary" @click="close">
              Tutup
            </button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    name: 'PriceDetailModal',
    props: {
      priceType: {
        type: String,
        required: true,
        validator: value => ['purchase', 'sale'].includes(value)
      },
      itemId: {
        type: String,
        required: true
      },
      quantity: {
        type: Number,
        default: 1
      }
    },
    emits: ['close'],
    data() {
      return {
        priceData: null,
        generalPrice: null,
        isLoading: false,
        vendors: [],
        customers: []
      };
    },
    mounted() {
      this.loadPriceDetails();
      this.loadVendors();
      this.loadCustomers();
    },
    methods: {
      async loadPriceDetails() {
        try {
          this.isLoading = true;
          
          const endpoint = this.priceType === 'purchase' 
            ? `/items/${this.itemId}/best-purchase-price`
            : `/items/${this.itemId}/best-sale-price`;
          
          const response = await axios.get(endpoint, {
            params: {
              quantity: this.quantity
            }
          });
          this.priceData = response.data.data;
          
          // Load general price for comparison if specific price is selected
          if (this.priceData[this.priceType === 'purchase' ? 'vendor_id' : 'customer_id']) {
            const generalResponse = await axios.get(endpoint, {
              params: {
                quantity: this.quantity
              }
            });
            this.generalPrice = generalResponse.data.data;
          }
        } catch (error) {
          console.error('Failed to load price details:', error);
          this.$toast.error('Gagal memuat detail harga');
        } finally {
          this.isLoading = false;
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
      getSpecificName() {
        if (this.priceType === 'purchase') {
          const vendor = this.vendors.find(v => v.vendor_id === this.priceData.vendor_id);
          return vendor ? vendor.name : 'Unknown Vendor';
        } else {
          const customer = this.customers.find(c => c.customer_id === this.priceData.customer_id);
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
      close() {
        this.$emit('close');
      }
    }
  };
  </script>
  
  <style scoped>
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
  
  .table th {
    font-weight: 600;
    color: var(--gray-700);
    background-color: var(--gray-50);
    border-top: 0;
  }
  
  .alert-heading {
    margin-bottom: 0.5rem;
    font-weight: 600;
  }
  
  .badge {
    padding: 0.5rem;
  }
  </style>