<!-- src/views/purchasing/CreatePOFromQuotation.vue -->
<template>
    <div class="create-po-from-quotation-container">
      <div class="page-header">
        <div class="header-left">
          <router-link to="/purchasing/rfqs" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Quotations
          </router-link>
          <h1>Create Purchase Order from Quotation</h1>
        </div>
      </div>
  
      <div v-if="isLoading" class="loading-container">
        <div class="loading-spinner">
          <i class="fas fa-spinner fa-spin"></i>
        </div>
        <p>Loading quotation data...</p>
      </div>
  
      <div v-else-if="!quotation" class="error-container">
        <div class="error-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2>Quotation Not Found</h2>
        <p>The requested quotation could not be found or may have been deleted.</p>
        <router-link to="/purchasing/rfqs" class="btn btn-primary">
          Return to Quotations List
        </router-link>
      </div>
  
      <div v-else class="po-form-container">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Quotation Information</h3>
          </div>
          <div class="card-body">
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">RFQ Number</span>
                <span class="info-value">{{ quotation.requestForQuotation.rfq_number }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Quotation Number</span>
                <span class="info-value">{{ quotation.quotation_number || 'N/A' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Vendor</span>
                <span class="info-value">{{ quotation.vendor.name }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Quotation Date</span>
                <span class="info-value">{{ formatDate(quotation.quotation_date) }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Validity Date</span>
                <span class="info-value">{{ formatDate(quotation.validity_date) }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Status</span>
                <span :class="['status-badge', getStatusClass(quotation.status)]">
                  {{ quotation.status }}
                </span>
              </div>
            </div>
          </div>
        </div>
  
        <div class="card mt-4">
          <div class="card-header">
            <h3 class="card-title">Quotation Line Items</h3>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>UOM</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                    <th class="text-center">Include in PO</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(line, index) in quotationLines" :key="index">
                    <td>
                      <div class="item-name">{{ line.item.name }}</div>
                      <div class="item-code">{{ line.item.item_code }}</div>
                    </td>
                    <td>
                      <input
                        type="number"
                        v-model="line.quantity"
                        class="form-control"
                        min="0.01"
                        step="0.01"
                        :disabled="!line.include"
                        @input="calculateLineTotal(line)"
                      />
                    </td>
                    <td>{{ line.unitOfMeasure.symbol }}</td>
                    <td>
                      <input
                        type="number"
                        v-model="line.unit_price"
                        class="form-control"
                        min="0.01"
                        step="0.01"
                        :disabled="!line.include"
                        @input="calculateLineTotal(line)"
                      />
                    </td>
                    <td>{{ formatCurrency(line.total) }}</td>
                    <td class="text-center">
                      <div class="form-check">
                        <input
                          type="checkbox"
                          class="form-check-input"
                          :id="`include-line-${index}`"
                          v-model="line.include"
                        />
                        <label class="form-check-label" :for="`include-line-${index}`"></label>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="quotationLines.length === 0">
                    <td colspan="6" class="text-center">No items available in this quotation.</td>
                  </tr>
                </tbody>
                <tfoot v-if="quotationLines.length > 0">
                  <tr>
                    <td colspan="4" class="text-right"><strong>Total:</strong></td>
                    <td colspan="2">{{ formatCurrency(calculateTotal()) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
  
        <div class="card mt-4">
          <div class="card-header">
            <h3 class="card-title">Purchase Order Details</h3>
          </div>
          <div class="card-body">
            <div class="grid-2">
              <div class="form-group">
                <label for="po_date">PO Date <span class="required">*</span></label>
                <input
                  id="po_date"
                  type="date"
                  v-model="poForm.po_date"
                  class="form-control"
                  :class="{ 'is-invalid': errors.po_date }"
                  required
                />
                <div class="invalid-feedback" v-if="errors.po_date">
                  {{ errors.po_date }}
                </div>
              </div>
  
              <div class="form-group">
                <label for="payment_terms">Payment Terms</label>
                <input
                  id="payment_terms"
                  type="text"
                  v-model="poForm.payment_terms"
                  class="form-control"
                  :class="{ 'is-invalid': errors.payment_terms }"
                  placeholder="e.g., Net 30, COD"
                />
                <div class="invalid-feedback" v-if="errors.payment_terms">
                  {{ errors.payment_terms }}
                </div>
              </div>
  
              <div class="form-group">
                <label for="delivery_terms">Delivery Terms</label>
                <input
                  id="delivery_terms"
                  type="text"
                  v-model="poForm.delivery_terms"
                  class="form-control"
                  :class="{ 'is-invalid': errors.delivery_terms }"
                  placeholder="e.g., FOB, CIF"
                />
                <div class="invalid-feedback" v-if="errors.delivery_terms">
                  {{ errors.delivery_terms }}
                </div>
              </div>
  
              <div class="form-group">
                <label for="expected_delivery">Expected Delivery Date</label>
                <input
                  id="expected_delivery"
                  type="date"
                  v-model="poForm.expected_delivery"
                  class="form-control"
                  :class="{ 'is-invalid': errors.expected_delivery }"
                />
                <div class="invalid-feedback" v-if="errors.expected_delivery">
                  {{ errors.expected_delivery }}
                </div>
              </div>
  
              <div class="form-group">
                <label for="currency_code">Currency</label>
                <select
                  id="currency_code"
                  v-model="poForm.currency_code"
                  class="form-control"
                  :class="{ 'is-invalid': errors.currency_code }"
                >
                  <option value="USD">USD - US Dollar</option>
                  <option value="EUR">EUR - Euro</option>
                  <option value="GBP">GBP - British Pound</option>
                  <option value="JPY">JPY - Japanese Yen</option>
                  <option value="IDR">IDR - Indonesian Rupiah</option>
                  <!-- Add more currencies as needed -->
                </select>
                <div class="invalid-feedback" v-if="errors.currency_code">
                  {{ errors.currency_code }}
                </div>
              </div>
            </div>
          </div>
        </div>
  
        <div class="form-actions mt-4">
          <button
            type="button"
            class="btn btn-secondary"
            @click="cancel"
            :disabled="isSubmitting"
          >
            Cancel
          </button>
          <button
            type="button"
            class="btn btn-primary ml-2"
            @click="createPurchaseOrder"
            :disabled="isSubmitting || !hasSelectedLines"
          >
            {{ isSubmitting ? 'Creating...' : 'Create Purchase Order' }}
          </button>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import PurchaseOrderService from '@/services/PurchaseOrderService';
  
  export default {
    name: 'CreatePOFromQuotation',
    props: {
      id: {
        type: [Number, String],
        required: true
      }
    },
    data() {
      return {
        quotation: null,
        quotationLines: [],
        poForm: {
          po_date: new Date().toISOString().substr(0, 10),
          payment_terms: '',
          delivery_terms: '',
          expected_delivery: '',
          currency_code: 'USD',
          quotation_id: null
        },
        isLoading: true,
        isSubmitting: false,
        errors: {}
      };
    },
    computed: {
      hasSelectedLines() {
        return this.quotationLines.some(line => line.include);
      }
    },
    methods: {
      async fetchQuotation() {
        this.isLoading = true;
        try {
          const response = await PurchaseOrderService.getVendorQuotationById(this.id);
          this.quotation = response.data.data;
          
          if (this.quotation && this.quotation.lines) {
            this.quotationLines = this.quotation.lines.map(line => ({
              ...line,
              include: true, // Default: include all lines
              total: line.unit_price * line.quantity
            }));
            
            // Set quotation ID
            this.poForm.quotation_id = this.quotation.quotation_id;
            
            // Set preferred currency if available
            if (this.quotation.vendor && this.quotation.vendor.preferred_currency) {
              this.poForm.currency_code = this.quotation.vendor.preferred_currency;
            }
          }
        } catch (error) {
          console.error('Error fetching quotation:', error);
          this.quotation = null;
        } finally {
          this.isLoading = false;
        }
      },
      
      calculateLineTotal(line) {
        line.total = line.unit_price * line.quantity;
      },
      
      calculateTotal() {
        return this.quotationLines
          .filter(line => line.include)
          .reduce((total, line) => total + line.total, 0);
      },
      
      formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: '2-digit'
        });
      },
      
      formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
          style: 'currency',
          currency: this.poForm.currency_code || 'USD'
        }).format(amount);
      },
      
      getStatusClass(status) {
        switch (status) {
          case 'draft':
            return 'status-draft';
          case 'sent':
            return 'status-sent';
          case 'accepted':
            return 'status-approved';
          case 'rejected':
            return 'status-canceled';
          default:
            return 'status-draft';
        }
      },
      
      validateForm() {
        this.errors = {};
        let isValid = true;
        
        if (!this.poForm.po_date) {
          this.errors.po_date = 'PO Date is required';
          isValid = false;
        }
        
        if (!this.hasSelectedLines) {
          isValid = false;
        }
        
        return isValid;
      },
      
      async createPurchaseOrder() {
        if (!this.validateForm()) {
          return;
        }
        
        this.isSubmitting = true;
        
        try {
          // Prepare PO data
          const poData = {
            ...this.poForm,
            vendor_id: this.quotation.vendor_id,
            lines: this.quotationLines
              .filter(line => line.include)
              .map(line => ({
                item_id: line.item_id,
                quantity: line.quantity,
                unit_price: line.unit_price,
                uom_id: line.uom_id
              }))
          };
          
          // Create PO
          const response = await PurchaseOrderService.createPurchaseOrder(poData);
          
          if (response.data && response.data.data) {
            // Redirect to PO detail page
            this.$router.push(`/purchasing/orders/${response.data.data.po_id}`);
          } else {
            throw new Error('Invalid response from server');
          }
        } catch (error) {
          console.error('Error creating purchase order:', error);
          
          // Handle validation errors
          if (error.response && error.response.status === 422) {
            this.errors = error.response.data.errors || {};
          } else if (error.response && error.response.data && error.response.data.message) {
            alert(error.response.data.message);
          } else {
            alert('Failed to create purchase order. Please try again.');
          }
          
          this.isSubmitting = false;
        }
      },
      
      cancel() {
        this.$router.push(`/purchasing/rfqs/${this.id}`);
      }
    },
    mounted() {
      this.fetchQuotation();
    }
  };
  </script>
  
  <style scoped>
  .create-po-from-quotation-container {
    padding: 1rem;
  }
  
  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
  }
  
  .header-left {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--gray-600);
    text-decoration: none;
    font-size: 0.875rem;
  }
  
  .back-link:hover {
    color: var(--primary-color);
  }
  
  .header-left h1 {
    margin: 0;
    font-size: 1.5rem;
    color: var(--gray-800);
  }
  
  .loading-container,
  .error-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    text-align: center;
  }
  
  .loading-spinner {
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
  }
  
  .error-icon {
    font-size: 3rem;
    color: var(--danger-color);
    margin-bottom: 1rem;
  }
  
  .card {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
  }
  
  .card-header {
    padding: 1rem 1.5rem;
    background-color: var(--gray-50);
    border-bottom: 1px solid var(--gray-200);
  }
  
  .card-title {
    margin: 0;
    font-size: 1.25rem;
    color: var(--gray-800);
  }
  
  .card-body {
    padding: 1.5rem;
  }
  
  .info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
  }
  
  .info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .info-label {
    font-size: 0.875rem;
    color: var(--gray-500);
  }
  
  .info-value {
    font-size: 1rem;
    color: var(--gray-800);
    font-weight: 500;
  }
  
  .table {
    width: 100%;
    margin-bottom: 1rem;
    color: var(--gray-700);
    border-collapse: collapse;
  }
  
  .table th,
  .table td {
    padding: 0.75rem;
    vertical-align: middle;
    border: 1px solid var(--gray-200);
  }
  
  .table th {
    background-color: var(--gray-50);
    font-weight: 500;
    text-align: left;
  }
  
  .table-responsive {
    overflow-x: auto;
  }
  
  .item-name {
    font-weight: 500;
  }
  
  .item-code {
    font-size: 0.75rem;
    color: var(--gray-500);
  }
  
  .form-control {
    display: block;
    width: 100%;
    padding: 0.5rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: var(--gray-700);
    background-color: #fff;
    border: 1px solid var(--gray-300);
    border-radius: 0.375rem;
    transition: border-color 0.15s ease-in-out;
  }
  
  .form-control:focus {
    border-color: var(--primary-color);
    outline: 0;
  }
  
  .form-control:disabled {
    background-color: var(--gray-100);
    cursor: not-allowed;
  }
  
  .form-control.is-invalid {
    border-color: var(--danger-color);
  }
  
  .invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: var(--danger-color);
  }
  
  .form-check {
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .form-check-input {
    width: 1.25rem;
    height: 1.25rem;
    cursor: pointer;
  }
  
  .grid-2 {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
  }
  
  .form-group {
    margin-bottom: 1rem;
  }
  
  label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--gray-700);
  }
  
  .required {
    color: var(--danger-color);
  }
  
  .form-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 1.5rem;
  }
  
  .btn {
    display: inline-block;
    font-weight: 500;
    text-align: center;
    vertical-align: middle;
    cursor: pointer;
    padding: 0.5rem 1rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: 0.375rem;
    transition: all 0.15s ease-in-out;
    border: none;
  }
  
  .btn-primary {
    background-color: var(--primary-color);
    color: white;
  }
  
  .btn-primary:hover:not(:disabled) {
    background-color: var(--primary-dark);
  }
  
  .btn-secondary {
    background-color: var(--gray-200);
    color: var(--gray-700);
  }
  
  .btn-secondary:hover:not(:disabled) {
    background-color: var(--gray-300);
  }
  
  .btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
  }
  
  .ml-2 {
    margin-left: 0.5rem;
  }
  
  .mt-4 {
    margin-top: 1rem;
  }
  
  .text-right {
    text-align: right;
  }
  
  .text-center {
    text-align: center;
  }
  
  .status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
  }
  
  .status-draft {
    background-color: var(--gray-100);
    color: var(--gray-700);
  }
  
  .status-sent {
    background-color: #dbeafe;
    color: #1e40af;
  }
  
  .status-approved {
    background-color: #dcfce7;
    color: #166534;
  }
  
  .status-canceled {
    background-color: #fee2e2;
    color: #b91c1c;
  }
  
  @media (max-width: 768px) {
    .grid-2 {
      grid-template-columns: 1fr;
    }
  }
  </style>