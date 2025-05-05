<!-- src/views/inventory/components/PriceFormModal.vue -->
<template>
    <div class="modal" tabindex="-1" style="display: block;">
      <div class="modal-backdrop fade show"></div>
      <div class="modal-dialog modal-md">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ isEditing ? 'Edit Harga Item' : 'Tambah Harga Item' }}
            </h5>
            <button type="button" class="btn-close" @click="close"></button>
          </div>
          <form @submit.prevent="submitForm">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Tipe Harga</label>
                <select class="form-select" v-model="form.price_type" required>
                  <option value="purchase">Beli</option>
                  <option value="sale">Jual</option>
                </select>
              </div>
  
              <div class="mb-3">
                <label class="form-label">Harga</label>
                <input 
                  type="number" 
                  class="form-control" 
                  v-model="form.price" 
                  step="0.01" 
                  required
                >
              </div>
  
              <div class="mb-3">
                <label class="form-label">Currency</label>
                <select class="form-select" v-model="form.currency_code" required>
                  <option value="IDR">IDR</option>
                  <option value="USD">USD</option>
                  <option value="EUR">EUR</option>
                  <option value="SGD">SGD</option>
                </select>
              </div>
  
              <div class="mb-3">
                <label class="form-label">Minimum Quantity</label>
                <input 
                  type="number" 
                  class="form-control" 
                  v-model="form.min_quantity"
                  step="1"
                  min="1"
                >
              </div>
  
              <div v-if="form.price_type === 'purchase'" class="mb-3">
                <label class="form-label">Vendor (Opsional)</label>
                <select class="form-select" v-model="form.vendor_id">
                  <option value="">-- Pilih Vendor --</option>
                  <option v-for="vendor in filteredVendors" :key="vendor.vendor_id" :value="vendor.vendor_id">
                    {{ vendor.vendor_code }} - {{ vendor.name }}
                  </option>
                </select>
              </div>
  
              <div v-if="form.price_type === 'sale'" class="mb-3">
                <label class="form-label">Customer (Opsional)</label>
                <select class="form-select" v-model="form.customer_id" @change="loadCustomerList">
                  <option value="">-- Pilih Customer --</option>
                  <option v-for="customer in customers" :key="customer.customer_id" :value="customer.customer_id">
                    {{ customer.customer_code }} - {{ customer.name }}
                  </option>
                </select>
              </div>
  
              <div class="mb-3">
                <label class="form-label">Tanggal Mulai</label>
                <input 
                  type="date" 
                  class="form-control" 
                  v-model="form.start_date"
                >
              </div>
  
              <div class="mb-3">
                <label class="form-label">Tanggal Berakhir</label>
                <input 
                  type="date" 
                  class="form-control" 
                  v-model="form.end_date"
                >
              </div>
  
              <div class="mb-3">
                <div class="form-check">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    v-model="form.is_active"
                    id="isActiveCheck"
                  >
                  <label class="form-check-label" for="isActiveCheck">
                    Aktif
                  </label>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="close">
                Batal
              </button>
              <button type="submit" class="btn btn-primary" :disabled="isLoading">
                <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                {{ isEditing ? 'Update' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    name: 'PriceFormModal',
    props: {
      isEditing: {
        type: Boolean,
        default: false
      },
      itemId: {
        type: String,
        required: true
      },
      price: {
        type: Object,
        default: null
      }
    },
    emits: ['close', 'saved'],
    data() {
      return {
        form: {
          price_type: 'purchase',
          price: 0,
          currency_code: 'IDR',
          min_quantity: 1,
          vendor_id: '',
          customer_id: '',
          start_date: '',
          end_date: '',
          is_active: true
        },
        vendors: [],
        customers: [],
        isLoading: false
      };
    },
    computed: {
      filteredVendors() {
        return (this.vendors || []).filter(vendor => vendor !== null && vendor !== undefined);
      }
    },
    watch: {
      'form.price_type'() {
        // Reset ID saat tipe harga berubah
        this.form.vendor_id = '';
        this.form.customer_id = '';
      }
    },
    mounted() {
      console.log('PriceFormModal mounted. isEditing:', this.isEditing, 'price:', this.price);
      if (this.isEditing && this.price !== null && this.price !== undefined) {
        // Ensure form is always an object and vendor_id/customer_id are strings
        Object.assign(this.form, { 
          price_type: this.price.price_type || 'purchase',
          price: this.price.price || 0,
          currency_code: this.price.currency_code || 'IDR',
          min_quantity: this.price.min_quantity || 1,
          vendor_id: this.price.vendor_id || '',
          customer_id: this.price.customer_id || '',
          start_date: this.price.start_date || '',
          end_date: this.price.end_date || '',
          is_active: this.price.is_active !== undefined ? this.price.is_active : true
        });
      } else {
        // Reset form to default values when adding new price
        this.form = {
          price_type: 'purchase',
          price: 0,
          currency_code: 'IDR',
          min_quantity: 1,
          vendor_id: '',
          customer_id: '',
          start_date: '',
          end_date: '',
          is_active: true
        };
      }
      
      if (this.form.price_type === 'purchase') {
        this.loadVendorList();
      } else {
        this.loadCustomerList();
      }
    },
    methods: {
      async loadVendorList() {
        try {
          const response = await axios.get('/vendors');
          console.log('Vendor API response:', response.data);
          const vendorData = response.data.data;
          if (vendorData && Array.isArray(vendorData.data)) {
            this.vendors = vendorData.data.filter(vendor => vendor !== null && vendor !== undefined);
          } else {
            console.warn('Vendor data is not an array:', vendorData);
            this.vendors = [];
          }
        } catch (error) {
          console.error('Failed to load vendors:', error);
        }
      },
      async loadCustomerList() {
        try {
          const response = await axios.get('/customers');
          this.customers = response.data.data;
        } catch (error) {
          console.error('Failed to load customers:', error);
        }
      },
      async submitForm() {
        try {
          this.isLoading = true;
          const url = this.isEditing 
            ? `/items/${this.itemId}/prices/${this.price.price_id}`
            : `/items/${this.itemId}/prices`;
          
          const method = this.isEditing ? 'put' : 'post';
          const response = await axios[method](url, this.form);
          
          this.$toast.success(this.isEditing ? 'Harga berhasil diupdate' : 'Harga berhasil ditambahkan');
          this.$emit('saved', response.data.data);
        } catch (error) {
          console.error('Failed to save price:', error);
          this.$toast.error('Gagal menyimpan harga');
        } finally {
          this.isLoading = false;
        }
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
  
  .form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
  }
  </style>
