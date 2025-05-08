<!-- src/views/accounting/CurrencyRateDetail.vue -->
<template>
    <div class="currency-rate-detail-container">
      <div class="page-header">
        <h1>Exchange Rate Details</h1>
        <div class="header-actions">
          <router-link to="/currency-rates" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Rates
          </router-link>
          <router-link v-if="rate.rate_id" :to="`/currency-rates/${rate.rate_id}/edit`" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit
          </router-link>
        </div>
      </div>
  
      <div v-if="loading" class="loading-container">
        <i class="fas fa-spinner fa-spin"></i> Loading exchange rate details...
      </div>
  
      <div v-else-if="error" class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> {{ error }}
      </div>
  
      <div v-else-if="!rate.rate_id" class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i> Exchange rate not found
      </div>
  
      <div v-else class="detail-container">
        <div class="detail-card">
          <div class="detail-section">
            <h2>Currency Information</h2>
            
            <div class="currency-badge-container">
              <div class="currency-badge">
                <div class="currency-code">{{ rate.from_currency }}</div>
                <div class="currency-arrow">
                  <i class="fas fa-arrow-right"></i>
                </div>
                <div class="currency-code">{{ rate.to_currency }}</div>
              </div>
            </div>
            
            <div class="detail-row">
              <div class="conversion-details">
                <div class="conversion-rate">
                  <div class="conversion-label">Exchange Rate:</div>
                  <div class="conversion-value">
                    <span class="conversion-number">{{ formatNumber(rate.rate) }}</span>
                    <span class="conversion-text">{{ rate.to_currency }} per 1 {{ rate.from_currency }}</span>
                  </div>
                </div>
                
                <div class="conversion-rate">
                  <div class="conversion-label">Inverse Rate:</div>
                  <div class="conversion-value">
                    <span class="conversion-number">{{ formatNumber(1 / rate.rate) }}</span>
                    <span class="conversion-text">{{ rate.from_currency }} per 1 {{ rate.to_currency }}</span>
                  </div>
                </div>
                
                <div class="sample-conversions">
                  <div class="sample-conversion">
                    <span class="sample-source">100 {{ rate.from_currency }}</span>
                    <span class="sample-equals">=</span>
                    <span class="sample-result">{{ formatNumber(100 * rate.rate) }} {{ rate.to_currency }}</span>
                  </div>
                  
                  <div class="sample-conversion">
                    <span class="sample-source">100 {{ rate.to_currency }}</span>
                    <span class="sample-equals">=</span>
                    <span class="sample-result">{{ formatNumber(100 / rate.rate) }} {{ rate.from_currency }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="detail-section">
            <h2>Validity Period</h2>
            
            <div class="info-grid">
              <div class="info-item">
                <div class="info-label">Effective Date:</div>
                <div class="info-value">{{ formatDate(rate.effective_date) }}</div>
              </div>
              
              <div class="info-item">
                <div class="info-label">End Date:</div>
                <div class="info-value">{{ rate.end_date ? formatDate(rate.end_date) : 'Indefinite' }}</div>
              </div>
              
              <div class="info-item">
                <div class="info-label">Status:</div>
                <div class="info-value">
                  <span
                    :class="[
                      'status-badge',
                      rate.is_active ? 'status-active' : 'status-inactive'
                    ]"
                  >
                    {{ rate.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="detail-card">
          <div class="detail-section">
            <h2>Quick Converter</h2>
            
            <div class="converter-container">
              <div class="converter-form">
                <div class="converter-input-group">
                  <div class="converter-input">
                    <input
                      type="number"
                      v-model="amount"
                      class="form-control"
                      min="0"
                      step="0.01"
                    />
                  </div>
                  
                  <div class="converter-select">
                    <select v-model="convertFrom" class="form-control">
                      <option :value="rate.from_currency">{{ rate.from_currency }}</option>
                      <option :value="rate.to_currency">{{ rate.to_currency }}</option>
                    </select>
                  </div>
                </div>
                
                <div class="converter-equal">
                  <i class="fas fa-equals"></i>
                </div>
                
                <div class="converter-result">
                  <div class="result-value">{{ formatNumber(convertedAmount) }}</div>
                  <div class="result-currency">{{ convertTo }}</div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="detail-section">
            <h2>History & Metadata</h2>
            
            <div class="info-grid">
              <div class="info-item">
                <div class="info-label">Created At:</div>
                <div class="info-value">{{ rate.created_at ? formatDateTime(rate.created_at) : 'N/A' }}</div>
              </div>
              
              <div class="info-item">
                <div class="info-label">Last Updated:</div>
                <div class="info-value">{{ rate.updated_at ? formatDateTime(rate.updated_at) : 'N/A' }}</div>
              </div>
              
              <div class="info-item">
                <div class="info-label">Rate ID:</div>
                <div class="info-value">{{ rate.rate_id }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    name: 'CurrencyRateDetail',
    data() {
      return {
        rate: {},
        loading: true,
        error: null,
        amount: 100,
        convertFrom: ''
      };
    },
    computed: {
      convertTo() {
        return this.convertFrom === this.rate.from_currency
          ? this.rate.to_currency
          : this.rate.from_currency;
      },
      convertedAmount() {
        if (!this.amount || !this.rate || !this.rate.rate) {
          return 0;
        }
        
        if (this.convertFrom === this.rate.from_currency) {
          return this.amount * this.rate.rate;
        } else {
          return this.amount / this.rate.rate;
        }
      }
    },
    mounted() {
      this.fetchExchangeRate();
    },
    methods: {
      async fetchExchangeRate() {
        this.loading = true;
        this.error = null;
        
        try {
          const response = await axios.get(`/accounting/currency-rates/${this.$route.params.id}`);
          
          if (response.data.status === 'success') {
            this.rate = response.data.data;
            this.convertFrom = this.rate.from_currency;
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
      formatNumber(value) {
        return new Intl.NumberFormat('en-US', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 6
        }).format(value);
      },
      formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
      },
      formatDateTime(dateTimeString) {
        if (!dateTimeString) return '';
        const date = new Date(dateTimeString);
        return date.toLocaleString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
      }
    }
  };
  </script>
  
  <style scoped>
  .currency-rate-detail-container {
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
  
  .header-actions {
    display: flex;
    gap: 0.5rem;
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
  
  .alert {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
  }
  
  .alert-danger {
    background-color: #fee2e2;
    color: #b91c1c;
  }
  
  .alert-warning {
    background-color: #fef3c7;
    color: #92400e;
  }
  
  .detail-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
  }
  
  .detail-card {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
  }
  
  .detail-section {
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-200);
  }
  
  .detail-section:last-child {
    border-bottom: none;
  }
  
  .detail-section h2 {
    margin-top: 0;
    margin-bottom: 1rem;
    font-size: 1.25rem;
    color: var(--gray-700);
  }
  
  .currency-badge-container {
    display: flex;
    justify-content: center;
    margin-bottom: 1.5rem;
  }
  
  .currency-badge {
    display: flex;
    align-items: center;
    background-color: var(--gray-100);
    border-radius: 0.5rem;
    padding: 0.75rem 1.5rem;
  }
  
  .currency-code {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--gray-800);
  }
  
  .currency-arrow {
    margin: 0 1.5rem;
    color: var(--gray-500);
  }
  
  .conversion-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  
  .conversion-rate {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  
  .conversion-label {
    font-weight: 500;
    color: var(--gray-600);
  }
  
  .conversion-value {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .conversion-number {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--gray-800);
  }
  
  .conversion-text {
    color: var(--gray-600);
  }
  
  .sample-conversions {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--gray-200);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .sample-conversion {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .sample-source {
    font-weight: 500;
  }
  
  .sample-equals {
    color: var(--gray-500);
  }
  
  .sample-result {
    font-weight: 600;
  }
  
  .info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
  }
  
  .info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .info-label {
    font-size: 0.875rem;
    color: var(--gray-600);
  }
  
  .info-value {
    font-weight: 500;
    color: var(--gray-800);
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
  
  .converter-container {
    margin-top: 1rem;
  }
  
  .converter-form {
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  
  .converter-input-group {
    display: flex;
    flex: 1;
  }
  
  .converter-input {
    flex: 1;
  }
  
  .converter-select {
    width: 100px;
  }
  
  .converter-equal {
    font-size: 1.5rem;
    color: var(--gray-500);
  }
  
  .converter-result {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
    flex: 1;
  }
  
  .result-value {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--primary-color);
  }
  
  .result-currency {
    font-weight: 500;
    color: var(--gray-600);
  }
  
  .form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid var(--gray-300);
    border-radius: 0.25rem;
    font-size: 0.875rem;
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
  
  @media (max-width: 992px) {
    .detail-container {
      grid-template-columns: 1fr;
    }
  }
  
  @media (max-width: 768px) {
    .page-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 1rem;
    }
    
    .header-actions {
      width: 100%;
      justify-content: space-between;
    }
    
    .conversion-rate {
      flex-direction: column;
      align-items: flex-start;
      gap: 0.25rem;
    }
    
    .converter-form {
      flex-direction: column;
      align-items: stretch;
    }
    
    .converter-equal {
      transform: rotate(90deg);
    }
  }
  </style>