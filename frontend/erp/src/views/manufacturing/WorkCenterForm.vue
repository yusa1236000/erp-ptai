<!-- src/views/manufacturing/WorkCenterForm.vue -->
<template>
    <div class="work-center-form">
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">{{ isEditMode ? 'Edit Work Center' : 'Create Work Center' }}</h2>
        </div>
        <div class="card-body">
          <div v-if="isLoading" class="text-center p-5">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Loading work center data...</p>
          </div>
          
          <form v-else @submit.prevent="saveWorkCenter">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="code">Code <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    id="code"
                    v-model="workCenter.code"
                    class="form-control"
                    :class="{ 'is-invalid': errors.code }"
                    required
                    maxlength="50"
                    placeholder="Enter unique code"
                  />
                  <div v-if="errors.code" class="invalid-feedback">{{ errors.code }}</div>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name">Name <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    id="name"
                    v-model="workCenter.name"
                    class="form-control"
                    :class="{ 'is-invalid': errors.name }"
                    required
                    maxlength="100"
                    placeholder="Enter work center name"
                  />
                  <div v-if="errors.name" class="invalid-feedback">{{ errors.name }}</div>
                </div>
              </div>
            </div>
            
            <div class="row mt-3">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="capacity">Capacity (units/hour) <span class="text-danger">*</span></label>
                  <input
                    type="number"
                    id="capacity"
                    v-model="workCenter.capacity"
                    class="form-control"
                    :class="{ 'is-invalid': errors.capacity }"
                    required
                    min="0"
                    step="0.01"
                    placeholder="Enter capacity"
                  />
                  <div v-if="errors.capacity" class="invalid-feedback">{{ errors.capacity }}</div>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="form-group">
                  <label for="cost_per_hour">Cost per Hour <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">$</span>
                    </div>
                    <input
                      type="number"
                      id="cost_per_hour"
                      v-model="workCenter.cost_per_hour"
                      class="form-control"
                      :class="{ 'is-invalid': errors.cost_per_hour }"
                      required
                      min="0"
                      step="0.01"
                      placeholder="Enter hourly cost"
                    />
                  </div>
                  <div v-if="errors.cost_per_hour" class="invalid-feedback">{{ errors.cost_per_hour }}</div>
                </div>
              </div>
            </div>
            
            <div class="form-group mt-3">
              <div class="custom-control custom-switch">
                <input
                  type="checkbox"
                  id="is_active"
                  v-model="workCenter.is_active"
                  class="custom-control-input"
                />
                <label class="custom-control-label" for="is_active">Active</label>
              </div>
            </div>
            
            <div class="form-group mt-4">
              <label for="description">Description</label>
              <textarea
                id="description"
                v-model="workCenter.description"
                class="form-control"
                :class="{ 'is-invalid': errors.description }"
                rows="4"
                placeholder="Enter optional description"
              ></textarea>
              <div v-if="errors.description" class="invalid-feedback">{{ errors.description }}</div>
            </div>
            
            <div class="form-actions mt-4">
              <button type="button" class="btn btn-secondary mr-2" @click="goBack">
                <i class="fas fa-arrow-left mr-2"></i> Cancel
              </button>
              <button type="submit" class="btn btn-primary" :disabled="isSaving">
                <i class="fas fa-save mr-2"></i> {{ isSaving ? 'Saving...' : 'Save Work Center' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, reactive, computed, onMounted } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import axios from 'axios';
  
  export default {
    name: 'WorkCenterForm',
    setup() {
      const route = useRoute();
      const router = useRouter();
      
      const workCenterId = computed(() => route.params.id);
      const isEditMode = computed(() => !!workCenterId.value);
      
      const workCenter = reactive({
        name: '',
        code: '',
        capacity: '',
        cost_per_hour: '',
        description: '',
        is_active: true
      });
      
      const errors = reactive({});
      const isLoading = ref(false);
      const isSaving = ref(false);
      
      const loadWorkCenter = async () => {
        if (!isEditMode.value) return;
        
        isLoading.value = true;
        try {
          const response = await axios.get(`/work-centers/${workCenterId.value}`);
          const data = response.data.data;
          
          // Update the reactive object properties
          Object.keys(workCenter).forEach(key => {
            if (data[key] !== undefined) {
              workCenter[key] = data[key];
            }
          });
        } catch (error) {
          console.error('Error loading work center:', error);
          alert('Failed to load work center details. Please try again later.');
          router.push('/manufacturing/work-centers');
        } finally {
          isLoading.value = false;
        }
      };
      
      const saveWorkCenter = async () => {
        isSaving.value = true;
        errors.value = {};
        
        try {
          let response;
          
          if (isEditMode.value) {
            response = await axios.put(`/work-centers/${workCenterId.value}`, workCenter);
          } else {
            response = await axios.post('/work-centers', workCenter);
          }
          
          // Navigate to the detail page after successful save
          const id = isEditMode.value ? workCenterId.value : response.data.data.workcenter_id;
          router.push(`/manufacturing/work-centers/${id}`);
        } catch (error) {
          if (error.response && error.response.data.errors) {
            // Set validation errors
            Object.assign(errors, error.response.data.errors);
          } else {
            console.error('Error saving work center:', error);
            alert('Failed to save work center. Please try again later.');
          }
        } finally {
          isSaving.value = false;
        }
      };
      
      const goBack = () => {
        router.go(-1);
      };
      
      onMounted(() => {
        loadWorkCenter();
      });
      
      return {
        workCenter,
        errors,
        isLoading,
        isSaving,
        isEditMode,
        saveWorkCenter,
        goBack
      };
    }
  };
  </script>
  
  <style scoped>
  .work-center-form {
    max-width: 1000px;
    margin: 0 auto;
    padding: 1rem 0;
  }
  
  .form-actions {
    display: flex;
    justify-content: flex-end;
  }
  
  .custom-control-input:checked ~ .custom-control-label::before {
    background-color: var(--success-color);
    border-color: var(--success-color);
  }
  
  .invalid-feedback {
    display: block;
  }
  </style>