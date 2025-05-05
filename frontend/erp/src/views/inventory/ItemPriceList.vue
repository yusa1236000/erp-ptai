<!-- src/views/inventory/ItemPriceList.vue -->
<template>
    <div class="container-fluid">
      <div class="page-header">
        <h1 class="h2 mb-4">Harga Item</h1>
      </div>
  
      <!-- Filter untuk memilih item -->
      <div class="card mb-4">
        <div class="card-body">
          <div class="row align-items-end">
            <div class="col-md-8">
              <label class="form-label">Pilih Item</label>
              <select class="form-select" v-model="selectedItemId" @change="loadItemPrices">
                <option value="">-- Pilih Item --</option>
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
                <i class="fas fa-plus me-2"></i>Tambah Harga
              </button>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Filter harga -->
      <div v-if="selectedItemId" class="card mb-4">
        <div class="card-body">
          <div class="row">
            <div class="col-md-3">
              <label class="form-label">Tipe Harga</label>
              <select class="form-select" v-model="priceTypeFilter" @change="filterPrices">
                <option value="">Semua</option>
                <option value="purchase">Beli</option>
                <option value="sale">Jual</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Status</label>
              <select class="form-select" v-model="activeFilter" @change="filterPrices">
                <option value="">Semua</option>
                <option value="1">Aktif</option>
                <option value="0">Tidak Aktif</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Currency</label>
              <select class="form-select" v-model="currencyFilter" @change="filterPrices">
                <option value="">Semua</option>
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
                  Hanya yang sedang berlaku
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Daftar harga -->
      <div v-if="selectedItemId" class="card">
        <div class="card-body">
          <div v-if="isLoading" class="text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
          <div v-else>
            <DataTable 
              :columns="tableColumns" 
              :items="filteredPrices"
              :isLoading="isLoading"
              emptyIcon="fas fa-tags"
              emptyTitle="Tidak ada data harga"
              emptyMessage="Belum ada harga yang ditetapkan untuk item ini"
            >
              <template #actions="{ item }">
                <button 
                  class="btn btn-sm btn-outline-primary me-2" 
                  @click="editPrice(item)"
                  title="Edit"
                >
                  <i class="fas fa-edit"></i>
                </button>
                <button 
                  class="btn btn-sm btn-outline-danger" 
                  @click="confirmDelete(item)"
                  title="Hapus"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </template>
            </DataTable>
          </div>
        </div>
      </div>
  
      <!-- Modal Form -->
      <PriceFormModal 
        v-if="showPriceModal"
        :key="editingMode ? selectedPrice?.price_id : 'new'"
        :isEditing="editingMode"
        :itemId="String(selectedItemId)"
        :price="selectedPrice"
        @close="closePriceModal"
        @saved="onPriceSaved"
      />
  
      <!-- Modal Konfirmasi Hapus -->
      <ConfirmationModal
        v-if="showDeleteModal"
        title="Konfirmasi Hapus"
        :message="`Yakin ingin menghapus harga ${deleteItem?.price} ${deleteItem?.currency_code}?`"
        confirmButtonText="Hapus"
        confirmButtonClass="btn btn-danger"
        @confirm="deletePrice"
        @close="showDeleteModal = false"
      />
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import DataTable from '@/components/common/DataTable.vue';
  import PriceFormModal from './components/PriceFormModal.vue';
  import ConfirmationModal from '@/components/common/ConfirmationModal.vue';
  
  export default {
    name: 'ItemPriceList',
    components: {
      DataTable,
      PriceFormModal,
      ConfirmationModal
    },
    data() {
      return {
        items: [],
        selectedItemId: '',
        prices: [],
        filteredPrices: [],
        isLoading: false,
        showPriceModal: false,
        editingMode: false,
        selectedPrice: null,
        showDeleteModal: false,
        deleteItem: null,
        priceTypeFilter: '',
        activeFilter: '',
        currencyFilter: '',
        currentOnlyFilter: false,
        currencies: ['IDR', 'USD', 'EUR', 'SGD']
      };
    },
    computed: {
      tableColumns() {
        return [
          { 
            key: 'price_type', 
            label: 'Tipe', 
            formatter: (value) => value === 'purchase' ? 'Beli' : 'Jual' 
          },
          { key: 'price', label: 'Harga', formatter: this.formatPrice },
          { key: 'currency_code', label: 'Currency' },
          { key: 'min_quantity', label: 'Min Qty.' },
          { 
            key: 'is_active', 
            label: 'Status', 
            formatter: (value) => value ? 
              '<span class="badge bg-success">Aktif</span>' : 
              '<span class="badge bg-secondary">Tidak Aktif</span>'
          },
          { key: 'start_date', label: 'Mulai', formatter: this.formatDate },
          { key: 'end_date', label: 'Berakhir', formatter: this.formatDate },
          { key: 'customer', template: 'customer' },
          { key: 'vendor', template: 'vendor' }
        ];
      }
    },
    mounted() {
      this.loadItems();
    },
    methods: {
      async loadItems() {
        try {
          this.isLoading = true;
          const response = await axios.get('/items');
          this.items = response.data.data;
        } catch (error) {
          console.error('Failed to load items:', error);
          this.$toast.error('Gagal memuat daftar item');
        } finally {
          this.isLoading = false;
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
          if (this.currentOnlyFilter) params.current_only = 1;
  
          const response = await axios.get(`/items/${this.selectedItemId}/prices`, { params });
          this.prices = response.data.data;
          this.filterPrices();
        } catch (error) {
          console.error('Failed to load item prices:', error);
          this.$toast.error('Gagal memuat daftar harga');
        } finally {
          this.isLoading = false;
        }
      },
      filterPrices() {
        let filtered = [...this.prices];
        
        if (this.currencyFilter) {
          filtered = filtered.filter(price => price.currency_code === this.currencyFilter);
        }
        
        this.filteredPrices = filtered;
      },
      openAddPriceModal() {
        this.editingMode = false;
        this.selectedPrice = null;
        this.showPriceModal = true;
      },
      editPrice(price) {
        this.editingMode = true;
        this.selectedPrice = { ...price };
        this.showPriceModal = true;
      },
      closePriceModal() {
        this.showPriceModal = false;
        this.selectedPrice = null;
      },
      onPriceSaved() {
        this.closePriceModal();
        this.loadItemPrices();
      },
      confirmDelete(price) {
        this.deleteItem = price;
        this.showDeleteModal = true;
      },
      async deletePrice() {
        try {
          await axios.delete(`/items/${this.selectedItemId}/prices/${this.deleteItem.price_id}`);
          this.$toast.success('Harga berhasil dihapus');
          this.showDeleteModal = false;
          this.deleteItem = null;
          this.loadItemPrices();
        } catch (error) {
          console.error('Failed to delete price:', error);
          this.$toast.error('Gagal menghapus harga');
        }
      },
      formatPrice(value) {
        return new Intl.NumberFormat('id-ID', {
          style: 'decimal',
          minimumFractionDigits: 2
        }).format(value);
      },
      formatDate(value) {
        if (!value) return '-';
        return new Date(value).toLocaleDateString('id-ID');
      }
    }
  };
  </script>
  
  <style scoped>
  .page-header {
    margin-bottom: 2rem;
  }
  
  .form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
  }
  
  .card {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }
  
  .badge {
    padding: 0.5rem;
  }
  </style>