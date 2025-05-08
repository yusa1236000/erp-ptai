<!-- src/views/accounting/CurrencyRateForm.vue -->
<template>
    <div class="currency-rate-form-container">
      <div class="page-header">
        <h1>{{ isEditMode ? 'Edit Exchange Rate' : 'Create Exchange Rate' }}</h1>
        <div class="header-actions">
          <router-link to="/currency-rates" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Rates
          </router-link>
        </div>
      </div>
  
      <div v-if="loading" class="loading-container">
        <i class="fas fa-spinner fa-spin"></i> Loading...
      </div>
  
      <div v-else class="form-container">
        <div v-if="error" class="alert alert-danger">
          <i class="fas fa-exclamation-triangle"></i> {{ error }}
        </div>
  
        <form @submit.prevent="submitForm">
          <div class="form-card">
            <div class="form-section">
              <h2>Currency Information</h2>
              
              <div class="form-row">
                <div class="form-group">
                  <label for="from-currency">From Currency <span class="required">*</span></label>
                  <input
                    type="text"
                    id="from-currency"
                    v-model="formData.from_currency"
                    :disabled="isEditMode"
                    class="form-control"
                    :class="{ 'is-invalid': validationErrors.from_currency }"
                    maxlength="3"
                    placeholder="USD"
                    required
                  />
                  <div v-if="validationErrors.from_currency" class="invalid-feedback">
                    {{ validationErrors.from_currency }}
                  </div>
                  <small class="form-text text-muted">Enter the 3-letter ISO currency code (e.g., USD, EUR, JPY)</small>
                </div>
  
                <div class="form-group">
                  <label for="to-currency">To Currency <span class="required">*</span></label>
                  <input
                    type="text"
                    id="to-currency"
                    v-model="formData.to_currency"
                    :disabled="isEditMode"
                    class="form-control"
                    :class="{ 'is-invalid': validationErrors.to_currency }"
                    maxlength="3"
                    placeholder="IDR"
                    required
                  />
                  <div v-if="validationErrors.to_currency" class="invalid-feedback">
                    {{ validationErrors.to_currency }}
                  </div>
                  <small class="form-text text-muted">Enter the 3-letter ISO currency code (e.g., IDR, SGD, MYR)</small>
                </div>
              </div>
              
              <div class="form-row">
                <div class="form-group">
                  <label for="rate">Exchange Rate <span class="required">*</span></label>
                  <input
                    type="number"
                    id="rate"
                    v-model="formData.rate"
                    class="form-control"
                    :class="{ 'is-invalid': validationErrors.rate }"
                    step="0.000001"
                    min="0.000001"
                    placeholder="14500.00"
                    required
                  />
                  <div v-if="validationErrors.rate" class="invalid-feedback">
                    {{ validationErrors.rate }}
                  </div>
                  <small class="form-text text-muted">
                    How many units of "To Currency" equal one unit of "From Currency"
                  </small>
                </div>
              </div>
              
              <div class="rate-preview" v-if="formData.from_currency && formData.to_currency && formData.rate">
                <div class="preview-label">Preview:</div>
                <div class="preview-value">
                  1 {{ formData.from_currency }} = {{ formatNumber(formData.rate) }} {{ formData.to_currency }}
                </div>
                <div class="preview-value">
                  1 {{ formData.to_currency }} = {{ formatNumber(1 / formData.rate) }} {{ formData.from_currency }}
                </div>
              </div>
            </div>
  
            <div class="form-section">
              <h2>Validity Period</h2>
              
              <div class="form-row">
                <div class="form-group">
                  <label for="effective-date">Effective Date <span class="required">*</span></label>
                  <input
                    type="date"
                    id="effective-date"
                    v-model="formData.effective_date"
                    class="form-control"
                    :class="{ 'is-invalid': validationErrors.effective_date }"
                    required
                  />
                  <div v-if="validationErrors.effective_date" class="invalid-feedback">
                    {{ validationErrors.effective_date }}
                  </div>
                  <small class="form-text text-muted">When this exchange rate should start to apply</small>
                </div>
  
                <div class="form-group">
                  <label for="end-date">End Date</label>
                  <input
                    type="date"
                    id="end-date"
                    v-model="formData.end_date"
                    class="form-control"
                    :class="{ 'is-invalid': validationErrors.end_date }"
                    :min="formData.effective_date"
                  />
                  <div v-if="validationErrors.end_date" class="invalid-feedback">
                    {{ validationErrors.end_date }}
                  </div>
                  <small class="form-text text-muted">Leave blank if this rate has no expiry date</small>
                </div>
              </div>
            </div>
  
            <div class="form-section">
              <h2>Status</h2>
              
              <div class="form-group form-check">
                <input
                  type="checkbox"
                  id="is-active"
                  v-model="formData.is_active"
                  class="form-check-input"
                />
                <label for="is-active" class="form-check-label">Active</label>
                <small class="form-text text-muted">
                  Inactive rates are not used for currency conversions
                </small>
              </div>
            </div>
          </div>
  
          <div class="form-actions">
            <button type="button" class="btn btn-secondary" @click="cancel">
              Cancel
            </button>
            <button type="submit" class="btn btn-primary" :disabled="submitting">
              <i v-if="submitting" class="fas fa-spinner fa-spin"></i>
              {{ isEditMode ? 'Update Exchange Rate' : 'Create Exchange Rate' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    name: 'CurrencyRateForm',
    data() {
      return {
        formData: {
          from_currency: '',
          to_currency: '',
          rate: '',
          effective_date: this.getCurrentDate(),
          end_date: '',
          is_active: true
        },
        loading: false,
        submitting: false,
        error: null,
        validationErrors: {}
      };
    },
    computed: {
      isEditMode() {
        return !!this.$route.params.id;
      }
    },
    mounted() {
      if (this.isEditMode) {
        this.fetchExchangeRate();
      }
    },
    methods: {
      async submitForm() {
        if (!this.validateForm()) {
          return;
        }
        
        this.submitting = true;
        this.error = null;
        
        try {
          let response;
          
          if (this.isEditMode) {
            // Update existing rate
            response = await axios.put(`/accounting/currency-rates/${this.$route.params.id}`, this.formData);
          } else {
            // Create new rate
            response = await axios.post('/accounting/currency-rates', this.formData);
          }
          
          if (response.data.status === 'success') {
            this.$toast.success(`Exchange rate ${this.isEditMode ? 'updated' : 'created'} successfully!`);
            this.$router.push('/currency-rates');
          } else {
            this.error = `Failed to ${this.isEditMode ? 'update' : 'create'} exchange rate`;
          }
        } catch (error) {
          console.error(`Error ${this.isEditMode ? 'updating' : 'creating'} exchange rate:`, error);
          
          // Handle validation errors from the server
          if (error.response?.status === 422 && error.response?.data?.errors) {
            this.validationErrors = error.response.data.errors;
            this.error = 'Please correct the errors in the form';
          } else {
            this.error = error.response?.data?.message || `An error occurred while ${this.isEditMode ? 'updating' : 'creating'} exchange rate`;
          }
        } finally {
          this.submitting = false;
        }
      },
      validateForm() {
        this.validationErrors = {};
        let isValid = true;
        
        if (!this.formData.from_currency || this.formData.from_currency.length !== 3) {
          this.validationErrors.from_currency = 'Please enter a valid 3-letter currency code';
          isValid = false;
        } else {
          // Convert to uppercase
          this.formData.from_currency = this.formData.from_currency.toUpperCase();
        }
        
        if (!this.formData.to_currency || this.formData.to_currency.length !== 3) {
          this.validationErrors.to_currency = 'Please enter a valid 3-letter currency code';
          isValid = false;
        } else {
          // Convert to uppercase
          this.formData.to_currency = this.formData.to_currency.toUpperCase();
        }
        
        if (this.formData.from_currency === this.formData.to_currency) {
          this.validationErrors.to_currency = 'From and To currencies must be different';
          isValid = false;
        }
        
        if (!this.formData.rate || this.formData.rate <= 0) {
          this.validationErrors.rate = 'Rate must be greater than zero';
          isValid = false;
        }
        
        if (!this.formData.effective_date) {
          this.validationErrors.effective_date = 'Effective date is required';
          isValid = false;
        }
        
        if (this.formData.end_date && new Date(this.formData.end_date) <= new Date(this.formData.effective_date)) {
          this.validationErrors.end_date = 'End date must be after effective date';
          isValid = false;
        }
        
        return isValid;
      },
      cancel() {
        this.$router.push('/currency-rates');
      },
      getCurrentDate() {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
      },
      async fetchExchangeRate() {
        this.loading = true;
        this.error = null;
        
        try {
          const response = await axios.get(`/accounting/currency-rates/${this.$route.params.id}`);
          
          if (response.data.status === 'success') {
            const rateData = response.data.data;
            
            // Format dates for form inputs
            if (rateData.effective_date) {
              rateData.effective_date = this.formatDateForInput(rateData.effective_date);
            }
            
            if (rateData.end_date) {
              rateData.end_date = this.formatDateForInput(rateData.end_date);
            }
            
            this.formData = rateData;
          } else {
            this.error = 'Failed to fetch exchange rate data';
          }
        } catch (error) {
          console.error('Error fetching exchange rate:', error);
          this.error = error.response?.data?.message || 'An error occurred while fetching exchange rate';
        } finally {
          this.loading = false;
        }
      },
      formatDateForInput(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
      },
      formatNumber(value) {
        return new Intl.NumberFormat('en-US', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 6
        }).format(value);
      }
    }
  };
  </script>
  
  <style scoped>
  .currency-rate-form-container {
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
  
  .loading-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2rem;
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }
  
  .form-container {
    max-width: 800px;
    margin: 0 auto;
  }
  
  .alert {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
  }
  
  .alert-danger {
    background-color: #fee2e2;
    color: #b91c1c;
  }
  
  .form-card {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 1.5rem;
  }
  
  .form-section {
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-200);
  }
  
  .form-section:last-child {
    border-bottom: none;
  }
  
  .form-section h2 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    font-size: 1.25rem;
    color: var(--gray-700);
  }
  
  .form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  .form-row:last-child {
    margin-bottom: 0;
  }
  
  .form-group {
    margin-bottom: 1rem;
  }
  
  .form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--gray-700);
  }
  
  .form-control {
    width: 100%;
    padding: 0.625rem;
    border: 1px solid var(--gray-300);
    border-radius: 0.375rem;
    font-size: 1rem;
  }
  
  .form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
  }
  
  .form-control.is-invalid {
    border-color: #dc2626;
  }
  
  .invalid-feedback {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #dc2626;
  }
  
  .form-text {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: var(--gray-500);
  }
  
  .form-check {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
  }
  
  .form-check-input {
    margin-right: 0.5rem;
  }
  
  .form-check-label {
    margin-bottom: 0;
  }
  
  .required {
    color: #dc2626;
  }
  
  .rate-preview {
    background-color: var(--gray-50);
    border-radius: 0.375rem;
    padding: 1rem;
    margin-top: 1rem;
  }
  
  .preview-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: var(--gray-600);
  }
  
  .preview-value {
    font-family: monospace;
    font-size: 1.125rem;
    margin-bottom: 0.25rem;
  }
  
  .form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
  }
  
  .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: 0.375rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
  }
  
  .btn-primary {
    background-color: var(--primary-color);
    color: white;
  }
  
  .btn-primary:hover {
    background-color: var(--primary-dark);
  }
  
  .btn-primary:disabled {
    background-color: var(--gray-300);
    cursor: not-allowed;
  }
  
  .btn-secondary {
    background-color: var(--gray-600);
    color: white;
  }
  
  .btn-secondary:hover {
    background-color: var(--gray-700);
  }
  
  @media (max-width: 768px) {
    .page-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 1rem;
    }
    
    .form-row {
      grid-template-columns: 1fr;
      gap: 1rem;
    }
    
    .form-actions {
      flex-direction: column-reverse;
    }
    
    .btn {
      width: 100%;
    }
  }
  </style>