<!-- src/views/accounting/CurrencyConverter.vue -->
<template>
  <div class="currency-converter-container">
    <div class="page-header">
      <h1>Currency Converter</h1>
      <div class="header-actions">
        <router-link to="/currency-rates" class="btn btn-secondary">
          <i class="fas fa-list"></i> View All Rates
        </router-link>
      </div>
    </div>

    <div class="converter-card">
      <div class="converter-header">
        <h2>Convert Between Currencies</h2>
        <p class="converter-description">
          Use this tool to convert amounts between currencies using the latest exchange rates.
        </p>
      </div>

      <div class="converter-form">
        <div class="form-row">
          <div class="form-group">
            <label for="amount">Amount</label>
            <input
              type="number"
              id="amount"
              v-model="amount"
              class="form-control"
              min="0"
              step="0.01"
              placeholder="Enter amount"
            />
          </div>
        </div>

        <div class="currency-exchange-container">
          <div class="currency-input-container">
            <div class="form-group">
              <label for="from-currency">From</label>
              <div class="currency-input">
                <select
                  id="from-currency"
                  v-model="fromCurrency"
                  class="form-control"
                  @change="fetchRate"
                >
                  <option value="">Select currency</option>
                  <option v-for="currency in currencies" :key="currency" :value="currency">
                    {{ currency }}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <div class="swap-button" @click="swapCurrencies">
            <i class="fas fa-exchange-alt"></i>
          </div>

          <div class="currency-input-container">
            <div class="form-group">
              <label for="to-currency">To</label>
              <div class="currency-input">
                <select
                  id="to-currency"
                  v-model="toCurrency"
                  class="form-control"
                  @change="fetchRate"
                >
                  <option value="">Select currency</option>
                  <option v-for="currency in currencies" :key="currency" :value="currency">
                    {{ currency }}
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div v-if="loadingRate" class="rate-loading">
          <i class="fas fa-spinner fa-spin"></i> Fetching latest rate...
        </div>

        <div v-else-if="error" class="rate-error">
          <i class="fas fa-exclamation-triangle"></i> {{ error }}
        </div>

        <div v-else-if="showRateInfo" class="rate-info">
          <div class="rate-details">
            <div class="conversion-rate">
              <span>1 {{ fromCurrency }}</span>
              <span class="equals">=</span>
              <span class="rate-value">{{ formatNumber(exchangeRate) }} {{ toCurrency }}</span>
            </div>
            <div class="rate-date">
              Rate as of {{ formatDate(rateDate) }}
            </div>
          </div>
        </div>

        <button
          @click="convert"
          class="btn btn-primary convert-btn"
          :disabled="!canConvert || loadingRate"
        >
          <i v-if="loading" class="fas fa-spinner fa-spin"></i>
          <i v-else class="fas fa-calculator"></i>
          Convert
        </button>
      </div>

      <div v-if="converted" class="conversion-result">
        <div class="result-header">Conversion Result</div>
        <div class="result-container">
          <div class="result-amount">
            <div class="amount-from">
              {{ formatNumber(amount) }} {{ fromCurrency }}
            </div>
            <div class="amount-equals">
              <i class="fas fa-equals"></i>
            </div>
            <div class="amount-to">
              {{ formatNumber(convertedAmount) }} {{ toCurrency }}
            </div>
          </div>
          <div class="result-rate">
            Rate: {{ formatNumber(exchangeRate) }} {{ toCurrency }} per 1 {{ fromCurrency }}
          </div>
        </div>
      </div>
    </div>

    <div class="historical-rates-card">
      <div class="card-header">
        <h2>Available Currency Pairs</h2>
        <div class="filter-container">
          <div class="form-group">
            <input
              type="text"
              v-model="filterCurrency"
              class="form-control"
              placeholder="Filter by currency code"
            />
          </div>
        </div>
      </div>

      <div v-if="loadingRates" class="loading-container">
        <i class="fas fa-spinner fa-spin"></i> Loading exchange rates...
      </div>

      <div v-else-if="ratesError" class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> {{ ratesError }}
      </div>

      <div v-else-if="filteredRates.length === 0" class="empty-state">
        <i class="fas fa-search empty-icon"></i>
        <h3>No matching currency pairs found</h3>
        <p>Try adjusting your filter or check if the currency rates are available.</p>
      </div>

      <div v-else class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th @click="sortRates('from_currency')" class="sortable">
                From Currency
                <i v-if="sortField === 'from_currency'" :class="sortIconClass"></i>
              </th>
              <th @click="sortRates('to_currency')" class="sortable">
                To Currency
                <i v-if="sortField === 'to_currency'" :class="sortIconClass"></i>
              </th>
              <th @click="sortRates('rate')" class="sortable">
                Rate
                <i v-if="sortField === 'rate'" :class="sortIconClass"></i>
              </th>
              <th @click="sortRates('effective_date')" class="sortable">
                Effective Date
                <i v-if="sortField === 'effective_date'" :class="sortIconClass"></i>
              </th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="rate in filteredRates" :key="rate.rate_id">
              <td>{{ rate.from_currency }}</td>
              <td>{{ rate.to_currency }}</td>
              <td>{{ formatNumber(rate.rate) }}</td>
              <td>{{ formatDate(rate.effective_date) }}</td>
              <td>
                <span
                  :class="[
                    'status-badge',
                    rate.is_active ? 'status-active' : 'status-inactive'
                  ]"
                >
                  {{ rate.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="actions-cell">
                <button
                  @click="selectRate(rate)"
                  class="btn btn-sm btn-primary"
                  title="Use this rate"
                >
                  <i class="fas fa-check"></i> Use
                </button>
                <router-link
                  :to="`/currency-rates/${rate.rate_id}`"
                  class="btn btn-sm btn-info"
                  title="View details"
                >
                  <i class="fas fa-eye"></i>
                </router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<script>
import axios from 'axios';

export default {
  name: 'CurrencyConverter',
  data() {
    return {
      amount: 100,
      fromCurrency: '',
      toCurrency: '',
      currencies: [],
      exchangeRate: 0,
      rateDate: null,
      loadingRate: false,
      loading: false,
      error: null,
      converted: false,
      convertedAmount: 0,
      rates: [],
      loadingRates: true,
      ratesError: null,
      filterCurrency: '',
      sortField: 'effective_date',
      sortDirection: 'desc'
    };
  },
  computed: {
    canConvert() {
      return this.amount > 0 && this.fromCurrency && this.toCurrency && this.exchangeRate > 0;
    },
    showRateInfo() {
      return this.fromCurrency && this.toCurrency && this.exchangeRate > 0;
    },
    filteredRates() {
      if (!this.filterCurrency) {
        return this.sortedRates;
      }
      
      const filter = this.filterCurrency.toUpperCase();
      return this.sortedRates.filter(rate => 
        rate.from_currency.includes(filter) || 
        rate.to_currency.includes(filter)
      );
    },
    sortedRates() {
      return [...this.rates].sort((a, b) => {
        let aValue = a[this.sortField];
        let bValue = b[this.sortField];
        
        // Handle null values
        if (aValue === null) return 1;
        if (bValue === null) return -1;
        
        // Handle dates
        if (this.sortField === 'effective_date' || this.sortField === 'end_date') {
          aValue = aValue ? new Date(aValue) : new Date(0);
          bValue = bValue ? new Date(bValue) : new Date(0);
        }
        
        // Compare values
        if (aValue < bValue) return this.sortDirection === 'asc' ? -1 : 1;
        if (aValue > bValue) return this.sortDirection === 'asc' ? 1 : -1;
        return 0;
      });
    },
    sortIconClass() {
      return this.sortDirection === 'asc'
        ? 'fas fa-sort-up'
        : 'fas fa-sort-down';
    }
  },
  mounted() {
    this.fetchCurrencies();
    this.fetchAllRates();
  },
  methods: {
    async fetchCurrencies() {
      try {
        // For a real app, you would fetch this from your API
        // This is a placeholder with common currencies
        this.currencies = [
          'USD', 'EUR', 'GBP', 'JPY', 'AUD', 
          'CAD', 'CHF', 'CNY', 'HKD', 'NZD', 
          'SGD', 'IDR', 'MYR', 'THB', 'VND'
        ];
      } catch (error) {
        console.error('Error fetching currencies:', error);
      }
    },
    async fetchAllRates() {
      this.loadingRates = true;
      this.ratesError = null;
      
      try {
        const response = await axios.get('/accounting/currency-rates', { 
          params: { is_active: true } 
        });
        
        if (response.data.status === 'success') {
          this.rates = response.data.data;
        } else {
          this.ratesError = 'Failed to fetch exchange rates';
        }
      } catch (error) {
        console.error('Error fetching exchange rates:', error);
        this.ratesError = error.response?.data?.message || 'An error occurred while fetching exchange rates';
      } finally {
        this.loadingRates = false;
      }
    },
    async fetchRate() {
      if (!this.fromCurrency || !this.toCurrency || this.fromCurrency === this.toCurrency) {
        this.exchangeRate = 0;
        this.error = null;
        return;
      }
      
      this.loadingRate = true;
      this.error = null;
      
      try {
        const response = await axios.get('/accounting/currency-rates/current-rate', {
          params: {
            from_currency: this.fromCurrency,
            to_currency: this.toCurrency,
            date: new Date().toISOString().split('T')[0]
          }
        });
        
        if (response.data.status === 'success') {
          this.exchangeRate = response.data.data.rate;
          this.rateDate = response.data.data.date;
        } else {
          this.error = 'Failed to fetch current exchange rate';
          this.exchangeRate = 0;
        }
      } catch (error) {
        console.error('Error fetching exchange rate:', error);
        if (error.response?.status === 404) {
          this.error = `No exchange rate found for ${this.fromCurrency} to ${this.toCurrency}`;
        } else {
          this.error = error.response?.data?.message || 'An error occurred while fetching exchange rate';
        }
        this.exchangeRate = 0;
      } finally {
        this.loadingRate = false;
      }
    },
    convert() {
      if (!this.canConvert) {
        return;
      }
      
      this.loading = true;
      
      try {
        this.convertedAmount = this.amount * this.exchangeRate;
        this.converted = true;
      } catch (error) {
        console.error('Error during conversion:', error);
      } finally {
        this.loading = false;
      }
    },
    swapCurrencies() {
      const temp = this.fromCurrency;
      this.fromCurrency = this.toCurrency;
      this.toCurrency = temp;
      
      if (this.fromCurrency && this.toCurrency) {
        this.fetchRate();
      }
    },
    selectRate(rate) {
      this.fromCurrency = rate.from_currency;
      this.toCurrency = rate.to_currency;
      this.exchangeRate = rate.rate;
      this.rateDate = rate.effective_date;
      
      // Scroll to the top of the page
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    sortRates(field) {
      if (this.sortField === field) {
        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortField = field;
        this.sortDirection = 'asc';
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
        month: 'short',
        day: 'numeric'
      });
    }
  }
};
</script>

<style scoped>
.currency-converter-container {
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

.converter-card,
.historical-rates-card {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
  overflow: hidden;
}

.converter-header,
.card-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--gray-200);
}

.converter-header h2,
.card-header h2 {
  margin: 0 0 0.5rem 0;
  font-size: 1.25rem;
  color: var(--gray-700);
}

.converter-description {
  margin: 0;
  color: var(--gray-600);
}

.converter-form {
  padding: 1.5rem;
  border-bottom: 1px solid var(--gray-200);
}

.form-row {
  margin-bottom: 1.5rem;
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

.currency-exchange-container {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.currency-input-container {
  flex: 1;
}

.swap-button {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 2.5rem;
  height: 2.5rem;
  background-color: var(--gray-100);
  border: 1px solid var(--gray-300);
  border-radius: 50%;
  color: var(--gray-600);
  cursor: pointer;
  transition: all 0.2s;
}

.swap-button:hover {
  background-color: var(--gray-200);
  color: var(--gray-800);
}

.rate-loading,
.rate-error,
.rate-info {
  margin-bottom: 1.5rem;
  padding: 1rem;
  border-radius: 0.375rem;
}

.rate-loading {
  background-color: var(--gray-100);
  color: var(--gray-600);
}

.rate-error {
  background-color: #fee2e2;
  color: #b91c1c;
}

.rate-info {
  background-color: #e0f2fe;
  color: #0369a1;
}

.rate-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.conversion-rate {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
}

.equals {
  margin: 0 0.25rem;
  color: var(--gray-500);
}

.rate-value {
  font-weight: 600;
}

.rate-date {
  font-size: 0.875rem;
  opacity: 0.8;
}

.convert-btn {
  width: 100%;
  padding: 0.75rem;
  font-size: 1rem;
}

.conversion-result {
  padding: 1.5rem;
  background-color: #f0fdf4;
}

.result-header {
  margin-bottom: 1rem;
  font-size: 1.125rem;
  font-weight: 600;
  color: #15803d;
}

.result-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.result-amount {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.amount-from,
.amount-to {
  padding: 0.75rem 1.25rem;
  border-radius: 0.375rem;
  font-size: 1.25rem;
  font-weight: 600;
}

.amount-from {
  background-color: white;
  border: 1px solid #d1fae5;
  color: var(--gray-800);
}

.amount-equals {
  color: #059669;
}

.amount-to {
  background-color: #10b981;
  color: white;
}

.result-rate {
  font-size: 0.875rem;
  color: var(--gray-600);
}

.filter-container {
  margin-top: 1rem;
}

.loading-container {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 2rem;
}

.alert {
  margin: 1.5rem;
  padding: 1rem;
  border-radius: 0.375rem;
}

.alert-danger {
  background-color: #fee2e2;
  color: #b91c1c;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 3rem 1rem;
  text-align: center;
}

.empty-icon {
  font-size: 3rem;
  color: var(--gray-400);
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-size: 1.25rem;
  margin-bottom: 0.5rem;
}

.empty-state p {
  max-width: 400px;
  margin-bottom: 1.5rem;
  color: var(--gray-600);
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table th,
.table td {
  padding: 0.75rem 1rem;
  text-align: left;
  border-bottom: 1px solid var(--gray-200);
}

.table th {
  font-weight: 600;
  background-color: var(--gray-50);
}

.sortable {
  cursor: pointer;
  user-select: none;
}

.sortable:hover {
  background-color: var(--gray-100);
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

.actions-cell {
  display: flex;
  gap: 0.5rem;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
  text-decoration: none;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
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

.btn-info {
  background-color: #0ea5e9;
  color: white;
}

.btn-info:hover {
  background-color: #0284c7;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .currency-exchange-container {
    flex-direction: column;
    gap: 1.5rem;
  }
  
  .swap-button {
    align-self: center;
    transform: rotate(90deg);
  }
  
  .result-amount {
    flex-direction: column;
    align-items: stretch;
  }
  
  .amount-equals {
    align-self: center;
    transform: rotate(90deg);
    margin: 0.5rem 0;
  }
  
  .table-responsive {
    overflow-x: auto;
  }
}
</style>