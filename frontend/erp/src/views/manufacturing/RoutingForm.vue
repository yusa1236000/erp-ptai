<!-- src/views/manufacturing/RoutingForm.vue -->
<template>
    <div class="routing-form-container">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h2 class="card-title">{{ isEditMode ? 'Edit Routing' : 'Buat Routing Baru' }}</h2>
          <div>
            <button @click="goBack" class="btn btn-secondary mr-2">
              <i class="fas fa-arrow-left mr-1"></i> Kembali
            </button>
            <button @click="saveRouting" class="btn btn-primary" :disabled="isSaving">
              <i class="fas fa-save mr-1"></i> {{ isSaving ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </div>
        <div class="card-body">
          <form @submit.prevent="saveRouting">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="item_id">Produk <span class="text-danger">*</span></label>
                  <select
                    id="item_id"
                    v-model="routing.item_id"
                    class="form-control"
                    :disabled="isEditMode"
                    required
                  >
                    <option value="" disabled>-- Pilih Produk --</option>
                    <option v-for="item in items" :key="item.item_id" :value="item.item_id">
                      {{ item.name }} ({{ item.item_code }})
                    </option>
                  </select>
                  <small v-if="errors.item_id" class="text-danger">{{ errors.item_id[0] }}</small>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="routing_code">Kode Routing <span class="text-danger">*</span></label>
                  <input
                    id="routing_code"
                    v-model="routing.routing_code"
                    type="text"
                    class="form-control"
                    required
                    maxlength="50"
                    placeholder="Kode unik untuk routing ini"
                  />
                  <small v-if="errors.routing_code" class="text-danger">{{ errors.routing_code[0] }}</small>
                </div>
              </div>
            </div>
  
            <div class="row mt-3">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="revision">Revisi <span class="text-danger">*</span></label>
                  <input
                    id="revision"
                    v-model="routing.revision"
                    type="text"
                    class="form-control"
                    required
                    maxlength="10"
                    placeholder="Mis: 01, A, 1.0"
                  />
                  <small v-if="errors.revision" class="text-danger">{{ errors.revision[0] }}</small>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="effective_date">Tanggal Efektif <span class="text-danger">*</span></label>
                  <input
                    id="effective_date"
                    v-model="routing.effective_date"
                    type="date"
                    class="form-control"
                    required
                  />
                  <small v-if="errors.effective_date" class="text-danger">{{ errors.effective_date[0] }}</small>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="status">Status <span class="text-danger">*</span></label>
                  <select id="status" v-model="routing.status" class="form-control" required>
                    <option value="Draft">Draft</option>
                    <option value="Active">Aktif</option>
                    <option value="Obsolete">Tidak Berlaku</option>
                  </select>
                  <small v-if="errors.status" class="text-danger">{{ errors.status[0] }}</small>
                </div>
              </div>
            </div>
  
            <div class="mt-4">
              <h3>Operasi Routing</h3>
              <p class="text-muted">
                {{ isEditMode ? 
                  'Operasi dapat ditambahkan setelah routing disimpan di halaman detail.' : 
                  'Operasi dapat ditambahkan setelah routing disimpan.' }}
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, reactive, computed, onMounted } from 'vue';
  import { useRouter, useRoute } from 'vue-router';
  import axios from 'axios';
  
  export default {
    name: 'RoutingForm',
    setup() {
      const router = useRouter();
      const route = useRoute();
      const routingId = computed(() => route.params.id);
      const isEditMode = computed(() => !!routingId.value);
      const isSaving = ref(false);
      const items = ref([]);
      const errors = ref({});
  
      // Routing form data
      const routing = reactive({
        item_id: '',
        routing_code: '',
        revision: '',
        effective_date: new Date().toISOString().substring(0, 10), // Default to today
        status: 'Draft',
      });
  
      // Load items for dropdown
      const loadItems = async () => {
        try {
          const response = await axios.get('/items');
          items.value = response.data.data;
        } catch (error) {
          console.error('Error loading items:', error);
        }
      };
  
      // Load existing routing data if in edit mode
      const loadRoutingData = async () => {
        if (!isEditMode.value) return;
        
        try {
          const response = await axios.get(`/routings/${routingId.value}`);
          const data = response.data.data;
          
          // Update reactive object with fetched data
          routing.item_id = data.item_id;
          routing.routing_code = data.routing_code;
          routing.revision = data.revision;
          routing.effective_date = new Date(data.effective_date).toISOString().substring(0, 10);
          routing.status = data.status;
        } catch (error) {
          console.error('Error loading routing data:', error);
          alert('Gagal memuat data routing. Silakan coba lagi.');
        }
      };
  
      // Save routing
      const saveRouting = async () => {
        isSaving.value = true;
        errors.value = {};
        
        try {
          let response;
          
          if (isEditMode.value) {
            response = await axios.put(`/routings/${routingId.value}`, routing);
          } else {
            response = await axios.post('/routings', routing);
          }
          
          // Navigate to detail page after successful save
          const savedRouting = response.data.data;
          router.push(`/manufacturing/routings/${savedRouting.routing_id}`);
        } catch (error) {
          console.error('Error saving routing:', error);
          
          if (error.response && error.response.data && error.response.data.errors) {
            errors.value = error.response.data.errors;
          } else {
            alert('Gagal menyimpan routing. Silakan coba lagi.');
          }
        } finally {
          isSaving.value = false;
        }
      };
  
      // Go back to previous page
      const goBack = () => {
        router.go(-1);
      };
  
      // Load necessary data on component mount
      onMounted(() => {
        loadItems();
        if (isEditMode.value) {
          loadRoutingData();
        }
      });
  
      return {
        routing,
        items,
        errors,
        isEditMode,
        isSaving,
        saveRouting,
        goBack,
      };
    },
  };
  </script>
  
  <style scoped>
  .routing-form-container {
    max-width: 1200px;
    margin: 0 auto;
  }
  </style>